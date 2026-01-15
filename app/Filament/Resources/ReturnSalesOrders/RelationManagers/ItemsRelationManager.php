<?php

namespace App\Filament\Resources\ReturnSalesOrders\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\ActionGroup;
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
use App\Models\SalesOrderItem;
use App\Models\ReturnSalesOrderItem;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $label = 'Produk';

    protected static ?string $title = 'Produk';

    protected static bool $isLazy = false;

    public ?float $maxValueReturn = 0;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Produk')
                    ->description('Informasi produk pada Sales Order')
                    ->aside()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->required()
                            ->searchable()
                            ->live()
                            ->columnSpanFull()
                            ->options(function(){
                                $items = SalesOrderItem::where('sales_order_id',$this->ownerRecord->sales_order_id)
                                            ->with('product')
                                            ->get()
                                            ->pluck('product.name','product_id');
                                return $items;
                            })
                            ->afterStateUpdated(function($state, $livewire, callable $set){
                                $salesOrderItem = SalesOrderItem::where('sales_order_id',$this->ownerRecord->sales_order_id)
                                                    ->where('product_id',$state)
                                                    ->first();
                                $returnedItemQty = ReturnSalesOrderItem::where('product_id',$state)
                                                    ->sum('qty');

                                $livewire->maxValueReturn = $salesOrderItem->qty - ($returnedItemQty??0);
                                $set('qty_order',$salesOrderItem->qty);
                                $set('uom',$salesOrderItem->uom);
                                $set('qty_returned',($returnedItemQty??0));
                                $set('sales_order_item_id',$salesOrderItem->id);
                                // dd($this->maxValueReturn);
                            }),
                        Hidden::make('sales_order_item_id'),
                        TextInput::make('qty_order')
                            ->label('Qty Order')
                            ->readOnly(),
                        TextInput::make('uom')
                            ->label('Satuan')
                            ->readOnly(),
                    ])
                    ->columns(2),
                Section::make('Retur')
                    ->description('Informasi Retur')
                    ->aside()
                    ->schema([
                        TextInput::make('qty_returned')
                            ->label('Qty yang sudah di-retur')
                            ->columnSpanFull()
                            ->readOnly(),
                        TextInput::make('qty')
                            ->label('Qty Retur')
                            ->helperText('Qty yang akan di-retur')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn ($livewire) => floatval($livewire->maxValueReturn)),
                        Radio::make('action')
                            ->label('Perlakuan')
                            ->options(return_types())
                            ->descriptions(return_descriptions())
                            ->default('skip')
                            ->required(),
                    ])
                    ->columns(2)
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode'),
                TextColumn::make('product.name')
                    ->label('Produk'),
                TextColumn::make('qty'),
                TextColumn::make('uom')
                    ->label('Satuan'),
                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->currency('IDR')
                    ->alignRight(),
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->currency('IDR')
                    ->alignRight(),
                TextColumn::make('discounted_total_price')
                    ->label('Setelah Diskon')
                    ->currency('IDR')
                    ->alignRight(),
                TextColumn::make('action')
                    ->label('Perlakuan')
                    ->formatStateUsing(fn($state)=>return_type_match($state))
                    ->color(fn($state)=>($state=='skip')?'danger':'success'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Pilih Produk')
                    ->modalHeading('Pilih Produk')
                    ->mutateDataUsing(function (array $data): array {
                        $salesOrderItem = SalesOrderItem::where('id',$data['sales_order_item_id'])->first();
                        $discount = ($salesOrderItem->salesOrder->discount_sales > $salesOrderItem->salesOrder->discount_company) ? $salesOrderItem->salesOrder->discount_sales : $salesOrderItem->salesOrder->discount_company;

                        $data['price'] = $salesOrderItem->price;
                        $data['total_price'] = $salesOrderItem->price * $data['qty'];
                        $data['discounted_total_price'] = $data['total_price'] * (100 - $discount) / 100;

                        return $data;
                    })
                    ->visible(fn()=>is_null($this->ownerRecord->processed_at)),
            ])
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make()
                        ->visible(fn()=>is_null($this->ownerRecord->processed_at)),
                    RestoreAction::make()
                        ->visible(fn($record)=>$record->deleted_at && is_null($this->ownerRecord->processed_at)),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn()=>is_null($this->ownerRecord->processed_at)),
                    RestoreBulkAction::make()
                        ->visible(fn()=>is_null($this->ownerRecord->processed_at)),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
