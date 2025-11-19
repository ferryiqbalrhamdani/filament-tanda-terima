<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Letter;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Actions\StaticAction;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\LetterResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LetterResource\RelationManagers;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;

class LetterResource extends Resource
{
    protected static ?string $model = Letter::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Penomoran';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make()
                //     ->schema([
                //         self::getItemsRepeater(),
                //     ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_surat')
                            ->visibleOn('create')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->visibleOn('edit')
                            ->disabled(),
                        Forms\Components\Select::make('company_id')
                            ->relationship(name: 'company', titleAttribute: 'slug')
                            ->disabled(fn(string $operation) => $operation === 'edit')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('pic_id')
                            ->label('PIC')
                            ->relationship(name: 'pic', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->modalHeading('Buat PIC ')
                                    ->modalSubmitActionLabel('Buat PIC ')
                                    ->modalWidth('lg');
                            }),
                        Forms\Components\Grid::make(2) // Menggunakan Grid untuk membuat 2 kolom
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_surat')
                                    ->timezone('Asia/Jakarta')
                                    ->native(false)
                                    ->required()
                                    ->default(now())
                                    ->reactive()
                                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                                    ->maxDate(now()),
                                Forms\Components\TextInput::make('title')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\FileUpload::make('file')
                            ->columnSpanFull()
                            ->getUploadedFileNameForStorageUsing(
                                fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                    ->prepend('file-'),
                            )
                            ->visibleOn('edit'),
                        Textarea::make('content')
                            ->columnSpanFull()
                            ->rows(5),
                    ])
                    ->columns(3), // Jumlah kolom utama tetap 3
            ]);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('')
            ->label('')
            ->relationship()
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Grid::make(2) // Menggunakan Grid untuk membuat 2 kolom
                            ->schema([
                                Forms\Components\Select::make('company_id')
                                    ->relationship(name: 'company', titleAttribute: 'slug')
                                    ->visibleOn('create')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->required(),
                                Forms\Components\Select::make('pic_id')
                                    ->label('PIC')
                                    ->relationship(name: 'pic', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Buat PIC ')
                                            ->modalSubmitActionLabel('Buat PIC ')
                                            ->modalWidth('lg');
                                    }),
                            ]),
                        Forms\Components\Grid::make(2) // Menggunakan Grid untuk membuat 2 kolom
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_surat')
                                    ->timezone('Asia/Jakarta')
                                    ->native(false)
                                    ->required()
                                    ->default(now())
                                    ->reactive()
                                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                                    ->maxDate(now()),
                                Forms\Components\TextInput::make('title')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\FileUpload::make('file')
                            ->columnSpanFull()
                            ->visibleOn('edit')
                            ->getUploadedFileNameForStorageUsing(
                                fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                    ->prepend('file-'),
                            ),
                        Textarea::make('content')
                            ->columnSpanFull()
                            ->rows(5),
                    ]),
            ])
            ->defaultItems(1)
            ->reorderable()
            ->minItems(1)
            ->collapsible()
            ->columnSpan(3)
        ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.slug')
                    ->label('Perusahaan')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pic.name')
                    ->label('PIC')
                    ->default('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->prefix('● ')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'terpakai' => 'success',
                        'tidak terpakai' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('file')
                    ->limit(14)
                    ->searchable()
                    ->icon('heroicon-o-document-text')
                    ->tooltip(fn(Letter $record): ?string => $record->file ?  basename($record->file) : null)
                    ->default(fn(Letter $record): ?string => $record->file ? basename($record->file) : 'Tidak ada file')
                    ->url(fn(Letter $record): ?string => $record->file ? asset('storage/' . $record->file) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->button()
                    ->tooltip('Edit')
                    ->disabled(fn(Letter $record): bool => $record->status === 'tidak terpakai'),
                Tables\Actions\Action::make('Batalkan Nomor Surat')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->modalDescription(function (Letter $record): string {
                        return 'Apakah Anda yakin ingin membatalkan Nomor Surat ' . $record->letter_number . '?';
                    })
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5),
                    ])
                    ->color('danger')
                    ->hiddenLabel()
                    ->button()
                    ->tooltip('Batalkan Nomor Surat')
                    ->action(function (Letter $record, array $data): void {
                        // dd($record, $data);

                        $record->cancelLetters()->create([
                            'reason' => $data['reason'],
                            'pic_id' =>  $record->pic_id,
                            'tanggal_surat' =>  $record->tanggal_surat,
                            'letter_number' =>  $record->letter_number,
                            'title' =>  $record->title,
                            'file' =>  $record->file,
                            'content' =>  $record->content,
                        ]);

                        $record->status = 'tidak terpakai';
                        $record->pic_id = null;
                        $record->title = null;
                        $record->file = null;
                        $record->content = null;
                        $record->save();

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Letter $record): bool => $record->status !== 'tidak terpakai'),
                Tables\Actions\Action::make('Gunakan Nomor Surat')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->slideOver()
                    ->modal()
                    ->color('success')
                    ->hiddenLabel()
                    ->button()
                    ->tooltip('Gunakan Nomor Surat')
                    ->closeModalByClickingAway(false)
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalWidth(MaxWidth::TwoExtraLarge)
                    ->form([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\View::make('forms.components.letter-number')
                                // Forms\Components\Placeholder::make('letter_number')
                                //     ->label('Nomor Surat')
                                //     ->content(fn(Letter $record): string => $record->letter_number)
                            ]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_surat')
                                    ->timezone('Asia/Jakarta')
                                    ->native(false)
                                    ->required(),
                                Forms\Components\Select::make('pic_id')
                                    ->label('PIC')
                                    ->relationship(name: 'pic', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Buat PIC ')
                                            ->modalSubmitActionLabel('Buat PIC ')
                                            ->modalWidth('lg');
                                    }),
                                Forms\Components\TextInput::make('title')
                                    ->columnSpanFull()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('file')
                                    ->columnSpanFull()
                                    ->getUploadedFileNameForStorageUsing(
                                        fn(TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                            ->prepend('file-'),
                                    ),
                                Textarea::make('content')
                                    ->columnSpanFull()
                                    ->rows(5),
                            ])
                            ->columns(2),
                    ])
                    ->action(function (Letter $record, array $data): void {
                        $tanggal = \Carbon\Carbon::parse($data['tanggal_surat']);
                        $bulanRomawi = Letter::getRomanMonth($tanggal->month);
                        $tahun = $tanggal->year;

                        $parts = explode('/', $record->letter_number);
                        $nomorUrut = $parts[0];
                        $namaPt = $parts[1];

                        // Buat ulang nomor surat
                        $newLetterNumber = "{$nomorUrut}/{$namaPt}/{$bulanRomawi}/{$tahun}";

                        $record->status = 'terpakai';
                        $record->letter_number = $newLetterNumber;
                        $record->tanggal_surat = $data['tanggal_surat'];
                        $record->pic_id = $data['pic_id'];
                        $record->title = $data['title'];
                        $record->file = $data['file'];
                        $record->content = $data['content'];
                        $record->save();

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Letter $record): bool => $record->status === 'tidak terpakai'),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->hiddenLabel()
                        ->closeModalByClickingAway(false)
                        ->stickyModalHeader()
                        ->stickyModalFooter()
                        ->modalWidth(MaxWidth::SixExtraLarge),
                    RelationManagerAction::make('history')
                        ->slideOver()
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(StaticAction $action) => $action->label('Tutup'))
                        ->icon('heroicon-o-clock')
                        ->relationManager(RelationManagers\CancelLettersRelationManager::make())
                        ->color('gray'),
                ])
                    ->tooltip('Action'),
            ])
            ->filters([
                DateRangeFilter::make('tanggal_surat')
                    ->label('Tanggal Surat'),

                // Filter berdasarkan status
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'terpakai' => 'Terpakai',
                                'tidak terpakai' => 'Tidak Terpakai',
                            ])
                            ->placeholder('Pilih Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Terapkan filter hanya jika 'status' diatur dan tidak kosong
                        if (isset($data['status']) && $data['status'] !== '') {
                            $query->where('status', $data['status']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        // Jika 'status' tidak diatur atau kosong, kembalikan indikator kosong
                        if (!isset($data['status']) || $data['status'] === '') {
                            return [];
                        }

                        $statusLabels = [
                            'terpakai' => 'Terpakai',
                            'tidak terpakai' => 'Tidak Terpakai',
                        ];

                        return ['Status: ' . $statusLabels[$data['status']]];
                    }),

                Tables\Filters\Filter::make('company_id')
                    ->form([
                        Forms\Components\Select::make('company_id')
                            ->label('Perusahaan')
                            ->multiple()
                            ->options(fn() => \App\Models\Company::pluck('slug', 'id'))
                            ->searchable()
                            ->placeholder('Pilih Perusahaan'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['company_id'])) {
                            $query->whereIn('company_id', $data['company_id']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        if (empty($data['company_id'])) {
                            return [];
                        }
                        $companyNames = \App\Models\Company::whereIn('id', $data['company_id'])->pluck('slug')->toArray();
                        return ['Perusahaan: ' . implode(', ', $companyNames)];
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('tanggal_surat')
                    ->collapsible()
                    ->date()
                    ->label('Tanggal Surat'),
                Tables\Grouping\Group::make('pic.name')
                    ->collapsible()
                    ->label('PIC'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CancelLettersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLetters::route('/'),
            'create' => Pages\CreateLetter::route('/create'),
            'edit' => Pages\EditLetter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Nomor Surat';
    }

    public static function getModelLabel(): string
    {
        return 'Nomor Surat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Nomor Surat';
    }

    public function getTitle(): string
    {
        return 'Nomor Surat';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('letter_number')
                            ->copyable()
                            ->label('Nomor Surat'),
                        TextEntry::make('tanggal_surat')
                            ->date()
                            ->label('Tanggal Surat'),
                        TextEntry::make('company.slug')
                            ->badge()
                            ->label('Perusahaan'),
                        TextEntry::make('pic.name')
                            ->default('-')
                            ->label('PIC'),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->prefix('● ')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'terpakai' => 'success',
                                        'tidak terpakai' => 'danger',
                                    }),
                                TextEntry::make('title')
                                    ->label('Judul Surat')
                                    ->default('-'),
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime(),

                            ]),
                    ])
                    ->columns(4),
                Fieldset::make('File')
                    ->schema([
                        TextEntry::make('file')
                            ->icon('heroicon-o-document-text')
                            ->default(fn(Letter $record): ?string => $record->file ? basename($record->file) : 'Tidak ada file')
                            ->url(fn(Letter $record): ?string => $record->file ? asset('storage/' . $record->file) : null)
                            ->openUrlInNewTab()
                            ->hiddenLabel(),
                    ]),
                Fieldset::make('Keterangan')
                    ->schema([
                        TextEntry::make('content')
                            ->default('-')
                            ->hiddenLabel(),
                    ]),
            ]);
    }
}
