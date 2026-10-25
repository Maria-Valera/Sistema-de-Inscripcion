<?php

namespace Tests\Unit;

use App\Models\AnioEscolar;
use App\Models\ConfiguracionHorario;
use App\Models\RecesoHorario;
use App\Services\ConfiguracionHorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConfiguracionHorarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConfiguracionHorarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConfiguracionHorarioService();
    }

    protected function crearAnioActivo(): AnioEscolar
    {
        return AnioEscolar::create([
            'inicio_anio_escolar' => now()->toDateString(),
            'cierre_anio_escolar' => now()->addYear()->toDateString(),
            'status' => 'Activo',
        ]);
    }

    /**
     * Valida el cálculo del marco horario con receso según el ejemplo GHC:
     * Inicio 07:00, 4 bloques de 90 min, receso de 20 min tras el 2.º bloque.
     */
    public function test_calculo_bloques_con_recesos_ejemplo_ghc(): void
    {
        $config = new ConfiguracionHorario([
            'hora_inicio' => '07:00:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        $receso = new RecesoHorario([
            'despues_de_bloque' => 2,
            'duracion_min' => 20,
        ]);

        $config->setRelation('recesos', collect([$receso]));

        $bloques = $this->service->bloques($config);

        $this->assertCount(4, $bloques);

        $this->assertEquals([
            1 => ['inicio' => '07:00', 'fin' => '08:30'],
            2 => ['inicio' => '08:30', 'fin' => '10:00'],
            3 => ['inicio' => '10:20', 'fin' => '11:50'],
            4 => ['inicio' => '11:50', 'fin' => '13:20'],
        ], $bloques->all());
    }

    /**
     * Valida que reducir numero_bloques por debajo de asignaciones existentes lance excepción.
     */
    public function test_lanza_excepcion_al_reducir_bloques_con_asignaciones_existentes(): void
    {
        $anio = $this->crearAnioActivo();

        // Insertar bloque reservado simulado en el bloque 3
        DB::table('bloque_reservados')->insert([
            'anio_escolar_id' => $anio->id,
            'dia' => 1,
            'bloque' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/No se puede reducir el número de bloques a 2 porque ya existen registros asignados hasta el bloque 3/i');

        // Intentar guardar con solo 2 bloques
        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 2,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);
    }

    /**
     * Valida que guardar() persista la configuración y sincronice los recesos correctamente.
     */
    public function test_guardar_persiste_configuracion_y_sincroniza_recesos(): void
    {
        $anio = $this->crearAnioActivo();

        $datos = [
            'hora_inicio' => '07:15',
            'numero_bloques' => 5,
            'duracion_bloque_min' => 85,
            'minutos_entre_bloques' => 5,
            'recesos' => [
                ['despues_de_bloque' => 2, 'duracion_min' => 15],
                ['despues_de_bloque' => 3, 'duracion_min' => 20],
            ],
        ];

        $guardada = $this->service->guardar($anio->id, $datos);

        $this->assertEquals('07:15', substr($guardada->hora_inicio, 0, 5));
        $this->assertEquals(5, $guardada->numero_bloques);
        $this->assertEquals(85, $guardada->duracion_bloque_min);
        $this->assertEquals(5, $guardada->minutos_entre_bloques);
        $this->assertCount(2, $guardada->recesos);
    }

    /**
     * Valida que no se permitan recesos en posiciones inválidas (ej. después del último bloque).
     */
    public function test_lanza_excepcion_si_receso_es_invalido(): void
    {
        $anio = $this->crearAnioActivo();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/El receso tras el bloque 4 no es válido/i');

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'recesos' => [
                ['despues_de_bloque' => 4, 'duracion_min' => 20],
            ],
        ]);
    }
}
