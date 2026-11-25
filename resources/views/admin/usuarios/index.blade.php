@extends('adminlte::page')

@section('title', 'Gestión de Usuarios y Accesos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('content')
    <div class="py-3">
        @livewire('admin.usuarios.gestionar-usuarios')
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
