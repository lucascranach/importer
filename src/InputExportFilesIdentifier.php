<?php

namespace CranachDigitalArchive\Importer;

use DirectoryIterator;
use CallbackFilterIterator;
use CranachDigitalArchive\Importer\Interfaces\IFileProbe;
use Error;

class InputExportFilesIdentifier
{
    private string $inputDirectoryPath;
    /** @var IFileProbe[] */
    private array $probes = [];
    /** @var array<string, string[]> */
    private array $probeClassTofilePathsMap = [];
    private array $remainingFilePaths = [];
    private array $unusedProbeClasses = [];

    private function __construct($inputDirectoryPath)
    {
        $this->inputDirectoryPath = $inputDirectoryPath;
    }

    public static function new(string $inputDirectoryPath)
    {
        return new self($inputDirectoryPath);
    }

    public function getInputDirectoryPath(): string
    {
        return $this->inputDirectoryPath;
    }

    public function registerProbes(IFileProbe ...$probes): self
    {
        $this->probes = array_merge($this->probes, $probes);
        return $this;
    }

    /**
     * Gets the file paths for a previously registered probe class
     *
     * @param      string  $probeClassName  The registered probe class
     *
     * @return     string[]  Found file paths for a
     */
    public function getFilePathsAssociatedWithProbeClass(string $probeClassName): array
    {
        $registeredProbeClasses = array_map(function ($probe) { return $probe::class; }, $this->probes);
        if (!in_array($probeClassName, $registeredProbeClasses, true)) {
            throw new Error('Unknown probe class "' . $probeClassName . '"' . "\n!");
        }

        return $this->probeClassTofilePathsMap[$probeClassName] ?? [];
    }

    public function hasRemainingFilePaths(): bool
    {
        return count($this->getRemainingFilePaths()) > 0;
    }

    public function getRemainingFilePaths(): array
    {
        return $this->remainingFilePaths;
    }

    public function hasUnusedProbes(): bool
    {
        return count($this->getUnusedProbeClasses()) > 0;
    }

    public function getUnusedProbeClasses(): array
    {
        return $this->unusedProbeClasses;
    }

    public function run(): void
    {
        $this->probeFilesInInputDirectory();
    }

    private function probeFilesInInputDirectory(): void
    {
        $filePaths = $this->getFilePathsFormInputDirectory();

        /** @var string[] */
        $usedProbeClasses = [];

        foreach ($filePaths as $filePath) {
            $fileWasSuccessfullyProbed = false;

            foreach ($this->probes as $probe) {
                if ($probe->probe($filePath)) {
                    if (!isset($this->probeClassTofilePathsMap[$probe::class])) {
                        $this->probeClassTofilePathsMap[$probe::class] = [];
                    }

                    $this->probeClassTofilePathsMap[$probe::class][] = $filePath;
                    $fileWasSuccessfullyProbed = true;

                    if (!in_array($probe::class, $usedProbeClasses, true)) {
                        $usedProbeClasses[] = $probe::class;
                    }
                    break;
                }
            }

            if (!$fileWasSuccessfullyProbed) {
                $this->remainingFilePaths[] = $filePath;
            }
        }

        foreach ($this->probes as $probe) {
            if (!in_array($probe::class, $usedProbeClasses, true)) {
                $this->unusedProbeClasses[] = $probe::class;
            }
        }
    }

    private function getFilePathsFormInputDirectory(): array
    {
        $filesIterator = new CallbackFilterIterator(
            new DirectoryIterator($this->inputDirectoryPath),
            function ($current) {
                return !$current->isDir() && !$current->isDot() && strcasecmp($current->getExtension(), 'xml') === 0;
            },
        );

        $filePaths = [];
        foreach ($filesIterator as $file) {
            $filePaths[] = $file->getPathname();
        }

        sort($filePaths);

        return $filePaths;
    }
}
