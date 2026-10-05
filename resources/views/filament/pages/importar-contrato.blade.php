<x-filament-panels::page>
    <form wire:submit="importar">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Importar e Cadastrar
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
