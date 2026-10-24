@extends('adminlte::page')

@section('title', 'Gestión de Usuarios y Accesos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('content')
    <div class="py-3">
        @livewire('admin.usuarios.gestionar-usuarios')
    </div>
@stop
