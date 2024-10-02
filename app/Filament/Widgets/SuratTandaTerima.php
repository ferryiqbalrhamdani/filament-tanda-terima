<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\SuratTandaTerima as ModelsSuratTandaTerima;

class SuratTandaTerima extends BaseWidget
{
    protected function getStats(): array
    {
        $tandaTerima = ModelsSuratTandaTerima::query();
        $companies = Company::query();
        $users = User::query();

        // Menghitung berdasarkan kolom tanggal bukan created_at
        $tandaTerima = $this->getMonthlyCounts($tandaTerima, 'tanggal');
        $companies = $this->getMonthlyCounts($companies, 'created_at'); // Company tetap menggunakan created_at
        $users = $this->getMonthlyCounts($users, 'created_at'); // User tetap menggunakan created_at

        return [
            Stat::make('Tanda Terima', ModelsSuratTandaTerima::count())
                ->color(ModelsSuratTandaTerima::count() > 0 ? 'success' : 'gray')
                ->chart($tandaTerima),
            Stat::make('Jumlah Perusahaan', Company::count())
                ->color(Company::count() > 0 ? 'success' : 'gray')
                ->chart($companies),
            Stat::make('Jumlah Pengguna', User::count())
                ->color(User::count() > 0 ? 'success' : 'gray')
                ->chart($users),
        ];
    }

    // Menambahkan parameter $dateColumn untuk memungkinkan perhitungan berdasarkan kolom yang berbeda
    protected function getMonthlyCounts($query, $dateColumn = 'created_at')
    {
        // Ambil 12 bulan terakhir dan inisialisasi array untuk menyimpan jumlah bulanan
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse();

        // Hitung record yang dikelompokkan berdasarkan bulan menggunakan kolom yang ditentukan
        $counts = $query->selectRaw("TO_CHAR($dateColumn, 'YYYY-MM') as month, COUNT(*) as count")
            ->whereBetween($dateColumn, [Carbon::now()->subYear(), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Mengembalikan jumlah per bulan atau 0 jika tidak ada record untuk bulan tertentu
        return $months->map(function ($month) use ($counts) {
            return $counts[$month] ?? 0;
        })->toArray();
    }
}
