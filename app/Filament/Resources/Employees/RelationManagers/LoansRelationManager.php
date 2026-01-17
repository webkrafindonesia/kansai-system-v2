<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Support\RawJs;

class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loans';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Pinjaman';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pinjaman Karyawan')
                    ->aside()
                    ->schema([
                        DatePicker::make('loan_date')
                            ->label('Tanggal Pinjam')
                            ->required()
                            ->native(false)
                            ->maxDate(now()),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->skipRenderAfterStateUpdated()
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
                            ->required(),
                        Textarea::make('notes'),
                    ])
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('loan_date')
            ->columns([
                TextColumn::make('loan_date')
                    ->label('Tanggal Pinjam')
                    ->date('d F Y'),
                TextColumn::make('amount')
                    ->label('Nominal Pinjaman')
                    ->currency('IDR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Pinjaman')
                            ->prefix('Rp ')
                    ),
                TextColumn::make('notes'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Pinjaman')
                    ->modalHeading('Pinjaman Baru')
                    ->mutateDataUsing(function (array $data): array {
                        $data['remaining'] = $data['amount'];
                        return $data;
                    }),
            ])
            ->recordActions([
                // DeleteAction::make(),
                // RestoreAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
