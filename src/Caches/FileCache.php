<?php

namespace CranachDigitalArchive\Importer\Caches;

use CranachDigitalArchive\Importer\Interfaces\ICache;

class FileCache implements ICache
{
    private string $filename;
    private string $directoryPath;
    private string $fileSuffix;
    private array $cache = [];
    private bool $wasUpdated = false;

    private function __construct(
        string $filename,
        string $directoryPath,
        bool $refresh = false,
        string $fileSuffix = '.cache',
    ) {
        $this->filename = ltrim($filename, DIRECTORY_SEPARATOR);
        $this->directoryPath = rtrim($directoryPath, DIRECTORY_SEPARATOR);
        $this->fileSuffix = $fileSuffix;

        if (!file_exists($this->directoryPath)) {
            @mkdir($this->directoryPath, 0777, true);
        }

        if (!$refresh) {
            $this->restoreCache();
        }
    }


    public static function new(
        string $filename,
        string $directoryPath,
        bool $refresh = false,
        string $fileSuffix = '.cache',
    ): self {
        return new self($filename, $directoryPath, $refresh, $fileSuffix);
    }


    public function store(): self
    {
        /* Preventing disk-writing if nothing changed / the cache was not updated */
        if ($this->wasUpdated) {
            $cacheAsJSON = json_encode($this->cache, JSON_UNESCAPED_UNICODE);
            file_put_contents($this->getCachePath(), $cacheAsJSON);
            $this->wasUpdated = false;
        }
        return $this;
    }


    public function reset(bool $force): self
    {
        $this->cache = [];
        $cachePath = $this->getCachePath();

        /* We verify that the cache file is really a file as a simple check;
         * if reset is not forced */
        if ($force || is_file($cachePath)) {
            unlink($this->getCachePath());
        }
        return $this;
    }


    public function set(string $key, mixed $value): self
    {
        $this->cache[$key] = $value;
        $this->wasUpdated = true;
        return $this;
    }


    public function get(string $key, mixed $orElse = null): mixed
    {
        return isset($this->cache[$key]) ? $this->cache[$key]: $orElse;
    }


    private function restoreCache(): void
    {
        $cacheFilepath = $this->getCachePath();
        if (!file_exists($cacheFilepath)) {
            return;
        }

        $cacheAsJSON = file_get_contents($cacheFilepath);

        $this->cache = json_decode($cacheAsJSON, true, 512, JSON_UNESCAPED_UNICODE);
    }


    private function getCachePath(): string
    {
        return $this->directoryPath . DIRECTORY_SEPARATOR . $this->filename . $this->fileSuffix;
    }

}
