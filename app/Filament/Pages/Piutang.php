<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use App\Filament\Widgets\TotalPiutang;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use App\Models\SalesOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use App\Filament\Widgets;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Tables\Columns\Summarizers\Sum;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;

class Piutang extends Page implements HasTable
{
    use InteractsWithTable;
    use ExposesTableToWidgets;
    use HasPageShield;

    protected static ?string $title = 'Piutang';

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/request-money.png';

    protected string $view = 'filament.pages.piutang';

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?int $navigationSort = 1;

    protected static ?string $pollingInterval = null;

    public ?string $activeTab = null;

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(SalesOrder::query()
                    ->whereNotNull('invoice_date')
                    ->where('invoice_status','Belum Lunas')
            )
            ->columns([
                TextColumn::make('salesorder_no')
                    ->label('Sales Order No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_no')
                    ->sortable()
                    ->label('No. Invoice'),
                TextColumn::make('invoice_date')
                    ->sortable()
                    ->date('d M Y')
                    ->label('Tanggal Invoice'),
                TextColumn::make('total_omset')
                    ->sortable()
                    ->label('Omset')
                    ->currency('IDR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Omset')
                            ->currency('IDR')
                    ),
            ])
            ->groups([
                'customer.name',
            ])
            ->defaultSort('invoice_date','desc')
            ->filters([
                Filter::make('invoice_date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Awal (Invoice)'),
                        DatePicker::make('endDate')
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
                        ->modalDescription('Anda yakin menandai Invoice ini sebagai Lunas?')
                        ->action(function (SalesOrder $record) {
                            $record->processPaid();

                            Notification::make()
                                ->title('Success')
                                ->body('Invoice berhasil ditandai sebagai Lunas.')
                                ->success()
                                ->color('success')
                                ->send();
                        })
                        ->visible(fn($record)=>$record->invoice_status=='Belum Lunas'),
                ])
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalPiutang::class,
        ];
    }
}
