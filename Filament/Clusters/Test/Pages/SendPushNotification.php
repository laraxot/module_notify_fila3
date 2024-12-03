<?php

declare(strict_types=1);

namespace Modules\Notify\Filament\Clusters\Test\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;
use Kreait\Firebase\Messaging\CloudMessage;
use Modules\Notify\Filament\Clusters\Test;
use Modules\User\Models\DeviceUser;
use Modules\Xot\Filament\Traits\NavigationLabelTrait;
use Webmozart\Assert\Assert;

use function Safe\json_encode;

/**
 * @property ComponentContainer $notificationForm
 */
class SendPushNotification extends Page implements HasForms
{
    use InteractsWithForms;

    // use NavigationLabelTrait;

    public ?array $notificationData = [];

    // protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string $view = 'notify::filament.pages.send-push-notification';

    protected static ?string $cluster = Test::class;

    public function mount(): void
    {
        $this->fillForms();
    }

    public function notificationForm(Form $form): Form
    {
        $devices = DeviceUser::with(['profile', 'device'])
            ->where('push_notifications_token', '!=', null)
            ->where('push_notifications_token', '!=', 'unknown')
            ->where('push_notifications_enabled', 1)
            // ->whereHas('profile') //db separato percio' da errore cosi'
            ->whereHas('device')
            ->get();

        /**
         * ---.
         */
        $callback = fn ($item) => [$item->push_notifications_token => $item->profile->full_name . ' (' . $item->device?->robot . ') ' . mb_substr($item->push_notifications_token, -5)];

        /**
         * ---.
         */
        $filterCallback = fn ($item): bool => $item->profile !== null;

        $to = $devices
            ->filter($filterCallback)
            ->mapWithKeys($callback)
            ->toArray();

        Assert::isArray($to);

        return $form
            ->schema(
                [
                    Forms\Components\Select::make('deviceToken')
                        ->label('profile.full_name (device.robot) ultimi 5 cararatteri push_notifications_token')
                        ->options($to),

                    Forms\Components\TextInput::make('type')
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->required(),
                    Forms\Components\TextInput::make('body')
                        ->required(),
                    Forms\Components\Repeater::make('data')
                        ->schema([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('value')->required(),
                        ]),
                ]
            )
            // ->model($this->getUser())
            ->statePath('notificationData');
    }

    public function sendNotification(): void
    {
        $data = $this->notificationForm->getState();

        $messaging = app('firebase.messaging');
        Assert::stringNotEmpty($deviceToken = $data['deviceToken']);

        /**
         * @var array<non-empty-string, string|Stringable>|\Kreait\Firebase\Messaging\MessageData
         */
        $push_data = [
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => json_encode($data['data']),
        ];

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withHighestPossiblePriority()
            ->withData($push_data);

        $messaging->send($message);

        Notification::make()
            ->success()
            // ->title(__('filament-panels::pages/auth/edit-profile.notifications.saved.title'))
            ->title(__('check your client'))
            ->send();
    }

    protected function getForms(): array
    {
        return [
            'notificationForm',
        ];
    }

    protected function getNotificationFormActions(): array
    {
        return [
            Action::make('notificationFormActions')
                // ->label(__('filament-panels::pages/auth/edit-profile.form.actions.save.label'))
                ->label(__('send'))
                ->submit('notificationFormActions'),
        ];
    }

    protected function getUser(): Authenticatable&Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        return $user;
    }

    protected function fillForms(): void
    {
        // $data = $this->getUser()->attributesToArray();

        // $this->editProfileForm->fill($data);
        $this->notificationForm->fill();
    }
}
