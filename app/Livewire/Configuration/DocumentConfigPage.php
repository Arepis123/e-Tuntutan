<?php

namespace App\Livewire\Configuration;

use App\Models\DocumentConfig;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
#[Title('Document Configuration')]
class DocumentConfigPage extends Component
{
    use WithFileUploads;

    public string $activeTab = 'fwhs';

    // Upload state
    public ?int $uploadingId = null;
    public $uploadFile = null;
    public string $uploadLabel = '';

    public function toggle(int $id, string $field): void
    {
        $config = DocumentConfig::findOrFail($id);
        $config->update([$field => !$config->$field]);
    }

    public function openUpload(int $id): void
    {
        $config = DocumentConfig::findOrFail($id);
        $this->uploadingId = $id;
        $this->uploadLabel = $config->label;
        $this->uploadFile  = null;
        $this->modal('upload-doc')->show();
    }

    public function saveUpload(): void
    {
        $this->validate([
            'uploadLabel' => 'required|string|max:255',
            'uploadFile'  => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $config = DocumentConfig::findOrFail($this->uploadingId);

        $updates = ['label' => $this->uploadLabel];

        if ($this->uploadFile) {
            if ($config->file_path && str_starts_with($config->file_path, 'documents/')) {
                Storage::disk('public')->delete($config->file_path);
            }
            $updates['file_path'] = $this->uploadFile->store('documents', 'public');
        }

        $config->update($updates);

        $this->uploadingId = null;
        $this->uploadLabel = '';
        $this->uploadFile  = null;
        $this->modal('upload-doc')->close();
        $this->dispatch('notify', message: 'Document updated successfully.');
    }

    public function removeFile(int $id): void
    {
        $config = DocumentConfig::findOrFail($id);

        if ($config->file_path && str_starts_with($config->file_path, 'documents/')) {
            Storage::disk('public')->delete($config->file_path);
        }

        $config->update(['file_path' => null]);
    }

    public function render()
    {
        $claimTypes = [
            'fwhs'       => 'Insurance (FWHS)',
            'green_card' => 'Green Card',
            'perkeso'    => 'PERKESO',
        ];

        $configs = DocumentConfig::where('claim_type', $this->activeTab)
            ->orderBy('claim_category')
            ->orderBy('incident_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($c) => $c->claim_category . '|' . ($c->incident_type ?? 'all'));

        return view('livewire.configuration.document-config-page', compact('configs', 'claimTypes'));
    }
}
