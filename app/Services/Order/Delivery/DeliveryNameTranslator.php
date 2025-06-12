<?php

namespace App\Services\Order\Delivery;


class DeliveryNameTranslator
{
    private static $translations = [
        'Pickup from our stores' => 'Самовивіз з наших магазинів',
        'Pickup from Nova Poshta post offices' => 'Самовивіз з поштоматів Нової Пошти',
        'Pickup from Nova Poshta' => 'Самовивіз з Нової Пошти',
        'Pickup from UKRPOSTA' => 'Самовивіз з УКРПОШТИ',
        'Nova Poshta courier' => 'Кур\'єр Нової Пошти',
        'UKRPOSTA courier' => 'Кур\'єр УКРПОШТИ',
    ];

    public static function toUkr(string $value): string
    {
        return self::$translations[$value] ?? $value;
    }
}
