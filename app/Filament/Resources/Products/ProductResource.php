<?php

namespace App\Filament\Resources\Products;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/network-cable.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Produk';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Barang Jadi (Tanpa Rakitan)';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->aside()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->columnSpan('full'),
                    ]),
                Section::make('Barang Jadi (Tanpa Rakitan)')
                    ->description('Informasi Barang Jadi (Tanpa Rakitan)')
                    ->aside()
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Produk')
                            ->maxLength(16)
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->default(null),
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->maxLength(255)
                            ->required()
                            ->default(null),
                        TextInput::make('uom')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(255)
                            ->default(null),
                        Select::make('product_category_id')
                            ->label('Kategori Produk')
                            ->relationship('productCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(null),
                        RichEditor::make('specification')
                            ->label('Spesifikasi Produk')
                            ->default(null)
                            ->columnSpan('full'),
                    ])
                    ->columns(2),
                Section::make('Harga')
                    ->description('Informasi Harga')
                    ->aside()
                    ->schema([
                        TextInput::make('buying_price')
                            ->label('Harga Beli')
                            ->required()
                            ->prefix('Rp')
                            ->rules([
                                'regex:/^[0-9.]+(,\d{1,5})?$/'
                            ])
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 5)'))
                            ->formatStateUsing(fn ($state) =>
                                numberFormat((float) $state, 5)
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                clean_numeric($state)
                            )
                            ->default(0),
                        TextInput::make('selling_price')
                            ->label('Harga Jual')
                            ->required()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Stock')
                    ->description('Informasi Stock')
                    ->aside()
                    ->schema([
                        TextInput::make('safety_stock')
                            ->label('Minimum Stock')
                            ->helperText('Minimum stock yang wajib ada di persediaan (total dari semua gudang).')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                        Placeholder::make('current_stock')
                            ->label('Stock Saat Ini')
                            ->content(fn($record)=>($record) ? $record->stocks()->sum('qty') : 0)
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
                    ->label('Kode Produk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('uom')
                    ->label('Satuan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('productCategory.name')
                    ->label('Kategori Produk')
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('types', 'finish_good')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
