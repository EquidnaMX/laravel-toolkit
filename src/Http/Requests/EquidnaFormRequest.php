<?php

namespace Equidna\Toolkit\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Equidna\Toolkit\Exceptions\BadRequestException;

/**
 * EquidnaFormRequest extends Laravel's FormRequest to provide
 * context-aware validation error handling. On validation failure,
 * it throws a BadRequestException with a standard message and the
 * validation errors array, ensuring unified error responses for API,
 * web, and other contexts.
 *
 * @package Equidna\Toolkit\Http\Requests
 */
class EquidnaFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws BadRequestException
     */
    public function failedValidation(Validator $validator)
    {
        throw new BadRequestException(
            message: 'Error de validación',
            errors: $validator->errors()->toArray()
        );
    }
}
