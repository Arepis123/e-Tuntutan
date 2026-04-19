<?php

namespace App\Livewire\Payments;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('components.layouts.app')]
#[Title('Payments')]
class PaymentList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        $payments = Payment::with(['claim.worker'])
            ->when($this->search, function ($q) {
                $q->whereHas('claim', function ($q) {
                    $q->where('claim_number', 'like', "%{$this->search}%")
                      ->orWhereHas('worker', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
                })->orWhere('beneficiary_name', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.payments.payment-list', compact('payments'));
    }
}
