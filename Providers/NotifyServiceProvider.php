<?php

declare(strict_types=1);

namespace Modules\Notify\Providers;

// use Illuminate\Support\Facades\Notification;
use Modules\Xot\Providers\XotBaseServiceProvider;

class NotifyServiceProvider extends XotBaseServiceProvider
{
    public string $module_name = 'notify';

    protected string $module_dir = __DIR__;

    protected string $module_ns = __NAMESPACE__;

    public function boot(): void
    {
        parent::boot();
        // BladeService::registerComponents($this->module_dir.'/../View/Components', 'Modules\\Media');
        // Notification::extend('esendex', function ($app) {
        //    return new \Modules\Notify\Notifications\Channels\EsendexChannel;
        // });
    }
}
