<?php

namespace CranachDigitalArchive\Importer\Modules\Main\Gates;

use Error;
use CranachDigitalArchive\Importer\Interfaces\Entities\IBaseItem;
use CranachDigitalArchive\Importer\Interfaces\Pipeline\ProducerInterface;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;

class SkipSoftDeletedArtefactGate extends Hybrid
{
    private $idDeletedChar = 'X';

    private $removedSoftDeletedArtefactIds = [];
    private $typeDescription = '';

    private function __construct()
    {
    }

    public static function new(string $typeDescription = ''): self
    {
        $gate = new self;

        $gate->typeDescription = $typeDescription;

        return $gate;
    }


    public function handleItem($item): bool
    {
        if (!($item instanceof IBaseItem)) {
            throw new Error('Pushed item expected to implement \'IBaseItem\' interface');
        }

        if (!$this->isSoftDeleted($item->getId())) {
            $this->next($item);
        } else {
            $this->removedSoftDeletedArtefactIds[$item->getId()] = true;
        }

        return true;
    }


    public function done(ProducerInterface $producer)
    {
        $typeDescriptionStr = (!empty($this->typeDescription)) ? ' (' . $this->typeDescription . ')' : '';
        echo "\n  Skipped soft-deleted Artefacts" . $typeDescriptionStr . ": \n";
        foreach (array_keys($this->removedSoftDeletedArtefactIds) as $artefactId) {
            echo '      - ' . $artefactId . "\n";
        }
        echo "\n";

        $this->removedSoftDeletedArtefactIds = [];

        parent::done($producer);
    }


    public function isSoftDeleted(string $id)
    {
        return strlen($id) > 0 && strtoupper($id[0]) === $this->idDeletedChar;
    }
}
