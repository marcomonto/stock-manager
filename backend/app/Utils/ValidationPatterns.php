<?php

namespace App\Utils;

class ValidationPatterns
{
    public const string ARRAY_REQUIRED = 'array|required';
    public const string STRING_REQUIRED = 'string|required';
    public const string STRING_REQUIRED_255 = 'string|required';
    public const string ULID_REQUIRED = 'string|required|ulid';
    public const string INT_REQUIRED_POSITIVE = 'int|required|min:1';
    public const string STRING_NULLABLE = 'string|nullable';
    public const string STRING_NULLABLE_255 = 'string|nullable|max:255';
    public const string BOOL_NULLABLE = 'bool|nullable';

    public const string DATE_NULLABLE = 'date_format:Y-m-d|nullable';
}
