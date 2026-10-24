<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Concerns\Auditable;

class Representante extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $table = "representantes";

    protected $fillable = [
        "persona_id",
        "estado_id",
        "ocupacion_representante",
        "convivenciaestudiante_representante",
        "municipio_id",
        "parroquia_id",
        "pais_id",
        "status"
    ];
    protected $attributes = [
        'status' => 1
    ];
    
    protected $dates = ['deleted_at']; 

        public function pais()
    {
        return $this->belongsTo(Pais::class, "pais_id", "id");
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, "estado_id", "id");
    }

    public function municipios()
    {
        return $this->belongsTo(Municipio::class, "municipio_id", "id");
    }


    public function localidads(){
        return $this->belongsTo(Localidad::class,"parroquia_id","id");
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class,"persona_id","id");
    }

    public function ocupacion()
    {
        return $this->belongsTo(Ocupacion::class, "ocupacion_representante", "id");
    }

    public function legal()
    {
        return $this->hasOne(RepresentanteLegal::class, 'representante_id', 'id');
    }

    

    public function inscripciones()
    {
        $representanteId = $this->id;
        return Inscripcion::where(function($query) use ($representanteId) {
            $query->where('padre_id', $representanteId)
                  ->orWhere('madre_id', $representanteId)
                  ->orWhere('representante_legal_id', $representanteId)
                  ->orWhereHas('representanteLegal', function ($rl) use ($representanteId) {
                      $rl->where('representante_id', $representanteId);
                  });
        });
    }

    public static function reportePDF($filtro=null){
    \Log::info('=== MODELO REPRESENTANTE - REPORTE PDF ===');
    \Log::info('Filtros recibidos en modelo:', ['filtros' => $filtro]);
    
    $queryIds = DB::table("representantes")
        ->where('representantes.status', 1)
        ->join("personas", "personas.id", "=", "representantes.persona_id")
        ->where('personas.status', 1);

    if (isset($filtro['es_legal'])) {
        \Log::info('Aplicando filtro es_legal en reporte:', ['es_legal' => $filtro['es_legal']]);
        if ($filtro['es_legal']) {
            $queryIds->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('representante_legal')
                    ->where('representante_legal.representante_id', DB::raw('representantes.id'));
            });
        } else {
            $queryIds->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('representante_legal')
                    ->where('representante_legal.representante_id', DB::raw('representantes.id'));
            });
        }
    }

    if (isset($filtro['grado_id']) && $filtro['grado_id'] !== '' && $filtro['grado_id'] !== null) {
        $gradoId = $filtro['grado_id'];
        \Log::info('Aplicando filtro grado_id en reporte:', ['grado_id' => $gradoId]);
        $queryIds->whereExists(function ($subquery) use ($gradoId) {
            $subquery->select(DB::raw(1))
                ->from('inscripcions')
                ->where(function ($q) use ($gradoId) {
                    $q->where('inscripcions.padre_id', DB::raw('representantes.id'))
                      ->orWhere('inscripcions.madre_id', DB::raw('representantes.id'))
                      ->orWhere('inscripcions.representante_legal_id', DB::raw('representantes.id'));
                })
                ->where('inscripcions.grado_id', $gradoId);
        });
    }

    if (isset($filtro['seccion_id']) && $filtro['seccion_id'] !== '' && $filtro['seccion_id'] !== null && $filtro['seccion_id'] != '0') {
        $seccionNombre = $filtro['seccion_id'];
        \Log::info('Aplicando filtro seccion_id en reporte:', ['seccion_id' => $seccionNombre]);
        $queryIds->whereExists(function ($subquery) use ($seccionNombre) {
            $subquery->select(DB::raw(1))
                ->from('inscripcions')
                ->join('seccions', 'seccions.id', '=', 'inscripcions.seccion_id')
                ->where(function ($q) use ($seccionNombre) {
                    $q->where('inscripcions.padre_id', DB::raw('representantes.id'))
                      ->orWhere('inscripcions.madre_id', DB::raw('representantes.id'))
                      ->orWhere('inscripcions.representante_legal_id', DB::raw('representantes.id'));
                })
                ->where('seccions.nombre', $seccionNombre);
        });
    }
    
    // Execute the query to get the filtered IDs
    $representantesIds = $queryIds->pluck('representantes.id')->toArray();
    
    $query = DB::table("representantes")
        ->where('representantes.status', 1)
        ->where('personas.status', 1)
        ->select(
            'representantes.id as representante_id',
            'representantes.ocupacion_representante',
            'representantes.status as representante_status',
            'personas.primer_nombre',
            'personas.segundo_nombre',
            'personas.tercer_nombre',
            'personas.primer_apellido',
            'personas.segundo_apellido',
            'personas.numero_documento',
            'personas.telefono',
            'personas.email',
            'personas.status',
            'personas.tipo_documento_id',
            'personas.genero_id',
            'personas.localidad_id',
            'personas.prefijo_id',
            'estados.nombre_estado as estado_nombre',
            'municipios.nombre_municipio as municipio_nombre',
            'localidads.nombre_localidad as localidad_nombre',
            'ocupacions.nombre_ocupacion as ocupacion_nombre',
            'representante_legal.parentesco',
            'representante_legal.correo_representante',
            'representante_legal.pertenece_a_organizacion_representante',
            'representante_legal.cual_organizacion_representante',
            'representante_legal.carnet_patria_afiliado',
            'representante_legal.serial_carnet_patria_representante',
            'representante_legal.tipo_cuenta',
            'representante_legal.codigo_carnet_patria_representante',
            'bancos.nombre_banco as banco_nombre',
            'personas_alumno.primer_nombre as alumno_primer_nombre',
            'personas_alumno.segundo_nombre as alumno_segundo_nombre',
            'personas_alumno.tercer_nombre as alumno_tercer_nombre',
            'personas_alumno.primer_apellido as alumno_primer_apellido',
            'personas_alumno.segundo_apellido as alumno_segundo_apellido',
            'personas_alumno.numero_documento as alumno_cedula',
            'anio_escolars.inicio_anio_escolar',
            'anio_escolars.cierre_anio_escolar',
            'seccions.nombre as seccion_nombre',
            'grados.numero_grado'
        )
        ->join("personas", "personas.id", "=", "representantes.persona_id")
        ->leftJoin("estados", "estados.id", "=", "representantes.estado_id")
        ->leftJoin("municipios", "municipios.id", "=", "representantes.municipio_id")
        ->leftJoin("localidads", "localidads.id", "=", "representantes.parroquia_id")
        ->leftJoin("ocupacions", "ocupacions.id", "=", "representantes.ocupacion_representante")
        ->leftJoin("representante_legal", "representante_legal.representante_id", "=", "representantes.id")
        ->leftJoin("bancos", "bancos.id", "=", "representante_legal.banco_id")
        ->leftJoin(DB::raw("(SELECT i.* FROM inscripcions i 
                   WHERE i.id = (
                       SELECT MIN(i2.id) 
                       FROM inscripcions i2 
                       WHERE i2.padre_id = i.padre_id 
                          OR i2.madre_id = i.madre_id 
                          OR i2.representante_legal_id = i.representante_legal_id
                   )
               ) inscripciones_filtradas"), function($join) {
            $join->on("inscripciones_filtradas.padre_id", "=", "representantes.id")
                 ->orOn("inscripciones_filtradas.madre_id", "=", "representantes.id")
                 ->orOn("inscripciones_filtradas.representante_legal_id", "=", "representantes.id");
        })
        ->leftJoin("alumnos", "alumnos.id", "=", "inscripciones_filtradas.alumno_id")
        ->leftJoin("personas as personas_alumno", "personas_alumno.id", "=", "alumnos.persona_id")
        ->leftJoin("anio_escolars", "anio_escolars.id", "=", "inscripciones_filtradas.anio_escolar_id")
        ->leftJoin("seccions", "seccions.id", "=", "inscripciones_filtradas.seccion_id")
        ->leftJoin("grados", "grados.id", "=", "inscripciones_filtradas.grado_id")
        ->whereIn('representantes.id', $representantesIds);
    
    $resultados = $query->get();
    
    return $resultados;
    }
}
