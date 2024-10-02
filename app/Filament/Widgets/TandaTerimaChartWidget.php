<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\SuratTandaTerima;
use Filament\Widgets\ChartWidget;

class TandaTerimaChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tanda Terima Chart';

    public ?string $filter = 'year'; // Default filter set to 'year'

    protected int | string | array $columnSpan = 'full';

    protected static string $color = 'info';

    protected static ?string $maxHeight = '300px';

    /**
     * Define the filters for the widget.
     */
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Terakhir',
            'month' => 'Bulan Terakhir',
            'year' => 'Tahun Ini',
        ];
    }

    /**
     * Get the chart data based on the selected filter.
     */
    protected function getData(): array
    {
        // Get the active filter (default is 'year')
        $activeFilter = $this->filter;

        // Determine the date range based on the active filter
        $dateRange = $this->getDateRangeByFilter($activeFilter);

        // Fetch the data based on the selected filter
        $dataQuery = Trend::model(SuratTandaTerima::class)
            ->dateColumn('tanggal')
            ->between(
                start: $dateRange['start'],
                end: $dateRange['end'],
            );

        // Apply perHour for 'today', perDay for other filters, and perMonth for 'year'
        if ($activeFilter === 'today') {
            $data = $dataQuery->perHour()->count();
        } elseif ($activeFilter === 'year') {
            $data = $dataQuery->perMonth()->count();
        } else {
            $data = $dataQuery->perDay()->count();
        }


        // Format the data for the chart
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tanda Terima',
                    'data' => $data->map(fn(TrendValue $value) => intval($value->aggregate)),
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $this->formatLabel($value->date, $activeFilter)),
        ];
    }

    /**
     * Helper method to determine the date range based on the filter.
     */
    protected function getDateRangeByFilter(string $filter): array
    {
        switch ($filter) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
            case 'year':
            default:
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear(),
                ];
        }
    }

    /**
     * Helper method to format the label based on the filter.
     */
    protected function formatLabel(string $date, string $filter): string
    {
        if ($filter === 'year') {
            return Carbon::parse($date)->format('M'); // Format as month for year filter
        } elseif ($filter === 'today') {
            return Carbon::parse($date)->format('H:i'); // Format as hour for today filter
        }

        return Carbon::parse($date)->format('d M'); // Format as day and month for other filters
    }

    /**
     * Define the type of chart.
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Get the description for the chart widget.
     */
    public function getDescription(): ?string
    {
        return 'Angka dari tanda terima berdasarkan ' . $this->getFilters()[$this->filter];
    }
}
