<?php

namespace App\Filament\Resources\SuratTandaTerimaResource\Pages;

use Filament\Actions;
use App\Models\Company;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SuratTandaTerimaResource;
use App\Models\SuratTandaTerima;

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
            $data[$company->slug] = Tab::make()->modifyQueryUsing(fn(Builder $query) => $query->where('company_id', $company->id));
        }

        $data = [];

        // Add a tab for all data
        $data['all'] = Tab::make('All')
            ->modifyQueryUsing(fn(Builder $query) => $query)
            ->badge(fn() => SuratTandaTerima::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->where('company_id', $company->id))
                ->badge(fn() => SuratTandaTerima::where('company_id', $company->id)->count());
        }

        return $data;
    }
}
