<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityDesignResource\Pages;
use App\Filament\Resources\ActivityDesignResource\RelationManagers;
use App\Models\ActivityDesign;
use App\Models\Designation;
use App\Models\EmployeeInformation;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityDesignResource extends Resource
{
    protected static ?string $model = ActivityDesign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General Information')
                            ->schema([
                                TextInput::make('title')->required()->columnSpanFull(),
                                Fieldset::make('')
                                    ->columns(3)
                                    ->schema([
                                        DatePicker::make('start_date')->required()->afterOrEqual(today()),
                                        DatePicker::make('end_date')->required()->after('start_date'),
                                        Placeholder::make('total_amount')->content(fn($record) => $record?->particulars()->sum('amount') ?? number_format(0, 2)),
                                    ]),
                                TableRepeater::make('particulars')
                                    ->extraFieldWrapperAttributes(['class' => ''])
                                    ->headers([
                                        Header::make('Description'),
                                        Header::make('Amount'),
                                    ])
                                    ->schema([
                                        TextInput::make('description')->required(),
                                        TextInput::make('amount')->numeric()->minValue(1)->required(),
                                    ])
                                    ->relationship('particulars'),
                                SpatieMediaLibraryFileUpload::make('attachments')
                                    ->collection('attachments')
                                    ->downloadable(),
                                Repeater::make('signatory_groups')
                                    ->relationship('signatory_groups')
                                    ->minItems(1)
                                    ->required()
                                    ->reorderable()
                                    ->orderColumn('order')
                                    ->itemLabel(fn(array $state): ?string => isset($state['order']) ? 'Signatory Group ' . $state['order'] : null)
                                    ->schema([
                                        TableRepeater::make('signatories')
                                            ->headers([
                                                Header::make('Signatory'),
                                                Header::make('Designation'),
                                            ])
                                            ->renderHeader(false)
                                            ->schema([
                                                Select::make('signatory_id')
                                                    ->options(EmployeeInformation::pluck('full_name', 'user_id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($set, $state) {
                                                        $set('designation_id', null);
                                                    })
                                                    ->preload(),
                                                Select::make('designation_id')
                                                    ->maxWidth('50%')
                                                    ->options(
                                                        fn($get) =>
                                                        Designation::query()
                                                            ->where('employee_information_id', EmployeeInformation::where('user_id', $get('signatory_id'))->first()?->id)
                                                            ->join('campuses', 'designations.campus_id', 'campuses.id')
                                                            ->join('offices', 'designations.office_id', 'offices.id')
                                                            ->join('positions', 'designations.position_id', 'positions.id')
                                                            ->selectRaw('designations.id, CONCAT_WS(" - ", positions.description, offices.name, campuses.name) as name')
                                                            ->pluck('name', 'designations.id')
                                                    )
                                                    ->required(),
                                            ])
                                            ->relationship('signatories')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Participants')
                            ->schema([
                                TableRepeater::make('participants')
                                    ->headers([
                                        Header::make('Employee'),
                                        Header::make('Name'),
                                    ])
                                    ->schema([
                                        Select::make('employee_information_id')
                                            ->options(EmployeeInformation::pluck('full_name', 'id'))
                                            ->searchable()
                                            ->afterStateUpdated(function ($set, $state) {
                                                $employee = EmployeeInformation::find($state);
                                                $set('name', $employee?->full_name);
                                            })
                                            ->reactive()
                                            ->preload(),
                                        TextInput::make('name')
                                            ->disabled(fn($get) => $get('employee_information_id'))
                                            ->dehydrated(true)
                                            ->required(),
                                    ])
                                    ->relationship('participants')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Coordinators')
                            ->schema([
                                TableRepeater::make('coordinators')
                                    ->headers([
                                        Header::make('Employee'),
                                        Header::make('Name'),
                                    ])
                                    ->schema([
                                        Select::make('employee_information_id')
                                            ->options(EmployeeInformation::pluck('full_name', 'id'))
                                            ->searchable()
                                            ->afterStateUpdated(function ($set, $state) {
                                                $employee = EmployeeInformation::find($state);
                                                $set('name', $employee?->full_name);
                                            })
                                            ->reactive()
                                            ->preload(),
                                        TextInput::make('name')
                                            ->disabled(fn($get) => $get('employee_information_id'))
                                            ->dehydrated(true)
                                            ->required(),
                                    ])
                                    ->relationship('coordinators')
                                    ->columnSpanFull()
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('start_date')->date(),
                TextColumn::make('end_date')->date(),
                TextColumn::make('total_amount')->numeric(2),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListActivityDesigns::route('/'),
            'create' => Pages\CreateActivityDesign::route('/create'),
            'edit' => Pages\EditActivityDesign::route('/{record}/edit'),
        ];
    }
}
