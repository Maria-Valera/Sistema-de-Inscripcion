<?php

namespace App\Enums;

enum ColorEvento : string
{
    // colores fijos : el sistema los asigna solo , el usuario no los elige
    case Rojo = 'rojo';
    case Cobalto = 'cobalto';
    case Lavanda = 'lavanda';
    case PavoReal = 'pavo_real';
    case Gris = 'gris';

    // paleta seleccionable : solo para eventos laborables que no son efemeride

    case Achicoria = 'achicoria';
    case Calabaza = 'calabaza';
    case Aguacate = 'aguacate';
    case Eucalipto = 'eucalipto';
    case FlorDeCerezo = 'flor_de_cerezo';
    case Mango = 'mango';
    case Pistacho = 'pistacho';
    case Arandano = 'arandano';
    case Flamenco = 'flamenco';
    case Platano = 'platano';
    case Albahaca = 'albahaca';
    case Wisteria = 'wisteria';
    case Mandarina = 'mandarina';
    case Citron = 'citron';
    case Salvia = 'salvia';
    case Amatista = 'amatista';
    case UvaNegra = 'uva_negra';
    case Cacao = 'cacao';
    case Abedul = 'abedul';

    public function label(): string{

    return match($this){
        self::Rojo => 'Rojo',
        self::Cobalto => 'Cobalto',
        self::Lavanda => 'Lavanda',
        self::PavoReal => 'Pavo Real',
        self::Gris => 'Gris',
        self::Achicoria => 'Achicoria',
        self::Calabaza => 'Calabaza',
        self::Aguacate => 'Aguacate',
        self::Eucalipto => 'Eucalipto',
        self::FlorDeCerezo => 'Flor de Cerezo',
        self::Mango => 'Mango',
        self::Pistacho => 'Pistacho',
        self::Arandano => 'Arándano',
        self::Flamenco => 'Flamenco',
        self::Platano => 'Plátano',
        self::Albahaca => 'Albahaca',
        self::Wisteria => 'Wisteria',
        self::Mandarina => 'Mandarina',
        self::Citron => 'Citron',
        self::Salvia => 'Salvia',
        self::Amatista => 'Amatista',
        self::UvaNegra => 'Uva Negra',
        self::Cacao => 'Cacao',
        self::Abedul => 'Abedul',
    };
    }

    public function hex(): string{
        return match ($this){
            self::Rojo => '#E53935',
            self::Cobalto => '#0047AB',
            self::Lavanda => '#B57EDC',
            self::PavoReal => '#117A8B',
            self::Gris => '#9E9E9E',
            self::Achicoria => '#6F5A9E',
            self::Calabaza => '#FF7518',
            self::Aguacate => '#568203',
            self::Eucalipto => '#6EAE8A',
            self::FlorDeCerezo => '#FFB7C5',
            self::Mango => '#FFB300',
            self::Pistacho => '#93C572',
            self::Arandano => '#4F86C6',
            self::Flamenco => '#FC8EAC',
            self::Platano => '#FFE135',
            self::Albahaca => '#4B6F44',
            self::Wisteria => '#C9A0DC',
            self::Mandarina => '#F28C28',
            self::Citron => '#9FA91F',
            self::Salvia => '#9CAF88',
            self::Amatista => '#9966CC',
            self::UvaNegra => '#4B0082',
            self::Cacao => '#6F4E37',
            self::Abedul => '#D2B48C',
        };
    }

    // los 5 colores fijos , que NUNCA deben aparecer en el selector manual por que el sistema los asigna solo segun las reglas
    // de negocio

    public static function fijos():array {
        return [
            self::Rojo,
            self::Cobalto,
            self::Lavanda,
            self::PavoReal,
            self::Gris,
        ];
    }

    public static function seleccionables():array {
        return array_values(array_filter(self::cases(), fn (self $color) => ! in_array($color, self::fijos(), true)));
    }

}
