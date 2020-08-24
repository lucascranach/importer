<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a the dimension in a structured way
 */
class StructuredDimension
{
    public $element = '';
    public $width = null;
    public $height = null;


    public function __construct()
    {
    }


    public function setElement(string $element)
    {
        $this->element = $element;
    }


    public function getElement(): string
    {
        return $this->element;
    }


    public function setWidth(string $width)
    {
        $this->width = $width;
    }


    public function getWidth(): string
    {
        return $this->width;
    }


    public function setHeight(string $height)
    {
        $this->height = $height;
    }


    public function getHeight(): string
    {
        return $this->height;
    }
}
