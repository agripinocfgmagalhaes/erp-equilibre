<?php

namespace Tests\Unit\Numero;

use App\Models\Sequencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_primeiro_numero_eh_um(): void
    {
        $this->assertSame(1, Sequencia::proximo('RC', 2026));
    }

    public function test_incrementa_sequencialmente(): void
    {
        Sequencia::proximo('RC', 2026);
        Sequencia::proximo('RC', 2026);

        $this->assertSame(3, Sequencia::proximo('RC', 2026));
    }

    public function test_tipos_diferentes_tem_contadores_independentes(): void
    {
        Sequencia::proximo('RC', 2026);
        Sequencia::proximo('RC', 2026);

        $this->assertSame(1, Sequencia::proximo('PC', 2026));
        $this->assertSame(1, Sequencia::proximo('CV', 2026));
    }

    public function test_anos_diferentes_tem_contadores_independentes(): void
    {
        Sequencia::proximo('RC', 2026);

        $this->assertSame(1, Sequencia::proximo('RC', 2027));
    }

    public function test_retorna_numero_formatado_corretamente(): void
    {
        $this->assertSame('RC-2026-0001', sprintf('RC-%d-%04d', 2026, Sequencia::proximo('RC', 2026)));
    }
}
