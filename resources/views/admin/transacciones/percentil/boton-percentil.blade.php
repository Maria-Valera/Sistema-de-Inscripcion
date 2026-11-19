@if (\App\Support\AnioEscolarSeleccionado::esActual())
<form id="form-percentil" action="{{ route('admin.transacciones.percentil.boton-percentil') }}" method="POST"
    class="d-inline">
    @csrf

    <input type="hidden" name="grado_id" value="{{ $gradoId }}">

    <button type="button" onclick="confirmarEnvio()" class="btn-primary-modern d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" style="height: 38px; padding: 0 16px; border-radius: 8px;"
        title="Ejecutar percentil y distribución de secciones">
        <i class="fas fa-cogs"></i>
        <span>Ejecutar Percentil</span>
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
            confirmButtonColor: '#4A6FC1',
            cancelButtonColor: '#94A3B8',
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
