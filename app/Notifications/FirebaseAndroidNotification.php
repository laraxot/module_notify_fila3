<?php

declare(strict_types=1);

/**
 * @see https://iftikhar-ahmed.medium.com/send-push-notifications-in-laravel-using-firebase-on-your-android-device-f585621db900
 */

namespace Modules\Notify\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Stringable;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Modules\Notify\Contracts\MobilePushNotification;
use Modules\Notify\Datas\FirebaseNotificationData;
use Modules\Notify\Notifications\Channels\FirebaseCloudMessagingChannel;

/**
 * Classe per inviare notifiche tramite Firebase Cloud Messaging ad Android.
 */
class FirebaseAndroidNotification extends Notification implements MobilePushNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param FirebaseNotificationData $data I dati della notifica Firebase
     */
    public function __construct(public FirebaseNotificationData $data)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable The entity to be notified
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [
            // 'firebase',
            FirebaseCloudMessagingChannel::class,
        ];
    }

    /**
     * Crea il messaggio Firebase Cloud per la notifica.
     *
     * @param object $notifiable The entity to be notified
     * @return CloudMessage
     */
    public function toFirebase(object $notifiable): CloudMessage
    {
        // Creiamo una configurazione Android valida
        $androidConfig = [
            'ttl' => '3600s',
            'priority' => 'high',
        ];
        
        // Aggiungiamo la notifica solo se i dati sono in un formato valido
        // Verifichiamo che $this->data->data sia accessibile
        $dataProperty = $this->data->data ?? null;
        if ($dataProperty !== null) {
            // Creiamo un array di notifica conforme alle aspettative di AndroidConfig
            $notification = [];
            
            // Aggiungiamo solo i campi supportati con i tipi corretti
            $allowedKeys = ['title', 'body', 'icon', 'color', 'sound', 'click_action'];
            
            foreach ($allowedKeys as $key) {
                if (isset($dataProperty[$key]) && (is_string($dataProperty[$key]) && $dataProperty[$key] !== '')) {
                    $notification[$key] = $dataProperty[$key];
                }
            }
            
            // Aggiungiamo la notifica solo se contiene dati validi
            if (!empty($notification)) {
                $androidConfig['notification'] = $notification;
            }
        }
        
        return CloudMessage::new()
            ->withNotification(FirebaseNotification::create($this->data->title, $this->data->body))
            ->withAndroidConfig(AndroidConfig::fromArray($androidConfig));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object|null $notifiable The entity to be notified
     * @return array<string, mixed>
     */
    public function toArray(?object $notifiable): array
    {
        // return $this->data->toArray();
        return [];
    }

    /**
     * Converti in un messaggio Cloud Firebase.
     *
     * @return Message
     */
    public function toCloudMessage(): Message
    {
        $notificationData = $this->data->data;
        
        /**
         * @var array<non-empty-string, string|Stringable>
         */
        $data = [];
        
        // Assicuriamoci che ogni chiave sia una stringa non vuota e ogni valore sia string o Stringable
        foreach ($notificationData as $key => $value) {
            if (is_string($key) && $key !== '' && (is_string($value) || $value instanceof Stringable)) {
                $data[$key] = $value;
            }
        }

        return CloudMessage::new()
            ->withHighestPossiblePriority()
            ->withData($data);
    }
}
