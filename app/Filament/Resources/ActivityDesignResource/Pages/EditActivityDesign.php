<?php

namespace App\Filament\Resources\ActivityDesignResource\Pages;

use App\Filament\Resources\ActivityDesignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivityDesign extends EditRecord
{
    protected static string $resource = ActivityDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
