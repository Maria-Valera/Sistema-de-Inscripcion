<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\Docente;
use App\Models\Genero;
use App\Models\Grado;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\Seccion;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Support\AnioEscolarSeleccionado;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HorarioDocentesAnioSeleccionadoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AnioEscolar $anioActual;
    protected AnioEscolar $anioAnterior;
    protected TipoDocumento $tipoDoc;
    protected Genero $genero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Admin',
        ]);
        $this->adminUser->assignRole('Admin');

        $this->tipoDoc = TipoDocumento::firstOrCreate(['id' => 1], ['nombre' => 'V', 'status' => true]);
        $this->genero = Genero::firstOrCreate(['id' => 1], ['genero' => 'M', 'status' => true]);

        $this->anioActual = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(2)->toDateString(),
            'cierre_anio_escolar' => now()->addMonths(10)->toDateString(),
            'status' => 'Activo',
        ]);

        $this->anioAnterior = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subYears(2)->toDateString(),
            'cierre_anio_escolar' => now()->subYear()->toDateString(),
            'status' => 'Inactivo',
        ]);
    }

    /**
     * 1. Con año anterior seleccionado: Los índices de Horario, Docentes, Cargas y Aulas cargan (200).
     */
    public function test_con_anio_anterior_seleccionado_los_indices_cargan_correctamente(): void
    {
        $responseDocentes = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.docente.index'));
        $responseDocentes->assertStatus(200);

        $responseHorarios = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.horario.index'));
        $responseHorarios->assertStatus(200);

        $responseCargas = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.transacciones.docente_area_grado.index'));
        $responseCargas->assertStatus(200);

        $responseSeccionAula = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.seccion_aula.index'));
        $responseSeccionAula->assertStatus(200);
    }

    /**
     * 2. Con año anterior seleccionado: Operaciones de mutación (POST/PUT/DELETE) devuelven 403 por el middleware.
     */
    public function test_con_anio_anterior_seleccionado_post_devuelve_403_por_middleware(): void
    {
        // Intento de crear docente en año anterior
        $responseDocente = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->post(route('admin.docente.store'), [
                'tipo_documento_id' => $this->tipoDoc->id,
                'numero_documento' => '25999888',
                'primer_nombre' => 'Carlos',
                'primer_apellido' => 'Perez',
                'genero' => $this->genero->id,
                'fecha_nacimiento' => '1990-05-15',
            ]);
        $responseDocente->assertStatus(403);

        // Intento de modificar configuración de horario en año anterior
        $responseConfig = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->put(route('admin.horario.configuracion.update'), [
                'hora_inicio' => '07:00',
                'numero_bloques' => 5,
                'duracion_bloque_min' => 45,
            ]);
        $responseConfig->assertStatus(403);
    }

    /**
     * 3. Con año actual seleccionado: El CRUD de Docente y Horario funciona normalmente.
     */
    public function test_con_anio_actual_seleccionado_creacion_de_docente_asigna_el_anio_seleccionado(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActual->id])
            ->post(route('admin.docente.store'), [
                'tipo_documento_id' => $this->tipoDoc->id,
                'numero_documento' => '27111222',
                'primer_nombre' => 'Elena',
                'primer_apellido' => 'Rivas',
                'genero' => $this->genero->id,
                'fecha_nacimiento' => '1992-03-20',
            ]);

        $persona = Persona::where('numero_documento', '27111222')->first();
        $this->assertNotNull($persona);

        $docente = Docente::where('persona_id', $persona->id)->first();
        $this->assertNotNull($docente);
        $this->assertEquals($this->anioActual->id, $docente->anio_escolar_id);

        $response->assertRedirect(route('admin.docente.estudios', $docente->id));
    }

    /**
     * 4. API /api/anio-escolar/activo devuelve el año seleccionado en la sesión activa.
     */
    public function test_api_anio_escolar_activo_retorna_el_anio_seleccionado_en_sesion(): void
    {
        $responseAnterior = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get('/api/anio-escolar/activo');

        $responseAnterior->assertStatus(200);
        $this->assertEquals($this->anioAnterior->id, $responseAnterior->json('id'));

        $responseActual = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActual->id])
            ->get('/api/anio-escolar/activo');

        $responseActual->assertStatus(200);
        $this->assertEquals($this->anioActual->id, $responseActual->json('id'));
    }
}
