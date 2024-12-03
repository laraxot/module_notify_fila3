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
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Modules\Notify\Contracts\MobilePushNotification;
use Modules\Notify\Datas\FirebaseNotificationData;
use Modules\Notify\Notifications\Channels\FirebaseCloudMessagingChannel;

class FirebaseAndroidNotification extends Notification implements MobilePushNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public FirebaseNotificationData $data)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable the entity to be notified
     *
     * @throws \InvalidArgumentException if no delivery channels are available for the notifiable entity
     */
    public function via(mixed $notifiable): array
    {
        return [
            // 'firebase',
            FirebaseCloudMessagingChannel::class,
        ];
    }

    /**
     * A description of the entire PHP function.
     *
     * @param mixed $notifiable the entity to be notified
     */
    public function toFirebase(mixed $notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withNotification(FirebaseNotification::create($this->data->title, $this->data->body))
            ->withAndroidConfig(AndroidConfig::fromArray([
                'ttl' => '3600s',
                'priority' => 'high',
                'notification' => $this->data->data,
                // 'notification' => [
                //    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                // ],
            ]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(?object $notifiable): array
    {
        // return $this->data->toArray();
        return [];
    }

    public function toCloudMessage(): Message
    {
        /**
         * var array<non-empty-string, string|Stringable>|\Kreait\Firebase\Messaging\MessageData.
         */
        $data = $this->data->data;

        return CloudMessage::new()
            ->withHighestPossiblePriority()
            ->withData($data);
    }
}
