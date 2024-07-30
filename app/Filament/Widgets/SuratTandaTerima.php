<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\SuratTandaTerima as ModelsSuratTandaTerima;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuratTandaTerima extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tanda Terima', ModelsSuratTandaTerima::count())
                ->chart([
                    1, 4, 3, 10, 15, 11, 20
                ]),
            Stat::make('Jumlah Perusahaan', Company::count()),
            Stat::make('Jumlah Pengguna', User::count()),
        ];
    }
}
