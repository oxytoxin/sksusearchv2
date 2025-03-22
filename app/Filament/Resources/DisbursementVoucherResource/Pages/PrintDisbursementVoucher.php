<?php

namespace App\Filament\Resources\DisbursementVoucherResource\Pages;

use App\Filament\Resources\DisbursementVoucherResource;
use App\Models\DisbursementVoucher;
use Filament\Resources\Pages\Page;

class PrintDisbursementVoucher extends Page
{
    protected static string $resource = DisbursementVoucherResource::class;

    public DisbursementVoucher $disbursement_voucher;

    protected static string $view = 'filament.resources.disbursement-voucher-resource.pages.print-disbursement-voucher';
}
