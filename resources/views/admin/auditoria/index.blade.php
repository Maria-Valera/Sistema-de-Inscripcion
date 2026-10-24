@extends('adminlte::page')

@section('title', 'Auditoría del Sistema')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .badge {
            font-size: 85%;
            font-weight: 600;
        }
        details summary {
            user-select: none;
            outline: none;
        }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h1 class="title-main">Auditoría del Sistema</h1>
                    <p class="title-subtitle">Registro y trazabilidad de eventos, seguridad y operaciones</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @livewire('admin.auditoria.index-auditoria')
@stop
