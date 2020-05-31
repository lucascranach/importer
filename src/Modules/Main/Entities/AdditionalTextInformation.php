<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing the structure of a single additional text information
 */
class AdditionalTextInformation
{
    public $type = '';
    public $text = '';
    public $date = '';
    public $year = null;
    public $author = '';


    public function __construct()
    {
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function setText(string $text)
    {
        $this->text = $text;
    }


    public function getText(): string
    {
        return $this->text;
    }


    public function setDate(string $date)
    {
        $this->date = $date;
    }


    public function getDate(): string
    {
        return $this->date;
    }


    public function setYear(int $year)
    {
        $this->year = $year;
    }


    public function getYear(): ?int
    {
        return $this->year;
    }


    public function setAuthor(string $author)
    {
        $this->author = $author;
    }


    public function getAuhtor(): ?string
    {
        return $this->author;
    }
}
