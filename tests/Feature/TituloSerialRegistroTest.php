<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\TituloSerial;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Registrar seriales de título: a mano (uno por línea) o por rango (prefijo + desde/hasta). Las dos guardan igual.
class TituloSerialRegistroTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AnioEscolar $anio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Admin',
        ]);
        $this->admin->assignRole('Admin');

        $this->anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-31',
            'status' => 'Activo',
        ]);
    }

    private function registrar(array $datos)
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.titulo_seriales.store'), $datos + ['_modal' => 'crearSeriales']);
    }

    public function test_la_pantalla_carga_y_pregunta_el_modo(): void
    {
        $this->actingAs($this->admin)->get(route('admin.titulo_seriales.index'))
            ->assertOk()
            ->assertSee('¿Cómo quieres registrar los seriales?')
            ->assertSee('Por rango (desde / hasta)');
    }

    public function test_sin_modo_no_se_registra(): void
    {
        $this->registrar(['seriales' => "A-001\nA-002"])->assertSessionHasErrors('modo');
        $this->assertSame(0, TituloSerial::count());
    }

    public function test_manual_sigue_funcionando(): void
    {
        $this->registrar(['modo' => 'manual', 'seriales' => "a-001\nA-002\n\n"])->assertSessionHasNoErrors();

        $this->assertSame(['A-001', 'A-002'], TituloSerial::orderBy('serial')->pluck('serial')->all());
    }

    public function test_por_rango_respeta_prefijo_y_ceros(): void
    {
        $this->registrar(['modo' => 'rango', 'prefijo' => 'a-', 'desde' => '0012345', 'hasta' => '0012349'])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            ['A-0012345', 'A-0012346', 'A-0012347', 'A-0012348', 'A-0012349'],
            TituloSerial::orderBy('serial')->pluck('serial')->all()
        );
        $this->assertSame(5, TituloSerial::where('anio_escolar_id', $this->anio->id)->where('estado', 'disponible')->count());
    }

    public function test_por_rango_sin_prefijo(): void
    {
        // Los ceros de "desde" se respetan: 098, 099, 100, 101, 102
        $this->registrar(['modo' => 'rango', 'desde' => '098', 'hasta' => '102'])->assertSessionHasNoErrors();

        $this->assertSame(['098', '099', '100', '101', '102'], TituloSerial::orderBy('serial')->pluck('serial')->all());
    }

    public function test_rango_al_reves_o_muy_grande_se_rechaza(): void
    {
        $this->registrar(['modo' => 'rango', 'desde' => '200', 'hasta' => '100'])->assertSessionHasErrors('rango_total');
        $this->registrar(['modo' => 'rango', 'desde' => '1', 'hasta' => '999999999'])->assertSessionHasErrors('rango_total');
        $this->registrar(['modo' => 'rango', 'desde' => 'abc', 'hasta' => '10'])->assertSessionHasErrors('desde');
        $this->registrar(['modo' => 'rango', 'desde' => '1'])->assertSessionHasErrors('hasta');

        $this->assertSame(0, TituloSerial::count());
    }

    public function test_rango_con_un_serial_ya_registrado_no_guarda_ninguno(): void
    {
        TituloSerial::create([
            'anio_escolar_id' => $this->anio->id,
            'serial' => 'B-0003',
            'estado' => 'disponible',
        ]);

        $this->registrar(['modo' => 'rango', 'prefijo' => 'B-', 'desde' => '0001', 'hasta' => '0005'])
            ->assertSessionHasErrors('lista.2');

        $this->assertSame(1, TituloSerial::count());
    }
}
