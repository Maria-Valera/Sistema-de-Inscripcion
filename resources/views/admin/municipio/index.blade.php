@extends('adminlte::page')

@section('title', 'Gestión de Municipios')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-city"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Municipios</h1>
                    <p class="title-subtitle">Administración de municipios asociados a cada estado</p>
                </div>
            </div>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalCrear"
                    onclick="if (typeof Livewire !== 'undefined') { Livewire.dispatch('abrirModalCrear'); }"
                    title="Nuevo Municipio">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Municipio</span>
                </button>
            @endif
        </div>
    </div>
@stop

@section('content')
    @livewire('admin.municipio-index')
@endsection

@section('js')
    <script src="{{ asset('js/validations/municipio.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalCrear = document.getElementById('modalCrear');
            if (modalCrear) {
                modalCrear.addEventListener('show.bs.modal', () => {
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('abrirModalCrear');
                    }
                    modalCrear.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
                modalCrear.addEventListener('hidden.bs.modal', () => {
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('abrirModalCrear');
                    }
                    modalCrear.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            }

            const modalEditar = document.getElementById('modalEditar');
            if (modalEditar) {
                modalEditar.addEventListener('hidden.bs.modal', () => {
                    modalEditar.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                });
            }
        });

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
