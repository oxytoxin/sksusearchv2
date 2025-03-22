<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisbursementVoucherResource\Pages;
use App\Filament\Resources\DisbursementVoucherResource\RelationManagers;
use App\Models\DisbursementVoucher;
use App\Models\DisbursementVoucherStep;
use App\Models\TravelOrderType;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisbursementVoucherResource extends Resource
{
    protected static ?string $model = DisbursementVoucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tracking_number')->searchable(),
                TextColumn::make('voucher_subtype.voucher_type.name')->limit(20)->tooltip(fn($record) => $record->voucher_subtype->voucher_type->name)->label('Voucher Type'),
                TextColumn::make('user.employee_information.full_name')->label('Requisitioner'),
                TextColumn::make('payee')
                    ->limit(10)
                    ->tooltip(fn($record) => $record->payee)
                    ->label('Payee'),
                TextColumn::make('submitted_at')->dateTime('F d, Y'),
                TextColumn::make('disbursement_voucher_particulars_sum_amount')->sum('disbursement_voucher_particulars', 'amount')->label('Amount')->money('php'),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::id())->where('for_cancellation', false))
            ->defaultSort('submitted_at', 'desc')
            ->recordUrl(fn($record) => route('filament.app.resources.disbursement-vouchers.print', ['disbursement_voucher' => $record]), true)
            ->filters([
                //
            ])
            ->actions([
                Action::make('Receive')->button()->action(function (DisbursementVoucher $record) {
                    if ($record->current_step->process == 'Forwarded to') {
                        DB::beginTransaction();
                        $record->update([
                            'current_step_id' => $record->current_step->next_step->id,
                        ]);
                        $record->refresh();
                        $record->activity_logs()->create([
                            'description' => $record->current_step->process . ' ' . Auth::user()->employee_information->full_name,
                        ]);
                        DB::commit();
                        Notification::make()->title('Document Received')->success()->send();
                    }
                })
                    ->visible(function ($record) {
                        if (!$record) {
                            Notification::make()->title('Selected document not found in office.')->warning()->send();
                            return false;
                        }
                        return $record->current_step_id == 1000;
                    })
                    ->requiresConfirmation(),
                Action::make('Forward')->button()->action(function ($record, $data) {
                    DB::beginTransaction();
                    if ($record->current_step_id >= ($record->previous_step_id ?? 0)) {
                        $record->update([
                            'current_step_id' => $record->current_step->next_step->id,
                        ]);
                    } else {
                        $record->update([
                            'current_step_id' => $record->previous_step_id,
                        ]);
                    }
                    $record->refresh();
                    $record->activity_logs()->create([
                        'description' => $record->current_step->process . ' ' . $record->current_step->recipient . ' by ' . Auth::user()->employee_information->full_name,
                        'remarks' => $data['remarks'] ?? null,
                    ]);
                    DB::commit();
                    Notification::make()->title('Document Forwarded')->success()->send();
                })
                    ->form(function () {
                        return [
                            RichEditor::make('remarks')
                                ->label('Remarks (Optional)')
                                ->fileAttachmentsDisk('remarks'),
                        ];
                    })
                    ->modalWidth('4xl')
                    ->visible(function ($record) {
                        if (!$record) {
                            Notification::make()->title('Selected document not found in office.')->warning()->send();
                            return false;
                        }
                        return $record->current_step_id == 2000;
                    })
                    ->requiresConfirmation(),
                Action::make('Cancel')->action(function ($record) {
                    DB::beginTransaction();
                    $record->update([
                        'for_cancellation' => true,
                    ]);
                    $record->refresh();
                    $record->activity_logs()->create([
                        'description' => 'Cancellation requested by ' . Auth::user()->employee_information->full_name,
                    ]);
                    if ($record->current_step_id < 5000 && $record->previous_step_id < 5000) {
                        $record->update([
                            'cancelled_at' => now(),
                        ]);
                        $record->activity_logs()->create([
                            'description' => 'Cancellation approved.',
                        ]);
                        DB::commit();
                        Notification::make()->title('Disbursement voucher cancelled.')->success()->send();
                        return;
                    }
                    DB::commit();
                    Notification::make()->title('Disbursement voucher requested for cancellation.')->success()->send();
                })
                    ->visible(fn($record) => !$record->cheque_number && !$record->cancelled_at && !$record->for_cancellation)
                    ->requiresConfirmation()
                    ->button()
                    ->color('danger'),
                ActionGroup::make([
                    ViewAction::make('progress')
                        ->label('Progress')
                        ->icon('ri-loader-4-fill')
                        ->modalHeading('Disbursement Voucher Progress')
                        ->modalContent(fn($record) => view('components.timeline_views.progress_logs', [
                            'record' => $record,
                            'steps' => DisbursementVoucherStep::whereEnabled(true)->where('id', '>', 2000)->get(),
                        ])),
                    ViewAction::make('logs')
                        ->label('Activity Timeline')
                        ->icon('ri-list-check-2')
                        ->modalHeading('Disbursement Voucher Activity Timeline')
                        ->modalContent(fn($record) => view('components.timeline_views.activity_logs', [
                            'record' => $record,
                        ])),
                    ViewAction::make('related_documents')
                        ->label('Related Documents')
                        ->icon('ri-file-copy-2-line')
                        ->modalHeading('Disbursement Voucher Related Documents')
                        ->modalContent(fn($record) => view('components.disbursement_vouchers.disbursement_voucher_documents', [
                            'disbursement_voucher' => $record,
                        ])),
                    ViewAction::make('ctc')
                        ->label('Certificate of Travel Completion')
                        ->icon('ri-file-text-line')
                        // ->url(fn($record) => route('ctc.show', ['ctc' => $record->travel_completed_certificate]), true)
                        ->visible(fn($record) => $record->travel_completed_certificate()->exists()),
                    ViewAction::make('actual_itinerary')
                        ->label('Actual Itinerary')
                        ->icon('ri-file-copy-line')
                        // ->url(fn($record) => route('signatory.itinerary.print', ['itinerary' => $record->travel_order->itineraries()->where('user_id', $record->user_id)->whereIsActual(true)->first()]), true)
                        ->visible(fn($record) => $record->travel_order?->travel_order_type_id == TravelOrderType::OFFICIAL_BUSINESS && $record->travel_order?->itineraries()->where('user_id', $record->user_id)->whereIsActual(true)->exists()),
                    ViewAction::make('view')
                        ->label('Preview')
                        ->openUrlInNewTab()
                        ->url(fn($record) => route('filament.app.resources.disbursement-vouchers.print', ['disbursement_voucher' => $record]), true),
                ])->icon('ri-eye-line'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisbursementVouchers::route('/'),
            'print' => Pages\PrintDisbursementVoucher::route('/{disbursement_voucher}/print'),
        ];
    }
}
