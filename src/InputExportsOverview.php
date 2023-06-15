<?php

namespace CranachDigitalArchive\Importer;

use DirectoryIterator;
use CallbackFilterIterator;
use SplFileInfo;

class InputExportsOverview
{
    private $inputSearchPath;

    private function __construct(string $searchpath)
    {
        $this->inputSearchPath = $searchpath;
    }

    public static function new(string $searchpath): self
    {
        return new self($searchpath);
    }

    /**
     * Gets the search path
     *
     * @return     string  The search path
     */
    public function getSearchPath(): string
    {
        return $this->inputSearchPath;
    }

    /**
     * Gets all directory entries
     *
     * @return     array<SplFileInfo>  All directory entries
     */
    public function getAllDirectoryEntries(): array
    {
        return $this->readDirectoryEntries();
    }

    /**
     * Gets the latest directory entry
     *
     * @return     SplFileInfo|null  The latest directory entry
     */
    public function getLatestDirectoryEntry(): SplFileInfo | null
    {
        $entries = $this->getAllDirectoryEntries();

        return $entries[count($entries) - 1] ?? null;
    }

    /**
     * Gets the directory entry for a given name
     *
     * @param      string            $name   The name
     *
     * @return     SplFileInfo|null  The directory entry matching the given name
     */
    public function getDirectoryEntryWithName(string $name): SplFileInfo | null
    {
        $filteredArr = array_filter(
            $this->getAllDirectoryEntries(),
            function ($entry) use ($name) {
                return $entry->getFilename() === $name;
            },
        );

        return count($filteredArr) > 0 ? current($filteredArr) : null;
    }

    /**
     * Read in all directory entries found in the search path
     *
     * @return     array<SplFileInfo>  All found directory entries
     */
    private function readDirectoryEntries()
    {
        $directoryEntriesIterator = new CallbackFilterIterator(
            new DirectoryIterator($this->inputSearchPath),
            function ($current) {
                return $current->isDir() && !$current->isDot();
            },
        );

        $directoryEntries = [];
        foreach ($directoryEntriesIterator as $directoryEntry) {
            $directoryEntries[] = clone $directoryEntry;
        }

        usort($directoryEntries, function ($entryA, $entryB) {
            return strcmp($entryA->getBasename(), $entryB->getBasename());
        });

        /** @var array<SplFileInfo> */
        return $directoryEntries;
    }
}
