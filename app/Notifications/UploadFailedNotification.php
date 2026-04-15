<?php

namespace App\Notifications;

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UploadFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $upload;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Upload $upload, string $errorMessage = '')
    {
        $this->upload = $upload;
        $this->errorMessage = $errorMessage;
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
        $message = (new MailMessage)
            ->subject('⚠️ Upload mislukt – EcoCheck')
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line("Het upload van bestand '{$this->upload->filename}' is helaas mislukt.");

        if ($this->errorMessage) {
            $message->line("**Foutmelding:** " . $this->errorMessage);
        }

        $message
            ->line("**Upload datum:** " . $this->upload->created_at->format('d-m-Y H:i'))
            ->action('Probeer opnieuw', route('dashboard'))
            ->line('Controleer het bestandsformaat en probeer het opnieuw.')
            ->line('Ondersteunde formaten: .xlsx, .xls')
            ->line('Maximale bestandsgrootte: 10 MB');

        return $message;
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
            'error' => $this->errorMessage,
        ];
    }
}
