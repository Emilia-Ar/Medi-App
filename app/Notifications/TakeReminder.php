<?php

namespace App\Notifications;

use App\Models\Take;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TakeReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $take;

    public function __construct(Take $take)
    {
        $this->take = $take;
    }

    /**
     * Canales de entrega (WebPush + Broadcast)
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class, 'broadcast'];
    }

    /**
     * 🔹 MÉTODO WEBPUSH — SIN CAMBIOS
     */
    public function toWebPush($notifiable, $notification)
    {
        $medication = $this->take->medication;
        $title = "¡Hora de tu medicina!";

        // Mensaje original intacto
        $body = "No olvides tu dosis de {$medication->name}.";

        // URL pública de la foto (si existe)
        $imageUrl = $medication->photo_path 
            ? Storage::url($medication->photo_path) 
            : null;

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon('/images/icons/icon-192x192.png')
            ->image($imageUrl)
            ->data([
                'url' => route('medications.show', $medication),
            ])
            ->requireInteraction(true)
            ->action('No olvides tu dosis', 'open_app')
            ->action('Saltar', 'skip');
    }

    /**
     * 🔹 Nuevo: evento Broadcast para Echo
     */
    public function toBroadcast($notifiable)
    {
        $medication = $this->take->medication;

        return new BroadcastMessage([
            'title' => '¡Recordatorio de Dosis!',
            'body'  => "Es hora de tu toma de {$medication->name}.",
            'url'   => route('medications.show', $medication),
        ]);
    }
}

