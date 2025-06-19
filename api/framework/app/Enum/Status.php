<?php

namespace App\Enum;

class Status
{
    // Constantes para os status
    public const ANDAMENTO = 'andamento';
    public const CANCELADO = 'cancelado';
    public const ENTREGUE = 'entregue';

    /**
     * Método estático para retornar todos os status disponíveis
     *
     * @return array
     */
    public static function todos(): array
    {
        return [
            self::ANDAMENTO,
            self::CANCELADO,
            self::ENTREGUE,
        ];
    }

    //
    /**
     * Método estático para verificar se um status é válido
     *
     * @param string $status
     * @return boolean
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::todos());
    }
}
