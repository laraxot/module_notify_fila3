<?php

declare(strict_types=1);

namespace Modules\Notify\Filament\Clusters\Test\Pages;

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
use Illuminate\Support\Collection;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

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
         * Callback per mappare i dispositivi in opzioni per il select.
         */
        $callback = function ($item) {
            // Verifichiamo che $item sia un oggetto
            if (!is_object($item)) {
                return [];
            }
            
            // Verifichiamo che $item abbia le proprietà necessarie
            if (!property_exists($item, 'push_notifications_token') || 
                !property_exists($item, 'profile') || 
                !is_object($item->profile) || 
                !property_exists($item->profile, 'full_name')) {
                return [];
            }
            
            // Otteniamo il token
            $token = $item->push_notifications_token;
            if (!is_string($token) || $token === '') {
                return [];
            }
            
            // Otteniamo il nome completo
            $fullName = $item->profile->full_name;
            if (!is_string($fullName)) {
                $fullName = 'Utente';
            }
            
            // Otteniamo il robot
            $robot = '';
            if (property_exists($item, 'device') && 
                is_object($item->device) && 
                property_exists($item->device, 'robot') && 
                is_string($item->device->robot)) {
                $robot = $item->device->robot;
            }
            
            // Creiamo la label con gli ultimi 5 caratteri del token
            $tokenSuffix = mb_substr($token, -5);
            
            return [$token => $fullName.' ('.$robot.') '.$tokenSuffix];
        };

        /**
         * Callback per filtrare i dispositivi.
         */
        $filterCallback = function ($item): bool {
            return is_object($item) && 
                   property_exists($item, 'profile') && 
                   $item->profile !== null;
        };

        $to = $devices
            ->filter($filterCallback)
            ->mapWithKeys($callback)
            ->toArray();

        Assert::isArray($to);

        return $form
            ->schema(
                [
                    Forms\Components\Select::make('deviceToken')
                        ->options(function () use ($to): array {
                            return $to;
                        }),
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
        $deviceToken = $data['deviceToken'] ?? '';

        // Verifichiamo che deviceToken sia una stringa non vuota
        if ($deviceToken === '') {
            Notification::make()
                ->danger()
                ->title('Errore')
                ->body('Token del dispositivo non valido')
                ->send();
            return;
        }

        // Verifichiamo che i dati siano del tipo corretto
        $type = $data['type'] ?? '';
        $title = $data['title'] ?? '';
        $body = $data['body'] ?? '';
        $jsonData = isset($data['data']) ? json_encode($data['data']) : '{}';
        
        // Verifichiamo che jsonData sia una stringa
        $jsonData = $jsonData ?: '{}';
        
        // Creiamo un array con chiavi non vuote e valori stringa che implementano Stringable
        /** @var array<non-empty-string, \Stringable|string> $pushData */
        $pushData = [];
        
        // Aggiungiamo i valori all'array solo se non sono vuoti
        if ($type !== '') {
            $pushData['type'] = $type;
        }
        if ($title !== '') {
            $pushData['title'] = $title;
        }
        if ($body !== '') {
            $pushData['body'] = $body;
        }
        if ($jsonData !== '') {
            $pushData['data'] = $jsonData;
        }

        // Verifichiamo che deviceToken sia una stringa non vuota (per soddisfare il tipo non-empty-string)
        Assert::stringNotEmpty($deviceToken, 'Il token del dispositivo non può essere vuoto');
        
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withHighestPossiblePriority()
            ->withData($pushData);
            
        try {
            // Otteniamo l'istanza di messaging e verifichiamo che sia valida
            $messaging = app('firebase.messaging');
            if (!is_object($messaging) || !method_exists($messaging, 'send')) {
                throw new \RuntimeException('Il servizio firebase.messaging non supporta il metodo send()');
            }
            
            $messaging->send($message);
        } catch (\Exception $e) {
            dddx([
                'message' => $e->getMessage(),
                'deviceToken' => $deviceToken,
            ]);
        }

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
                //

                ->submit('notificationFormActions'),
        ];
    }

    protected function getUser(): Authenticatable&Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new \Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
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
