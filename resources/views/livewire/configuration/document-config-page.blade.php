<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:text class="text-2xl font-bold mb-2" variant="strong">Configuration</flux:text>
            <flux:subheading>Manage required documents for each claim type and category</flux:subheading>
        </div>
        <flux:button wire:click="openAddDocument" variant="primary" icon="plus">
            Add Document
        </flux:button>
    </div>

    {{-- Claim Type Tabs --}}
    @php
        $tabIcons = ['fwhs' => 'building-office-2', 'green_card' => 'credit-card', 'perkeso' => 'shield-check'];
    @endphp
    <flux:tab.group>
        <flux:tabs wire:model.live="activeTab">
            @foreach ($claimTypes as $key => $label)
            <flux:tab name="{{ $key }}" icon="{{ $tabIcons[$key] ?? 'document' }}">{{ $label }}</flux:tab>
            @endforeach
        </flux:tabs>

        @foreach ($claimTypes as $key => $label)
        <flux:tab.panel name="{{ $key }}">
            <div class="space-y-6 mt-4">
                @forelse ($configs as $groupKey => $docs)
                @php
                    [$category, $incidentType] = explode('|', $groupKey);
                    $groupLabel = match(true) {
                        $category === 'hospitalization' && $incidentType === 'accident'     => 'Hospitalization — Accident',
                        $category === 'hospitalization' && $incidentType === 'non_accident' => 'Hospitalization — Non-Accident',
                        $category === 'death'                                               => 'Death',
                        default                                                             => ucfirst($category),
                    };
                @endphp
                <flux:card class="dark:bg-zinc-900">
                    <flux:heading size="md" class="mb-4">{{ $groupLabel }}</flux:heading>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left">
                                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">Document</th>
                                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 text-center w-28">Required</th>
                                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 text-center w-28">Downloadable</th>
                                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 w-64">File</th>
                                    <th class="pb-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($docs as $doc)
                                <tr wire:key="doc-{{ $doc->id }}">
                                    <td class="py-3 font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $doc->label }}
                                        <span class="ml-2 font-mono text-xs text-zinc-400">{{ $doc->document_type }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <flux:switch
                                            wire:click="toggle({{ $doc->id }}, 'is_required')"
                                            :checked="$doc->is_required"
                                        />
                                    </td>
                                    <td class="py-3 text-center">
                                        <flux:switch
                                            wire:click="toggle({{ $doc->id }}, 'is_downloadable')"
                                            :checked="$doc->is_downloadable"
                                        />
                                    </td>
                                    <td class="py-3">
                                        @if (in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']))
                                            <span class="text-xs text-zinc-400 italic">Auto-generated</span>
                                        @elseif ($doc->is_downloadable)
                                            @if ($doc->file_path)
                                            <div class="flex items-center gap-2">
                                                <flux:icon.document-check class="w-4 h-4 text-green-500 shrink-0" />
                                                <span class="text-xs text-zinc-600 dark:text-zinc-300 truncate max-w-[120px]" title="{{ basename($doc->file_path) }}">
                                                    {{ basename($doc->file_path) }}
                                                </span>
                                                <flux:button
                                                    wire:click="openUpload({{ $doc->id }})"
                                                    size="xs"
                                                    variant="ghost"
                                                    icon="arrow-up-tray"
                                                    title="Replace file"
                                                />
                                                <flux:button
                                                    wire:click="removeFile({{ $doc->id }})"
                                                    wire:confirm="Remove this file?"
                                                    size="xs"
                                                    variant="ghost"
                                                    icon="trash"
                                                    class="text-red-500 hover:text-red-600"
                                                    title="Remove file"
                                                />
                                            </div>
                                            @else
                                            <flux:button
                                                wire:click="openUpload({{ $doc->id }})"
                                                size="xs"
                                                variant="outline"
                                                icon="arrow-up-tray"
                                            >
                                                Upload
                                            </flux:button>
                                            @endif
                                        @else
                                            <span class="text-xs text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <flux:button
                                            wire:click="deleteDocument({{ $doc->id }})"
                                            wire:confirm="Delete this document? This cannot be undone."
                                            size="xs"
                                            variant="ghost"
                                            icon="trash"
                                            class="text-red-500 hover:text-red-600"
                                        />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </flux:card>
                @empty
                <p class="text-zinc-400 text-sm">No documents configured for this claim type.</p>
                @endforelse
            </div>
        </flux:tab.panel>
        @endforeach
    </flux:tab.group>

    {{-- Add Document Modal --}}
    <flux:modal name="add-doc" class="max-w-lg">
        <div class="p-6 space-y-4">
            <flux:heading size="lg">Add Document</flux:heading>

            <flux:field>
                <flux:label>Document Label</flux:label>
                <flux:input wire:model="newLabel" placeholder="e.g. Police Report" />
                <flux:error name="newLabel" />
            </flux:field>

            <flux:field>
                <flux:label>Document Type <span class="text-zinc-400 font-normal">(lowercase, underscores only)</span></flux:label>
                <flux:input wire:model="newDocumentType" placeholder="e.g. police_report" />
                <flux:error name="newDocumentType" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Category</flux:label>
                    <flux:select variant="listbox" multiple wire:model.live="newClaimCategory" placeholder="Choose categories...">
                        <flux:select.option value="hospitalization">Hospitalization</flux:select.option>
                        <flux:select.option value="death">Death</flux:select.option>
                    </flux:select>
                    <flux:error name="newClaimCategory" />
                </flux:field>

                @if (in_array('hospitalization', $newClaimCategory))
                <flux:field>
                    <flux:label>Incident Type</flux:label>
                    <flux:select variant="listbox" multiple wire:model="newIncidentType" placeholder="Choose incident types...">
                        <flux:select.option value="accident">Accident</flux:select.option>
                        <flux:select.option value="non_accident">Non-Accident</flux:select.option>
                    </flux:select>
                    <flux:error name="newIncidentType" />
                </flux:field>
                @endif
            </div>

            <div class="flex gap-6">
                <flux:field variant="inline">
                    <flux:checkbox wire:model="newIsRequired" />
                    <flux:label>Required</flux:label>
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model.live="newIsDownloadable" />
                    <flux:label>Downloadable</flux:label>
                </flux:field>
            </div>

            @if ($newIsDownloadable)
            <flux:field>
                <flux:label>PDF File <span class="text-zinc-400 font-normal">(optional)</span></flux:label>
                <input
                    type="file"
                    wire:model="newFile"
                    accept=".pdf"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                           file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                           file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                           dark:file:bg-zinc-800 dark:file:text-zinc-300
                           hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700"
                />
                <div wire:loading wire:target="newFile" class="text-xs text-zinc-400 mt-1">Uploading…</div>
                <flux:error name="newFile" />
            </flux:field>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveDocument" variant="primary" icon="plus">
                    Add
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Edit/Upload Modal --}}
    <flux:modal name="upload-doc" class="max-w-md">
        <div class="p-6 space-y-4">
            <flux:heading size="lg">Edit Document</flux:heading>
            <flux:text>Rename the label and optionally upload a new PDF file.</flux:text>

            <flux:field>
                <flux:label>Document Label</flux:label>
                <flux:input wire:model="uploadLabel" placeholder="e.g. Accident FCL Form" />
                <flux:error name="uploadLabel" />
            </flux:field>

            <flux:field>
                <flux:label>PDF File <span class="text-zinc-400 font-normal">(optional — leave blank to keep existing)</span></flux:label>
                <input
                    type="file"
                    wire:model="uploadFile"
                    accept=".pdf"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                           file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                           file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                           dark:file:bg-zinc-800 dark:file:text-zinc-300
                           hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700"
                />
                <div wire:loading wire:target="uploadFile" class="text-xs text-zinc-400 mt-1">Uploading…</div>
                <flux:error name="uploadFile" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveUpload" variant="primary" icon="arrow-up-tray">
                    Save
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
