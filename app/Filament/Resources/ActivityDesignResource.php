<?php

namespace App\Filament\Resources;

use App\Actions\ActivityDesign\SubmitActivityDesign;
use App\Enums\ActivityDesignSignatoryGroupStatus;
use App\Enums\ActivityDesignStatus;
use App\Filament\Resources\ActivityDesignResource\Pages;
use App\Filament\Resources\ActivityDesignResource\RelationManagers;
use App\Filament\Traits\ActivityDesign\ActivityDesignCommons;
use App\Models\ActivityDesign;
use App\Models\Designation;
use App\Models\EmployeeInformation;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\Tabs as InfolistTabs;
use Filament\Infolists\Components\Tabs\Tab as InfolistTab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActivityDesignResource extends Resource
{
    use ActivityDesignCommons;

    protected static ?string $model = ActivityDesign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel = 'Drafts';

    protected static ?string $navigationGroup = 'Activity Designs';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query
                    ->where('requisitioner_id', Auth::id())
                    ->where('status', ActivityDesignStatus::DRAFT);
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
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function getTableActions()
    {
        return [
            TableAction::make('submit')
                ->button()->outlined()
                ->requiresConfirmation()
                ->action(function ($record) {
                    SubmitActivityDesign::execute($record);
                    return redirect(SubmittedActivityDesignResource::getUrl('view', ['record' => $record]));
                })
                ->visible(fn($record) => $record->status === ActivityDesignStatus::DRAFT),
            Tables\Actions\EditAction::make()->visible(fn($record) => $record->status === ActivityDesignStatus::DRAFT),
            Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->status === ActivityDesignStatus::DRAFT),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityDesigns::route('/'),
            'create' => Pages\CreateActivityDesign::route('/create'),
            'view' => Pages\ViewActivityDesign::route('/{record}'),
            'edit' => Pages\EditActivityDesign::route('/{record}/edit'),
        ];
    }
}
