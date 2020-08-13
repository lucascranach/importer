<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

/**
 * Representing an item with multiple images
 */
abstract class AbstractImagesItem
{
    public $images = null;

    /* Awaited images structure if images exist
        [
            'infos' => [
                'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
            ],
            'sizes' => [
                'xs' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                's' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'm' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'l' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
                'xl' => [
                    'dimensions' => [ 'width' => 0, 'height' => 0 ],
                    'src' => '',
                ],
            ],
        ]
    */


    abstract public function getId(): string;

    abstract public function getImageId(): string;


    public function setImages(?array $images)
    {
        $this->images = $images;
    }


    public function getImages(): array
    {
        return $this->images;
    }
}
