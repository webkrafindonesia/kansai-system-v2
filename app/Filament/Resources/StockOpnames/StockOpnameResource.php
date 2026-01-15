<?php

namespace App\Filament\Resources\StockOpnames;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use App\Filament\Resources\StockOpnames\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\StockOpnames\Pages\ListStockOpnames;
use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use App\Filament\Resources\StockOpnames\Pages\ViewStockOpname;
use App\Filament\Resources\StockOpnames\Pages\EditStockOpname;
use App\Filament\Resources\StockOpnameResource\Pages;
use App\Filament\Resources\StockOpnameResource\RelationManagers;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->aside()
                    ->schema([
                        Placeholder::make('status')
                            ->content(fn($record) => ($record)?$record->status:'-'),
                    ])
                    ->visible(fn($operation)=> $operation != 'create'),
                Section::make('Stock Opname')
                    ->description('Informasi Stock Opname')
                    ->aside()
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->native(false)
                            ->required(),
                        DateTimePicker::make('opname_date')
                            ->label('Waktu Opname')
                            ->native(false)
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Select::make('options')
                            ->options([
                                'raw_material' => 'Semua Raw Material',
                                'finished_assembled' => 'Semua Barang Jadi dan Rakit',
                                'all' => 'Semua Barang',
                                'manual' => 'Pilih Manual',
                            ])
                            ->default(null)
                            ->native(false)
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2)
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->searchable(),
                TextColumn::make('opname_date')
                    ->label('Waktu Opname')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status'),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                ])
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
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
            'index' => ListStockOpnames::route('/'),
            'create' => CreateStockOpname::route('/create'),
            'view' => ViewStockOpname::route('/{record}'),
            'edit' => EditStockOpname::route('/{record}/edit'),
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
