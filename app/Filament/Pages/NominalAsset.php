<?php

namespace App\Filament\Pages;

use DB;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use App\Models\Stock;
use App\Models\ProductHistory;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class NominalAsset extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Nominal Asset';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/warehouse.png';

    protected string $view = 'filament.pages.rawMaterialUsage';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Nominal Asset';

    protected static ?int $navigationSort = 6;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function getSubheading(): ?string
    {
        return 'Menampilkan nominal asset untuk Bahan Baku dan Barang Jadi (Tanpa Rakitan).';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Stock::query()
                    ->select(
                        'stocks.product_id as id',
                        'stocks.product_id',
                        'stocks.uom',
                        'stocks.qty',
                    )
                    ->addSelect(
                        [
                            'avg_price' => ProductHistory::select(
                                DB::raw('SUM(total_nominal)/SUM(qty)')
                            )
                            ->whereNotNull('total_nominal')
                            ->where('total_nominal','<>',0)
                            ->whereColumn('product_id','stocks.product_id')
                        ]
                    )
                    ->withSum('histories as sum_price', 'total_nominal')
                    ->groupBy('stocks.product_id','stocks.uom', 'stocks.qty')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.types')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state)=>product_type_match($state))
                    ->sortable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Jumlah Stok')
                    ->sortable()
                    ->numeric()
                    ->alignCenter(),
                    // ->summarize(Sum::make()),
                TextColumn::make('uom')
                    ->label('Satuan')
                    ->alignCenter(),
                TextColumn::make('avg_price')
                    ->label('Rata-rata Harga')
                    ->prefix('Rp ')
                    ->formatStateUsing(fn($state)=>numberFormat($state))
                    ->alignCenter(),
                TextColumn::make('sum_price')
                    ->label('Total')
                    // ->currency('IDR')
                    ->prefix('Rp ')
                    ->formatStateUsing(fn($record)=>numberFormat($record->qty * $record->avg_price))
                    ->alignCenter()
                    // ->summarize(
                    //     Sum::make()
                    //         ->label('Total')
                    //         ->prefix('Rp ')
                    // ),
            ])
            ->groups([
                Group::make('product.types')
                    ->label('Tipe Produk')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record)=>product_type_match($record->product->types))
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan tipe produk'),
                Group::make('product.productCategory.name')
                    ->label('Kategori Produk')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan kategori produk'),
            ])
            ->defaultSort('product.name','asc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('Pelunasan')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Pelunasan')
                        ->modalDescription('Anda yakin menandai Pembelian ini sebagai Lunas?')
                        ->action(function (SalesOrder $record) {
                            $record->processPaid();

                            Notification::make()
                                ->title('Success')
                                ->body('Pembelian berhasil ditandai sebagai Lunas.')
                                ->success()
                                ->color('success')
                                ->send();
                        })
                        ->visible(fn($record)=>$record->payment_status=='Belum Lunas'),
                ])
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}
