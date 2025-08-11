<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'المبيعات';

    protected function getData(): array
    {
        $data = Purchase::where('payment_status', 'approved')
            ->whereBetween('created_at', [
                Carbon::now()->subDays(30),
                Carbon::now(),
            ])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#10B981',
                ],
            ],
            'labels' => $data->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
} 