<?php

namespace App\Filament\Resources\Assemblies\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\DeliveryOrder;
use App\Services\GeneratePDFBreakdownItemAssembly;
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
use App\Jobs\AssemblyProcess;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Produk';

    protected static ?string $label = 'Produk';

    protected static bool $isLazy = false;

    protected $assemblyID;

    public function form(Schema $schema): Schema
    {
        $this->assemblyID = $this->ownerRecord->id;

        return $schema
            ->components([
                Section::make('Produk')
                    ->description('Informasi Produk')
                    ->aside()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(function() {
                                return Cache::rememberForever('assembly_products', function () {
                                    $products = Product::whereIn('types', ['assembled_good'])
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
                            ->partiallyRenderComponentsAfterStateUpdated(['breakdowns'])
                            ->disabled(fn($operation)=>$operation=='edit')
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get, $record) {
                                $article = Product::find($state);
                                if($state != 0){
                                    $set('uom', $article->uom);
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
                                    'assembly_id' => $this->assemblyID,
                                ])->toArray());
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
                            ->default(1),
                        TextInput::make('uom')
                            ->label('Satuan')
                            ->helperText('Satuan hanya bisa diedit untuk produk custom')
                            ->required()
                            ->readonly()
                            ->default('Pcs')
                            ->readonly(fn(callable $get)=>$get('product_id') != 0),
                    ])->columns(2),
                Hidden::make('is_customizable')
                    ->default(0),
                Repeater::make('breakdowns')
                    ->relationship('breakdowns') // relasi di SalesOrderItem model
                    ->schema([
                        Select::make('product_id')
                            ->label('Raw Material')
                            ->relationship('product','name')
                            ->columnSpan(2)
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
                            ->helperText('NB: untuk 1 buah produk.')
                            ->numeric()
                            ->label('Qty'),
                        TextInput::make('uom')
                            ->label('Satuan'),
                        Hidden::make('assembly_id')
                            ->default($this->assemblyID)
                    ])
                    ->default([]) // kosong dulu, nanti diisi dari afterStateUpdated
                    ->columns(4)
                    ->addable(fn (callable $get) => $get('is_customizable'))
                    ->deletable(fn (callable $get) => $get('is_customizable'))
                    ->minItems(0)
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product_id')
                    ->label('Nama Produk')
                    ->searchable()
                    ->formatStateUsing(fn($record)=>($record->product_id != 0) ? $record->product->name : 'Custom'),
                TextColumn::make('qty'),
                TextColumn::make('uom')
                    ->label('Satuan'),
                TextColumn::make('created_at')
                    ->label('Breakdown')
                    ->formatStateUsing(function($record){
                        $breakdowns = $record->breakdowns;

                        $data = '';
                        foreach($breakdowns as $breakdown){
                            $data .= (($breakdown->product->productCategory) ? $breakdown->product->productCategory->name : 'Custom').' - '.$breakdown->product->name.' (qty: '.$breakdown->qty.' '.$breakdown->uom.')<br/>';
                        }
                        return $data;
                    })
                    ->html()
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($livewire, $data, $record) {
                        if($record->product_id == 0){
                            // create product custom
                            $new_product = new Product;
                            $new_product->code = get_counter('CUSTOM-');
                            $new_product->name = $data['custom_name'];
                            $new_product->uom = $record->uom;
                            $new_product->types = 'assembled_good';
                            $new_product->product_category_id = 'custom';
                            $new_product->selling_price = 0; // karena bukan dari Sales Order
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
                        }
                    })
                    ->visible(fn() => $this->ownerRecord->status == 'Draft'),
                Action::make('Print Breakdown')
                    ->color(Color::generateV3Palette('#bb72ff6f'))
                    ->icon('https://img.icons8.com/color/96/print.png')
                    ->modalHeading('Breakdown')
                    ->modalSubmitAction(false) // nggak ada tombol "Submit"
                    ->modalCancelActionLabel('Tutup')
                    ->schema(function(){
                        $pdf = new GeneratePDFBreakdownItemAssembly($this->ownerRecord);

                        return [
                            PdfViewerEntry::make('file')
                                    ->label('Breakdown Raw Material')
                                    ->minHeight('50svh')
                                    ->fileUrl($pdf->getPDF())
                                    ->columnSpanFull()
                        ];
                    })
                    ->visible(fn() => $this->ownerRecord->items->count() > 0),
                Action::make('Proses Perakitan')
                    ->color(Color::generateV3Palette('#00bbff6f'))
                    ->icon('https://img.icons8.com/color/96/circled-play--v1.png')
                    ->requiresConfirmation()
                    ->modalHeading('Anda yakin untuk memroses perakitan?')
                    ->modalDescription('Proses ini akan memotong stock raw material yang dibutuhkan untuk produksi dari Gudang Bahan Baku dan menambahkan barang rakit ke Gudang Barang Jadi?')
                    ->action(function(){
                        AssemblyProcess::dispatch($this->ownerRecord, auth()->user());

                        $this->ownerRecord->status = 'In Progress';
                        $this->ownerRecord->save();

                        Notification::make()
                            ->title('Proses Sedang Berjalan')
                            ->body('Proses pemotongan dan penambahan stock sedang berjalan. Notifkasi akan diberikan bila proses telah selesai.')
                            ->warning()
                            ->color('warning')
                            ->send();
                    })
                    ->after(function ($livewire) {
                        $livewire->dispatch('refresh');
                    })
                    ->visible(fn()=>$this->ownerRecord->breakdowns->count() && $this->ownerRecord->status == 'Draft'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    // Tables\Actions\ForceDeleteAction::make(),
                    // Tables\Actions\RestoreAction::make(),
                ])->visible(fn() => $this->ownerRecord->status == 'Draft'),
            ])
            ->toolbarActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
