<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Models\ProductHistory as ProductHistoryModel;
use App\Models\Stock;
use Filament\Pages\Page;
use Filament\Infolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\Route;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Pages\Stocks;

class ProductHistory extends Page implements HasInfolists, HasTable
{
    use InteractsWithInfolists, InteractsWithTable;
    use HasPageShield;

    protected static ?string $title = 'Product History';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.product-history';

    protected static bool $shouldRegisterNavigation = false;

    public ?Model $parentRecord = null;

    public function getBreadcrumbs(): array
    {
        return [
            Stocks::getUrl() => 'Stok',
            '' => 'Product History',
        ];
    }

    public function mount(Stock $record): void
    {
        $this->record = $record;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                Section::make('Product Info')
                    ->schema([
                        TextEntry::make('product.name')->label('Product Name'),
                        TextEntry::make('warehouse.name')->label('Warehouse Name'),
                        TextEntry::make('qty'),
                        TextEntry::make('uom'),
                    ])
                    ->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductHistoryModel::query()
                    ->where('product_id', $this->record->product_id)
                    ->where('warehouse_id', $this->record->warehouse_id)
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('description')
                    ->wrap()
                    ->description(fn($record)=>'Ref ID : '.$record->reference_id),
                TextColumn::make('qty')
                    ->color(function($state){
                        if($state > 0){
                            return 'success';
                        } elseif($state < 0){
                            return 'danger';
                        } else {
                            return 'secondary';
                        }
                    }),
                TextColumn::make('uom'),
                TextColumn::make('types')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mutation_in' => 'success',
                        'mutation_out' => 'danger',
                        'production_in' => 'danger',
                        'production_out' => 'success',
                        'purchase' => 'success',
                        'delivery_out' => 'danger',
                        'return' => 'success',
                        'stock_opname' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'mutation_in' => 'Transfer Masuk',
                        'mutation_out' => 'Transfer Keluar',
                        'production_in' => 'Keluar - Proses Produksi',
                        'production_out' => 'Masuk - Hasil Produksi',
                        'purchase' => 'Pembelian (Masuk)',
                        'delivery_out' => 'Pengiriman (Keluar) ',
                        'return' => 'Retur (Masuk)',
                        'stock_opname' => 'Stock Opname',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent) // filter tampil di atas
            ->paginated([10, 25, 50, 100, 'all']); // pagination custom
    }

    protected static ?string $slug = 'product-history/{record}';

}
