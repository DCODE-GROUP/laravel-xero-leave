<?php

namespace Dcodegroup\LaravelXeroLeave\Http\Requests;

use Dcodegroup\LaravelXeroLeave\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeave extends FormRequest
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
            'description' => 'nullable|max:200',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|min:3|max:50',
            'units' => 'nullable|numeric|min:0.5|max:'.config('laravel-xero-leave.default_work_hours'),
            'user_id' => 'required|integer|exists:users,id',
            'xero_leave_type_id' => ['required','string', Rule::in(Leave::getValidXeroLeaveTypes())],
        ];
    }
}
