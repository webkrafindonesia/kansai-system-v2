<?php

namespace App\Filament\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms;
use App\Models\SalesOrder;
use Filament\Support\Colors\Color;
use App\Services\GeneratePDFInvoiceSales;
use App\Services\GeneratePDFInvoiceCustomer;
use App\Filament\Resources\SalesOrderResource;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Notifications\Notification;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Invoices extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'https://img.icons8.com/color/96/purchase-order.png';

    protected string $view = 'filament.pages.invoices';

    protected static ?string $title = 'Invoice';

    protected static ?string $label = 'Invoice';

    protected static string | \UnitEnum | null $navigationGroup = 'Dokumen';

    public ?Model $parentRecord = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(SalesOrder::query()
                ->whereNotNull('invoice_no')
            ) // ganti sesuai model
            ->defaultSort('invoice_no','asc')
            ->columns([
                Split::make([
                    TextColumn::make('index')
                        ->rowIndex()
                        ->suffix('.')
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('invoice_no')
                            ->sortable()
                            ->searchable()
                            ->icon('heroicon-m-document-text'),
                        TextColumn::make('invoice_date')
                            ->sortable()
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar-days')
                            ->searchable(),
                    ]),
                    Stack::make([
                        TextColumn::make('customer.name')
                            ->icon('https://img.icons8.com/color/96/budget.png')
                            ->suffix(fn($record)=>($record->discount_sales > $record->discount_company)?'':' 💰'),
                        // Tables\Columns\TextColumn::make('purchaseorder_no')
                        //     ->sortable()
                        //     ->searchable()
                        //     ->icon('https://img.icons8.com/color/96/order-completed.png'),
                    ]),
                    Stack::make([
                        TextColumn::make('sales.name')
                            ->icon('https://img.icons8.com/color/96/collaborator-male--v1.png')
                            ->suffix(fn($record)=>($record->discount_sales > $record->discount_company)?' 💰':''),
                        TextColumn::make('items')
                            ->label('Nominal')
                            ->formatStateUsing(function($record){
                                if($record->discount_sales > $record->discount_company){
                                    $total_price = $record->items()->sum('master_total_price');
                                    $after_discount = $total_price * (100 - $record->discount_sales) / 100;
                                }
                                else{
                                    $total_price = $record->items()->sum('total_price');
                                    $after_discount = $total_price * (100 - $record->discount_company) / 100;
                                }

                                return 'Rp '.numberFormat(moneyFormat($after_discount));
                            })
                            ->html()
                            ->icon('https://img.icons8.com/color/96/us-dollar-circled--v1.png'),
                    ]),
                    Stack::make([
                        TextColumn::make('term_of_payment')
                            ->sortable()
                            ->date('F Y')
                            ->searchable()
                            ->icon('https://img.icons8.com/color/96/hourglass.png'),
                        TextColumn::make('invoice_status')
                            ->color(function($state){
                                return ($state == 'Belum Lunas') ? 'danger' : 'success';
                            })
                            ->description(function($state, $record){
                                if($state == 'Lunas'){
                                    return $record->paid_at->format('d M Y, H:i');
                                }
                            })
                            ->icon('heroicon-m-information-circle')
                    ])
                ]),
                Panel::make([
                    Split::make([
                        TextColumn::make('discount_sales')
                            ->prefix('Diskon: ')
                            ->formatStateUsing(function($record){
                                if($record->discount_sales > $record->discount_company)
                                    return '(Sales) '.$record->discount_sales.' %';
                                else
                                    return '(Company) '.$record->discount_company.' %';
                            }),
                    ])
                ])->collapsible()
            ])
            // ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->filters([
                Filter::make('invoice_date_filter')
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
                    Action::make('Invoice Sales')
                        ->label('Invoice Sales')
                        ->color(Color::generateV3Palette('#ff7272ff'))
                        ->icon('https://img.icons8.com/color/96/bill.png')
                        ->modalHeading('Invoice Sales')
                        ->modalSubmitAction(false) // nggak ada tombol "Submit"
                        ->modalCancelActionLabel('Tutup')
                        ->schema(function($record){
                            $pdf = new GeneratePDFInvoiceSales($record);

                            return [
                                ViewField::make('preview')
                                    ->view('components.file-preview')
                                    ->viewData([
                                        'fileUrl' => $pdf->getPDF(),
                                    ]),
                            ];
                        })
                        ->visible(fn($record)=>!is_null($record->sales_id)),
                    Action::make('Invoice Customer')
                        ->label('Invoice Customer')
                        ->color(Color::generateV3Palette('#b829ffff'))
                        ->icon('https://img.icons8.com/color/96/bill.png')
                        ->modalHeading('Invoice Customer')
                        ->modalSubmitAction(false) // nggak ada tombol "Submit"
                        ->modalCancelActionLabel('Tutup')
                        ->schema(function($record){
                            $pdf = new GeneratePDFInvoiceCustomer($record);

                            return [
                                ViewField::make('preview')
                                    ->view('components.file-preview')
                                    ->viewData([
                                        'fileUrl' => $pdf->getPDF(),
                                    ]),
                            ];
                        }),
                    Action::make('Sales Order')
                        ->icon('https://img.icons8.com/color/96/add-shopping-cart--v1.png')
                        ->url(fn($record)=>SalesOrderResource::getURL('view', ['record' => $record->id]))
                        ->openUrlInNewTab(),
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
                    Action::make('change_date')
                        ->label('Ganti Tanggal')
                        ->color('primary')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Ganti Tanggal')
                        ->modalDescription('Jika Anda mengganti tanggal, sistem akan melakukan generate ulang nomor invoice dan nomor invoice yang lama tidak bisa digunakan lagi.')
                        ->schema([
                            DatePicker::make('invoice_date')
                                ->label('Tanggal Invoice')
                                ->date()
                                ->native(false)
                                ->default(fn($record)=>$record->invoice_date)
                                ->required(),
                        ])
                        ->action(function (array $data, SalesOrder $record) {
                            $invoiceSales = new GeneratePDFInvoiceSales($record);
                            $invoiceSales->generate(true, $data['invoice_date']);

                            $invoiceCustomer = new GeneratePDFInvoiceCustomer($record);
                            $invoiceCustomer->generate(false);

                            Notification::make()
                                ->title('Success')
                                ->body('Tanggal Invoice berhasil diubah dan invoice di-generate ulang.')
                                ->success()
                                ->color('success')
                                ->send();
                        })
                        ->visible(fn($record)=>$record->invoice_status=='Belum Lunas'),
                ])
            ])
            ->toolbarActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
