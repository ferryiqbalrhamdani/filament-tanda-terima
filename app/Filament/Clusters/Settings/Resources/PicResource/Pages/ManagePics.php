<?php

namespace App\Filament\Clusters\Settings\Resources\PicResource\Pages;

use App\Filament\Clusters\Settings\Resources\PicResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePics extends ManageRecords
{
    protected static string $resource = PicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
