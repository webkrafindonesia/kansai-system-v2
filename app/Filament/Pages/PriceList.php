<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Exports\PriceListExporter;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;

class PriceList extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Price List';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/total-sales-1.png';

    protected string $view = 'filament.pages.price-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Produk';

    protected static ?string $navigationLabel = 'Price List';

    protected static ?int $navigationSort = 5;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()
                        ->whereNotIn('types',['raw_material'])
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('types')
                //     ->label('Tipe')
                //     ->formatStateUsing(fn($state)=>product_type_match($state))
                //     ->sortable(),
                TextColumn::make('productCategory.name')
                    ->label('Kategori'),
                TextColumn::make('uom')
                    ->toggleable()
                    ->label('Satuan'),
                TextColumn::make('selling_price')
                    ->currency('IDR')
                    ->label('Harga Jual')
                    ->color('success')
                    ->alignRight(),
            ])
            ->groups([
                // Group::make('types')
                //     ->label('Tipe Produk')
                //     ->collapsible()
                //     ->getTitleFromRecordUsing(fn ($record)=>product_type_match($record->types))
                //     ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan tipe produk'),
                Group::make('productCategory.name')
                    ->label('Kategori Produk')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(fn ($record)=>'Grouping berdasarkan kategori produk'),
            ])
            ->defaultSort('name','asc')
            // ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->filters([
                //
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Ekspor .xlsx')
                ->modalHeading('Ekspor Price List (.xlsx)')
                ->icon('https://img.icons8.com/color/96/microsoft-excel-2019--v1.png')
                ->exporter(PriceListExporter::class)
                ->formats([
                    ExportFormat::Xlsx,
                ])
        ];
    }
}
