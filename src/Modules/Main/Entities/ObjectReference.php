<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a single object reference by inventory number
 */
class ObjectReference
{
    public $text = '';
    public $inventoryNumber = '';
    public $remark = '';


    public function __construct()
    {
    }


    public function setText(string $text)
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setInventoryNumber(string $inventoryNumber)
    {
        $this->inventoryNumber = $inventoryNumber;
    }


    public function getInventoryNumber(): string
    {
        return $this->inventoryNumber;
    }


    public function setRemark(string $remark)
    {
        $this->remark = $remark;
    }


    public function getRemark(): string
    {
        return $this->remark;
    }
}
