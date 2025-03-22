<?php

namespace App\Filament\Resources\DisbursementVoucherResource\Pages;

use App\Filament\Resources\DisbursementVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisbursementVouchers extends ListRecords
{
    protected static string $resource = DisbursementVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
