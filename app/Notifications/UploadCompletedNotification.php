<?php

namespace App\Notifications;

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UploadCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $upload;
    protected $recordCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(Upload $upload, int $recordCount)
    {
        $this->upload = $upload;
        $this->recordCount = $recordCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📤 Bestand succesvol geüpload – EcoCheck')
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line("Uw bestand '{$this->upload->filename}' is succesvol verwerkt.")
            ->line("**Verwerkte records:** {$this->recordCount}")
            ->line("**Upload datum:** " . $this->upload->created_at->format('d-m-Y H:i'))
            ->action('Bekijk Dashboard', route('dashboard'))
            ->line('U kunt nu uw data analyseren op het dashboard.')
            ->line('Bedankt voor het gebruik van EcoCheck!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'upload_id' => $this->upload->bestand_id,
            'filename' => $this->upload->filename,
            'record_count' => $this->recordCount,
        ];
    }
}
