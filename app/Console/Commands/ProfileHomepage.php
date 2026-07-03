<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileHomepage extends Command
{
    protected $signature = 'storefront:profile-homepage
        {--path=/ : Storefront path to profile}
        {--repeat=1 : Number of times to request each target}
        {--warm : Warm storefront caches before profiling}
        {--sections : Profile deferred homepage section endpoints too}
        {--show-queries : Show duplicate and slow query families for each target}';

    protected $description = 'Profile homepage response time and DB query count from the Laravel request path.';

    public function handle(HttpKernel $kernel): int
    {
        if ($this->option('warm')) {
            $this->call('storefront:cache-warm', ['--with-sections' => true]);
        }

        $repeat = max(1, (int) $this->option('repeat'));
        $targets = [['Homepage', (string) $this->option('path')]];

        if ($this->option('sections')) {
            $targets = array_merge($targets, [
                ['Featured section', route('home.section.featured', [], false)],
                ['Today deal section', route('home.section.todays_deal', [], false)],
                ['Best selling section', route('home.section.best_selling', [], false)],
                ['Newest section', route('home.section.newest_products', [], false)],
                ['Home categories section', route('home.section.home_categories', [], false)],
                ['Best sellers section', route('home.section.best_sellers', [], false)],
                ['Preorder section', route('home.section.preorder_products', [], false)],
            ]);
        }

        $rows = [];
        $hadFailure = false;

        foreach ($targets as [$label, $path]) {
            for ($run = 1; $run <= $repeat; $run++) {
                try {
                    $profile = $this->profileTarget($kernel, $label, $path, $run);
                    $rows[] = $profile['row'];

                    if ($this->option('show-queries')) {
                        $this->renderQuerySummary($label, $profile['queries']);
                    }
                } catch (Throwable $exception) {
                    $hadFailure = true;
                    $rows[] = [
                        'target' => $label,
                        'run' => $run,
                        'status' => 'ERR',
                        'bytes' => 0,
                        'app_ms' => 0,
                        'db_ms' => 0,
                        'queries' => 0,
                    ];

                    $this->error($label.' failed: '.$exception->getMessage());
                }
            }
        }

        $this->table(['Target', 'Run', 'Status', 'Bytes', 'App ms', 'DB ms', 'Queries'], $rows);

        return $hadFailure ? self::FAILURE : self::SUCCESS;
    }

    private function profileTarget(HttpKernel $kernel, string $label, string $path, int $run): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $startedAt = hrtime(true);
        $request = Request::create($path, 'GET', [], [], [], $this->serverParameters());

        try {
            $response = $kernel->handle($request);
            $kernel->terminate($request, $response);
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
        }

        return [
            'row' => [
            'target' => $label,
            'run' => $run,
            'status' => $response->getStatusCode(),
            'bytes' => strlen($response->getContent()),
            'app_ms' => number_format((hrtime(true) - $startedAt) / 1_000_000, 1, '.', ''),
            'db_ms' => number_format(collect($queries)->sum('time'), 1, '.', ''),
            'queries' => count($queries),
            ],
            'queries' => $queries,
        ];
    }

    private function renderQuerySummary(string $label, array $queries): void
    {
        if ($queries === []) {
            return;
        }

        $summary = collect($queries)
            ->groupBy(fn ($query) => $this->normalizeQuery($query['query']))
            ->map(fn ($items, $query) => [
                'count' => $items->count(),
                'total_ms' => number_format($items->sum('time'), 1, '.', ''),
                'query' => mb_strimwidth($query, 0, 120, '...'),
            ])
            ->sortByDesc('count')
            ->take(8)
            ->values()
            ->all();

        $this->line('');
        $this->line($label.' query families:');
        $this->table(['Count', 'Total ms', 'Query'], $summary);
    }

    private function normalizeQuery(string $query): string
    {
        return preg_replace('/\s+/', ' ', trim($query));
    }

    private function serverParameters(): array
    {
        $appUrl = (string) config('app.url', 'http://localhost');
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';

        return [
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
            'HTTPS' => $scheme === 'https' ? 'on' : 'off',
        ];
    }
}
