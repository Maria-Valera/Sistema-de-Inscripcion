@extends('adminlte::page')

@section('title', 'Gestión de Instituciones de Procedencia')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">

            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Instituciones de Procedencia</h1>
                    <p class="title-subtitle">Administración de las instituciones registradas en el sistema</p>
                </div>
            </div>

            {{-- Botón crear --}}
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button"
                    class="btn-create"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCrear"
                    title="Crear Institución">
                    <i class="fas fa-plus"></i>
                    <span>Nueva Institución</span>
                </button>
            @endif

        </div>
    </div>
@stop

{{-- Estilos --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('content')
    @livewire('admin.institucion-procedencia-index')
@endsection

@section('js')
<!-- Incluir el archivo de validaciones -->
<script src="{{ asset('js/validations/institucionProcedencia.js') }}"></script>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('cerrarModal', () => {
            const modales = document.querySelectorAll('.modal.show');
            modales.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
</script>
@stop
