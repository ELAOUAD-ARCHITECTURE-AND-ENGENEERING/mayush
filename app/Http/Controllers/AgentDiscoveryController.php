<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class AgentDiscoveryController extends Controller
{
    public function apiCatalog(): Response
    {
        $catalog = [
            'linkset' => [
                [
                    'anchor' => url('/api/v2'),
                    'service-desc' => [
                        [
                            'href' => url('/openapi.json'),
                            'type' => 'application/openapi+json',
                        ],
                    ],
                    'service-doc' => [
                        [
                            'href' => url('/docs/api'),
                            'type' => 'text/html',
                        ],
                    ],
                    'status' => [
                        [
                            'href' => url('/up'),
                            'type' => 'text/plain',
                        ],
                    ],
                ],
            ],
        ];

        return $this->jsonResponse($catalog, 'application/linkset+json; charset=UTF-8');
    }

    public function openApi(): Response
    {
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Mayush Public API',
                'version' => '1.0.0',
                'description' => 'Machine-readable description for the documented Mayush API surface intentionally exposed to authenticated clients.',
            ],
            'servers' => [
                ['url' => url('/api/v2')],
            ],
            'paths' => [
                '/promotions' => [
                    'get' => [
                        'summary' => 'List seller promotion requests',
                        'security' => [['systemKey' => [], 'bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'status',
                                'in' => 'query',
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['awaiting_admin_review', 'approved', 'rejected', 'expired'],
                                ],
                            ],
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Successful response'],
                            '401' => ['description' => 'Missing or invalid bearer token'],
                            '403' => ['description' => 'Missing or invalid System-Key header'],
                        ],
                    ],
                    'post' => [
                        'summary' => 'Create a seller promotion request',
                        'security' => [['systemKey' => [], 'bearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['product_id', 'tier', 'start_date', 'end_date'],
                                        'properties' => [
                                            'product_id' => ['type' => 'integer'],
                                            'tier' => [
                                                'type' => 'string',
                                                'enum' => ['standard', 'premium', 'gold'],
                                            ],
                                            'start_date' => ['type' => 'string', 'format' => 'date'],
                                            'end_date' => ['type' => 'string', 'format' => 'date'],
                                            'notes' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Promotion created'],
                            '401' => ['description' => 'Missing or invalid bearer token'],
                            '403' => ['description' => 'Missing System-Key, insufficient credits, or limit reached'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/promotions/{id}' => [
                    'patch' => [
                        'summary' => 'Update a promotion request',
                        'security' => [['systemKey' => [], 'bearerAuth' => []]],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'status' => [
                                                'type' => 'string',
                                                'enum' => ['approved', 'rejected', 'expired'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Status updated'],
                            '401' => ['description' => 'Missing or invalid bearer token'],
                            '403' => ['description' => 'Missing or invalid System-Key header'],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                    'systemKey' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'System-Key',
                    ],
                ],
            ],
        ];

        return $this->jsonResponse($openApi, 'application/openapi+json; charset=UTF-8');
    }

    public function apiDocs(): Response
    {
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Mayush API Documentation</title></head><body><main><h1>Mayush API Documentation</h1><p>The public API catalog advertises only the documented authenticated promotions API surface.</p><p>OpenAPI description: <a href="' . e(url('/openapi.json')) . '">/openapi.json</a></p><p>Status endpoint: <a href="' . e(url('/up')) . '">/up</a></p></main></body></html>';

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function agentSkillsIndex(): Response
    {
        $skills = [];
        foreach ($this->agentSkills() as $slug => $skill) {
            $body = $this->agentSkillBody($slug);
            $skills[] = [
                'name' => $skill['name'],
                'type' => 'reference',
                'description' => $skill['description'],
                'url' => url("/.well-known/agent-skills/{$slug}.json"),
                'sha256' => hash('sha256', $body),
            ];
        }

        return $this->jsonResponse([
            '$schema' => 'https://agentskills.io/schemas/agent-skills-index-v0.2.json',
            'skills' => $skills,
        ]);
    }

    public function agentSkill(string $slug): Response
    {
        if (!array_key_exists($slug, $this->agentSkills())) {
            abort(404);
        }

        return response($this->agentSkillBody($slug), 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    private function agentSkillBody(string $slug): string
    {
        return json_encode($this->agentSkills()[$slug], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function agentSkills(): array
    {
        return [
            'product-discovery' => [
                'name' => 'mayush-product-discovery',
                'description' => 'Guidance for discovering public Mayush furniture, decor, seller, category, and brand pages.',
                'instructions' => [
                    'Use public category, brand, seller, and product pages for discovery.',
                    'Respect robots.txt and sitemap.xml.',
                    'Do not automate checkout, account, payment, or seller actions from this skill.',
                ],
                'entrypoints' => [
                    url('/'),
                    url('/categories'),
                    url('/brands'),
                    url('/sellers'),
                    url('/sitemap.xml'),
                ],
            ],
            'policy-lookup' => [
                'name' => 'mayush-policy-lookup',
                'description' => 'Guidance for reading Mayush public policy pages for returns, support, seller terms, privacy, and terms.',
                'instructions' => [
                    'Use public policy pages for informational answers.',
                    'Prefer canonical URLs and quote policy text only when necessary.',
                    'Do not infer account-specific or order-specific policy outcomes.',
                ],
                'entrypoints' => [
                    url('/return-policy'),
                    url('/support-policy'),
                    url('/seller-policy'),
                    url('/privacy-policy'),
                    url('/terms'),
                ],
            ],
        ];
    }

    private function jsonResponse(array $data, string $contentType = 'application/json; charset=UTF-8'): Response
    {
        return response(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => $contentType,
        ]);
    }
}
