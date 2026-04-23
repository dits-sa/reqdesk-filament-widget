<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-check">
                {{ __('reqdesk-widget::reqdesk-widget.actions.save') }}
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-o-signal"
                wire:click="testConnection"
            >
                {{ __('reqdesk-widget::reqdesk-widget.actions.test_connection') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
