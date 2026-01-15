<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\DeliveryOrder;
use App\Services\GeneratePDFBreakdownItem;
use App\Services\AutoAssemblyItem;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use App\Services\MutationProcess;
use App\Services\AutoDeliveryItem;
use Filament\Notifications\Notification;
use DB;
use Auth;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Cache;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use App\Filament\Resources\Assemblies\AssemblyResource;
use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Produk';

    protected static ?string $label = 'Produk';

    protected static bool $isLazy = false;

    protected $salesOrderID;

    public function form(Schema $schema): Schema
    {
        $this->salesOrderID = $this->ownerRecord->id;

        return $schema
            ->components([
                Section::make('Produk')
                    ->description('Informasi Produk')
                    ->aside()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(function() {
                                return Cache::rememberForever('sales_order_products', function () {
                                    $products = Product::whereIn('types', ['finish_good', 'assembled_good'])
                                                    ->active()
                                                    ->orderBy('name')
                                                    ->get();

                                    foreach ($products as $key => $product) {
                                        $options[product_type_match($product->types)][$product->id] = '['.$product->code.'] '.(($product->productCategory) ? $product->productCategory->name : 'Custom').' - '.$product->name;
                                    }

                                    $options[0] = 'Custom';

                                    return $options ?? [];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn($operation)=>$operation=='edit')
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get, $record) {
                                $article = Product::find($state);
                                if($state != 0){
                                    $set('uom', $article->uom);
                                    $set('price', moneyFormat($article->selling_price));
                                }

                                $set('is_customizable', fn() => ($state != 0) ? 0 : 1);

                                if (! $state) {
                                    $set('breakdowns', []);
                                    return;
                                }

                                $product = Product::with('products')->find($state);

                                // isi repeater breakdown dari default breakdown produk
                                $set('breakdowns', $product->products->map(fn ($bd) => [
                                    'product_id' => $bd->product_breakdown_id,
                                    'qty' => $bd->qty,
                                    'uom' => $bd->uom,
                                    'sales_order_id' => $this->salesOrderID,
                                ])->toArray());

                                $price = $get('price') ?? 0;
                                $qty = $get('qty') ?? 0;
                                $total = $qty * $article->selling_price;
                                $set('total_price', (moneyFormat($total)));
                                $set('master_price', $article->selling_price);
                                $set('master_total_price', $total);
                            }),
                        TextInput::make('custom_name')
                            ->label('Nama Custom')
                            ->helperText('Nama ini dibutuhkan pada saat penyimpanan di gudang dan mutasi barang, termasuk saat Delivery Order.')
                            ->requiredIf('product_id',0)
                            ->visible(fn(callable $get)=> $get('product_id') != null && $get('product_id') == 0)
                    ]),
                Section::make('Kuantitas')
                    ->description('Informasi Kuantitas Barang')
                    ->aside()
                    ->schema([
                        TextInput::make('qty')
                            ->label('Qty')
                            ->required()
                            ->numeric()
                            ->live()
                            ->default(1)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get) {
                                $price = $get('price') ?? 0;
                                $total = bcmul($state, clean_numeric($price), 5);
                                $set('total_price', ($total));

                                $master_price = $get('master_price') ?? 0;
                                $master_total = $state * $master_price;
                                $set('master_price', $master_price);
                                $set('master_total_price', $master_total);
                            }),
                        TextInput::make('uom')
                            ->label('Satuan')
                            ->helperText('Satuan hanya bisa diedit untuk produk custom')
                            ->required()
                            ->readonly()
                            ->default('Pcs')
                            ->readonly(fn(callable $get)=>$get('product_id') != 0),
                    ])->columns(2),
                Section::make('Harga')
                    ->description('Informasi Harga Barang')
                    ->aside()
                    ->schema([
                        Hidden::make('master_price'),
                        Hidden::make('master_total_price'),
                        TextInput::make('price')
                            ->label('Harga Satuan')
                            ->required()
                            ->live(debounce:1000)
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->default(0)
                            // ->mask(RawJs::make('$money($input)'))
                            // ->stripCharacters(',')
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get) {
                                $qty = $get('qty') ?? 0;
                                $total = bcmul(clean_numeric($state), $qty, 5);
                                $set('total_price', ($total));
                            }),
                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->required()
                            ->default(0)
                            ->readonly(),
                    ])->columns(2),
                Section::make('Breakdown Items')
                    ->description('Rincian untuk barang rakit.')
                    ->aside()
                    ->schema([
                        Hidden::make('is_customizable')
                            ->default(0),
                        Repeater::make('breakdowns')
                            ->relationship('breakdowns') // relasi di SalesOrderItem model
                            ->schema([
                                Select::make('product_id')
                                    ->label('Raw Material')
                                    ->relationship('product','name')
                                    ->live()
                                    ->preload()
                                    ->afterStateUpdated(function (?string $state, callable $set) {
                                        if($state){
                                            $article = Product::find($state);
                                            $set('uom', $article->uom);
                                        }
                                    })
                                    ->searchable(),
                                TextInput::make('qty')
                                    ->helperText('Jumlah item untuk membuat 1 buah produk.')
                                    ->numeric()
                                    ->label('Qty'),
                                TextInput::make('uom')
                                    ->label('Satuan'),
                                Hidden::make('sales_order_id')
                                    ->default($this->salesOrderID)
                            ])
                            ->default([]) // kosong dulu, nanti diisi dari afterStateUpdated
                            ->columns(3)
                            ->addable(fn (callable $get) => $get('is_customizable'))
                            ->deletable(fn (callable $get) => $get('is_customizable'))
                            ->minItems(0)
                            ->columnSpanFull(),
                    ])
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->heading('Produk Sales Order')
            ->description('Jumlah maksimal produk adalah 15. Apabila lebih dari itu, dapat membuat Sales Order baru.')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product_id')
                    ->label('Nama Produk')
                    ->toggleable()
                    ->searchable()
                    ->formatStateUsing(fn($record)=>($record->product_id != 0) ? $record->product->name : 'Custom'),
                TextColumn::make('qty')
                    ->toggleable()
                    ->alignCenter(),
                TextColumn::make('stocks_sum_qty')
                    ->label('Stok Tersedia')
                    ->toggleable()
                    ->alignCenter()
                    ->sum('stocks','qty')
                    ->default(0)
                    ->color(function($state, $record){
                        $stockQty = $state ?? 0;
                        if($stockQty <= 0){
                            return 'danger';
                        }
                        elseif($stockQty < $record->qty){
                            return 'warning';
                        }
                        else{
                            return 'success';
                        }
                    })
                    ->visible(fn($record)=> is_null($this->ownerRecord->delivery_order_id)),
                TextColumn::make('qty_to_replenish')
                    ->label('Qty Tambah')
                    ->toggleable()
                    ->alignCenter()
                    ->default(function($record){
                        $stockQty = $record->stocks()->sum('qty') ?? 0;
                        $qtyToReplenish = $record->qty - $stockQty;
                        return ($qtyToReplenish > 0) ? $qtyToReplenish : '';
                    })
                    ->color(function($state){
                        if(is_numeric($state)){
                            return 'warning';
                        }
                    })
                    ->visible(fn($record)=> is_null($this->ownerRecord->delivery_order_id)),
                TextColumn::make('uom')
                    ->label('Satuan')
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->toggleable()
                    ->currency('IDR'),
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->toggleable()
                    ->currency('IDR')
                    ->summarize(Sum::make()->currency('IDR')),
                TextColumn::make('created_at')
                    ->label('Breakdown')
                    ->toggleable()
                    ->formatStateUsing(function($record){
                        $breakdowns = $record->breakdowns;

                        $data = '';
                        foreach($breakdowns as $breakdown){
                            $data .= (($breakdown->product->productCategory) ? $breakdown->product->productCategory->name : 'Custom').' - '.$breakdown->product->name.' (qty: '.$breakdown->qty.' '.$breakdown->uom.')<br/>';
                        }
                        return $data;
                    })
                    ->html()
                    ->wrap(),
                TextColumn::make('assembly.code')
                    ->label('Perakitan')
                    ->toggleable()
                    ->url(fn ($record) => $record->assembly ? AssemblyResource::getUrl('view', ['record' => $record->assembly->id]) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Produk')
                    ->after(function ($livewire, $data, $record) {
                        if($record->product_id == 0){
                            // create product custom
                            $new_product = new Product;
                            $new_product->code = get_counter('CUSTOM-');
                            $new_product->name = $data['custom_name'];
                            $new_product->uom = $record->uom;
                            $new_product->types = 'assembled_good';
                            $new_product->product_category_id = 'custom';
                            $new_product->selling_price = $record->price;
                            $new_product->purchasable = 0;
                            $new_product->save();

                            $record->product_id = $new_product->id;
                            $record->save();

                            $new_product->products()->createMany(
                                $record->breakdowns->map(function($breakdown) use ($new_product){
                                    return [
                                        'product_id' => $new_product->id,
                                        'product_breakdown_id' => $breakdown->product_id,
                                        'qty' => $breakdown->qty,
                                        'uom' => $breakdown->uom,
                                        'created_by' => Auth::user()->name,
                                        'updated_by' => Auth::user()->name,
                                    ];
                                })->toArray()
                            );

                            Cache::forget('sales_order_products');
                        }
                        $livewire->dispatch('refresh');
                    })
                    ->visible(fn() => is_null($this->ownerRecord->delivery_order_id) && $this->ownerRecord->items()->count() < 15),
                Action::make('Print WO')
                    ->label('Print Working Order')
                    ->color(Color::generateV3Palette('#003cff63'))
                    ->icon('https://img.icons8.com/color/96/print.png')
                    ->modalHeading('Breakdown')
                    ->modalSubmitAction(false) // nggak ada tombol "Submit"
                    ->modalCancelActionLabel('Tutup')
                    ->schema(function(){
                        $pdf = new GeneratePDFBreakdownItem($this->ownerRecord);

                        return [
                            ViewField::make('preview')
                                ->view('components.file-preview')
                                ->viewData([
                                    'fileUrl' => $pdf->getPDF(),
                                ]),
                        ];
                    })
                    ->visible(fn() => $this->ownerRecord->items->count()),
                Action::make('Generate Perakitan')
                     ->label('Generate Perakitan')
                    ->color(Color::generateV3Palette('#ff747470'))
                    ->icon('https://img.icons8.com/color/96/puzzle-matching.png')
                    ->requiresConfirmation()
                    ->modalHeading('Anda yakin untuk membuat Perakitan baru?')
                    ->modalDescription('Proses ini akan membuat Draft Perakitan untuk produk rakit yang belum memiliki kode perakitan.')
                    ->action(function(){
                        $autoAssembly = new AutoAssemblyItem();
                        $response = $autoAssembly->generateItems($this->ownerRecord, auth()->user(), false);

                        Notification::make()
                            ->title('Sukses')
                            ->body($response['message'])
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->visible(fn()=>$this->ownerRecord->items->count()
                                && is_null($this->ownerRecord->delivery_order_id)),
                Action::make('Buat Surat Jalan')
                    ->color(Color::generateV3Palette('#babd006f'))
                    ->icon('https://img.icons8.com/color/96/loading-truck.png')
                    ->requiresConfirmation()
                    ->modalHeading('Anda yakin untuk membuat Surat Jalan?')
                    ->modalDescription('Proses ini akan membuat Draft Surat Jalan.')
                    ->action(function(){
                        // buat delivery order

                        DB::beginTransaction();

                        $deliveryOrder = new DeliveryOrder;
                        $deliveryOrder->sales_order_id = $this->ownerRecord->id;
                        $deliveryOrder->delivery_date = date('Y-m-d');
                        $deliveryOrder->warehouse_id = Warehouse::where('types','finish_good')->first()->id;
                        $deliveryOrder->created_by = auth()->user()->name;
                        $deliveryOrder->updated_by = auth()->user()->name;
                        $deliveryOrder->save();

                        $doItem = new AutoDeliveryItem();
                        $doItem->generateItems($deliveryOrder);

                        // update sales order dengan delivery order id
                        $this->ownerRecord->delivery_order_id = $deliveryOrder->id;
                        $this->ownerRecord->save();

                        DB::commit();

                        Notification::make()
                            ->title('Sukses')
                            ->body('Draft Surat Jalan telah dibuat. Sales Order telah dikunci.')
                            ->success()
                            ->color('success')
                            ->send();
                    })
                    ->visible(fn()=>$this->ownerRecord->items->count()
                                && is_null($this->ownerRecord->delivery_order_id)),
                // Tables\Actions\Action::make('Lihat Perakitan')
                //     ->color(Color::hex('#bd00b4ff'))
                //     ->icon('https://img.icons8.com/color/96/puzzle-matching.png')
                //     ->visible(fn()=>$this->ownerRecord->assembly)
                //     ->url(fn (): string => AssemblyResource::getUrl('view', ['record' => $this->ownerRecord->assembly->id]))
                //     ->openUrlInNewTab(),
                Action::make('Lihat Surat Jalan')
                    ->color(Color::generateV3Palette('#00bd06ff'))
                    ->icon('https://img.icons8.com/color/96/loading-truck.png')
                    ->visible(fn()=>$this->ownerRecord->delivery_order_id)
                    ->url(fn (): string => DeliveryOrderResource::getUrl('view', ['record' => $this->ownerRecord->delivery_order_id]))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn() => is_null($this->ownerRecord->delivery_order_id)),
                    DeleteAction::make()
                        ->after(function ($livewire) {
                            $livewire->dispatch('refresh');
                        })
                        ->visible(fn() => is_null($this->ownerRecord->delivery_order_id)),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => is_null($this->ownerRecord->delivery_order_id)),
                ]),
            ]);
    }
}
