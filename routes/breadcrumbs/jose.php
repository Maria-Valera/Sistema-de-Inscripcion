<?php

// Breadcrumbs de los maestros de José. Solo José edita este archivo.

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

Breadcrumbs::for('admin.pensums.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Planes de Estudio (Pensums)', route('admin.pensums.index'));
});

Breadcrumbs::for('admin.malla-curricular.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Malla Curricular', route('admin.malla-curricular.index'));
});

Breadcrumbs::for('admin.conversion-reglas.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Reglas de Conversión', route('admin.conversion-reglas.index'));
});

Breadcrumbs::for('admin.menciones.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Menciones y Ciclos', route('admin.menciones.index'));
});
