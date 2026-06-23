<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardStatsService;
use Livewire\Component;

class Index extends Component
{
    public array $stats = [];

    public function mount(
        DashboardStatsService $service
    ): void {
        $this->stats = $service->get();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}