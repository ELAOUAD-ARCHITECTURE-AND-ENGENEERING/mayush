<?php

namespace App\Contracts;

interface ProductTranslationService
{
    /**
     * Translate the supplied product fields while preserving their keys and shape.
     *
     * @return array{success: bool, fields: array, failed_fields: array, errors: array, error_code?: string}
     */
    public function translateFields(
        array $fields,
        string $sourceLanguage = 'fr',
        string $targetLanguage = 'ar'
    ): array;
}
