<?php
namespace App\Filament\Pages;

use App\Models\ConfiguracaoInter;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class IntegracaoInter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Integracao Inter';
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';
    protected static ?string $slug = 'integracao-inter';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.integracao-inter';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ConfiguracaoInter::atual()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Credenciais Banco Inter')->schema([
                Select::make('ambiente')->label('Ambiente')
                    ->options(['sandbox' => 'Sandbox (testes)', 'producao' => 'Producao'])
                    ->required()->native(false),
                TextInput::make('client_id')->label('Client ID')->required(),
                TextInput::make('client_secret')->label('Client Secret')->password()->revealable()->required(),
                TextInput::make('conta_corrente')->label('Conta Corrente'),
                \Filament\Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancaria (sistema)')->options(\App\Models\ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->helperText('Qual conta bancaria do ERP corresponde a essa integracao Inter'),
                TextInput::make('cedente_cnpj')->label('CNPJ Cedente')->required()->mask('99.999.999/9999-99'),
                FileUpload::make('cert_path')->label('Certificado (.crt)')
                    ->disk('local')->directory('inter')->visibility('private')
                    ->required(),
                FileUpload::make('key_path')->label('Chave privada (.key)')
                    ->disk('local')->directory('inter')->visibility('private')
                    ->required(),
            ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        ConfiguracaoInter::atual()->update($this->form->getState());
        Notification::make()->title('Configuracao salva')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [Action::make('save')->label('Salvar')->submit('save')];
    }

    public function getTitle(): string
    {
        return 'Integracao Banco Inter';
    }
}
