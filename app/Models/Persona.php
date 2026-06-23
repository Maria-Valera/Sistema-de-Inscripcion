<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ConvierteAMayusculas;
use App\Traits\Capitalizar;

class Persona extends Model
{
    use HasFactory;
    use ConvierteAMayusculas;
    use Capitalizar;
    protected $table = 'personas';
    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'numero_documento',
        'fecha_nacimiento',
        'direccion',
        'email',
        'telefono',
        'telefono_dos',
        'status',
        'tipo_documento_id',
        'genero_id',
        'localidad_id',
        'prefijo_id',
        'prefijo_dos_id',
    ];

    protected $capitalizar = [
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'direccion',
    ];

    protected $mayusculas = [
        'numero_documento'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'status' => 'boolean',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id', 'id');
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'genero_id', 'id');
    }

    public function prefijoTelefono()
    {
        return $this->belongsTo(PrefijoTelefono::class, 'prefijo_id', 'id');
    }

    public function prefijo()
    {
        return $this->prefijoTelefono();
    }

    public function prefijoDos()
    {
        return $this->belongsTo(PrefijoTelefono::class, 'prefijo_dos_id', 'id');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id', 'id');
    }

    public function docente()
    {
        return $this->hasOne(Docente::class, 'persona_id', 'id');
    }

    public function representante()
    {
        return $this->hasOne(Representante::class, 'persona_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'persona_id');
    }

    public function getNombreCompletoAttribute()
    {
        return trim(
            $this->primer_nombre . ' ' .
                ($this->segundo_nombre ?? '') . ' ' .
                ($this->tercer_nombre ?? '') . ' ' .
                $this->primer_apellido . ' ' .
                ($this->segundo_apellido ?? '')
        );
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getTelefonoCompletoAttribute()
    {
        if (!$this->telefono) {
            return null;
        }

        return trim(
            ($this->prefijoTelefono->prefijo ?? '') . ' ' . $this->telefono
        );
    }

    public function getTelefonoDosCompletoAttribute()
    {
        if (!$this->telefono_dos) {
            return null;
        }

        return trim(
            ($this->prefijoDos->prefijo ?? '') . ' ' . $this->telefono_dos
        );
    }
}
