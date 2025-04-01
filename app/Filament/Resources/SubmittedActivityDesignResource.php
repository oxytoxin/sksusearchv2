<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmittedActivityDesignResource\Pages;
use App\Filament\Resources\SubmittedActivityDesignResource\RelationManagers;
use App\Filament\Traits\ActivityDesign\ActivityDesignCommons;
use App\Models\ActivityDesign;
use App\Models\SubmittedActivityDesign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubmittedActivityDesignResource extends Resource
{
    use ActivityDesignCommons;

    protected static ?string $model = ActivityDesign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel = 'Submitted';

    protected static ?string $navigationGroup = 'Activity Designs';

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
        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmittedActivityDesigns::route('/'),
            'view' => Pages\ViewSubmittedActivityDesign::route('/{record}')
        ];
    }
}
