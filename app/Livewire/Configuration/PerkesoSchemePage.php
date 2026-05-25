<?php

namespace App\Livewire\Configuration;

use App\Models\PerkesoScheme;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('PERKESO Categories')]
class PerkesoSchemePage extends Component
{
    public string $newSchemeLabel = '';
    public string $newSchemeValue = '';

    public function openAddScheme(): void
    {
        $this->reset('newSchemeLabel', 'newSchemeValue');
        $this->modal('add-perkeso-scheme')->show();
    }

    public function saveScheme(): void
    {
        $this->authorize('configuration.manage');
        $this->validate([
            'newSchemeLabel' => 'required|string|max:100',
            'newSchemeValue' => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:perkeso_schemes,value',
        ]);

        $maxOrder = PerkesoScheme::max('sort_order') ?? 0;

        PerkesoScheme::create([
            'label'      => $this->newSchemeLabel,
            'value'      => $this->newSchemeValue,
            'sort_order' => $maxOrder + 1,
        ]);

        $this->reset('newSchemeLabel', 'newSchemeValue');
        $this->modal('add-perkeso-scheme')->close();
        $this->dispatch('notify', message: 'PERKESO category added.');
    }

    public function toggleScheme(int $id): void
    {
        $this->authorize('configuration.manage');
        $scheme = PerkesoScheme::findOrFail($id);
        $scheme->update(['is_active' => ! $scheme->is_active]);

        $this->dispatch('notify', message: $scheme->is_active
            ? "'{$scheme->label}' is now active."
            : "'{$scheme->label}' is now inactive."
        );
    }

    public function render()
    {
        return view('livewire.configuration.perkeso-scheme-page', [
            'schemes' => PerkesoScheme::ordered()->get(),
        ]);
    }
}
