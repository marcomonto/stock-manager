<?php

namespace App\Utils;

class ValidationPatterns
{
    public const string ARRAY_REQUIRED = 'array|required';
    public const string STRING_REQUIRED = 'string|required';
    public const string ULID_REQUIRED = 'string|required|ulid';
    public const string INT_REQUIRED_POSITIVE = 'int|required|min:0';
    public const string STRING_NULLABLE = 'string|nullable';
}
