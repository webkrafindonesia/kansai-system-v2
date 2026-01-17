<?php

namespace App\Filament\Resources\Assemblies;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Assemblies\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Assemblies\Pages\ListAssemblies;
use App\Filament\Resources\Assemblies\Pages\CreateAssembly;
use App\Filament\Resources\Assemblies\Pages\ViewAssembly;
use App\Filament\Resources\Assemblies\Pages\EditAssembly;
use App\Filament\Resources\AssemblyResource\Pages;
use App\Filament\Resources\AssemblyResource\RelationManagers;
use App\Models\Assembly;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssemblyResource extends Resource
{
    protected static ?string $model = Assembly::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/puzzle-matching.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?string $title = 'Perakitan';

    protected static ?string $label = 'Perakitan';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->description('Status Perakitan')
                    ->aside()
                    ->schema([
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn ($record): string => $record ? $record->status : '-'),
                        Placeholder::make('processed_at')
                            ->label('Diproses pada')
                            ->content(fn ($record): string => !is_null($record->processed_at) ? $record->processed_at->format('d F Y, H:i') : '-')
                            ->visible(fn($record)=> !is_null($record->processed_at)),
                    ])
                    ->visible(fn($operation) => $operation != 'create')
                    ->columns(2),
                Section::make('Informasi Perakitan')
                    ->aside()
                    ->schema([
                        Placeholder::make('code')
                            ->label('Kode')
                            ->visible(fn($operation) => $operation != 'create'),
                        DatePicker::make('assembly_date')
                            ->label('Tanggal Rakitan')
                            ->native(false)
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assembly_date')
                    ->label('Tanggal Rakitan')
                    ->date()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => match ($state) {
                        'Draft' => 'warning',
                        'In Progress' => 'info',
                        'Done' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('notes')
                    ->wrap()
                    ->searchable(),
            ])
            ->defaultSort('code','desc')
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                Filter::make('invoice_date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->label('Tanggal Awal'),
                        DatePicker::make('endDate')
                            ->default(now())
                            ->native(false)
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('assembly_date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('assembly_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Perakitan dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Perakitan hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssemblies::route('/'),
            'create' => CreateAssembly::route('/create'),
            'view' => ViewAssembly::route('/{record}'),
            'edit' => EditAssembly::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
