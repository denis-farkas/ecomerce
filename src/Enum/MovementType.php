<?php

namespace App\Enum;

enum MovementType: string
{
    case IN = 'IN';           // Entrée de stock
    case OUT = 'OUT';         // Sortie de stock
    case RESERVED = 'RESERVED'; // Réservation
    case RELEASED = 'RELEASED'; // Libération de réservation
    case ADJUSTMENT = 'ADJUSTMENT'; // Ajustement manuel
}