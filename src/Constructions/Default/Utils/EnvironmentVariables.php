<?php

namespace CranachDigitalArchive\Importer\Constructions\Default\Utils;

final class EnvironmentVariables
{
    private string $imagesAPIKey;
    private string $cacheDirectoryPath;
    private string $excludeInventoryNumberPrefix;

    private function __construct(string $rootPath)
    {
        $this->loadEnv($rootPath);

        $fallbackCacheDirectoryPath = './.cache';

        $fallbackExcludeInventoryNumberPrefix = '==';

        $this->imagesAPIKey = $_ENV['IMAGES_API_KEY'];
        $this->cacheDirectoryPath = $_ENV['CACHE_DIR'] ?? $fallbackCacheDirectoryPath;

        $excludeInventoryNumberPrefix = trim(strval($_ENV['EXCLUDE_INVENTORY_NUMBER_PREFIX'] ?? ''));
        $this->excludeInventoryNumberPrefix = ($excludeInventoryNumberPrefix !== '')
            ? $excludeInventoryNumberPrefix
            : $fallbackExcludeInventoryNumberPrefix;
    }

    public static function new(string $rootPath): self
    {
        return new self($rootPath);
    }

    public function getImagesAPIKey(): string
    {
        return $this->imagesAPIKey;
    }

    public function getCacheDirectoryPath(): string
    {
        return $this->cacheDirectoryPath;
    }

    public function getExcludeInventoryNumberPrefix(): string
    {
        return $this->excludeInventoryNumberPrefix;
    }

    private function loadEnv(string $rootPath): void
    {
        /* Read .env file */
        $dotenv = \Dotenv\Dotenv::createImmutable($rootPath);

        try {
            $dotenv->load();
        } catch (\Throwable $e) {
            echo "Missing .env file!\nSee README.md for more.\n\n";
            exit();
        }
    }
}
