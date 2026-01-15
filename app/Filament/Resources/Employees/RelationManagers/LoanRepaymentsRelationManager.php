<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Tables\Columns\Summarizers\Sum;

class LoanRepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'loanRepayments';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Pembayaran Pinjaman';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('repayment_date')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('repayment_date')
            ->columns([
                TextColumn::make('repayment_date')
                    ->label('Tanggal Pembayaran')
                    ->date('d F Y'),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->currency('IDR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Pelunasan')
                            ->currency('IDR')
                    ),
                TextColumn::make('notes')
                    ->label('Catatan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->recordActions([

            ])
            ->toolbarActions([

            ]);
    }
}
