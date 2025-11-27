<?php

namespace App\Enums;

final class ContractType
{
    public const NOMBRADO = 'nombrado';
    public const TIEMPO_COMPLETO = 'a tiempo completo';
    public const TEMPORAL = 'temporal';

    public static function eligibleForVacation(): array
    {
        return [self::NOMBRADO, self::TIEMPO_COMPLETO];
    }

    public static function all(): array
    {
        return [self::NOMBRADO, self::TIEMPO_COMPLETO, self::TEMPORAL];
    }
}



