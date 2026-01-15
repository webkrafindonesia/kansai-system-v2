<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Support\Colors\Color;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use App\Models\EmployeeLoanRepayment;
use App\Services\GeneratePDFPayrollSlip;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Penggajian';

    public function form(Schema $schema): Schema
    {
        $loan = $this->ownerRecord->loans()->sum('amount');
        $repayment = $this->ownerRecord->loanRepayments()->sum('amount');
        $remaining_loan = moneyFormat($loan-$repayment);

        return $schema
            ->components([
                Section::make('Gaji')
                    ->aside()
                    ->schema([
                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'Mingguan' => 'Mingguan',
                                'Bulanan' => 'Bulanan',
                            ])
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function($state, callable $get, callable $set){
                                $employee = $this->ownerRecord;
                                if($state == 'Mingguan')
                                    $salary = moneyFormat($employee->weekly_salary);
                                elseif($state == 'Bulanan')
                                    $salary = moneyFormat($employee->monthly_salary);
                                else
                                    $salary = 0;
                                $set('amount',number_format($salary, 0, ',','.'));
                                $this->calculate_total_salary($get,$set);
                            })
                            ->native(false)
                            ->required(),
                        DatePicker::make('payroll_date')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        TextInput::make('amount')
                            ->label('Nominal Gaji')
                            ->live(debounce: 1000)
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->afterStateUpdated(fn($state, callable $get, callable $set)=>$this->calculate_total_salary($get,$set))
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Pinjaman')
                    ->aside()
                    ->schema([
                        Placeholder::make('remaining_loan')
                            ->label('Sisa Pinjaman')
                            ->content(function() use($remaining_loan){
                                return 'Rp '.number_format($remaining_loan,0,',','.');
                            }),
                        TextInput::make('amount_loan_repayment')
                            ->label('Pembayaran Pinjaman')
                            ->required()
                            ->live(debounce: 1000)
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->maxValue($remaining_loan*100)
                            ->disabled(fn() => $remaining_loan <= 0)
                            ->afterStateUpdated(fn($state, callable $get, callable $set)=>$this->calculate_total_salary($get,$set))
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Total Gaji')
                    ->description('Gaji yang harus dibayarkan')
                    ->aside()
                    ->schema([
                        TextInput::make('amount_after_loan_repayment')
                            ->label('Total Gaji')
                            ->required()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->readOnly()
                            ->default(0)
                            ->minValue(0),
                    ])
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payroll_date')
            ->columns([
                TextColumn::make('payroll_date')
                    ->label('Tanggal Pengggajian')
                    ->date('d F Y'),
                TextColumn::make('type')
                    ->label('Jenis'),
                TextColumn::make('amount')
                    ->label('Nominal Gaji')
                    ->currency('IDR'),
                TextColumn::make('amount_loan_repayment')
                    ->label('Pembayaran Pinjaman')
                    ->currency('IDR'),
                TextColumn::make('amount_after_loan_repayment')
                    ->label('Total Gaji')
                    ->currency('IDR'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Penggajian')
                    ->modalHeading('Penggajian Baru')
                    ->after(function($record){
                        if($record->amount_loan_repayment > 0){
                            $repayment = new EmployeeLoanRepayment;
                            $repayment->employee_id = $record->employee_id;
                            $repayment->employee_payroll_id = $record->id;
                            $repayment->repayment_date = $record->payroll_date;
                            $repayment->amount = $record->amount_loan_repayment;
                            $repayment->notes = 'Dibayarkan saat gaji '.$record->type.' tanggal '.$record->payroll_date->format('d F Y').'.';
                            $repayment->save();
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('payroll_slip')
                        ->label('Payroll Slip')
                        ->color(Color::generateV3Palette('#b829ffff'))
                        ->icon('https://img.icons8.com/color/96/bill.png')
                        ->modalHeading('Payroll Slip')
                        ->modalSubmitAction(false) // nggak ada tombol "Submit"
                        ->modalCancelActionLabel('Tutup')
                        ->schema(function($record){
                            $pdf = new GeneratePDFPayrollSlip($record);

                            return [
                                ViewField::make('preview')
                                    ->view('components.file-preview')
                                    ->viewData([
                                        'fileUrl' => $pdf->getPDF(),
                                    ]),
                            ];
                        }),
                    DeleteAction::make()
                        ->after(function($record){
                            $repayment = EmployeeLoanRepayment::where('employee_payroll_id',$record->id)->first();


                            if($repayment instanceof EmployeeLoanRepayment){
                                $repayment->delete();
                            }
                        }),
                    RestoreAction::make()
                        ->after(function($record){
                            $repayment = EmployeeLoanRepayment::onlyTrashed()->where('employee_payroll_id',$record->id)->first();
                            if($repayment instanceof EmployeeLoanRepayment){
                                $repayment->restore();
                            }
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    protected function calculate_total_salary($get, $set)
    {
        // dd(clean_numeric($get('amount')) - clean_numeric($get('amount_loan_repayment')));
        $total = clean_numeric($get('amount')) - clean_numeric($get('amount_loan_repayment'));

        return $set('amount_after_loan_repayment',number_format($total,env('MONEY_DECIMAL_DIGITS'),',','.'));
    }
}
