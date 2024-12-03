<?php

declare(strict_types=1);

namespace Modules\Notify\Filament\Resources\NotifyThemeResource\Pages;

use Modules\UI\Enums\TableLayoutEnum;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Xot\Filament\Pages\XotBaseListRecords;
use Modules\Notify\Filament\Resources\NotifyThemeResource;
use Modules\UI\Filament\Actions\Table\TableLayoutToggleTableAction;

class ListNotifyThemes extends XotBaseListRecords
{
    
    protected static string $resource = NotifyThemeResource::class;

    protected function getTableHeaderActions(): array
    {
        return [
            TableLayoutToggleTableAction::make(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
