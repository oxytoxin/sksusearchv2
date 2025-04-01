<?php

namespace App\Filament\Resources\ActivityDesignResource\Pages;

use App\Actions\ActivityDesign\SubmitActivityDesign;
use App\Filament\Resources\ActivityDesignResource;
use App\Filament\Resources\SubmittedActivityDesignResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityDesign extends ViewRecord
{
    protected static string $resource = ActivityDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->requiresConfirmation()
                ->action(function ($record) {
                    SubmitActivityDesign::execute($record);
                    return redirect(SubmittedActivityDesignResource::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
