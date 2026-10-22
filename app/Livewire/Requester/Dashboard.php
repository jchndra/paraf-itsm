<?php

namespace App\Livewire\Requester;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Dashboard extends Component
{
    public string $statusMessage = '';

    #[On('rfc-deleted')]
    public function handleRfcDeleted(string $message = ''): void
    {
        $this->statusMessage = $message !== ''
            ? $message
            : 'RFC berhasil dihapus dan dipindahkan ke Riwayat Penghapusan.';
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        // Keamanan: Hanya ambil RFC yang dibuat oleh user ini
        $baseQuery = ChangeRequest::where('submitted_by_id', $user->id);

        // Menghitung statistik berdasarkan grup status yang sama dengan label tabel.
        $stats = [
            'draft' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('draft'))->count(),
            'in_process' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('in_process'))->count(),
            'needs_revision' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('needs_revision'))->count(),
            'approved' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('approved'))->count(),
            'rejected' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('rejected'))->count(),
        ];

        // 5 RFC terbaru untuk tabel
        $recentRfcs = (clone $baseQuery)
            ->with('application')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // RFC terakhir yang sedang aktif untuk timeline status
        $latestActiveRfc = (clone $baseQuery)
            ->whereNotIn('status', [RfcStatus::DRAFT->value, RfcStatus::CANCELLED->value])
            ->latest('updated_at')
            ->first();

        $timeline = $latestActiveRfc ? $this->buildTimeline($latestActiveRfc) : [];

        // Notifikasi terbaru (sudah dibaca maupun belum)
        $notifications = $user->notifications()->take(4)->get();

        return view('livewire.requester.dashboard', [
            'stats' => $stats,
            'recentRfcs' => $recentRfcs,
            'latestActiveRfc' => $latestActiveRfc,
            'timeline' => $timeline,
            'notifications' => $notifications,
        ])->title('Dashboard Pemohon');
    }

    /**
     * Susun langkah perjalanan RFC beserta waktu kejadiannya.
     *
     * @return array<int, array{key: string, label: string, description: string, state: string, time: ?Carbon}>
     */
    private function buildTimeline(ChangeRequest $rfc): array
    {
        $status = RfcStatus::fromValue($rfc->status) ?? RfcStatus::DRAFT;

        // Waktu setiap perubahan status diambil dari activity log.
        // Nilai lama (complete/assessed) tetap dibiarkan apa adanya.
        $timestamps = Activity::query()
            ->where('subject_type', ChangeRequest::class)
            ->where('subject_id', $rfc->id)
            ->whereNotNull('properties->new_status')
            ->orderBy('created_at')
            ->get()
            ->mapWithKeys(fn (Activity $activity) => [
                data_get($activity->properties, 'new_status') => $activity->created_at,
            ]);

        $rfc->loadMissing('riskAssessment');
        $decision = $rfc->decisions()->latest('decision_date')->first();
        $hasRisk = $rfc->riskAssessment !== null;

        $timeFor = function (array $keys) use ($timestamps): ?Carbon {
            foreach ($keys as $key) {
                $time = $timestamps->get($key);

                if ($time) {
                    return $time;
                }
            }

            return null;
        };

        $decisionTime = $decision?->decision_date
            ? Carbon::parse($decision->decision_date)
            : null;

        $steps = [
            [
                'key' => 'created',
                'label' => 'Dibuat',
                'description' => 'RFC berhasil dibuat oleh Anda',
                'time' => $rfc->created_at,
            ],
            [
                'key' => 'reviewed',
                'label' => 'Diperiksa Admin',
                'description' => match ($status) {
                    RfcStatus::NEEDS_REVISION => 'Dikembalikan untuk perbaikan',
                    RfcStatus::DRAFT, RfcStatus::SUBMITTED => 'Menunggu pemeriksaan kelengkapan',
                    RfcStatus::RETURNED_TO_ADMIN => 'Menunggu tindak lanjut Admin',
                    default => 'Kelengkapan dokumen telah diverifikasi',
                },
                'time' => $timeFor([
                    RfcStatus::TICKET_CREATED->value,
                    RfcStatus::LEGACY_COMPLETE,
                    RfcStatus::NEEDS_REVISION->value,
                ]),
            ],
            [
                'key' => 'assessed',
                'label' => 'Analisis Change Manager',
                'description' => $hasRisk
                    ? 'Penilaian risiko dan dampak selesai'
                    : 'Menunggu analisis risiko',
                'time' => $rfc->riskAssessment?->created_at ?? $timestamps->get(RfcStatus::LEGACY_ASSESSED),
            ],
            [
                'key' => 'decided',
                'label' => 'Persetujuan',
                'description' => match ($status) {
                    RfcStatus::REJECTED => 'RFC ditolak oleh Change Manager',
                    RfcStatus::APPROVED,
                    RfcStatus::IN_PROGRESS,
                    RfcStatus::IMPLEMENTED,
                    RfcStatus::FAILED,
                    RfcStatus::CLOSED => 'RFC disetujui untuk diimplementasikan',
                    RfcStatus::RETURNED_TO_ADMIN => 'Dikembalikan ke Admin untuk ditinjau',
                    default => 'Menunggu keputusan Change Manager',
                },
                'time' => $timeFor([
                    RfcStatus::APPROVED->value,
                    RfcStatus::REJECTED->value,
                ]) ?? $decisionTime,
            ],
            [
                'key' => 'implemented',
                'label' => 'Implementasi',
                'description' => match ($status) {
                    RfcStatus::IN_PROGRESS => 'Sedang dalam proses rilis',
                    RfcStatus::IMPLEMENTED, RfcStatus::CLOSED => 'Perubahan telah diimplementasikan',
                    RfcStatus::FAILED => 'Implementasi gagal dilaksanakan',
                    default => 'Menunggu jadwal implementasi',
                },
                'time' => $timeFor([
                    RfcStatus::IMPLEMENTED->value,
                    RfcStatus::IN_PROGRESS->value,
                    RfcStatus::LEGACY_SUCCESSFUL,
                ]),
            ],
            [
                'key' => 'closed',
                'label' => 'Selesai',
                'description' => match ($status) {
                    RfcStatus::CLOSED => 'RFC telah ditutup',
                    RfcStatus::IMPLEMENTED, RfcStatus::FAILED => 'Menunggu penutupan RFC',
                    default => 'Menunggu penutupan RFC',
                },
                'time' => $rfc->closed_at ?? $timestamps->get(RfcStatus::CLOSED->value),
            ],
        ];

        $states = match ($status) {
            RfcStatus::DRAFT => ['created' => 'current'],
            RfcStatus::SUBMITTED => ['created' => 'done', 'reviewed' => 'current'],
            RfcStatus::NEEDS_REVISION => ['created' => 'done', 'reviewed' => 'warning'],
            RfcStatus::TICKET_CREATED, RfcStatus::RETURNED_TO_ADMIN => $hasRisk
                ? ['created' => 'done', 'reviewed' => 'done', 'assessed' => 'done', 'decided' => 'current']
                : ['created' => 'done', 'reviewed' => 'done', 'assessed' => 'current'],
            RfcStatus::APPROVED, RfcStatus::IN_PROGRESS => [
                'created' => 'done', 'reviewed' => 'done', 'assessed' => 'done',
                'decided' => 'done', 'implemented' => 'current',
            ],
            RfcStatus::IMPLEMENTED => [
                'created' => 'done', 'reviewed' => 'done', 'assessed' => 'done',
                'decided' => 'done', 'implemented' => 'done', 'closed' => 'current',
            ],
            RfcStatus::FAILED => [
                'created' => 'done', 'reviewed' => 'done', 'assessed' => 'done',
                'decided' => 'done', 'implemented' => 'danger', 'closed' => 'current',
            ],
            RfcStatus::CLOSED => [
                'created' => 'done', 'reviewed' => 'done', 'assessed' => 'done',
                'decided' => 'done', 'implemented' => 'done', 'closed' => 'done',
            ],
            RfcStatus::REJECTED => [
                'created' => 'done', 'reviewed' => 'done', 'assessed' => 'done', 'decided' => 'failed',
            ],
            RfcStatus::CANCELLED => ['created' => 'warning'],
        };

        return array_map(
            fn (array $step) => $step + ['state' => $states[$step['key']] ?? 'pending'],
            $steps
        );
    }
}
