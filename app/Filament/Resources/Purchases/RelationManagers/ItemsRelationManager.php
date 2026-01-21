<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Support\RawJs;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Produk';

    protected static ?string $label = 'Produk';

    protected static bool $isLazy = false;

    public ?string $lastItemId = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Produk')
                    ->description('Informasi Produk')
                    ->aside()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(function() {
                                return Cache::rememberForever('purchase_products', function () {
                                        $products = Product::purchasable()
                                                        ->active()
                                                        ->orderBy('name')
                                                        ->get();

                                        foreach ($products as $key => $product) {
                                            $options[product_type_match($product->types)][$product->id] = '['.$product->code.'] '.$product->productCategory->name.' - '.$product->name;
                                        }

                                        return $options ?? [];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->skipRenderAfterStateUpdated()
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get) {
                                if(!is_null($state)){
                                    $article = Product::find($state);
                                    $set('uom', $article->uom);
                                    $set('price', numberFormat($article->buying_price,5));
                                    $qty = $get('qty') ?? 0;
                                    $total = ($article->buying_price) * ($qty);
                                    $set('total_price', (numberFormat($total,5)));
                                }
                            })
                            ->default(null),
                    ]),
                Section::make('Kuantitas')
                    ->description('Informasi Kuantitas Barang')
                    ->aside()
                    ->schema([
                        TextInput::make('qty')
                            ->label('Qty')
                            ->rules([
                                'regex:/^[\d.]+$/'
                            ])
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->formatStateUsing(fn ($state) =>
                                numberFormat((float) $state, 0)
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                clean_numeric($state)
                            )
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->skipRenderAfterStateUpdated()
                            ->afterStateUpdatedJs(<<<'JS'
                                const price = $get('price') ?? 0;
                                const total = bcmulJS(clean_numericJS($state), clean_numericJS(price), 5);
                                $set('total_price', numberFormatJS(total, 5));
                            JS),
                        TextInput::make('uom')
                            ->label('Satuan')
                            ->maxLength(255)
                            ->required()
                            ->readonly()
                            ->default(null),
                    ])->columns(2),
                Section::make('Harga')
                    ->description('Informasi Harga Barang')
                    ->aside()
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga Satuan')
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
                            ->default(0)
                            ->skipRenderAfterStateUpdated()
                            ->afterStateUpdatedJs(<<<'JS'
                                const qty = $get('qty') ?? 0;
                                const total = bcmulJS(clean_numericJS($state), clean_numericJS(qty), 5);
                                $set('total_price', numberFormatJS(total, 5));
                            JS),
                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->required()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->formatStateUsing(fn ($state) =>
                                numberFormat((float) $state, 5)
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                clean_numeric($state)
                            )
                            ->default(0)
                            ->readonly(),
                    ])->columns(2),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->summarize(Sum::make()),
                TextColumn::make('uom')
                    ->label('Satuan'),
                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->currency('IDR5'),
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->currency('IDR5')
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->prefix('Rp ')
                    ),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                Action::make('Accept Items')
                    ->label('Terima Barang')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Terima Barang')
                    ->modalDescription('Anda yakin untuk menerima barang-barang ini dan memasukkannya sebagai stok di gudang (yang ditentukan di atas)?')
                    ->action(function () {
                        $this->ownerRecord->acceptItems();

                        Notification::make()
                            ->title('Success')
                            ->body('Barang telah diterima dan stok telah diperbarui.')
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->visible(fn () => $this->ownerRecord->items->count() > 0 && !$this->ownerRecord->is_accepted),
                CreateAction::make()
                    ->label('Tambah Produk'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    // Tables\Actions\ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
