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

    #[Url(as: 'sort')]
    public string $sortBy = 'created_at';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $allowedSorts = [
            'claim_number', 'claim_type', 'claim_category',
            'status', 'in_progress_at', 'created_at',
            'worker_name', 'worker_type', 'employer_name',
        ];
        $sortCol = in_array($this->sortBy, $allowedSorts) ? $this->sortBy : 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query = Claim::with(['worker', 'user', 'documents'])
            ->when($user->hasRole('employer'), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, fn ($q) => $q->whereHas('worker', fn ($wq) => $wq->where('name', 'like', "%{$this->search}%")
                ->orWhere('passport_number', 'like', "%{$this->search}%"))
                ->orWhere('claim_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('claim_type', $this->typeFilter));

        if (in_array($sortCol, ['worker_name', 'worker_type', 'employer_name'])) {
            $query->join('workers', 'claims.worker_id', '=', 'workers.id')
                  ->orderBy('workers.' . match($sortCol) {
                      'worker_name'   => 'name',
                      'worker_type'   => 'worker_type',
                      'employer_name' => 'employer_name',
                  }, $sortDir)
                  ->select('claims.*');
        } elseif ($sortCol === 'in_progress_at') {
            $query
                ->selectRaw(
                    "claims.*, " .
                    "CASE WHEN claims.in_progress_at IS NULL THEN NULL " .
                    "WHEN claims.status = 'closed' THEN DATEDIFF(claims.closed_at, claims.in_progress_at) " .
                    "WHEN claims.status = 'cancelled' THEN DATEDIFF(claims.cancelled_at, claims.in_progress_at) " .
                    "ELSE DATEDIFF(NOW(), claims.in_progress_at) END AS days_in_progress"
                )
                ->orderByRaw("(days_in_progress IS NULL) ASC, days_in_progress {$sortDir}");
        } else {
            $query->orderBy($sortCol, $sortDir);
        }

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
