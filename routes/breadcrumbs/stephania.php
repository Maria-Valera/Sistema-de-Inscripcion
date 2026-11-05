<?php

// Breadcrumbs de los maestros de Stephania. Solo Stephania edita este archivo.

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

Breadcrumbs::for('admin.institucion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Datos del Plantel', route('admin.institucion.index'));
});

Breadcrumbs::for('admin.escala-literal.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Escala Literal', route('admin.escala-literal.index'));
});
