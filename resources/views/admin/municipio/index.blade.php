@extends('adminlte::page')
@section('title', 'Gestión de Municipios')
@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Municipios</h1>
                    <p class="title-subtitle">Administración de los municipios del sistema</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear"
                    title="Nuevo Municipio">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Municipio</span>
                </button>
            @endif
        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop
@section('content')
    @livewire('admin.municipio-index')
@endsection

@section('js')
    <script src="{{ asset('js/validations/municipio.js') }}"></script>

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
