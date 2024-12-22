<?php

namespace CranachDigitalArchive\Importer\Constructions\Default\Utils;

use CranachDigitalArchive\Importer\InputExportFilesIdentifier;
use CranachDigitalArchive\Importer\InputExportsOverview;
use CranachDigitalArchive\Importer\Modules\Archivals\ArchivalsFileProbe;
use CranachDigitalArchive\Importer\Modules\Graphics\GraphicsFileProbe;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\LiteratureReferencesFileProbe;
use CranachDigitalArchive\Importer\Modules\Paintings\PaintingsFileProbe;
use CranachDigitalArchive\Importer\Modules\Drawings\DrawingsFileProbe;
use CranachDigitalArchive\Importer\Modules\Restorations\GraphicsRestorationsFileProbe;
use CranachDigitalArchive\Importer\Modules\Restorations\PaintingsRestorationsFileProbe;
use CranachDigitalArchive\Importer\Modules\Restorations\DrawingsRestorationsFileProbe;
use CranachDigitalArchive\Importer\Modules\Thesaurus\ThesaurusFileProbe;

class Paths
{
    private Parameters $parameters;
    private string $rootDirectoryPath;
    private string $inputDirectoryName;
    private string $outputDirectoryName;
    private string $resourcesDirectoryName;
    private string $elasticsearchOutputDirectoryName;

    private InputExportsOverview $inputExportsOverview;
    private string $selectedExportId;
    private InputExportFilesIdentifier $filesIdentifier;

    private function __construct(
        string $rootDirectoryPath,
        Parameters $parameters,
        string $inputDirectoryName,
        string $outputDirectoryName,
        string $resourcesDirectoryName,
        string $elasticsearchOutputDirectoryName,
    ) {
        $this->parameters = $parameters;

        $this->rootDirectoryPath = self::normalizePath($rootDirectoryPath);
        $this->inputDirectoryName = self::normalizePath($inputDirectoryName);
        $this->outputDirectoryName = self::normalizePath($outputDirectoryName);
        $this->resourcesDirectoryName = self::normalizePath($resourcesDirectoryName);
        $this->elasticsearchOutputDirectoryName = self::normalizePath($elasticsearchOutputDirectoryName);

        /* Input folder identification helper */
        $this->inputExportsOverview = InputExportsOverview::new($this->getInputBasePath());

        /* We need to fall back to the latest export id found in the input directory,
         * if no id was given as parameter */
        $selectedExportIdParameter = $this->parameters->getSelectedExportId();
        $this->selectedExportId = is_null($selectedExportIdParameter)
            ? $this->getLatestExportId()
            : $selectedExportIdParameter;

        /* Does the input directory exist?
         * Can only be checked after determining the final selectedExportId. */
        if(!$this->inputDirectoryExists()) {
            exit('Source input directory not found for selected export id \'' . $this->getSelectedExportId() . '\': ' . $this->getInputPath() . "\n\n");
        }

        $this->filesIdentifier = $this->createFilesIdentifier();

        self::checkFilesIdentifier($this->filesIdentifier);
    }

    public static function new(
        string $rootDirectoryPath,
        Parameters $parameters,
        string $inputDirectoryName = 'input',
        string $outputDirectoryName = 'docs',
        string $resourcesDirectoryName = 'resources',
        string $elasticsearchOutputDirectoryName = 'elasticsearch',
    ): self {
        return new self(
            $rootDirectoryPath,
            $parameters,
            $inputDirectoryName,
            $outputDirectoryName,
            $resourcesDirectoryName,
            $elasticsearchOutputDirectoryName,
        );
    }

    public function getRootPath(string ...$additional): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->rootDirectoryPath, ...$additional]);
    }

    public function getInputBasePath(string ...$additional): string
    {
        return $this->getRootPath(
            $this->inputDirectoryName,
            ...$additional,
        );
    }

    public function getInputPath(string ...$additional): string
    {
        return $this->getInputBasePath(
            $this->getSelectedExportId(),
            ...$additional,
        );
    }

    private function inputDirectoryExists(): bool
    {
        return file_exists($this->getInputPath());
    }

    public function getResourcesPath(string ...$additional): string
    {
        return $this->getRootPath(
            $this->resourcesDirectoryName,
            ...$additional,
        );
    }

    public function getOutputBasePath(string ...$additional): string
    {
        return $this->getRootPath(
            $this->outputDirectoryName,
            ...$additional,
        );
    }

    public function getOutputPath(string ...$additional): string
    {
        return $this->getOutputBasePath(
            $this->getSelectedExportId(),
            ...$additional,
        );
    }

    public function getElasticsearchOutputPath(string ...$additional): string
    {
        return $this->getOutputPath(
            $this->elasticsearchOutputDirectoryName,
            ...$additional,
        );
    }

    public function getSelectedExportId(): string
    {
        return $this->selectedExportId;
    }

    public function getInputExportsOverview(): InputExportsOverview
    {
        return $this->inputExportsOverview;
    }

    public function getFilesIdentifier(): InputExportFilesIdentifier
    {
        return $this->filesIdentifier;
    }

    public function getCacheDirectoryPath(): string
    {
        return $this->parameters->getEnvironmentVariables()->getCacheDirectoryPath();
    }

    public function getThesaurusInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(ThesaurusFileProbe::class);
    }

    public function getPaintingsRestorationInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(PaintingsRestorationsFileProbe::class);
    }

    public function getPaintingsInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(PaintingsFileProbe::class);
    }

    public function getDrawingsRestorationInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(DrawingsRestorationsFileProbe::class);
    }

    public function getDrawingsInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(DrawingsFileProbe::class);
    }

    public function getGraphicsRestorationInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(GraphicsRestorationsFileProbe::class);
    }

    public function getGraphicsInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(GraphicsFileProbe::class);
    }

    public function getLiteratureReferencesInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(LiteratureReferencesFileProbe::class);
    }

    public function getArchivalsInputFilePaths(): array
    {
        return $this->filesIdentifier->getFilePathsAssociatedWithProbeClass(ArchivalsFileProbe::class);
    }

    private static function normalizePath(string $path): string
    {
        return rtrim(trim($path), DIRECTORY_SEPARATOR);
    }

    private function getLatestExportId(): string
    {
        $latestInputExportEntry = $this->inputExportsOverview->getLatestDirectoryEntry();

        if (!is_null($latestInputExportEntry)) {
            return $latestInputExportEntry->getFilename();
        } else {
            exit('No possible export found in \'' . $this->inputExportsOverview->getSearchPath() . '\'!');
        }
    }

    private function createFilesIdentifier(): InputExportFilesIdentifier
    {
        $selectedExportDirectory = $this->getInputExportsOverview()->getDirectoryEntryWithName($this->getSelectedExportId());
        if (is_null($selectedExportDirectory)) {
            exit('Unknown export with name \'' . $this->getSelectedExportId() . '\'' . "\n\n");
        }

        $filesIdentifier = InputExportFilesIdentifier::new($selectedExportDirectory->getPathname())
        ->registerProbes(
            ArchivalsFileProbe::new(),
            GraphicsFileProbe::new(),
            LiteratureReferencesFileProbe::new(),
            PaintingsFileProbe::new(),
            DrawingsFileProbe::new(),
            GraphicsRestorationsFileProbe::new(),
            PaintingsRestorationsFileProbe::new(),
            // DrawingsRestorationsFileProbe::new(),      //Entkommentieren, wenn Restorationsdaten vorhanden sind
            ThesaurusFileProbe::new(),
        );
        $filesIdentifier->run();

        return $filesIdentifier;
    }

    private static function checkFilesIdentifier(InputExportFilesIdentifier $filesIdentifier): void
    {
        if ($filesIdentifier->hasRemainingFilePaths() || $filesIdentifier->hasUnusedProbes()) {
            /** @var string[] */
            $lines = [];

            if ($filesIdentifier->hasRemainingFilePaths()) {
                $lines[] = "Remaining input file(s) not associable with a registered probe:";
                foreach ($filesIdentifier->getRemainingFilePaths() as $remainingFilePath) {
                    $lines[] = "\t- " . basename($remainingFilePath);
                }
            }

            if (count($lines) > 0) {
                $lines[] = "";
            }

            if ($filesIdentifier->hasUnusedProbes()) {
                $lines[] = "Unused registered probe(s):";
                foreach ($filesIdentifier->getUnusedProbeClasses() as $unusedProbeClasses) {
                    $lines[] = "\t- " . $unusedProbeClasses ;
                }
            }

            $lines[] = "\n";

            echo implode("\n", $lines);

            exit();
        }
    }
}
