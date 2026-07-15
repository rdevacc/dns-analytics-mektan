<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DnsQueryDataRequest extends FormRequest
{
    private const ALLOWED_LENGTHS = [
        10,
        25,
        50,
        100,
        250,
        500,
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'draw' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'start' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'length' => [
                'nullable',
                'integer',
                Rule::in(self::ALLOWED_LENGTHS),
            ],

            'order' => [
                'nullable',
                'array',
            ],

            'order.0.column' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'order.0.dir' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],

            'search' => [
                'nullable',
                'array',
            ],

            'search.value' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
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

            'domain' => [
                'nullable',
                'string',
                'max:253',
            ],

            'query_type' => [
                'nullable',
                'string',
                'max:20',
            ],

            'status' => [
                'nullable',
                'string',
                'max:100',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:100',
            ],

            'disallowed' => [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ],

            'cached' => [
                'nullable',
                Rule::in(['0', '1', 0, 1]),
            ],

            'upstream' => [
                'nullable',
                'string',
                'max:255',
            ],

            'filter_id' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'matched_rule' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}