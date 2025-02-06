<?php

namespace App\Filament\Resources\LetterResource\Pages;

use App\Filament\Resources\LetterResource;
use App\Models\Company;
use App\Models\Letter;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLetters extends ListRecords
{
    protected static string $resource = LetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Nomor Surat'),
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
            ->badge(fn() => Letter::count());

        // Get companies, excluding specific slugs and names
        $companies = Company::orderBy('name', 'asc')
            ->get();

        foreach ($companies as $company) {
            $data[$company->slug] = Tab::make($company->slug)
                ->modifyQueryUsing(fn(Builder $query) => $query->where('company_id', $company->id))
                ->badge(fn() => Letter::where('company_id', $company->id)->count());
        }

        return $data;
    }
}
