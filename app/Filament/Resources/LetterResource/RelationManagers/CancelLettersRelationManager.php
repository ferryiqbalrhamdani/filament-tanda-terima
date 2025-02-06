<?php

namespace App\Filament\Resources\LetterResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CancelLetter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;

class CancelLettersRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;

    protected static string $relationship = 'cancelLetters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('letter.letter_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('reason')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('letter.letter_number')
                    ->label('Nomor Surat'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Keterangan'),
                Tables\Columns\TextColumn::make('pic.name')
                    ->label('PIC'),
                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->date(),
                Tables\Columns\TextColumn::make('file')
                    ->limit(14)
                    ->icon('heroicon-o-document-text')
                    ->tooltip(fn(CancelLetter $record): ?string => $record->file ?  basename($record->file) : null)
                    ->default(fn(CancelLetter $record): ?string => $record->file ? basename($record->file) : 'Tidak ada file')
                    ->url(fn(CancelLetter $record): ?string => $record->file ? asset('storage/' . $record->file) : null)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
