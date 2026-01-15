<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use App\Filament\Widgets\TotalSalesCommission;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use App\Models\SalesOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class SalesCommission extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Komisi Sales';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/cash-in-hand.png';

    protected string $view = 'filament.pages.salesCommission';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Komisi Sales';

    protected static ?int $navigationSort = 4;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function getSubheading(): ?string
    {
        return 'Komisi Sales berdasarkan Penjualan yang telah dilakukan dan dihitung dari diskon sales dan tanggal invoice.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SalesOrder::query()
                    ->whereNotNull('sales_id')
                    ->where('discount_sales','!=',0)
                    ->whereNotNull('invoice_no')
            )
            ->columns([
                TextColumn::make('salesorder_no')
                    ->label('Sales Order No.')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('purchaseorder_no')
                //     ->label('Purchase Order No.')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->sortable()
                    ->date('d F Y')
                    ->alignCenter(),
                TextColumn::make('sales.name')
                    ->label('Sales')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sales_commission')
                    ->label('Komisi')
                    ->prefix('Rp ')
                    ->formatStateUsing(fn($state)=>numberFormat($state))
                    ->alignRight(),
            ])
            ->groups([
                'sales.name',
            ])
            ->defaultSort('invoice_date','desc')
            ->filters([
                Filter::make('date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->label('Tanggal Awal (Invoice)'),
                        DatePicker::make('endDate')
                            ->default(now())
                            ->native(false)
                            ->label('Tanggal Akhir (Invoice)'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('invoice_date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('invoice_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Invoice dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Invoice hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
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
            TotalSalesCommission::class,
        ];
    }
}
