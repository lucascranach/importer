<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Entities;

use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;

/**
 * Representing an item with multiple images
 */
abstract class AbstractImagesItem implements IBaseItem
{
    public $images = null;

    /* Awaited images structure if images exist
        [
            'overall' => [
                'infos' => [
                    'maxDimensions' => [ 'width' => 0, 'height' => 0 ],
                ],
                'images' => [
                    [
                        'id' => ''
                        'sizes' => [
                            'xsmall' => [
                                'dimensions' => [ 'width' => 0, 'height' => 0 ],
                                'src' => '',
                                'type' => 'plain',
                            ],
                            'small' => [
                                'dimensions' => [ 'width' => 0, 'height' => 0 ],
                                'src' => '',
                                'type' => 'plain',
                            ],
                            'medium' => [
                                'dimensions' => [ 'width' => 0, 'height' => 0 ],
                                'src' => '',
                                'type' => 'plain',
                            ],
                            'origin' => [
                                'dimensions' => [ 'width' => 0, 'height' => 0 ],
                                'src' => '',
                                'type' => 'plain',
                            ],
                            'tiles' => [
                                'dimensions' => [ 'width' => 0, 'height' => 0 ],
                                'src' => '',
                                'type' => 'dzi',
                            ],
                        ],
                    ],
                    // ... multiple variants
                ],
            ],

            // ... multiple image types possible
        ]
    */

    public $documents = null;


    abstract public function getId(): string;

    abstract public function getMetadata(): ?Metadata;

    abstract public function getRemoteId(): string;


    public function setImages(?array $images): void
    {
        $this->images = $images;
    }


    public function getImages(): ?array
    {
        return $this->images;
    }


    public function setDocuments(?array $documents): void
    {
        $this->documents = $documents;
    }


    public function getDocuments(): ?array
    {
        return $this->documents;
    }
}
