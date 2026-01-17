<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use App\Filament\Widgets\TotalHutang;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use App\Models\Purchase;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Hutang extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Hutang';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/initiate-money-transfer.png';

    protected string $view = 'filament.pages.hutang';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Hutang';

    protected static ?int $navigationSort = 2;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Purchase::query()
                    ->where('is_accepted',1)
                    ->where('payment_status','Belum Lunas')
            )
            ->columns([
                TextColumn::make('purchase_no')
                    ->label('Purchase Order No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->sortable()
                    ->date('d M Y')
                    ->label('Tanggal Pembelian'),
                TextColumn::make('total_price')
                    ->sortable()
                    ->currency('IDR')
                    ->label('Hutang')
                    ->summarize(
                        Sum::make()
                            ->label('Total Hutang')
                            ->prefix('Rp ')
                    ),
            ])
            ->groups([
                'supplier.name',
            ])
            ->defaultSort('date','desc')
            ->filters([
                Filter::make('date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Awal (Pembelian)'),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir (Pembelian)'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when($data['startDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('date', '>=', $date)
                            )
                            ->when($data['endDate'] ?? null, fn ($q, $date) =>
                                $q->whereDate('date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Pembelian dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Pembelian hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
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
                        ->action(function (Purchase $record) {
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
            TotalHutang::class,
        ];
    }
}
