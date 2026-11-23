<?php

namespace Tests\Feature;

use App\Models\AreaFormacion;
use App\Models\ConversionRegla;
use App\Models\Grado;
use App\Models\Pensum;
use App\Models\User;
use Database\Seeders\AreaFormacionSeeder;
use Database\Seeders\ConversionReglasSeeder;
use Database\Seeders\AulaSeeder;
use Database\Seeders\GradoSeeder;
use Database\Seeders\JoseMaestrosSeeder;
use Database\Seeders\MallaCurricularSeeder;
use Database\Seeders\PensumSeeder;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConversionReglaTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

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
    }

    /**
     * Prueba que los seeders crean las reglas oficiales de la Circular N° 01.
     */
    public function test_seeder_crea_reglas_oficiales_circular_01(): void
    {
        // 1. Sembrar grados
        $this->seed(GradoSeeder::class);

        // 2. Sembrar catálogos y mallas
        $this->seed(AulaSeeder::class);
        $this->seed(AreaFormacionSeeder::class);
        $this->seed(JoseMaestrosSeeder::class);

        $pensum31059 = Pensum::where('codigo', '31059')->firstOrFail();
        $pensum31060 = Pensum::where('codigo', '31060')->firstOrFail();

        // Verificar que para 1er año (grado 1) existen las 11 reglas
        $reglas1erAno = ConversionRegla::where('pensum_origen_id', $pensum31059->id)
            ->where('pensum_destino_id', $pensum31060->id)
            ->where('grado_destino_id', 1)
            ->get();

        $this->assertCount(11, $reglas1erAno);

        // REGLA 8 (GHSN - id 19): debe ser promedio y provenir de Arte y Patrimonio (2) + GHC (8)
        $reglaGhsn = $reglas1erAno->firstWhere('area_destino_id', 19);
        $this->assertNotNull($reglaGhsn);
        $this->assertEquals('promedio', $reglaGhsn->tipo_regla);
        $this->assertCount(2, $reglaGhsn->origenes);

        $areasOrigenGhsn = $reglaGhsn->origenes->pluck('area_origen_id')->sort()->values()->toArray();
        $this->assertEquals([2, 8], $areasOrigenGhsn, 'GHSN debe promediar Arte y Patrimonio (2) y GHC (8) según Consideración 3');

        // REGLA FÍSICA (10): Exonerada sin orígenes
        $reglaFisica = $reglas1erAno->firstWhere('area_destino_id', 10);
        $this->assertNotNull($reglaFisica);
        $this->assertEquals('exonerada', $reglaFisica->tipo_regla);
        $this->assertCount(0, $reglaFisica->origenes);

        // REGLA QUÍMICA (11): Exonerada sin orígenes
        $reglaQuimica = $reglas1erAno->firstWhere('area_destino_id', 11);
        $this->assertNotNull($reglaQuimica);
        $this->assertEquals('exonerada', $reglaQuimica->tipo_regla);
        $this->assertCount(0, $reglaQuimica->origenes);

        // REGLA PGCRP (no se toma en cuenta): destino NULL
        $reglaNoCuenta = $reglas1erAno->firstWhere('tipo_regla', 'no_cuenta');
        $this->assertNotNull($reglaNoCuenta);
        $this->assertNull($reglaNoCuenta->area_destino_id);
    }

    /**
     * Prueba que la vista principal lista las reglas correctamente.
     */
    public function test_index_muestra_matriz_de_conversion(): void
    {
        $this->seed(GradoSeeder::class);

        $this->seed(AulaSeeder::class);
        $this->seed(AreaFormacionSeeder::class);
        $this->seed(JoseMaestrosSeeder::class);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.conversion-reglas.index'));

        $response->assertStatus(200);
        $response->assertSee('Reglas de Conversión entre Pensums');
        $response->assertSee('Matriz de Conversión: 31059');
    }
}

