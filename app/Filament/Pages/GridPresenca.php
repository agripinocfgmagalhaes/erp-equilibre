<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Funcionario;
use App\Models\FuncionarioPresenca;
use App\Models\FolhaFechamento;
use App\Models\Projeto;
use Carbon\Carbon;

class GridPresenca extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Grid de Presença';
    protected static string | \UnitEnum | null $navigationGroup = 'Pessoal';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'grid-presenca';
    protected string $view = 'filament.pages.grid-presenca';

    private const ORDEM_STATUS = ['integral', 'falta', 'meia_manha', 'meia_tarde'];

    public ?int $projetoId = null;
    public string $periodoInicio;
    public string $periodoFim;

    public function mount(): void
    {
        $hoje = Carbon::today();
        if ($hoje->day <= 15) {
            $this->periodoInicio = $hoje->copy()->startOfMonth()->toDateString();
            $this->periodoFim = $hoje->copy()->startOfMonth()->addDays(14)->toDateString();
        } else {
            $this->periodoInicio = $hoje->copy()->startOfMonth()->addDays(15)->toDateString();
            $this->periodoFim = $hoje->copy()->endOfMonth()->toDateString();
        }
    }

    public function getTitle(): string { return 'Grid de Presença'; }

    public function getProjetosProperty()
    {
        return Projeto::orderBy('nome')->pluck('nome', 'id');
    }

    public function getFuncionariosProperty()
    {
        return Funcionario::ativos()
            ->when($this->projetoId, fn ($q) => $q->where('projeto_id', $this->projetoId))
            ->orderBy('nome')
            ->get();
    }

    public function getDiasProperty(): array
    {
        $dias = [];
        $cursor = Carbon::parse($this->periodoInicio);
        $fim = Carbon::parse($this->periodoFim);
        while ($cursor->lte($fim)) {
            $dias[] = $cursor->toDateString();
            $cursor->addDay();
        }
        return $dias;
    }

    public function getPresencasProperty(): array
    {
        $funcionarioIds = $this->funcionarios->pluck('id');
        $registros = FuncionarioPresenca::whereIn('funcionario_id', $funcionarioIds)
            ->whereBetween('data', [$this->periodoInicio, $this->periodoFim])
            ->get();

        $mapa = [];
        foreach ($registros as $r) {
            $mapa[$r->funcionario_id][$r->data->toDateString()] = $r->status;
        }
        return $mapa;
    }

    public function getFechamentosProperty(): array
    {
        return FolhaFechamento::whereIn('funcionario_id', $this->funcionarios->pluck('id'))
            ->where('periodo_inicio', $this->periodoInicio)
            ->where('periodo_fim', $this->periodoFim)
            ->pluck('id', 'funcionario_id')
            ->toArray();
    }

    public function toggle(int $funcionarioId, string $data): void
    {
        if (isset($this->fechamentos[$funcionarioId])) {
            Notification::make()->title('Quinzena já fechada para este funcionário')->warning()->send();
            return;
        }

        $registro = FuncionarioPresenca::where('funcionario_id', $funcionarioId)->where('data', $data)->first();

        if (! $registro) {
            FuncionarioPresenca::create(['funcionario_id' => $funcionarioId, 'data' => $data, 'status' => self::ORDEM_STATUS[0]]);
            return;
        }

        $proximoIndex = array_search($registro->status, self::ORDEM_STATUS, true) + 1;

        if ($proximoIndex >= count(self::ORDEM_STATUS)) {
            $registro->delete();
            return;
        }

        $registro->update(['status' => self::ORDEM_STATUS[$proximoIndex]]);
    }

    public function fecharQuinzena(): void
    {
        $gerados = 0;
        foreach ($this->funcionarios as $funcionario) {
            if (isset($this->fechamentos[$funcionario->id])) continue;
            FolhaFechamento::gerar($funcionario, $this->periodoInicio, $this->periodoFim);
            $gerados++;
        }

        Notification::make()
            ->title($gerados > 0 ? "$gerados fechamento(s) gerado(s) e enviado(s) pra Contas a Pagar" : 'Nenhum funcionário pendente de fechamento')
            ->success()
            ->send();
    }
}
