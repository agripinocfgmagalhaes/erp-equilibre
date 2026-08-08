<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;

class Configuracoes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configurações';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'configuracoes';
    protected string $view = 'filament.pages.configuracoes';

    public function getTitle(): string
    {
        return 'Configurações';
    }

    public function getCards(): array
    {
        return [
            ['label' => 'Empreendimentos', 'icon' => 'heroicon-o-building-office-2', 'url' => \App\Filament\Resources\ProjetoResource::getUrl()],
            ['label' => 'Contas Bancárias', 'icon' => 'heroicon-o-banknotes', 'url' => \App\Filament\Resources\ContaBancariaResource::getUrl()],
            ['label' => 'Fases Padrão', 'icon' => 'heroicon-o-list-bullet', 'url' => \App\Filament\Resources\FasePadraoResource::getUrl()],
            ['label' => 'Catálogo de Serviços', 'icon' => 'heroicon-o-wrench-screwdriver', 'url' => \App\Filament\Resources\ServicoResource::getUrl()],
            ['label' => 'Plano de Contas', 'icon' => 'heroicon-o-chart-bar', 'url' => \App\Filament\Resources\PlanoContaResource::getUrl()],
            ['label' => 'Integração Inter', 'icon' => 'heroicon-o-key', 'url' => \App\Filament\Pages\IntegracaoInter::getUrl()],
            ['label' => 'Usuários', 'icon' => 'heroicon-o-user-group', 'url' => \App\Filament\Resources\UserResource::getUrl()],
            ['label' => 'Perfis de Acesso (Roles)', 'icon' => 'heroicon-o-shield-check', 'url' => \BezhanSalleh\FilamentShield\Resources\Roles\RoleResource::getUrl()],
        ];
    }
}
