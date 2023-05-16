<?php

namespace CranachDigitalArchive\Importer\Interfaces;

interface ICache
{
    public function store(): self;
    public function reset(bool $force): self;
    public function set(string $key, mixed $value): self;
    public function get(string $key, mixed $orElse = null): mixed;
}
