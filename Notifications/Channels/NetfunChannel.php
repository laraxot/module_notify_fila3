<?php

declare(strict_types=1);

namespace Modules\Notify\Notifications\Channels;

use Modules\Notify\Actions\NetfunSendAction;
use Modules\Notify\Contracts\CanThemeNotificationContract;
use Modules\Notify\Notifications\ThemeNotification;

// use Modules\Xot\Contracts\ModelContactContract;

class NetfunChannel
{
    /**
     * Send the given notification.
     */
    public function send(CanThemeNotificationContract $notifiable, ThemeNotification $themeNotification): void
    {
        $smsData = $themeNotification->toSms($notifiable);
        // Send notification to the $notifiable instance...
        $data = app(NetfunSendAction::class)->execute($smsData);
        $notifiable->increase('sms', $data);
        /*
        $data['sms_sent_at'] = now();
        $data['sms_count'] = (int) $notifiable->sms_count + 1;
        $notifiable->update($data);
        */
    }
}
