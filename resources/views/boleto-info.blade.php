<div class="space-y-4 p-2">
    <div>
        <span class="font-semibold">Situação:</span> {{ $conta->inter_situacao }}
    </div>
    <div>
        <span class="font-semibold">Linha digitável:</span>
        <div class="font-mono text-sm break-all bg-gray-100 dark:bg-gray-800 p-2 rounded">{{ $conta->inter_linha_digitavel }}</div>
    </div>
    @if($conta->inter_pix_copia_cola)
    <div>
        <span class="font-semibold">PIX Copia e Cola:</span>
        <div class="font-mono text-xs break-all bg-gray-100 dark:bg-gray-800 p-2 rounded">{{ $conta->inter_pix_copia_cola }}</div>
    </div>
    @endif
</div>
