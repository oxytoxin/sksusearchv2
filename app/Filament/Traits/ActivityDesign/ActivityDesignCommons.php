<?php

namespace App\Filament\Traits\ActivityDesign;

use App\Enums\ActivityDesignSignatoryGroupStatus;
use App\Filament\Resources\ActivityDesignResource;
use App\Models\Designation;
use App\Models\EmployeeInformation;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait ActivityDesignCommons
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistTabs::make('Tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        InfolistTab::make('General Information')
                            ->schema([
                                TextEntry::make('title'),
                                TextEntry::make('fund_cluster.name'),
                                TextEntry::make('start_date')->date(),
                                TextEntry::make('end_date')->date(),
                                TextEntry::make('total_amount')->money('PHP'),
                                TextEntry::make('created_at')->date(),
                                RepeatableEntry::make('particulars')
                                    ->schema([
                                        TextEntry::make('description'),
                                        TextEntry::make('amount')->numeric(2),
                                    ])
                                    ->columns(2),
                                RepeatableEntry::make('attachments')
                                    ->schema([
                                        TextEntry::make('description'),
                                        TextEntry::make('file_name')
                                            ->label('File')
                                            ->prefixAction(
                                                Action::make('download')
                                                    ->icon('heroicon-o-arrow-down-on-square')
                                                    ->action(fn($record) => Storage::disk('public')->download($record->path, $record->file_name))
                                            ),
                                    ])
                                    ->columns(2),
                                RepeatableEntry::make('participants')
                                    ->schema([
                                        TextEntry::make('name')->hiddenLabel()
                                    ]),
                                RepeatableEntry::make('coordinators')
                                    ->schema([
                                        TextEntry::make('name')->hiddenLabel()
                                    ])
                            ])->columns(2),
                        InfolistTab::make('Approvals')
                            ->schema([
                                RepeatableEntry::make('signatory_groups')
                                    ->schema([
                                        RepeatableEntry::make('signatories')
                                            ->label(fn($record) => 'Signatory Group ' . $record->order)
                                            ->schema([
                                                TextEntry::make('user.employee_information.full_name')->label('Name'),
                                                TextEntry::make('designation.full_designation'),
                                                TextEntry::make('is_approved')->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
                                                Actions::make([
                                                    Action::make('approve')
                                                        ->visible(fn($record) => $record->signatory_id === Auth::id() && $record->signatory_group->status === ActivityDesignSignatoryGroupStatus::IN_APPROVAL && !$record->is_approved)
                                                        ->action(function ($record) {
                                                            $record->update(['is_approved' => true]);
                                                        })
                                                        ->requiresConfirmation()
                                                ])
                                            ]),
                                        TextEntry::make('status')->hiddenLabel()->formatStateUsing(fn($state) => 'Status: ' . $state->description())->size('xs'),

                                    ])
                            ])
                    ])
            ])->columns(1);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        Select::make('fund_cluster_id')
                                            ->relationship('fund_cluster', 'name')
                                            ->required(),
                                    ]),
                                Fieldset::make('')
                                    ->columns(3)
                                    ->schema([
                                        DatePicker::make('start_date')->required()->afterOrEqual(today()),
                                        DatePicker::make('end_date')->required()->after('start_date'),
                                        Placeholder::make('total_amount')->content(fn($record) => $record?->particulars()->sum('amount') ?? number_format(0, 2)),
                                    ]),
                                Section::make('Particulars')
                                    ->schema([
                                        TableRepeater::make('particulars')
                                            ->addActionAlignment(Alignment::End)
                                            ->hiddenLabel()
                                            ->headers([
                                                Header::make('Description'),
                                                Header::make('Amount'),
                                            ])
                                            ->schema([
                                                TextInput::make('description')->required(),
                                                TextInput::make('amount')->numeric()->minValue(1)->required(),
                                            ])
                                            ->relationship('particulars'),
                                    ]),
                                Section::make('Attachments')
                                    ->schema([
                                        TableRepeater::make('attachments')
                                            ->addActionAlignment(Alignment::End)
                                            ->hiddenLabel()
                                            ->headers([
                                                Header::make('Description'),
                                                Header::make('File'),
                                            ])
                                            ->relationship('attachments')
                                            ->schema([
                                                Textarea::make('description')->rows(3)->required()->maxWidth('50%'),
                                                FileUpload::make('path')
                                                    ->required()
                                                    ->removeUploadedFileButtonPosition('right')
                                                    ->validationAttribute('file')
                                                    ->storeFileNamesIn('file_name')
                                                    ->downloadable()
                                                    ->maxFiles(1)
                                            ]),
                                    ]),
                                Section::make('Signatories')
                                    ->schema([
                                        Repeater::make('signatory_groups')
                                            ->hiddenLabel()
                                            ->addActionAlignment(Alignment::End)
                                            ->relationship('signatory_groups')
                                            ->minItems(1)
                                            ->required()
                                            ->reorderable()
                                            ->orderColumn('order')
                                            ->itemLabel(fn(array $state): ?string => isset($state['order']) ? 'Signatory Group ' . $state['order'] : null)
                                            ->schema([
                                                TableRepeater::make('signatories')
                                                    ->addActionAlignment(Alignment::End)
                                                    ->hiddenLabel()
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
                                    ])
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
                TextColumn::make('status')
            ])
            ->recordUrl(fn($record) => self::getUrl('view', ['record' => $record]))
            ->filters([
                //
            ])
            ->actions(self::getTableActions())
            ->bulkActions(self::getBulkActions());
    }
}
