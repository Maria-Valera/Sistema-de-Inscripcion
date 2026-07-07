<?php

namespace App\Livewire\Admin\TransaccionInscripcion;

use Livewire\Component;
use App\Models\AnioEscolar;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\ProsecucionArea;
use App\Models\InscripcionProsecucion as ModeloInscripcionProsecucion;
use Illuminate\Support\Facades\DB;

class InscripcionProsecucion extends Component
{
    public $alumnoId;
    public $alumnoSeleccionado;
    public $alumnos = [];
    public $inscripcionAnterior;
    public $grados = [];
    public $secciones = [];
    public $gradoAnteriorId = null;
    public $gradoPromocionId = null;
    public $seccion_id = null;
    public $esPrimerGrado = false;
    public $gradosPermitidos = [];
    public $materias = [];
    public $materiasSeleccionadas = [];
    public $repite_grado = false;
    public $acepta_normas_contrato = true;
    public $observaciones;
    public ?int $gradoSugeridoId = null;
    public ?int $seccionSugeridaId = null;
    public ?string $mensajeSugerencia = null;
    public bool $seleccionarTodasArrastradas = false;
    public bool $seleccionarTodasActuales = false;

    protected $listeners = [
        'seleccionarAlumno' => 'seleccionarAlumno',
        'actualizarAlumno' => 'manejarActualizacionAlumno'
    ];

    protected function rules()
    {
        return [
            'alumnoId' => 'required|exists:alumnos,id',
            'gradoPromocionId' => [
                'required',
                'exists:grados,id',
                function ($attribute, $value, $fail) {
                    if (!$this->validarGradoPermitido($value)) {
                        $fail('El nivel academico seleccionado no es válido para este estudiante.');
                    }
                }
            ],
            'seccion_id' => $this->esPrimerGrado
                ? 'nullable'
                : 'required|exists:seccions,id',
            'materiasSeleccionadas' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $pendientesActuales = count($this->materiasPendientesActuales());

                    if ($pendientesActuales >= 4 && !$this->repite_grado) {
                        $fail('Con 4 o más areas de formacion pendientes del nivel academico actual el estudiante debe repetir nivel academico.');
                    }
                }
            ],
            'acepta_normas_contrato' => 'accepted',
            'repite_grado' => 'boolean',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'alumnoId.required' => 'Debe seleccionar un estudiante.',
        'alumnoId.exists' => 'El estudiante seleccionado no es válido.',
        'gradoPromocionId.required' => 'Este campo es requerido.',
        'gradoPromocionId.exists' => 'El nivel academico seleccionado no existe.',
        'seccion_id.required' => 'Debe seleccionar una sección.',
        'seccion_id.exists' => 'La sección seleccionada no es válida.',
        'acepta_normas_contrato.accepted' => 'Debe aceptar las normas de convivencia para continuar.',
        'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->cargarDatosIniciales();
        $this->cargarGrados();
    }

    public function cargarDatosIniciales()
    {
        $anioActual = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        if (!$anioActual) {
            $this->alumnos = collect();
            session()->flash('warning', 'No hay un Calendario Escolar activo.');
            return;
        }

        $anioAnterior = AnioEscolar::where('inicio_anio_escolar', '<', $anioActual->inicio_anio_escolar)
            ->orderByDesc('inicio_anio_escolar')
            ->first();

        if (!$anioAnterior) {
            $this->alumnos = collect();
            session()->flash('warning', 'No se encontró el Calendario Escolar anterior.');
            return;
        }

        $query = Alumno::whereHas('inscripciones', function ($q) use ($anioAnterior) {
            $q->where('anio_escolar_id', $anioAnterior->id);
        })
            ->whereDoesntHave('inscripciones', function ($q) use ($anioActual) {
                $q->where('anio_escolar_id', $anioActual->id);
            });

        // En el portal del representante, limitar a sus representados por correo registrado
        if (request()->routeIs('portal-representante.*') && auth()->check() && auth()->user()->hasRole('Representante')) {
            $userEmail = auth()->user()->email;
            $query->whereHas('inscripciones', function ($q) use ($userEmail, $anioAnterior) {
                $q->where('anio_escolar_id', $anioAnterior->id)
                  ->where(function ($sub) use ($userEmail) {
                      $sub->whereHas('padre.persona', function ($p) use ($userEmail) {
                          $p->where('email', $userEmail);
                      })
                      ->orWhereHas('madre.persona', function ($p) use ($userEmail) {
                          $p->where('email', $userEmail);
                      })
                      ->orWhereHas('representanteLegal.representante.persona', function ($p) use ($userEmail) {
                          $p->where('email', $userEmail);
                      });
                  });
            });
        }

        $this->alumnos = $query->with([
                'persona.tipoDocumento',
                'inscripciones' => function ($q) use ($anioAnterior) {
                    $q->where('anio_escolar_id', $anioAnterior->id)
                        ->with('grado');
                },
            ])
            ->orderBy('id')
            ->get();
    }


    public function cargarGrados()
    {
        $this->grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();
    }

    private function cargarGradosPermitidos()
    {
        if (!$this->gradoAnteriorId) {
            $this->gradosPermitidos = collect();
            return;
        }
        $gradoAnterior = Grado::where('id', $this->gradoAnteriorId)
            ->where('status', true)
            ->first();
        if (!$gradoAnterior) {
            $this->gradosPermitidos = collect();
            return;
        }
        if ($this->repite_grado) {
            $this->gradosPermitidos = collect([$gradoAnterior]);
            return;
        }
        $this->gradosPermitidos = Grado::where('status', true)
            ->whereIn('numero_grado', [
                $gradoAnterior->numero_grado,
                $gradoAnterior->numero_grado + 1,
            ])
            ->orderBy('numero_grado')
            ->get();
    }

    public function seleccionarAlumno($alumnoId)
    {
        if (is_array($alumnoId)) {
            $alumnoId = $alumnoId['alumnoId'] ?? null;
        }

        if (!$alumnoId) {
            return;
        }

        $this->reset([
            'alumnoSeleccionado',
            'gradoAnteriorId',
            'gradoPromocionId',
            'seccion_id',
            'secciones',
            'materias',
            'materiasSeleccionadas',
            'repite_grado',
            'acepta_normas_contrato',
            'observaciones',
            'gradosPermitidos',
            'inscripcionAnterior',
        ]);
        $this->resetErrorBag();
        $this->alumnoId = $alumnoId;

        $this->alumnoSeleccionado = Alumno::with([
            'persona.tipoDocumento',
            'inscripciones.grado',
        ])->find($alumnoId);

        if (!$this->alumnoSeleccionado) {
            session()->flash('error', 'No se pudo cargar el alumno seleccionado.');
            return;
        }

        // Obtener el año escolar actual
        $anioActual = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        if (!$anioActual) {
            session()->flash('error', 'No hay un Calendario Escolar activo.');
            return;
        }

        // Obtener el año escolar anterior
        $anioAnterior = AnioEscolar::where('inicio_anio_escolar', '<', $anioActual->inicio_anio_escolar)
            ->orderByDesc('inicio_anio_escolar')
            ->first();

        if (!$anioAnterior) {
            session()->flash('error', 'No se encontró el Calendario Escolar anterior.');
            return;
        }

        // Obtener la inscripción del año anterior con todos los datos de representantes
        $this->inscripcionAnterior = Inscripcion::with([
            'grado',
            'seccion',
            'anioEscolar',
            'padre.persona.tipoDocumento',
            'madre.persona.tipoDocumento',
            'representanteLegal.representante.persona.tipoDocumento',
        ])
            ->where('alumno_id', $alumnoId)
            ->where('anio_escolar_id', $anioAnterior->id)
            ->first();

        if (!$this->inscripcionAnterior) {
            session()->flash('error', 'No se encontró la inscripción anterior del estudiante.');
            return;
        }

        $this->cargarGradoDesdeInscripcionAnterior();
        $this->calcularSugerenciaInscripcion();
    }

    public function getRepresentantesProperty()
    {
        // Usar directamente la inscripción anterior ya cargada
        return $this->inscripcionAnterior?->representanteLegal;
    }


    private function cargarGradoDesdeInscripcionAnterior()
    {
        if (!$this->inscripcionAnterior || !$this->inscripcionAnterior->grado) {
            return;
        }

        $this->gradoAnteriorId = $this->inscripcionAnterior->grado->id;
        $this->cargarGradosPermitidos();
        $this->cargarMateriasParaProsecucion($this->gradoAnteriorId);
    }
    private function cargarMateriasParaProsecucion($gradoId)
    {
        $this->materias = [];
        $grado = Grado::with('gradoAreaFormacion.area_formacion')
            ->find($gradoId);
        if ($grado) {
            foreach ($grado->gradoAreaFormacion->where('status', true) as $item) {
                $this->materias[] = [
                    'id' => $item->id,
                    'nombre' => $item->area_formacion->nombre_area_formacion,
                    'codigo' => $item->codigo,
                    'origen' => 'grado_actual',
                    'grado' => $grado->numero_grado,
                ];
            }
        }
        $pendientes = $this->alumnoSeleccionado->materiasPendientesUltimaProsecucion();

        foreach ($pendientes as $pendiente) {
            $this->materias[] = [
                'id' => $pendiente->grado_area_formacion_id,
                'nombre' => $pendiente->gradoAreaFormacion->area_formacion->nombre_area_formacion,
                'codigo' => $pendiente->gradoAreaFormacion->codigo,
                'origen' => 'pendiente_anterior',
                'grado' => $pendiente->gradoAreaFormacion->grado->numero_grado,
            ];
        }
        $this->materias = collect($this->materias)
            ->unique('id')
            ->sortBy('nombre')
            ->values()
            ->toArray();
    }

    public function updatedMateriasSeleccionadas()
    {
        $this->recalcularPorCambioDeMaterias();
    }

    private function validarMateriasPendientes()
    {
        $this->resetErrorBag('materiasSeleccionadas');
        $pendientesActuales = $this->materiasPendientesActuales();
        $cantidadPendientes = count($pendientesActuales);
        if ($cantidadPendientes >= 4) {
            $this->repite_grado = true;
            $this->addError(
                'materiasSeleccionadas',
                'Con 4 o más areas de formacion pendientes del nivel academico actual el estudiante debe repetir nivel academico.'
            );
        } else {
            $this->repite_grado = false;
        }
    }

    private function tieneMateriasArrastradasNoAprobadas(): bool
    {
        $idsArrastradas = collect($this->materias)
            ->where('origen', 'pendiente_anterior')
            ->pluck('id');
        $noAprobadas = $idsArrastradas->diff($this->materiasSeleccionadas);
        return $noAprobadas->count() > 0;
    }

    public function updatedRepiteGrado()
    {
        $this->calcularSugerenciaInscripcion();
    }

    private function materiasArrastradasSeleccionadas(): array
    {
        return collect($this->materias)
            ->where('origen', 'pendiente_anterior')
            ->pluck('id')
            ->intersect($this->materiasSeleccionadas)
            ->values()
            ->toArray();
    }

    private function materiasPendientesActuales(): array
    {
        $idsGradoActual = collect($this->materias)
            ->where('origen', 'grado_actual')
            ->pluck('id');
        return $idsGradoActual
            ->diff($this->materiasSeleccionadas)
            ->values()
            ->toArray();
    }

    public function updatedGradoPromocionId($value)
    {
        $this->seccion_id = null;
        $this->secciones = collect();
        if (!$value) return;
        $grado = Grado::find($value);
        if (!$grado) return;
        $this->esPrimerGrado = ((int) $grado->numero_grado === 1);
        if (!$this->esPrimerGrado) {
            $this->secciones = Seccion::where('grado_id', $value)
                ->where('status', true)
                ->orderBy('nombre', 'asc')
                ->get();
        }
        $this->validarMateriasPendientes();
    }

    private function validarGradoPermitido($gradoId): bool
    {
        if ($this->gradosPermitidos->isEmpty()) {
            $this->cargarGradosPermitidos();
        }
        return $this->gradosPermitidos->contains('id', $gradoId);
    }

    private function alumnoYaPromovido($alumnoId, $anioActualId): bool
    {
        return Inscripcion::where('alumno_id', $alumnoId)
            ->where('anio_escolar_id', $anioActualId)
            ->exists()
            || ModeloInscripcionProsecucion::whereHas('inscripcion', function ($q) use ($alumnoId, $anioActualId) {
                $q->where('inscripcions.alumno_id', $alumnoId)
                    ->where('inscripcions.anio_escolar_id', $anioActualId);
            })->exists();
    }

    private function calcularSugerenciaInscripcion(): void
    {
        $this->mensajeSugerencia = null;
        $this->gradoSugeridoId = null;
        $this->seccionSugeridaId = null;

        if (!$this->inscripcionAnterior || !$this->gradoAnteriorId) {
            return;
        }

        $gradoAnterior = Grado::find($this->gradoAnteriorId);
        if (!$gradoAnterior) {
            return;
        }

        // Determinar grado sugerido
        if ($this->repite_grado) {
            $gradoSugerido = $gradoAnterior;
        } else {
            $gradoSugerido = Grado::where('numero_grado', $gradoAnterior->numero_grado + 1)
                ->where('status', true)
                ->first();
        }

        if (!$gradoSugerido) {
            return;
        }

        $this->gradoSugeridoId = $gradoSugerido->id;

        if (!$this->repite_grado && $this->inscripcionAnterior->seccion) {
            $nombreSeccion = $this->inscripcionAnterior->seccion->nombre;

            $seccionSugerida = Seccion::where('grado_id', $gradoSugerido->id)
                ->where('nombre', $nombreSeccion)
                ->where('status', true)
                ->first();

            if ($seccionSugerida) {
                $this->seccionSugeridaId = $seccionSugerida->id;
            }
        }

        // Mensaje final
        $this->mensajeSugerencia = sprintf(
            'Sugerencia: inscribir en %s° Nivel Académico%s.',
            $gradoSugerido->numero_grado,
            $this->seccionSugeridaId
                ? ' Sección ' . optional(Seccion::find($this->seccionSugeridaId))->nombre
                : ''
        );
    }


    public function updatedSeleccionarTodasArrastradas($value)
    {
        $ids = collect($this->materias)
            ->where('origen', 'pendiente_anterior')
            ->pluck('id');
        $this->materiasSeleccionadas = $value
            ? collect($this->materiasSeleccionadas)->merge($ids)->unique()->values()->toArray()
            : collect($this->materiasSeleccionadas)->diff($ids)->values()->toArray();
        $this->recalcularPorCambioDeMaterias();
    }

    public function updatedSeleccionarTodasActuales($value)
    {
        $ids = collect($this->materias)
            ->where('origen', 'grado_actual')
            ->pluck('id');
        $this->materiasSeleccionadas = $value
            ? collect($this->materiasSeleccionadas)->merge($ids)->unique()->values()->toArray()
            : collect($this->materiasSeleccionadas)->diff($ids)->values()->toArray();
        $this->recalcularPorCambioDeMaterias();
    }

    private function sincronizarSelectAll()
    {
        $arrastradas = collect($this->materias)->where('origen', 'pendiente_anterior')->pluck('id');
        $actuales = collect($this->materias)->where('origen', 'grado_actual')->pluck('id');
        $this->seleccionarTodasArrastradas =
            $arrastradas->isNotEmpty() &&
            $arrastradas->diff($this->materiasSeleccionadas)->isEmpty();
        $this->seleccionarTodasActuales =
            $actuales->isNotEmpty() &&
            $actuales->diff($this->materiasSeleccionadas)->isEmpty();
    }

    private function recalcularPorCambioDeMaterias(): void
    {
        if ($this->tieneMateriasArrastradasNoAprobadas()) {
            $this->repite_grado = true;
        } else {
            $this->validarMateriasPendientes();
        }
        $this->cargarGradosPermitidos();
        $this->calcularSugerenciaInscripcion();
        $this->sincronizarSelectAll();
        $this->resetErrorBag('gradoPromocionId');
    }

    public function finalizar()
    {
        $this->validate();

        $anioActual = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();
        if (!$anioActual) {
            $this->addError('general', 'No hay un Calendario Escolar activo.');
            return;
        }

        if (!$this->validarAntesDeGuardar()) {
            return;
        }

        if ($this->alumnoYaPromovido($this->alumnoId, $anioActual->id)) {
            $this->addError(
                'alumnoId',
                'Este estudiante ya fue inscrito o promovido en el Calendario Escolar actual.'
            );
            return;
        }

        // USAR $this->inscripcionAnterior en lugar de llamar al método
        if (!$this->inscripcionAnterior) {
            throw new \Exception('No se encontró la inscripción anterior del alumno.');
        }

        DB::beginTransaction();
        try {
            // Obtener IDs de representantes de la inscripción anterior ya cargada
            $padreId = $this->inscripcionAnterior->padre_id;
            $madreId = $this->inscripcionAnterior->madre_id;
            $representanteLegalId = $this->inscripcionAnterior->representante_legal_id;

            $nuevaInscripcion = Inscripcion::create([
                'tipo_inscripcion' => 'prosecucion',
                'anio_escolar_id' => $anioActual->id,
                'alumno_id' => $this->alumnoId,
                'grado_id' => $this->gradoPromocionId,
                'seccion_id' => $this->seccion_id,
                'status' => 'Activo',
                'documentos' => $this->inscripcionAnterior->documentos ?? null,
                'observaciones' => filled($this->observaciones)
                    ? $this->observaciones
                    : 'Promoción por prosecución',
                'acepta_normas_contrato' => $this->acepta_normas_contrato,
                'padre_id' => $padreId,
                'madre_id' => $madreId,
                'representante_legal_id' => $representanteLegalId,
            ]);

            $prosecucion = ModeloInscripcionProsecucion::create([
                'inscripcion_id' => $nuevaInscripcion->id,
                'inscripcion_anterior_id' => $this->inscripcionAnterior->id,
                'anio_escolar_id' => $anioActual->id,
                'grado_id' => $this->gradoPromocionId,
                'seccion_id' => $this->seccion_id,
                'promovido' => !$this->repite_grado,
                'repite_grado' => $this->repite_grado,
                'observaciones' => filled($this->observaciones)
                    ? $this->observaciones
                    : 'Sin observaciones',
                'acepta_normas_contrato' => $this->acepta_normas_contrato,
                'status' => 'Activo',
            ]);

            if ($this->seccion_id) {
                $seccion = Seccion::lockForUpdate()->find($this->seccion_id);
                if (!$seccion) {
                    throw new \Exception('La sección seleccionada no existe.');
                }
                $grado = Grado::findOrFail($this->gradoPromocionId);
                $capacidadMax = $grado->max_seccion ?? 35;
                if ($seccion->cantidad_actual >= $capacidadMax) {
                    throw new \Exception(
                        "La sección {$seccion->nombre} ya alcanzó su capacidad máxima ({$capacidadMax} alumnos)."
                    );
                }
                $seccion->increment('cantidad_actual');
            }

            $this->guardarMateriasEstado($prosecucion->id);

            DB::commit();
            session()->flash('success', 'Inscripción por prosecución registrada correctamente.');

            if (auth()->check() && auth()->user()->hasRole('Representante') && request()->routeIs('portal-representante.*')) {
                return redirect()->route('portal-representante.prosecucion.index');
            }

            return redirect()->route('admin.transacciones.inscripcion_prosecucion.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'Error al guardar la inscripción: ' . $e->getMessage());
        }
    }

    private function validarAntesDeGuardar(): bool
    {
        if ($this->tieneMateriasArrastradasNoAprobadas()) {
            $this->repite_grado = true;
            if ($this->gradoPromocionId != $this->gradoAnteriorId) {
                $this->addError(
                    'gradoPromocionId',
                    'El estudiante tiene areas de formacion reprobadas sin aprobar. Debe repetir el nivel academico.'
                );
                return false;
            }
        }
        $cantidadPendientes = count($this->materiasPendientesActuales());
        if ($cantidadPendientes >= 4) {
            $this->repite_grado = true;
            if ($this->gradoPromocionId != $this->gradoAnteriorId) {
                $this->addError(
                    'gradoPromocionId',
                    'Con 4 o más mareas de formacion pendientes debe repetir el nivel academico.'
                );
                return false;
            }
        }
        if ($this->tieneMateriasArrastradasNoAprobadas()) {
            $this->repite_grado = true;
            if ($this->gradoPromocionId != $this->gradoAnteriorId) {
                $this->addError(
                    'gradoPromocionId',
                    'El estudiante tiene areas de formacion reprobadas sin aprobar. Debe recursar el nivel academico.'
                );
                return false;
            }
        }
        return true;
    }

    private function guardarMateriasEstado($prosecucionId)
    {
        $materiasSeleccionadas = collect($this->materiasSeleccionadas);
        foreach ($this->materias as $materia) {
            ProsecucionArea::create([
                'inscripcion_prosecucion_id' => $prosecucionId,
                'grado_area_formacion_id' => $materia['id'],
                'status' => $materiasSeleccionadas->contains($materia['id'])
                    ? 'aprobada'
                    : 'pendiente',
            ]);
        }
    }

    public function manejarActualizacionAlumno()
    {
        if (!$this->alumnoId) {
            return;
        }
        $this->alumnoSeleccionado = Alumno::with([
            'persona.tipoDocumento',
            'inscripciones.grado',
            'inscripcionProsecucions.grado'
        ])->find($this->alumnoId);
        $this->cargarDatosIniciales();
        $this->dispatch('refreshSelectAlumno');
        session()->flash(
            'success',
            'Datos del estudiante actualizados correctamente.'
        );
    }

    public function render()
    {
        return view('livewire.admin.transaccion-inscripcion.inscripcion-prosecucion');
    }
}
