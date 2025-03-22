<?php

namespace App\Filament\Resources\DisbursementVoucherResource\Pages;

use App\Filament\Resources\DisbursementVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDisbursementVoucher extends EditRecord
{
    protected static string $resource = DisbursementVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
