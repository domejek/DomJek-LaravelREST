<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aufgaben-Deadline überschritten')
            ->line('Ihre Aufgabe "'.$this->task->title.'" hat die Deadline überschritten.')
            ->action('Aufgabe anzeigen', url('/api/tasks/'.$this->task->id))
            ->line('Bitte aktualisieren Sie die Aufgabe oder kontaktieren Sie Ihren Administrator.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => 'Ihre Aufgabe hat die Deadline überschritten.',
            'deadline' => $this->task->deadline,
        ];
    }
}
