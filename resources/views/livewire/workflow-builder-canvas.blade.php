<div class="space-y-6" x-data="workflowBuilder()">
    {{-- Action Palette --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Add Step:</label>
            <select wire:model="selectedActionType"
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">Choose an action...</option>
                @foreach($this->actionOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <button wire:click="addStep"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50"
                    @disabled(empty($selectedActionType))>
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Workflow Canvas --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 min-h-[500px]">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                    Workflow Steps
                </h3>

                @if(empty($steps))
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                        <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <p class="text-lg font-medium">No steps yet</p>
                        <p class="text-sm">Add an action from the palette above to get started.</p>
                    </div>
                @else
                    {{-- Trigger indicator --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="inline-flex items-center px-4 py-2 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 rounded-full text-sm font-medium">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Trigger: {{ $workflow->trigger_type }}
                        </div>
                    </div>

                    {{-- Step cards --}}
                    <div class="space-y-3">
                        @foreach($steps as $index => $step)
                            {{-- Connector line --}}
                            @if($index > 0)
                                <div class="flex justify-center">
                                    <div class="w-0.5 h-6 bg-gray-300 dark:bg-gray-600"></div>
                                </div>
                                <div class="flex justify-center">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- Step card --}}
                            <div wire:click="selectStep({{ $index }})"
                                 class="relative group cursor-pointer rounded-lg border-2 p-4 transition-all
                                    {{ $selectedStepIndex === $index
                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-200 dark:ring-primary-800'
                                        : 'border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 hover:border-gray-300 dark:hover:border-gray-500' }}">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        {{-- Action type icon --}}
                                        @php
                                            $iconColor = match($step['action_type']) {
                                                'condition' => 'text-purple-500',
                                                'send-email' => 'text-blue-500',
                                                'http-request' => 'text-green-500',
                                                'create-record', 'update-record', 'delete-record' => 'text-orange-500',
                                                'run-workflow' => 'text-indigo-500',
                                                default => 'text-gray-500',
                                            };
                                        @endphp
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-white dark:bg-gray-800 shadow-sm {{ $iconColor }}">
                                            @if($step['action_type'] === 'condition')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @elseif($step['action_type'] === 'send-email')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            @elseif($step['action_type'] === 'http-request')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $step['label'] ?? $step['action_type'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $step['id'] }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Step controls --}}
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click.stop="moveStepUp({{ $index }})"
                                                class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                                @disabled($index === 0)
                                                title="Move up">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <button wire:click.stop="moveStepDown({{ $index }})"
                                                class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                                @disabled($index === count($steps) - 1)
                                                title="Move down">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <button wire:click.stop="removeStep({{ $index }})"
                                                class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30"
                                                title="Remove step">
                                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Condition branch indicators --}}
                                @if($step['action_type'] === 'condition')
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="text-center text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded">
                                            True → {{ count($step['true_steps'] ?? []) }} step(s)
                                        </div>
                                        <div class="text-center text-xs px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded">
                                            False → {{ count($step['false_steps'] ?? []) }} step(s)
                                        </div>
                                    </div>
                                @endif

                                {{-- Config preview --}}
                                @if(!empty($step['config']))
                                    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500 truncate">
                                        @foreach(array_slice($step['config'], 0, 3) as $key => $value)
                                            <span class="inline-block mr-2">{{ $key }}: {{ is_string($value) ? Str::limit($value, 30) : json_encode($value) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Configuration Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                @if($selectedStepIndex !== null && isset($steps[$selectedStepIndex]))
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        Configure: {{ $steps[$selectedStepIndex]['label'] ?? 'Step' }}
                    </h3>

                    <div class="space-y-4">
                        @foreach($this->selectedStepSchema as $field => $schema)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $schema['label'] ?? $field }}
                                    @if($schema['required'] ?? false)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if(($schema['type'] ?? 'text') === 'textarea')
                                    <textarea wire:model.live.debounce.500ms="stepConfig.{{ $field }}"
                                              wire:change="updateStepConfig"
                                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm"
                                              rows="3"
                                              placeholder="{{ $schema['placeholder'] ?? '' }}"></textarea>
                                @elseif(($schema['type'] ?? 'text') === 'select')
                                    <select wire:model.live="stepConfig.{{ $field }}"
                                            wire:change="updateStepConfig"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm">
                                        <option value="">Select...</option>
                                        @if(isset($schema['options']))
                                            @foreach($schema['options'] as $optKey => $optLabel)
                                                <option value="{{ is_int($optKey) ? $optLabel : $optKey }}">{{ $optLabel }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @elseif(($schema['type'] ?? 'text') === 'number')
                                    <input type="number"
                                           wire:model.live.debounce.500ms="stepConfig.{{ $field }}"
                                           wire:change="updateStepConfig"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm"
                                           placeholder="{{ $schema['placeholder'] ?? '' }}"
                                           value="{{ $schema['default'] ?? '' }}">
                                @else
                                    <input type="text"
                                           wire:model.live.debounce.500ms="stepConfig.{{ $field }}"
                                           wire:change="updateStepConfig"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm"
                                           placeholder="{{ $schema['placeholder'] ?? '' }}">
                                @endif

                                @if(isset($schema['helperText']))
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $schema['helperText'] }}</p>
                                @endif
                            </div>
                        @endforeach

                        <button wire:click="updateStepConfig"
                                class="w-full mt-4 inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            Save Configuration
                        </button>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm font-medium">No step selected</p>
                        <p class="text-xs mt-1">Click a step to configure it.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Workflow JSON preview (collapsible) --}}
    <details class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
            View Workflow JSON
        </summary>
        <pre class="px-4 pb-4 text-xs text-gray-600 dark:text-gray-400 overflow-x-auto"><code>{{ json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
    </details>
</div>

@script
<script>
    Alpine.data('workflowBuilder', () => ({
        // Future: drag-and-drop enhancements via Alpine
    }))
</script>
@endscript
