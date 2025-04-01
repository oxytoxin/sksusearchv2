<?php

namespace App\Filament\Resources;

use App\Enums\ActivityDesignSignatoryGroupStatus;
use App\Enums\ActivityDesignStatus;
use App\Filament\Resources\SignatoryActivityDesignResource\Pages;
use App\Filament\Resources\SignatoryActivityDesignResource\RelationManagers;
use App\Filament\Traits\ActivityDesign\ActivityDesignCommons;
use App\Models\ActivityDesign;
use App\Models\SignatoryActivityDesign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SignatoryActivityDesignResource extends Resource
{
    use ActivityDesignCommons;

    protected static ?string $model = ActivityDesign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel = 'For Signature';

    protected static ?string $navigationGroup = 'Activity Designs';

    protected static ?int $navigationSort = 30;


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query
                    ->whereRelation('signatories', 'signatory_id', Auth::id())
                    ->where('status', ActivityDesignStatus::IN_APPROVAL);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getBulkActions()
    {
        return [];
    }


    public static function getHeaderActions()
    {
        return [];
    }


    public static function getTableActions()
    {
        return [
            Action::make('approve')
                ->button()
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->signatories()->where('signatory_id', Auth::id())->whereRelation('signatory_group', 'status', ActivityDesignSignatoryGroupStatus::IN_APPROVAL)->get()->each->update(['is_approved' => true]);
                })
                ->visible(fn($record) => $record->signatories()->where('signatory_id', Auth::id())->whereRelation('signatory_group', 'status', ActivityDesignSignatoryGroupStatus::IN_APPROVAL)->exists())
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignatoryActivityDesigns::route('/'),
            'view' => Pages\ViewSignatoryActivityDesign::route('/{record}'),
        ];
    }
}
