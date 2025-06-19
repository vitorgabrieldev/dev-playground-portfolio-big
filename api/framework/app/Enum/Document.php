<?php

namespace App\Enum;

class Document
{
    // Constantes para os tipos de documentos
    public const CPF = 'CPF';
    public const CNPJ = 'CNPJ';
    public const DESCONHECIDO = 'DESCONHECIDO';

    /**
     * Método estático para retornar todos os status disponíveis
     *
     * @return array
     */
    public static function todos(): array
    {
        return [
            self::CPF,
            self::CNPJ,
            self::DESCONHECIDO,
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
