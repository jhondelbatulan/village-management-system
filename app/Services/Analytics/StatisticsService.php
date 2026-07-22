<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;

class StatisticsService
{
    public function cached(string $key, callable $callback, int $ttl = 300): mixed
    {
        return Cache::remember("analytics:{$key}", $ttl, $callback);
    }

    public function flushCache(?string $prefix = null): void
    {
        $cacheStore = Cache::getStore();

        if (method_exists($cacheStore, 'getRedis')) {
            $redis = $cacheStore->getRedis();
            $keys = $prefix
                ? $redis->keys("analytics:{$prefix}*")
                : $redis->keys("analytics:*");

            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            if ($prefix) {
                $tags = ["analytics:{$prefix}"];
            } else {
                $tags = ['analytics'];
            }
            $this->flushByPrefix($prefix);
        }
    }

    protected function flushByPrefix(?string $prefix = null): void
    {
        $tag = $prefix ? "analytics:{$prefix}" : 'analytics';
        $cacheDir = storage_path('framework/cache/data');
        if (!is_dir($cacheDir)) return;

        $files = glob($cacheDir . '/*/*');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content !== false && str_contains($content, $tag)) {
                @unlink($file);
            }
        }
    }

    public function frequencyCount(array $data): array
    {
        $total = array_sum($data);
        $result = [];

        foreach ($data as $label => $count) {
            $result[$label] = [
                'label' => $label,
                'count' => (int) $count,
                'percentage' => $this->percentage((int) $count, $total),
            ];
        }

        return $result;
    }

    public function percentage(float $part, float $total, int $decimals = 2): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($part / $total) * 100, $decimals);
    }

    public function mean(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $filtered = array_filter($values, fn($v) => is_numeric($v));

        if (empty($filtered)) {
            return 0.0;
        }

        return round(array_sum($filtered) / count($filtered), 2);
    }

    public function median(array $values): float
    {
        $filtered = array_values(array_filter($values, fn($v) => is_numeric($v)));

        if (empty($filtered)) {
            return 0.0;
        }

        sort($filtered);
        $count = count($filtered);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            return round(($filtered[$middle - 1] + $filtered[$middle]) / 2, 2);
        }

        return round($filtered[$middle], 2);
    }

    public function growthRate(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function percentageDistribution(array $frequencies): array
    {
        $total = array_sum($frequencies);

        return array_map(fn($count) => $this->percentage($count, $total), $frequencies);
    }

    public function frequencyTable(array $frequencies): array
    {
        $total = array_sum($frequencies);
        $result = [];

        foreach ($frequencies as $label => $count) {
            $result[] = [
                'label' => $label,
                'frequency' => (int) $count,
                'percentage' => $this->percentage((int) $count, $total),
                'cumulative' => 0,
            ];
        }

        $cumulative = 0;
        foreach ($result as &$row) {
            $cumulative += $row['percentage'];
            $row['cumulative'] = round($cumulative, 2);
        }
        unset($row);

        return $result;
    }
}
