<x-filament-panels::page>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
        @foreach ($this->getCards() as $card)
            <a href="{{ $card['url'] }}"
               style="display:flex;align-items:center;gap:12px;padding:16px;border:1px solid rgba(128,128,128,.25);border-radius:12px;text-decoration:none;background:var(--fi-panel-page-bg, #fff);">
                <span style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:10px;background:rgba(59,130,246,.12);color:#2563eb;flex-shrink:0;">
                    @svg($card['icon'], 'h-5 w-5')
                </span>
                <span style="font-weight:600;font-size:14px;">{{ $card['label'] }}</span>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
