<?php
/**
 * --.
 */
declare(strict_types=1);

namespace Modules\Notify\Models\Panels\Actions;

// -------- services --------
<<<<<<< HEAD
<<<<<<< HEAD
=======
//-------- services --------
>>>>>>> 42aa20e (.)
=======
>>>>>>> 9f492fe (up)
=======
>>>>>>> fe06862 (.)

use Illuminate\Support\Facades\Notification;
use Modules\Cms\Actions\GetViewAction;
use Modules\Cms\Models\Panels\Actions\XotBasePanelAction;
use Modules\Notify\Datas\NotificationData;
use Modules\Notify\Notifications\SampleNotification;

// -------- bases -----------
<<<<<<< HEAD
<<<<<<< HEAD
=======
//-------- bases -----------
>>>>>>> 42aa20e (.)
=======
>>>>>>> 9f492fe (up)
=======
>>>>>>> fe06862 (.)

/**
 * Class TestAction.
 */
<<<<<<< HEAD
<<<<<<< HEAD:Models/Panels/Actions/TestSmsAction.php
class TestSmsAction extends XotBasePanelAction {
<<<<<<< HEAD
=======
class TestSmsAction extends XotBasePanelAction
{
>>>>>>> 42aa20e (.)
=======
>>>>>>> 9f492fe (up)
=======
class TrySmsAction extends XotBasePanelAction {
>>>>>>> 64529a0 (up):Models/Panels/Actions/TrySmsAction.php
=======
class TrySmsAction extends XotBasePanelAction {
>>>>>>> fe06862 (.)
    public bool $onItem = true;
    public string $icon = '<i class="fas fa-vial"></i>SMS';

    /**
     * @return mixed
     */
    public function handle() {
<<<<<<< HEAD
<<<<<<< HEAD
=======
    public function handle()
    {
>>>>>>> 42aa20e (.)
=======
>>>>>>> 9f492fe (up)
=======
>>>>>>> fe06862 (.)
        $drivers = [
            'netfun' => 'netfun',
            'esendex' => 'esendex',
        ];
        $i = request('i');

        $driver = isset($drivers[$i]) ? $drivers[$i] : null;
        $view = app(GetViewAction::class)->execute();

        $view_params = [
            'view' => $view,
            'drivers' => $drivers,
            'driver' => $driver,
        ];

        return view($view, $view_params);
    }

    /**
     * @return mixed
     */
    public function postHandle() {
        $data = request()->all();
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        $vars = collect($data)->only(['driver', 'from', 'to', 'body'])->all();
        SmsService::make()->setLocalVars($vars)->send();
<<<<<<< HEAD
=======
        
        return view()->make($view, $view_params);
        
    }

    public function postHandle(){
       $data=request()->all();
       $vars=collect($data)->only(['driver','from','to','body'])->all();
       SmsService::make()->setLocalVars($vars)->send();
>>>>>>> 42aa20e (.)
=======
>>>>>>> 9f492fe (up)
=======
        
        //$hows=NotificationData::collection([$data]);
=======
>>>>>>> ace9eb3 (up)
=======
        $data['channels']=[$data['driver']];
>>>>>>> f0164c0 (up)
        $hows=NotificationData::from($data);
        Notification::send([$hows], new SampleNotification($data));
<<<<<<< HEAD
        //Call to a member function routeNotificationFor() on string
        dddx('fine');
>>>>>>> 0cbdb01 (up)
=======
        echo '<h3>+Done</h3>';
>>>>>>> ace9eb3 (up)
=======
        $data['channels']=[$data['driver']];
        $hows=NotificationData::from($data);
=======
        $data['channels'] = [$data['driver']];
        $hows = NotificationData::from($data);
>>>>>>> 5cbe3de (up)
        Notification::send([$hows], new SampleNotification($data));
        echo '<h3>+Done</h3>';
>>>>>>> fe06862 (.)
    }
}