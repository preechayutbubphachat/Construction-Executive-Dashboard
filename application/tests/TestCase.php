<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $override = getenv('CED_TEST_RUNTIME_PATH');
        $runtimePath = $override === false || $override === ''
            ? null
            : $this->resolveRuntimePath($override);

        $runtimePath ??= $this->resolveRuntimePath();
        $storageOverride = getenv('LARAVEL_STORAGE_PATH');
        $storagePath = $storageOverride === false || $storageOverride === ''
            ? "{$runtimePath}/storage"
            : $storageOverride;
        $files = new Filesystem;
        $files->deleteDirectory($runtimePath);
        $files->ensureDirectoryExists("{$storagePath}/framework/views");

        parent::setUp();

        $files->ensureDirectoryExists("{$runtimePath}/views");
        if ($storageOverride === false || $storageOverride === '') {
            $this->app->useStoragePath($storagePath);
        }
        config()->set('app.key', str_repeat('0', 32));
        config()->set('view.compiled', "{$runtimePath}/views");
        config()->set('logging.channels.single.path', "{$runtimePath}/logs/laravel.log");
        config()->set('filesystems.disks.evidence.root', "{$runtimePath}/evidence");
        $this->beforeApplicationDestroyed(function () use ($files, $runtimePath): void {
            $resolvedRuntimePath = realpath($runtimePath);

            if ($resolvedRuntimePath === false
                || $this->normalizeWindowsPath($resolvedRuntimePath) !== $this->normalizeWindowsPath($runtimePath)) {
                throw new \RuntimeException('Runtime cleanup path escaped its resolved boundary.');
            }

            $files->deleteDirectory($resolvedRuntimePath);
        });
    }

    protected function resolveRuntimePath(?string $override = null): string
    {
        $override ??= getenv('CED_TEST_RUNTIME_PATH');

        if ($override === false || $override === '') {
            return dirname(__DIR__).DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'.runtime';
        }

        $normalized = $this->normalizeWindowsPath($override);
        $tempRoot = realpath(sys_get_temp_dir());
        $applicationRoot = realpath(dirname(__DIR__));
        $productRoot = $applicationRoot === false ? false : realpath(dirname($applicationRoot));

        if ($normalized === null || $tempRoot === false || $applicationRoot === false || $productRoot === false) {
            throw new \InvalidArgumentException('CED_TEST_RUNTIME_PATH must be an absolute, safe Windows path.');
        }

        $tempRoot = $this->normalizeWindowsPath($tempRoot);
        $candidate = realpath($override);
        $candidate = $candidate === false ? $normalized : $this->normalizeWindowsPath($candidate);
        $relativeToTemp = $candidate === null ? null : $this->relativePath($candidate, $tempRoot);

        if ($candidate === null || ! $this->isWithinPath($candidate, $tempRoot)
            || $relativeToTemp === null || $relativeToTemp === '' || ! str_contains($relativeToTemp, '/')
            || ! preg_match('~^CED(?:[-_.]|$)~i', explode('/', $relativeToTemp)[0])
            || $this->isWithinAnyPath($candidate, [
                $productRoot,
                realpath(__DIR__),
                realpath($applicationRoot.DIRECTORY_SEPARATOR.'storage'),
                realpath($applicationRoot.DIRECTORY_SEPARATOR.'vendor'),
                $this->normalizeWindowsPath(getcwd() ?: ''),
            ])) {
            throw new \InvalidArgumentException('CED_TEST_RUNTIME_PATH must remain a strict descendant of a CED temp namespace.');
        }

        if (realpath($override) === false) {
            $parent = realpath(dirname($override));
            if ($parent === false || ! $this->isWithinPath($this->normalizeWindowsPath($parent), $tempRoot)) {
                throw new \InvalidArgumentException('CED_TEST_RUNTIME_PATH must be safely creatable under a CED temp child.');
            }
        }

        return $candidate;
    }

    protected function normalizeWindowsPath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));

        if (! preg_match('~^[A-Za-z]:/~', $path) || preg_match('~^[A-Za-z]:$|^//|^/+$~', $path)) {
            return null;
        }

        $prefix = substr($path, 0, 3);
        $parts = [];
        foreach (explode('/', substr($path, 3)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                if ($parts === []) {
                    return null;
                }
                array_pop($parts);

                continue;
            }
            $parts[] = $part;
        }

        return $parts === [] ? null : $prefix.implode('/', $parts);
    }

    protected function isWithinAnyPath(string $path, array $roots): bool
    {
        foreach ($roots as $root) {
            if ($root !== false && $root !== null && $this->isWithinPath($path, $root)) {
                return true;
            }
        }

        return false;
    }

    protected function isWithinPath(string $path, string $root): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return strcasecmp($path, $root) === 0
            || strncasecmp($path, $root.'/', strlen($root) + 1) === 0;
    }

    protected function relativePath(string $path, string $root): ?string
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');

        if (strcasecmp($path, $root) === 0) {
            return '';
        }

        if (strncasecmp($path, $root.'/', strlen($root) + 1) !== 0) {
            return null;
        }

        return substr($path, strlen($root) + 1);
    }
}
