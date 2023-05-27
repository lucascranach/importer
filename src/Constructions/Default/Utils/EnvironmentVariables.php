<?php

namespace CranachDigitalArchive\Importer\Constructions\Default\Utils;

final class EnvironmentVariables
{
    private string $imagesAPIKey;
    private string $cacheDirectoryPath;

    private function __construct(string $rootPath)
    {
        $this->loadEnv($rootPath);

        $fallbackCacheDirectoryPath = './.cache';

        $this->imagesAPIKey = $_ENV['IMAGES_API_KEY'];
        $this->cacheDirectoryPath = $_ENV['CACHE_DIR'] ?? $fallbackCacheDirectoryPath;
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
