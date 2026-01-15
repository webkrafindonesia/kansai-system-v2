<?php

namespace App\Filament\Resources\Employees;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Employees\RelationManagers\PayrollsRelationManager;
use App\Filament\Resources\Employees\RelationManagers\LoansRelationManager;
use App\Filament\Resources\Employees\RelationManagers\LoanRepaymentsRelationManager;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use DateTime;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/worker-male--v1.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Karyawan';

    protected static ?string $label = 'Karyawan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Biodata')
                    ->description('Biodata Karyawan')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                        DatePicker::make('birth_date')
                            ->native(false),
                        Placeholder::make('age')
                            ->label('Umur')
                            ->content(function(callable $get){
                                if($get('birth_date')){
                                    $birth_date = new DateTime($get('birth_date'));
                                    $current_date = new DateTime(date('Y-m-d'));
                                    $diff = $current_date->diff($birth_date);
                                    return $diff->y.' tahun';
                                }
                            }),
                        DatePicker::make('join_date')
                            ->native(false),
                        Placeholder::make('anniversary_working')
                            ->label('Lama Kerja')
                            ->content(function(callable $get){
                                if($get('join_date')){
                                    $join_date = new DateTime($get('join_date'));
                                    $current_date = new DateTime(date('Y-m-d'));
                                    $diff = $current_date->diff($join_date);
                                    return $diff->y.' tahun';
                                }
                            }),
                        Radio::make('gender')
                            ->options(function(){
                                return [
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ];
                            })
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Salary')
                    ->aside()
                    ->schema([
                        TextInput::make('weekly_salary')
                            ->required()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->default(0),
                        TextInput::make('monthly_salary')
                            ->required()
                            ->prefix('Rp')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',',precision: 0)
                            ->default(0),
                    ])
                    ->columns(2),

            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('join_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PayrollsRelationManager::class,
            LoansRelationManager::class,
            LoanRepaymentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
