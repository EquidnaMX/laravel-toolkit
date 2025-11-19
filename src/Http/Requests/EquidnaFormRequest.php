<?php

/**
 * Provides a base FormRequest with consistent validation failure responses.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Http\Requests
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/validation#form-request-validation Documentation
 */

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
     * Throws a BadRequestException when validation fails to maintain response parity.
     *
     * @param  Validator           $validator Validation result instance containing messages.
     * @return void
     * @throws BadRequestException When validation data is invalid.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new BadRequestException(
            message: 'Validation error',
            errors: $validator->errors()->toArray(),
        );
    }
}
