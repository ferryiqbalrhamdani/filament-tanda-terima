<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Company;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SuratTandaTerima;
use Filament\Resources\Resource;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratTandaTerimaResource\Pages;
use App\Filament\Resources\SuratTandaTerimaResource\RelationManagers;

class SuratTandaTerimaResource extends Resource
{
    protected static ?string $model = SuratTandaTerima::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship(name: 'company', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->disabled(fn($livewire) => $livewire instanceof Pages\EditSuratTandaTerima) // Lock in edit mode
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $tanggal = $get('tanggal');
                                if ($state && $tanggal) {
                                    $nomorDocument = SuratTandaTerima::generateNomorDocument($state, $tanggal);
                                    $set('nomor_document', $nomorDocument);
                                }
                            }),
                        Forms\Components\TextInput::make('kepada')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_document')
                            ->label('Nomor Tanda Terima')
                            ->unique(SuratTandaTerima::class, 'nomor_document', ignoreRecord: true)
                            ->required()
                            ->readOnly() // Set this to disabled to prevent manual editing
                            ->helperText('Nomor Tanda Terima Akan Otomatis Dibuat')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $companyId = $get('company_id');
                                if ($companyId && $state) {
                                    // Fetch the latest document for the company
                                    $latestDocument = SuratTandaTerima::where('company_id', $companyId)
                                        ->orderBy('tanggal', 'desc')
                                        ->first();

                                    // If a previous document exists, validate the new date
                                    if ($latestDocument && Carbon::parse($state)->lt($latestDocument->tanggal)) {
                                        $set('tanggal', null); // Reset the date if invalid
                                        Notification::make()
                                            ->title('Error')
                                            ->body('Tanggal tidak boleh lebih kecil dari nomor surat sebelumnya.')
                                            ->danger()
                                            ->send();
                                    } else {
                                        $nomorDocument = SuratTandaTerima::generateNomorDocument($companyId, $state);
                                        $set('nomor_document', $nomorDocument);
                                    }
                                }
                            }),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        self::getItemsRepeater(),
                    ]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('total')
                            ->readOnly()
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_document')
                    ->label('Nomor Tanda Terima')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kepada')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('date_transaction')
                    ->form([
                        Forms\Components\DatePicker::make('date_transaction_from'),
                        Forms\Components\DatePicker::make('date_transaction_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_transaction_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['date_transaction_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_transaction_from'] ?? null) {
                            $indicators['date_transaction_from'] = 'Order from ' . Carbon::parse($data['date_transaction_from'])->toFormattedDateString();
                        }
                        if ($data['date_transaction_until'] ?? null) {
                            $indicators['date_transaction_until'] = 'Order until ' . Carbon::parse($data['date_transaction_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),



            ])
            ->defaultSort('nomor_document', 'desc')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ])->tooltip('Actions'),
                Tables\Actions\Action::make('print')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(SuratTandaTerima $record) => route('print.surat_tanda_terima', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->label('')
            ->relationship()
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Textarea::make('keterangan')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('nomor_document')
                                    ->label('Nomor Dokumen'),
                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->minValue(1)
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('satuan'),
                            ])->columns(3),
                    ]),
            ])

            ->reorderable()
            ->collapsible()
            ->columnSpan(3)
            ->live()
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                self::updateTotal($get, $set);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }




    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor_document', 'company.name', 'kepada',];
    }



    protected static function updateTotal(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('items'));

        $total = $selectedProducts->reduce(function ($total, $product) {
            return $total + ($product['qty']);
        }, 0);

        $set('total', $total);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuratTandaTerimas::route('/'),
            'create' => Pages\CreateSuratTandaTerima::route('/create'),
            'edit' => Pages\EditSuratTandaTerima::route('/{record}/edit'),
        ];
    }
}
