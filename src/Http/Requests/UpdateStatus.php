<?php

namespace Dcodegroup\LaravelXeroLeave\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatus extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => ['required', 'string', Rule::in(['approve', 'decline', 'pending'])],
            'reason' => ['nullable', 'string', Rule::requiredIf(function () {
                return $this->get('action') == 'decline';
            })],
        ];
    }
}
