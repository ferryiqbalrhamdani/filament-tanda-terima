<?php

namespace App\Filament\Resources\SuratTandaTerimaResource\Pages;

use App\Filament\Resources\SuratTandaTerimaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratTandaTerima extends EditRecord
{
    protected static string $resource = SuratTandaTerimaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
