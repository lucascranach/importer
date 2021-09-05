<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities\Search;

/**
 * Representing a single filter info item
 */
class FilterInfoItem
{
    public $id = null;
    public $parentId = null;
    public $text = '';
    public $order = 0;


    public function __construct()
    {
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }


    public function getParentId(): ?string
    {
        return $this->parentId;
    }


    public function setText(string $text): void
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }


    public function getOrder(): int
    {
        return $this->order;
    }
}
