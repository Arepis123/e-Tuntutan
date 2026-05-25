<?php

namespace App\Livewire\Configuration;

use App\Models\DocumentConfig;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
#[Title('Document Configuration')]
class DocumentConfigPage extends Component
{
    use WithFileUploads;

    public string $activeTab = 'fwhs';

    // Upload/edit state
    public ?int $uploadingId = null;
    public $uploadFile = null;
    public string $uploadLabel = '';

    // Add document state
    public string $newLabel          = '';
    public string $newDocumentType   = '';
    public array  $newClaimCategory  = ['hospitalization'];
    public array  $newIncidentType   = ['accident', 'non_accident'];
    public bool   $newIsRequired     = true;
    public bool   $newIsDownloadable = false;
    public $newFile                  = null;

    public function toggle(int $id, string $field): void
    {
        $this->authorize('configuration.manage');
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
        $this->authorize('configuration.manage');
        $this->validate([
            'uploadLabel' => 'required|string|max:255',
            'uploadFile'  => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $config  = DocumentConfig::findOrFail($this->uploadingId);
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

    public function openAddDocument(): void
    {
        $this->reset('newLabel', 'newDocumentType');
        $this->newClaimCategory  = ['hospitalization'];
        $this->newIncidentType   = ['accident', 'non_accident'];
        $this->newIsRequired     = true;
        $this->newIsDownloadable = false;
        $this->newFile           = null;
        $this->modal('add-doc')->show();
    }

    public function saveDocument(): void
    {
        $this->authorize('configuration.manage');
        $rules = [
            'newLabel'           => 'required|string|max:255',
            'newDocumentType'    => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'newClaimCategory'   => 'required|array|min:1',
            'newClaimCategory.*' => 'in:hospitalization,death',
            'newFile'            => 'nullable|file|mimes:pdf|max:10240',
        ];

        if (in_array('hospitalization', $this->newClaimCategory)) {
            $rules['newIncidentType']    = 'required|array|min:1';
            $rules['newIncidentType.*']  = 'in:accident,non_accident';
        }

        $this->validate($rules);

        $filePath = $this->newFile?->store('documents', 'public');

        $count = 0;
        foreach ($this->newClaimCategory as $category) {
            $incidentTypes = $category === 'death' ? [null] : $this->newIncidentType;

            foreach ($incidentTypes as $incidentType) {
                $maxOrder = DocumentConfig::where('claim_type', $this->activeTab)
                    ->where('claim_category', $category)
                    ->where('incident_type', $incidentType)
                    ->max('sort_order') ?? 0;

                DocumentConfig::create([
                    'claim_type'      => $this->activeTab,
                    'claim_category'  => $category,
                    'incident_type'   => $incidentType,
                    'document_type'   => $this->newDocumentType,
                    'label'           => $this->newLabel,
                    'is_required'     => $this->newIsRequired,
                    'is_downloadable' => $this->newIsDownloadable,
                    'file_path'       => $filePath,
                    'sort_order'      => $maxOrder + 1,
                ]);
                $count++;
            }
        }

        $this->modal('add-doc')->close();
        $this->dispatch('notify', message: "Document added to {$count} group(s) successfully.");
    }

    public function deleteDocument(int $id): void
    {
        $this->authorize('configuration.manage');
        $config = DocumentConfig::findOrFail($id);

        if ($config->file_path && str_starts_with($config->file_path, 'documents/')) {
            Storage::disk('public')->delete($config->file_path);
        }

        $config->delete();
        $this->dispatch('notify', message: 'Document removed.');
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
