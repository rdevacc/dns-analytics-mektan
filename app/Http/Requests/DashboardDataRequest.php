<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'vlan_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'client_ip' => [
                'nullable',
                'ip',
            ],
            'client_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}