<?php

namespace App\Filament\Resources\PettyCashes;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\PettyCashes\Pages\ManagePettyCashes;
use App\Filament\Resources\PettyCashes\Widgets\TotalPettyCash;
use App\Filament\Resources\PettyCashResource\Pages;
use App\Filament\Resources\PettyCashResource\RelationManagers;
use App\Filament\Resources\PettyCashResource\Widgets;
use App\Models\PettyCash;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Support\RawJs;

class PettyCashResource extends Resource implements HasActions
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static ?string $model = PettyCash::class;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/get-cash.png';

    protected static string | \UnitEnum | null $navigationGroup = 'Kas Kecil';

    protected static ?string $title = 'Kas Kecil';

    protected static ?string $label = 'Kas Kecil';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi')
                    ->description('Informasi Kas Kecil')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama / Deskripsi')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('trx_date')
                            ->label('Tanggal')
                            ->default(now())
                            ->maxDate(now())
                            ->native(false)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Nominal')
                    ->description('Informasi Kas Kecil')
                    ->aside()
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Transaksi')
                            ->required()
                            ->options([
                                'Pemasukan' => 'Pemasukan',
                                'Pengeluaran' => 'Pengeluaran',
                            ])
                            ->default('Pemasukan')
                            ->native(false)
                            ->live(),
                        TextInput::make('trx_in')
                            ->label('Pemasukan')
                            ->prefix('Rp')
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
                            ->default(0)
                            ->requiredIf('type','Pemasukan')
                            ->minValue(0)
                            ->visible(fn(callable $get)=>$get('type') == 'Pemasukan'),
                        TextInput::make('trx_out')
                            ->label('Pengeluaran')
                            ->prefix('Rp')
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
                            ->default(0)
                            ->requiredIf('type','Pengeluaran')
                            ->minValue(0)
                            ->visible(fn(callable $get)=>$get('type') == 'Pengeluaran'),
                    ])
                    ->columns(2),
                Section::make('Bukti Transaksi')
                    ->aside()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->helperText('File yang diijinkan: jpg, jpeg, png')
                            ->openable()
                            ->image()
                            ->collection('pettycash')
                            ->disk('minio')
                            ->conversion('jpg')
                            ->skipRenderAfterStateUpdated(),
                    ])
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama / Deskripsi')
                    ->searchable(),
                TextColumn::make('trx_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('trx_in')
                    ->label('Pemasukan')
                    ->currency('IDR'),
                TextColumn::make('trx_out')
                    ->label('Pengeluaran')
                    ->currency('IDR'),
            ])
            ->defaultSort('trx_date','desc')
            ->filters([
                TrashedFilter::make(),
                Filter::make('trx_date_filter')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Awal'),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function ( $query, $data){
                        return $query
                            ->when(
                                $data['startDate'] ?? null,
                                fn ( $query, $date)=> $query->whereDate('trx_date', '>=', $date),
                            )
                            ->when(
                                $data['endDate'] ?? null,
                                fn ( $query, $date)=> $query->whereDate('trx_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $indicators = [];

                        if ($data['startDate'] ?? null) {
                            $indicators['startDate'] = 'Trans. dari ' . Carbon::parse($data['startDate'])->toFormattedDateString();
                        }
                        if ($data['endDate'] ?? null) {
                            $indicators['endDate'] = 'Trans. hingga ' . Carbon::parse($data['endDate'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePettyCashes::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TotalPettyCash::class,
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
