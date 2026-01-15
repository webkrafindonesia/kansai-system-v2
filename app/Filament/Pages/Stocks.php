<?php

namespace App\Filament\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use DB;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use App\Models\Stock;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Stocks extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $title = 'Stocks';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/scan-stock.png';

    protected string $view = 'filament.pages.stocks';

    protected static string | \UnitEnum | null $navigationGroup = 'Logistik';

    protected static ?int $navigationSort = 2;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Stock::query()
                        ->select(
                            'stocks.id',
                            'qty',
                            'safety_stock',
                            'product_id',
                            'warehouse_id'
                        )
                        ->join('products','products.id','stocks.product_id')
            )
            ->defaultSort('product.name','asc')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->formatStateUsing(fn($state)=>str_contains($state,'CUSTOM-')?'':$state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.types')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state)=>product_type_match($state))
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->color(function($record){
                        if($record->product->stocks()->sum('qty') < $record->product->safety_stock)
                            return 'danger';
                    })
                    ->summarize(Sum::make()),
                TextColumn::make('uom'),
            ])
            ->groups([
                Group::make('product.productCategory.name')
                    ->label('Kategori Produk')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan kategori produk'),
                Group::make('product.types')
                    ->label('Tipe Produk')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan tipe produk'),
                Group::make('warehouse.name')
                    ->label('Gudang')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan gudang'),
            ])
            ->defaultSort('product.code','asc')
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filters([
                //Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('product_id')
                    ->options(function() {
                        $options = [];
                        $products = Product::all();
                        foreach($products as $product){
                            $options[$product->id] = '['.$product->code.'] '.$product->name;
                        }
                        return $options;
                    })
                    ->searchable()
                    ->label('Product'),
                SelectFilter::make('types')
                    ->label('Tipe Produk')
                    ->options([
                        'raw_material' => 'Raw Material',
                        'assembled_good' => 'Barang Rakit',
                        'finish_good' => 'Barang Jadi (Tanpa Rakitan)',
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $state): Builder {
                        if (empty($state['value'])) {
                            return $query;
                        }

                        return $query->whereHas('product', function (Builder $q) use ($state) {
                            $q->where('types', $state['value']);
                        });
                    })
                    ->searchable(),
                SelectFilter::make('warehouse_id')
                    ->options(fn() => Warehouse::all()->pluck('name', 'id'))
                    ->searchable()
                    ->label('Warehouse'),
                SelectFilter::make('stock_status')
                    ->label('Status Stok')
                    ->options([
                        'safe_stock' => 'Stok Aman',
                        'non_safe_stock' => 'Perlu Re-Stock',
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $state): Builder {
                        if (empty($state['value'])) {
                            return $query;
                        }

                        return $query->whereHas('product', function (Builder $q) use ($state) {
                            if($state['value'] == 'safe_stock'){
                                $q->withSum('stocks', 'qty')
                                    ->having('stocks_sum_qty', '>=', DB::raw('safety_stock'));
                            }
                            else{
                                $q->withSum('stocks', 'qty')
                                    ->having('stocks_sum_qty', '<', DB::raw('safety_stock'));
                            }
                        });
                    })
                    ->searchable(),

            ])
            ->recordActions([
                Action::make('View History')
                    ->url(fn ($record) => route('filament.admin.pages.product-history.{record}', $record)),
            ])
            ->toolbarActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
