<?php

namespace App\Livewire;

use App\Models\Claim;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $query = Claim::query();

        if ($user->hasRole('employer')) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'open'        => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed'      => (clone $query)->where('status', 'closed')->count(),
            'total'       => (clone $query)->count(),
        ];

        $recentClaims = (clone $query)
            ->with(['worker', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard', compact('stats', 'recentClaims'));
    }
}
