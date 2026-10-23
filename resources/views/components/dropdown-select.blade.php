@props([
    'name',
    'id' => null,
    'options' => [], // array of ['value' => ..., 'label' => ...] or Eloquent collection or associative array
    'selected' => null,
    'placeholder' => '-- Pilih --',
    'required' => false,
])

@php
    $inputId = $id ?? $name;
    // Normalize options into list of ['value' => ..., 'label' => ...]
    $normalizedOptions = [];
    foreach ($options as $key => $option) {
        if (is_array($option)) {
            $normalizedOptions[] = [
                'value' => (string) ($option['value'] ?? $key),
                'label' => (string) ($option['label'] ?? $option['name'] ?? $key),
            ];
        } elseif (is_object($option)) {
            $normalizedOptions[] = [
                'value' => (string) ($option->id ?? $option->value ?? $key),
                'label' => (string) ($option->name ?? $option->label ?? $key),
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $option,
            ];
        }
    }

    $initialValue = (string) old($name, $selected ?? '');
    $initialLabel = $placeholder;
    foreach ($normalizedOptions as $opt) {
        if ((string) $opt['value'] === $initialValue) {
            $initialLabel = $opt['label'];
            break;
        }
    }
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        value: @js($initialValue),
        label: @js($initialLabel),
        placeholder: @js($placeholder),
        options: @js($normalizedOptions),
        get filteredOptions() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        select(val, lbl) {
            this.value = val;
            this.label = lbl;
            this.open = false;
            this.search = '';
            $nextTick(() => {
                const hiddenInput = document.getElementById('{{ $inputId }}');
                if (hiddenInput) {
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (window.lucide) lucide.createIcons();
            });
        }
    }"
    class="relative w-full"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <!-- Hidden Form Input for standard HTML form submission -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        id="{{ $inputId }}" 
        :value="value"
        @if($required) required @endif
    >

    <!-- Custom Select Trigger Button -->
    <button
        type="button"
        @click="open = !open; if (open) $nextTick(() => { if (window.lucide) lucide.createIcons(); $refs.searchInput?.focus(); })"
        class="w-full px-3.5 py-2.5 bg-[#141312] border rounded-xl text-xs font-medium text-left flex items-center justify-between transition-all focus:outline-none select-none"
        :class="open ? 'border-amber-500 ring-1 ring-amber-500/50 bg-[#181716]' : 'border-[#2D2A26] hover:border-stone-700'"
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <span 
            class="truncate"
            :class="value !== '' ? 'text-white font-medium' : 'text-stone-500'"
            x-text="value !== '' ? label : placeholder"
        ></span>

        <span class="ml-2 text-stone-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-500' : ''">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </span>
    </button>

    <!-- Custom Dropdown Menu Panel -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute left-0 right-0 z-50 mt-1.5 max-h-64 overflow-y-auto bg-[#181716] border border-stone-800 rounded-xl shadow-2xl p-1 space-y-0.5 focus:outline-none"
        role="listbox"
        x-cloak
    >
        <!-- Search filter if options > 6 -->
        <div x-show="options.length > 6" class="p-1 pb-1.5 border-b border-stone-800/80 sticky top-0 bg-[#181716] z-10">
            <input 
                type="text" 
                x-ref="searchInput"
                x-model="search" 
                placeholder="Cari..." 
                class="w-full bg-[#141312] border border-stone-800 rounded-lg px-2.5 py-1.5 text-xs text-stone-200 placeholder-stone-500 focus:outline-none focus:border-amber-500"
                @click.stop
                @keydown.enter.prevent="if (filteredOptions.length > 0) select(filteredOptions[0].value, filteredOptions[0].label)"
            >
        </div>

        <!-- Placeholder / Reset Option -->
        @if(!$required)
            <div
                @click="select('', placeholder)"
                class="px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between text-stone-500 hover:bg-stone-800/80 hover:text-stone-300 transition-colors"
                :class="value === '' ? 'bg-stone-800/60 text-stone-300 font-semibold' : ''"
                role="option"
            >
                <span x-text="placeholder"></span>
            </div>
        @endif

        <!-- Filtered Options List -->
        <template x-for="item in filteredOptions" :key="item.value">
            <div
                @click="select(item.value, item.label)"
                class="px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors group"
                :class="value === String(item.value) ? 'bg-amber-500/15 text-amber-400 font-semibold' : 'text-stone-200 hover:bg-stone-800 hover:text-white'"
                role="option"
                :aria-selected="value === String(item.value)"
            >
                <span x-text="item.label" class="truncate"></span>
                <span x-show="value === String(item.value)" class="text-amber-500 shrink-0 ml-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
            </div>
        </template>

        <div x-show="filteredOptions.length === 0" class="px-3 py-3 text-xs text-stone-500 text-center">
            Pilihan tidak ditemukan
        </div>
    </div>
</div>
