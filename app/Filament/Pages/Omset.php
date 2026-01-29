<?php

namespace App\Filament\Pages;

use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use App\Filament\Widgets\TotalOmset;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use App\Models\SalesOrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Omset extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Omset';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/total-sales-1.png';

    protected string $view = 'filament.pages.omset';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Omset';

    protected static ?int $navigationSort = 3;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(SalesOrderItem::query()
                ->selectRaw('
                    product_id as id,
                    sales_order_id,
                    product_id,
                    sum(qty) as total_qty,
                    uom,
                    total_price,
                    SUM(
                    CASE
                        WHEN sales_orders.discount_sales > sales_orders.discount_company THEN ((100-sales_orders.discount_sales)/100) * master_total_price
                        ELSE ((100-sales_orders.discount_companyOMPANY)/100) * total_price
                    END
                    ) as discounted_total_price
                ')
                ->join('sales_orders','sales_orders.id','=','sales_order_items.sales_order_id')
                ->whereHas('salesOrder',function($q){
                    $q->whereNotNull('invoice_date');
                })
                ->groupBy('sales_order_id','product_id','uom','total_price')
            )
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori'),
                TextColumn::make('total_qty')
                    ->sortable()
                    ->label('Qty')
                    ->summarize(Sum::make()),
                TextColumn::make('discounted_total_price')
                    ->sortable()
                    ->label('Harga')
                    ->prefix('Rp ')
                    ->formatStateUsing(fn($state)=>numberFormat($state))
                    ->summarize(
                        Sum::make()
                            ->label('Total Omset')
                            ->prefix('Rp ')
                    ),
                TextColumn::make('uom')
                    ->label('Satuan'),
            ])
            ->groups([
                Group::make('product.productCategory.name')
                    ->label('Kategori Produk')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan kategori produk'),
            ])
            ->defaultSort('product.name','asc')
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filters([
                Filter::make('invoice_date_start')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->label('Tanggal Awal (Invoice)')
                            ->native(false),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereHas('salesOrder', fn ($qq) =>
                                    $qq->whereDate('invoice_date', '>=', $date)
                                )
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Invoice dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                Filter::make('invoice_date_end')
                    ->schema([
                        DatePicker::make('endDate')
                            ->default(now())
                            ->label('Tanggal Akhir (Invoice)')
                            ->native(false),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereHas('salesOrder', fn ($qq) =>
                                    $qq->whereDate('invoice_date', '<=', $date)
                                )
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Invoice hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalOmset::class,
        ];
    }
}
