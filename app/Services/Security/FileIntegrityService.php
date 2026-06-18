<?php

namespace RoyalPanel\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FileIntegrityService
{
    protected array $directories = [
        'app',
        'config',
    ];

    protected array $files = [
        '.env',
        'composer.json',
        'composer.lock',
    ];

    public function scan(): array
    {
        $results = [];
        $basePath = base_path();

        foreach ($this->directories as $dir) {
            $path = $basePath . '/' . $dir;
            if (!is_dir($path)) continue;
            $files = File::allFiles($path);
            foreach ($files as $file) {
                $relativePath = $dir . '/' . $file->getRelativePathname();
                if ($this->isExcluded($relativePath)) continue;
                $hash = md5_file($file->getRealPath());
                $results[] = $this->checkFile($relativePath, $hash);
            }
        }

        foreach ($this->files as $file) {
            $path = $basePath . '/' . $file;
            if (!file_exists($path)) continue;
            $hash = md5_file($path);
            $results[] = $this->checkFile($file, $hash);
        }

        return $results;
    }

    public function getResults(): array
    {
        return DB::table('file_integrity_checks')
            ->orderByDesc('last_checked_at')
            ->get()
            ->toArray();
    }

    public function getStats(): array
    {
        $results = $this->getResults();
        return [
            'total' => count($results),
            'clean' => count(array_filter($results, fn($r) => $r->status === 'clean')),
            'modified' => count(array_filter($results, fn($r) => $r->status === 'modified')),
            'missing' => count(array_filter($results, fn($r) => $r->status === 'missing')),
            'new' => count(array_filter($results, fn($r) => $r->status === 'new')),
        ];
    }

    protected function checkFile(string $relativePath, string $hash): array
    {
        $existing = DB::table('file_integrity_checks')
            ->where('file_path', $relativePath)
            ->first();

        if (!$existing) {
            DB::table('file_integrity_checks')->insert([
                'file_path' => $relativePath,
                'expected_hash' => $hash,
                'status' => 'new',
                'last_checked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return ['file_path' => $relativePath, 'status' => 'new'];
        }

        if (!file_exists(base_path($relativePath))) {
            DB::table('file_integrity_checks')
                ->where('id', $existing->id)
                ->update(['status' => 'missing', 'last_checked_at' => now(), 'updated_at' => now()]);
            return ['file_path' => $relativePath, 'status' => 'missing'];
        }

        if ($existing->expected_hash !== $hash) {
            DB::table('file_integrity_checks')
                ->where('id', $existing->id)
                ->update(['expected_hash' => $hash, 'status' => 'modified', 'last_checked_at' => now(), 'updated_at' => now()]);
            return ['file_path' => $relativePath, 'status' => 'modified'];
        }

        DB::table('file_integrity_checks')
            ->where('id', $existing->id)
            ->update(['status' => 'clean', 'last_checked_at' => now(), 'updated_at' => now()]);
        return ['file_path' => $relativePath, 'status' => 'clean'];
    }

    protected function isExcluded(string $path): bool
    {
        $excluded = ['/cache/', '/storage/', '/vendor/', '/node_modules/', '.git'];
        foreach ($excluded as $pattern) {
            if (str_contains($path, $pattern)) return true;
        }
        return false;
    }
}
