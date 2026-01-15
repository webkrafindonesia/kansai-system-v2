<?php

namespace App\Filament\Resources\PettyCashes\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Models\PettyCash;
use App\Filament\Resources\PettyCashes\Pages\ManagePettyCashes;

class TotalPettyCash extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithPageTable;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ManagePettyCashes::class;
    }

    protected function getStats(): array
    {
        $trx_in = $this->getPageTableQuery()->sum('trx_in');
        $trx_out = $this->getPageTableQuery()->sum('trx_out');
        $total = PettyCash::selectRaw('sum(trx_in)-sum(trx_out) as total')->first();

        return [
            Stat::make('Total Kas Kecil', numberFormat(moneyFormat($total->total)))
                ->color('primary')
                ->description('Total Kas Kecil dari semua transaksi')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Total Pemasukan', numberFormat(moneyFormat($trx_in)))
                ->color('success')
                ->description('Pemasukan Kas Kecil berdasarkan filter tanggal')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Total Pengeluaran', numberFormat(moneyFormat($trx_out)))
                ->color('danger')
                ->description('Pengeluaran Kas Kecil berdasarkan filter tanggal')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
        ];
    }
}
