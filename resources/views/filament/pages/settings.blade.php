<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-x-3">
            <x-filament-actions::actions :actions="$this->getFormActions()" />
        </div>
    </form>
</x-filament-panels::page>