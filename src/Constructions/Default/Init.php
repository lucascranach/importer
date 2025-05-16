<?php

namespace CranachDigitalArchive\Importer\Constructions\Default;

use CranachDigitalArchive\Importer\Constructions\Default\Utils\Parameters;
use CranachDigitalArchive\Importer\Constructions\Default\Utils\Paths;

final class Init
{
    private Base $base;
    private Thesaurus $thesaurus;
    private MemoryFilters $memoryFilters;
    private PaintingsRestoration $paintingsRestoration;
    private Paintings $paintings;
    private DrawingsRestoration $drawingsRestoration;
    private Drawings $drawings;
    private GraphicsRestoration $graphicsRestoration;
    private Graphics $graphics;
    private LiteratureReferences $literatureReferences;
    private Archivals $archivals;
    private Filters $filters;

    private function __construct(Parameters $parameters, Paths $paths)
    {
        echo 'Selected Export : ' . $paths->getSelectedExportId() . "\n\n\n";

        /* Initialization of submodules */
        $this->base = Base::new($paths)->run(); /* LocationsSource and MetaReferenceCollector */
        $this->thesaurus = Thesaurus::new($paths)->run(); /* ThesaurusMemoryExporter */
        $this->memoryFilters = MemoryFilters::new($paths)->run(); /* CustomFiltersMemoryExporter */

        $this->paintingsRestoration = PaintingsRestoration::new($paths, $this->memoryFilters); /* (Paintings)-RestorationsMemoryExporter */
        if (in_array("paintings", $parameters->getImportTypes(), true)) {
            $this->paintings = Paintings::new(
                $paths,
                $parameters,
                $this->base,
                $this->memoryFilters,
                $this->thesaurus,
                $this->paintingsRestoration,
            );
        }

        $this->drawingsRestoration = DrawingsRestoration::new($paths, $this->memoryFilters);
        if (in_array("drawings", $parameters->getImportTypes(), true)) {
            $this->drawings = Drawings::new(
                $paths,
                $parameters,
                $this->base,
                $this->memoryFilters,
                $this->thesaurus,
                $this->drawingsRestoration,
            );
        }

        if (in_array("drawings", $parameters->getImportTypes(), true)) {
            $this->graphicsRestoration = GraphicsRestoration::new($paths); /* CustomFiltersMemoryExporter */
            $this->graphics = Graphics::new(
                $paths,
                $parameters,
                $this->base,
                $this->memoryFilters,
                $this->thesaurus,
                $this->graphicsRestoration,
            );
        }

        $this->literatureReferences = LiteratureReferences::new($paths, $this->memoryFilters);

        $this->archivals = Archivals::new($paths, $parameters);

        $this->filters = Filters::new(
            $paths,
            $this->base,
            $this->thesaurus,
            $this->memoryFilters,
        );
    }

    public static function new(Parameters $parameters, Paths $paths): self
    {
        return new self($parameters, $paths);
    }

    public function run(Parameters $parameters): self
    {
        if (in_array("paintings", $parameters->getImportTypes(), true)) {
            $this->paintingsRestoration->run();
            $this->paintings->run();
        }

        if (in_array("drawings", $parameters->getImportTypes(), true)) {
            $this->drawingsRestoration->run();
            $this->drawings->run();
        }

        if (in_array("graphics", $parameters->getImportTypes(), true)) {
            $this->graphicsRestoration->run();
            $this->graphics->run();
        }

        if (in_array("archivals", $parameters->getImportTypes(), true)) {
            $this->archivals->run();
        }
        $this->literatureReferences->run();

        $this->filters->run();

        /* Wraping up the import process */
        $this->base->getLocationsSource()->store();

        return $this;
    }

    public function cleanUp(Parameters $parameters): void
    {
        $this->base->cleanUp();
        $this->thesaurus->cleanUp();

        if (in_array("paintings", $parameters->getImportTypes(), true)) {
            $this->paintingsRestoration->cleanUp();
            $this->paintings->cleanUp();
        }

        if (in_array("drawings", $parameters->getImportTypes(), true)) {
            $this->drawingsRestoration->cleanUp();
            $this->drawings->cleanUp();
        }

        if (in_array("graphics", $parameters->getImportTypes(), true)) {
            $this->graphicsRestoration->cleanUp();
            $this->graphics->cleanUp();
        }
    }
}
