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

        $this->paintings = Paintings::new(
            $paths,
            $parameters,
            $this->base,
            $this->memoryFilters,
            $this->thesaurus,
            $this->paintingsRestoration,
        );

        $this->graphicsRestoration = GraphicsRestoration::new($paths); /* CustomFiltersMemoryExporter */
        $this->graphics = Graphics::new(
            $paths,
            $parameters,
            $this->base,
            $this->memoryFilters,
            $this->thesaurus,
            $this->graphicsRestoration,
        );

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

    public function run(): self
    {
        $this->paintingsRestoration->run();
        $this->paintings->run();
        $this->graphicsRestoration->run();
        $this->graphics->run();
        $this->literatureReferences->run();
        $this->archivals->run();
        $this->filters->run();

        /* Wraping up the import process */
        $this->base->getLocationsSource()->store();

        return $this;
    }

    public function cleanUp(): void
    {
        $this->base->cleanUp();
        $this->thesaurus->cleanUp();
        $this->paintingsRestoration->cleanUp();
        $this->paintings->cleanUp();
        $this->graphicsRestoration->cleanUp();
        $this->graphics->cleanUp();
    }
}
