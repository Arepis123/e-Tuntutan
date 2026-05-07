<?php

namespace App\Livewire\Claims;

use App\Models\Claim;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('components.layouts.app')]
#[Title('Senarai Tuntutan')]
class ClaimList extends Component
{
    use WithPagination;

    #[Url(as: 'cari')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'jenis')]
    public string $typeFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $query = Claim::with(['worker', 'user', 'documents'])
            ->when($user->hasRole('employer'), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, fn ($q) => $q->whereHas('worker', fn ($wq) => $wq->where('name', 'like', "%{$this->search}%")
                ->orWhere('passport_number', 'like', "%{$this->search}%"))
                ->orWhere('claim_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('claim_type', $this->typeFilter))
            ->latest();

        $claims = $query->paginate(15);

        $baseQuery = Claim::when($user->hasRole('employer'), fn ($q) => $q->where('user_id', $user->id));
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'open'        => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'closed'      => (clone $baseQuery)->where('status', 'closed')->count(),
        ];

        $view = $user->hasRole('employer')
            ? 'livewire.claims.claim-list-client'
            : 'livewire.claims.claim-list';

        return view($view, compact('claims', 'stats'));
    }
}
