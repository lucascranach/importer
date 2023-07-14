<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing a metadata block
 */
class Metadata
{
    public $entityType = '';
    public $langCode = '';
    public $id = '';
    public $title = '';
    public $subtitle = '';
    public $date = '';
    public $additionalInfos = [];
    public $classification = '';
    public $imgSrc = '';
    public $isPublished = false;


    public function __construct()
    {
    }


    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }


    /**
     * @param string $entityType
     */
    public function setEntityType(string $entityType)
    {
        $this->entityType = $entityType;
    }


    /**
     * @return string
     */
    public function getLangCode(): string
    {
        return $this->langCode;
    }


    /**
     * @param string $langCode
     */
    public function setLangCode(string $langCode)
    {
        $this->langCode = $langCode;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }


    /**
     * @param string $subtitle
     */
    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }


    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * @param string $date
     */
    public function setDate(string $date)
    {
        $this->date = $date;
    }


    /**
     * @return array
     */
    public function getAdditionalInfos()
    {
        return $this->additionalInfos;
    }


    /**
     * @param string $additionalInfos
     */
    public function addAdditionalInfo(string $additionalInfo)
    {
        $this->additionalInfos[] = $additionalInfo;
    }


    /**
     * @return string
     */
    public function getClassification()
    {
        return $this->classification;
    }


    /**
     * @param string $classification
     */
    public function setClassification(string $classification)
    {
        $this->classification = $classification;
    }


    /**
     * @return string
     */
    public function getImgSrc()
    {
        return $this->imgSrc;
    }


    /**
     * @param string $imgSrc
     */
    public function setImgSrc(string $imgSrc)
    {
        $this->imgSrc = $imgSrc;
    }


    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }


    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }
}
