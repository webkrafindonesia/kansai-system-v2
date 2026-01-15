<?php

namespace App\Filament\Pages;

use DB;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use App\Models\ProductHistory;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class RawMaterialUsage extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Penggunaan Raw Material';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/storage.png';

    protected string $view = 'filament.pages.rawMaterialUsage';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Penggunaan Raw Material';

    protected static ?int $navigationSort = 5;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(ProductHistory::query()
                    ->select(
                        'product_id as id',
                        'product_id',
                        'uom',
                        DB::raw('SUM(qty) as qty')
                    )
                    ->where('types','production_in')
                    ->where('reference','Assembly')
                    ->groupBy('product_id','uom')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Total Penggunaan')
                    ->sortable()
                    ->numeric()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => abs($state))
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => abs($state))
                    ),
                TextColumn::make('uom')
                    ->label('Satuan')
                    ->alignCenter(),
            ])
            ->groups([
                'product.productCategory.name',
            ])
            ->defaultSort('product.name','asc')
            ->filters([
                Filter::make('date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->label('Tanggal Awal'),
                        DatePicker::make('endDate')
                            ->default(now())
                            ->native(false)
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('created_at', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Penggunaan dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Penggunaan hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
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
