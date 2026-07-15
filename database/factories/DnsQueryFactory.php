<?php

namespace Database\Factories;

use App\Models\DnsQuery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DnsQueryFactory extends Factory
{
    protected $model = DnsQuery::class;

    public function definition(): array
    {
        $clients = [
            [
                'ip' => '172.16.0.10',
                'name' => 'PC-ADMIN-01',
                'vlan' => 'VLAN ADMIN',
            ],
            [
                'ip' => '172.16.0.11',
                'name' => 'PC-ADMIN-02',
                'vlan' => 'VLAN ADMIN',
            ],
            [
                'ip' => '172.16.10.20',
                'name' => 'PC-KARYAWAN-01',
                'vlan' => 'VLAN KARYAWAN',
            ],
            [
                'ip' => '172.16.10.21',
                'name' => 'LAPTOP-KARYAWAN-01',
                'vlan' => 'VLAN KARYAWAN',
            ],
            [
                'ip' => '172.16.20.30',
                'name' => 'HP-TAMU-01',
                'vlan' => 'VLAN TAMU',
            ],
            [
                'ip' => '172.16.30.40',
                'name' => 'CCTV-NVR-01',
                'vlan' => 'VLAN CCTV',
            ],
        ];

        $domains = [
            'google.com',
            'www.google.com',
            'youtube.com',
            'www.youtube.com',
            'facebook.com',
            'www.facebook.com',
            'instagram.com',
            'www.instagram.com',
            'tiktok.com',
            'www.tiktok.com',
            'github.com',
            'api.github.com',
            'microsoft.com',
            'windowsupdate.com',
            'cloudflare.com',
            'openai.com',
            'chatgpt.com',
            'netflix.com',
            'spotify.com',
            'example.org',
        ];

        $queryTypes = [
            'A',
            'AAAA',
            'HTTPS',
            'CNAME',
            'MX',
            'TXT',
            'PTR',
        ];

        $upstreams = [
            '1.1.1.1:53',
            '8.8.8.8:53',
            '9.9.9.9:53',
            'https://dns.cloudflare.com/dns-query',
        ];

        $client = fake()->randomElement($clients);
        $domain = fake()->randomElement($domains);
        $queryType = fake()->randomElement($queryTypes);

        $isBlocked = fake()->boolean(20);
        $isCached = fake()->boolean(35);

        $status = $isBlocked
            ? fake()->randomElement([
                'FilteredBlackList',
                'FilteredSafeBrowsing',
                'FilteredParental',
            ])
            : 'Processed';

        $reason = $isBlocked
            ? fake()->randomElement([
                'FilteredBlackList',
                'SafeBrowsing',
                'Parental',
            ])
            : '';

        $filterId = $isBlocked
            ? fake()->randomElement([1, 2, 3, 10, 15])
            : 0;

        $matchedRule = $isBlocked
            ? '||' . $domain . '^'
            : null;

        $eventTime = fake()->dateTimeBetween('-30 days', 'now', 'UTC');

        return [
            'query_id' => hash(
                'sha256',
                Str::uuid()->toString()
            ),

            'event_time' => $eventTime,

            'client_ip' => $client['ip'],
            'client_name' => $client['name'],
            'vlan_name' => $client['vlan'],

            'client_proto' => fake()->randomElement([
                'udp',
                'tcp',
                'tls',
                'https',
            ]),

            'domain' => $domain,

            'query_class' => 'IN',
            'query_type' => $queryType,

            'status' => $status,
            'reason' => $reason,

            'filter_id' => $filterId,
            'matched_rule' => $matchedRule,

            'cached' => $isCached,

            'elapsed_ms' => fake()->randomFloat(
                3,
                0.05,
                250
            ),

            'upstream' => $isCached
                ? ''
                : fake()->randomElement($upstreams),

            'answer_dnssec' => fake()->boolean(10),

            'disallowed' => $isBlocked,

            'disallowed_rule' => $isBlocked
                ? $matchedRule
                : null,

            'client_whois_json' => json_encode([]),

            'answers_json' => json_encode([
                [
                    'type' => $queryType,
                    'ttl' => fake()->numberBetween(60, 3600),
                ],
            ]),

            'rules_json' => $isBlocked
                ? json_encode([
                    [
                        'filter_id' => $filterId,
                        'rule' => $matchedRule,
                    ],
                ])
                : json_encode([]),

            'raw_json' => json_encode([
                'question' => [
                    'name' => $domain,
                    'type' => $queryType,
                ],
            ]),

            'ingested_at' => now('UTC'),
        ];
    }
}