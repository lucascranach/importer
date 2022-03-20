<?php

namespace CranachDigitalArchive\Importer\Modules\Filters\Entities;

/**
 * Container class to connect filters to a language
 */
class LangFilterContainer
{
    public $lang = '';
    public $filter = null;


    public function __construct(string $lang, Filter $filter)
    {
        $this->setLang($lang);
        $this->setFilter($filter);
    }


    public function getLang(): string
    {
        return $this->lang;
    }


    public function setLang(string $lang)
    {
        $this->lang = $lang;
    }


    public function getFilter(): Filter
    {
        return $this->filter;
    }


    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
    }
}
