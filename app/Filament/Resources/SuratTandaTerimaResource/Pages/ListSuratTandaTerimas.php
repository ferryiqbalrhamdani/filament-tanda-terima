<?php

namespace App\Filament\Resources\SuratTandaTerimaResource\Pages;

use Filament\Actions;
use App\Models\Company;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratTandaTerimaResource;

class ListSuratTandaTerimas extends ListRecords
{
    protected static string $resource = SuratTandaTerimaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Surat Tanda Terima'),
        ];
    }

    public function getTabs(): array
    {

        $data = [];

        $companies = Company::orderBy('name', 'asc')->get();
        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('company_id', $company->id));
        }
        return $data;
    }
}
