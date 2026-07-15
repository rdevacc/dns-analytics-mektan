<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DnsQuery extends Model
{
    use HasFactory;

    protected $table = 'dns_queries';

    public $timestamps = false;

    protected $fillable = [
        'query_id',
        'event_time',
        'client_ip',
        'client_name',
        'vlan_name',
        'client_proto',
        'domain',
        'query_class',
        'query_type',
        'status',
        'reason',
        'filter_id',
        'matched_rule',
        'cached',
        'elapsed_ms',
        'upstream',
        'answer_dnssec',
        'disallowed',
        'disallowed_rule',
        'client_whois_json',
        'answers_json',
        'rules_json',
        'raw_json',
        'ingested_at',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'filter_id' => 'integer',
            'cached' => 'boolean',
            'elapsed_ms' => 'float',
            'answer_dnssec' => 'boolean',
            'disallowed' => 'boolean',
            'ingested_at' => 'datetime',
        ];
    }
}