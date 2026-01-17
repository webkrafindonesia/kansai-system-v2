<?php

namespace App\Filament\Resources\AssembledGoods;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\AssembledGoods\Pages\ListAssembledGoods;
use App\Filament\Resources\AssembledGoods\Pages\CreateAssembledGood;
use App\Filament\Resources\AssembledGoods\Pages\ViewAssembledGood;
use App\Filament\Resources\AssembledGoods\Pages\EditAssembledGood;
use App\Filament\Resources\AssembledGoodResource\Pages;
use App\Filament\Resources\AssembledGoodResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Illuminate\Support\Facades\Cache;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Support\Enums\Alignment;

class AssembledGoodResource extends Resource
{
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/deployment.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Produk';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Barang Rakit';

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
                Section::make('Barang Rakit')
                    ->description('Informasi Barang Rakit')
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
                            ->createOptionForm([
                                TextInput::make('code')
                                    ->label('Kode')
                                    ->required()
                                    ->default(null),
                                TextInput::make('name')
                                    ->label('Nama Kategori')
                                    ->maxLength(255)
                                    ->required()
                                    ->default(null),
                            ])
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
                        TextInput::make('selling_price')
                            ->label('Harga Jual')
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2),
                TableRepeater::make('raw_materials')
                    ->relationship('products')
                    ->schema([
                        Select::make('product_breakdown_id')
                            ->options(function(){
                                return Cache::rememberForever('raw_material_list', function () {
                                    $products = Product::where('types','=','raw_material')
                                                    ->active()
                                                    ->get();
                                    foreach($products as $product){
                                        if($product->productCategory)
                                            $options[$product->id] = $product->productCategory->name.' - '.$product->name;
                                        else
                                            $options[$product->id] = $product->name;
                                    }
                                    return $options;
                                });
                            })
                            ->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->skipRenderAfterStateUpdated()
                            ->label('Bahan Baku / Artikel')
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                if(!is_null($state)){
                                    $article = Product::find($state);
                                    $set('uom', $article->uom);
                                }
                                else{
                                    $set('qty', 0);
                                    $set('uom', '');
                                }
                            }),
                        TextInput::make('qty')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('uom')
                            ->required()
                            ->readonly(),
                    ])
                    ->columns(3)
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
            // RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssembledGoods::route('/'),
            'create' => CreateAssembledGood::route('/create'),
            'view' => ViewAssembledGood::route('/{record}'),
            'edit' => EditAssembledGood::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('types', 'assembled_good')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPluralLabel(): ?string
    {
        return "Barang Rakit" ;
    }
}
