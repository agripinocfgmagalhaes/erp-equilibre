<x-filament-panels::page>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">
        @foreach ($this->getCards() as $item)
            <a href="{{ $item['url'] }}"
               style="display:flex; align-items:center; gap:0.75rem; border:1px solid #e5e7eb; border-radius:0.75rem; background:#fff; padding:1rem; text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:box-shadow 0.15s;"
               onmouseover="this.style.boxShadow='0 4px 10px rgba(0,0,0,0.08)'"
               onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
                <div style="display:flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border-radius:0.5rem; background:#f1f5f9; flex-shrink:0;">
                    <x-filament::icon :icon="$item['icon']" style="width:1.5rem; height:1.5rem; color:#475569;" />
                </div>
                <span style="font-weight:600; color:#0f172a; font-size:0.925rem;">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
