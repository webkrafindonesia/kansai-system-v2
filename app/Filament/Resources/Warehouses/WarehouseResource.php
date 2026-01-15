<?php

namespace App\Filament\Resources\Warehouses;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Warehouses\Pages\ListWarehouses;
use App\Filament\Resources\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\Warehouses\Pages\ViewWarehouse;
use App\Filament\Resources\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/garage-closed.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?string $title = 'Gudang';

    protected static ?string $label = 'Gudang';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi')
                    ->description('Informasi Gudang')
                    ->aside()
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Gudang')
                            ->required()
                            ->default(null),
                        TextInput::make('name')
                            ->label('Nama Gudang')
                            ->maxLength(255)
                            ->required()
                            ->default(null),
                        Radio::make('types')
                            ->label('Jenis Gudang')
                            ->default('others')
                            ->required()
                            ->options([
                                'raw_material' => 'Gudang Bahan Baku',
                                'finish_good' => 'Gudang Barang Jadi',
                                'production' => 'Gudang Produksi',
                                'virtual' => 'Gudang Virtual',
                                'others' => 'Lain-lain'
                            ])
                            ->descriptions([
                                'raw_material' => 'Gudang untuk menyimpan bahan baku produksi.',
                                'finish_good' => 'Gudang untuk menyimpan barang yang siap jual.',
                                'production' => 'Gudang untuk barang yang akan dirakit.',
                                'virtual' => 'Bisa digunakan untuk keperluan peminjaman barang, misal oleh sales.',
                                'others' => 'Bisa digunakan untuk gudang transit.'
                            ]),
                        Textarea::make('address')
                            ->label('Alamat Utama')
                            ->required()
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Gudang')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Gudang')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('types')
                    ->label('Jenis Gudang')
                    ->formatStateUsing(fn($state)=>format_warehouse_type($state))
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Alamat Utama')
                    ->html()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->html()
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view' => ViewWarehouse::route('/{record}'),
            'edit' => EditWarehouse::route('/{record}/edit'),
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
