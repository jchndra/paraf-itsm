<?php

namespace App\Notifications;

use App\Models\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RfcStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ChangeRequest $rfc,
        public string $previousStatus,
        public string $newStatus,
        public ?string $note = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[PARAF] Pembaruan Status RFC: {$this->rfc->rfc_number}")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Status untuk permohonan perubahan **{$this->rfc->rfc_number}** ({$this->rfc->title}) telah diperbarui dari **{$this->previousStatus}** menjadi **{$this->newStatus}**.");

        if ($this->note) {
            $mail->line("**Catatan Tambahan:** {$this->note}");
        }

        return $mail->action('Lihat Detail RFC', url("/change-requests/{$this->rfc->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'rfc_number' => $this->rfc->rfc_number,
            'title' => $this->rfc->title,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'note' => $this->note,
        ];
    }
}
    