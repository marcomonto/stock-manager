<?php

namespace App\Utils;

class ValidationPatterns
{
    public const string ARRAY_REQUIRED = 'array|required';
    public const string STRING_REQUIRED = 'string|required';
    public const string STRING_REQUIRED_255 = 'string|required|max:255';
    public const string ULID_REQUIRED = 'string|required|regex:/^[0-9A-Z]{26}$/';
    public const string INT_REQUIRED_POSITIVE = 'int|required|min:1';
    public const string STRING_NULLABLE = 'string|nullable';
    public const string STRING_NULLABLE_255 = 'string|nullable|max:255';
    public const string BOOL_NULLABLE = 'string|nullable|in:0,1,true,false';

    public const string DATE_NULLABLE = 'date_format:Y-m-d|nullable';

    public static function toBoolean(?string $value = null): bool
    {
        return match (strtolower((string) $value)) {
            '1', 'true' => true,
            default => false,
        };
    }
}
