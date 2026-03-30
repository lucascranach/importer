<?php
namespace CranachDigitalArchive\Importer\Modules\Graphics\Transformers;

use CranachDigitalArchive\Importer\Interfaces\Pipeline\IProducer;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Pipeline\Hybrid;
use Error;

/**
 * Bubbles keywords from real graphics up to their virtual parent graphic
 * Virtual graphics contain multiple real graphics (via reprintReferences)
 * Since only virtual graphics are shown in search, they need all keywords from their children
 *
 * This transformer collects all graphics first, then processes them, then passes them through
 */
class VirtualGraphicKeywordBubbler extends Hybrid
{
    private $allGraphics               = [];
    private $graphicsByInventoryNumber = [];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self;
    }

    public function handleItem($item): bool
    {
        if (! ($item instanceof GraphicLanguageCollection)) {
            throw new Error('Pushed item is not of expected class \'GraphicLanguageCollection\'');
        }

        $inventoryNumber = $item->getInventoryNumber();

        // Store graphic for later processing
        $this->allGraphics[]                               = $item;
        $this->graphicsByInventoryNumber[$inventoryNumber] = $item;

        // DO NOT pass through yet - we need to collect all first
        return true;
    }

    public function done(IProducer $producer)
    {
        // First, bubble keywords from real to virtual graphics
        foreach ($this->allGraphics as $graphic) {
            if ($graphic->getIsVirtual()) {
                $this->bubbleKeywordsToVirtualGraphic($graphic);
            }
        }

        // Now pass all graphics through the pipeline
        foreach ($this->allGraphics as $graphic) {
            $this->next($graphic);
        }

        parent::done($producer);
        $this->cleanUp();
    }

    private function bubbleKeywordsToVirtualGraphic(GraphicLanguageCollection $virtualGraphic): void
    {
        // Get all reprint references (= real graphics that belong to this virtual graphic)
        $firstGraphic = $virtualGraphic->first();
        if (! $firstGraphic) {
            return;
        }

        $reprintReferences = $firstGraphic->getReprintReferences();

        foreach ($reprintReferences as $reprintRef) {
            $realInventoryNumber = $reprintRef->getInventoryNumber();

            // Find the real graphic by inventory number
            if (! isset($this->graphicsByInventoryNumber[$realInventoryNumber])) {
                continue;
            }

            $realGraphic = $this->graphicsByInventoryNumber[$realInventoryNumber];

            // Get keywords from real graphic
            $realGraphicFirst = $realGraphic->first();
            if (! $realGraphicFirst) {
                continue;
            }

            $realKeywords = $realGraphicFirst->getKeywords();

            // Add each keyword to virtual graphic if not already present
            foreach ($realKeywords as $keyword) {
                $this->addKeywordIfNotExists($virtualGraphic, $keyword);
            }
        }
    }

    private function addKeywordIfNotExists(GraphicLanguageCollection $virtualGraphic, MetaReference $newKeyword): void
    {
        $firstGraphic = $virtualGraphic->first();
        if (! $firstGraphic) {
            return;
        }

        $existingKeywords = $firstGraphic->getKeywords();

        // Check if keyword already exists
        foreach ($existingKeywords as $existingKeyword) {
            if (MetaReference::equal($existingKeyword, $newKeyword)) {
                return; // Already exists
            }
        }

        // Add keyword to all languages in the collection
        $virtualGraphic->addKeyword($newKeyword);
    }

    private function cleanUp(): void
    {
        $this->allGraphics               = [];
        $this->graphicsByInventoryNumber = [];
    }
}
