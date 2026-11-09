@if (\App\Support\AnioEscolarSeleccionado::esActual())
<form id="form-percentil" action="{{ route('admin.transacciones.percentil.boton-percentil') }}" method="POST"
    class="d-inline">
    @csrf

    <input type="hidden" name="grado_id" value="{{ $gradoId }}">

    <button type="button" onclick="confirmarEnvio()" class="btn-percentil"
        title="Ejecutar percentil y distribución de secciones">
        <i class="fas fa-calculator me-1"></i>
        Ejecutar Percentil
    </button>
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmarEnvio() {
        Swal.fire({
            title: '¿Desea ejecutar el percentil para este nivel academico?',
            text: '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ejecutar percentil',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-percentil').submit();
            }
        });
    }
</script>
@endif
