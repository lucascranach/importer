<?php

namespace CranachDigitalArchive\Importer\Interfaces;

interface IFileProbe
{
    /**
     * Probe a given input
     *
     * @param      string  $filepath  Path of the file to be probed
     *
     * @return     bool    File was probed successfully
     */
    public function probe(string $filepath): bool;
}
