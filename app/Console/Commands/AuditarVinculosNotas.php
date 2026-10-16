<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditarVinculosNotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notas:auditar-vinculos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audita en modo solo lectura los vínculos de usuarios (rol Docente sin persona o sin docente) y persona_id duplicados en users.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================================================');
        $this->info('  AUDITORÍA DE VÍNCULOS DE USUARIOS (MÓDULO DE NOTAS - SOLO LECTURA)    ');
        $this->info('========================================================================');
        $this->newLine();

        $hayInconsistencias = false;

        // 1. Usuarios con rol Docente cuyo persona_id sea nulo o cuya persona no tenga docente
        $this->info('1. Verificando usuarios con rol "Docente":');

        $docenteUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Docente');
        })->with(['persona.docente'])->get();

        $docentesProblematicos = [];

        foreach ($docenteUsers as $user) {
            $motivo = null;

            if (is_null($user->persona_id)) {
                $motivo = 'persona_id es nulo (no tiene Persona vinculada)';
            } elseif (is_null($user->persona)) {
                $motivo = "persona_id ({$user->persona_id}) no existe en tabla personas";
            } elseif (is_null($user->persona->docente)) {
                $motivo = "La persona (ID {$user->persona_id}) no tiene registro asociado en tabla docentes";
            }

            if ($motivo) {
                $hayInconsistencias = true;
                $docentesProblematicos[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'persona_id' => $user->persona_id ?? 'NULL',
                    'motivo' => $motivo,
                ];
            }
        }

        if (count($docentesProblematicos) > 0) {
            $this->warn(sprintf('Se encontraron %d usuario(s) con rol Docente con inconsistencias en sus vínculos:', count($docentesProblematicos)));
            $this->table(
                ['ID Usuario', 'Nombre', 'Email', 'persona_id', 'Inconsistencia detectada'],
                $docentesProblematicos
            );
        } else {
            $this->info('✓ Todos los usuarios con rol "Docente" tienen persona_id asignado y registro correspondiente en docentes.');
        }

        $this->newLine();

        // 2. persona_id repetidos en users
        $this->info('2. Verificando persona_id repetidos en la tabla users:');

        $personaIdDuplicados = DB::table('users')
            ->select('persona_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('persona_id')
            ->groupBy('persona_id')
            ->having('total', '>', 1)
            ->get();

        if ($personaIdDuplicados->isNotEmpty()) {
            $hayInconsistencias = true;
            $filasDuplicadas = [];

            foreach ($personaIdDuplicados as $item) {
                $usuariosAfectados = User::where('persona_id', $item->persona_id)->get(['id', 'name', 'email']);
                foreach ($usuariosAfectados as $u) {
                    $filasDuplicadas[] = [
                        'persona_id' => $item->persona_id,
                        'total' => $item->total,
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                    ];
                }
            }

            $this->warn(sprintf('Se encontraron %d persona_id con múltiples usuarios asociados en users:', $personaIdDuplicados->count()));
            $this->table(
                ['persona_id', 'Total Cuentas', 'ID Usuario', 'Nombre de Usuario', 'Email'],
                $filasDuplicadas
            );
        } else {
            $this->info('✓ No se encontraron persona_id duplicados en la tabla users.');
        }

        $this->newLine();
        $this->info('------------------------------------------------------------------------');
        if ($hayInconsistencias) {
            $this->warn('Auditoría finalizada: Se detectaron inconsistencias. No se modificó ningún dato.');
        } else {
            $this->info('Auditoría finalizada: Sistema en estado íntegro para los vínculos analizados.');
        }
        $this->info('------------------------------------------------------------------------');

        return Command::SUCCESS;
    }
}
