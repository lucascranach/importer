<?php

namespace CranachDigitalArchive\Importer\Constructions\Default\Utils;

class Parameters
{
    /* See: https://www.php.net/manual/de/function.getopt.php */
    private $cliParamsDefinition = [
        'shortOpts' => [],
        'longOpts' => [
            'keep-soft-deleted-artefacts',      /* optional */

            'refresh-remote-all-cache::',       /* optional value; default is 'all' */

            'refresh-remote-images-cache::',    /* optional value; default is 'all' */
            'refresh-remote-documents-cache::', /* optional value; default is 'all' */
            'refresh-all-remote-caches',        /* optional */

            'use-export:'                       /* required value */,
        ],
    ];
    private $supportedCachesKeys = ['paintings', 'graphics', 'archivals', 'drawings'];

    private array $remoteImagesCachesToRefresh = [];
    private array $remoteDocumentsCachesToRefresh = [];
    private bool $keepSoftDeletedArterfacts = false;
    private string | null $selectedExportId = null;
    private EnvironmentVariables $envVars;

    private function __construct(EnvironmentVariables $envVars)
    {
        $this->remoteImagesCachesToRefresh = array_fill_keys($this->supportedCachesKeys, false);
        $this->remoteDocumentsCachesToRefresh = array_fill_keys($this->supportedCachesKeys, false);

        $this->envVars = $envVars;

        $this->initialize();
    }

    public static function new(EnvironmentVariables $envVars): self
    {
        return new self($envVars);
    }

    public function setRemoteImagesCachesToRefresh(array $remoteImagesCachesToRefresh): self
    {
        $this->remoteImagesCachesToRefresh = $remoteImagesCachesToRefresh;
        return $this;
    }

    public function getRemoteImagesCachesToRefresh(): array
    {
        return $this->remoteImagesCachesToRefresh;
    }

    public function setRemoteDocumentsCachesToRefresh(array $remoteDocumentsCachesToRefresh): self
    {
        $this->remoteDocumentsCachesToRefresh = $remoteDocumentsCachesToRefresh;
        return $this;
    }

    public function getRemoteDocumentsCachesToRefresh(): array
    {
        return $this->remoteDocumentsCachesToRefresh;
    }

    public function setKeepSoftDeletedAretefacts(bool $keepSoftDeletedArterfacts): self
    {
        $this->keepSoftDeletedArterfacts = $keepSoftDeletedArterfacts;
        return $this;
    }

    public function getKeepSoftDeletedAretefacts(): bool
    {
        return $this->keepSoftDeletedArterfacts;
    }

    public function setSelectedExportId(string $selectedExportId): self
    {
        $this->selectedExportId = $selectedExportId;
        return $this;
    }

    public function getSelectedExportId(): string | null
    {
        return $this->selectedExportId;
    }

    public function getEnvironmentVariables(): EnvironmentVariables
    {
        return $this->envVars;
    }

    private function initialize(): void
    {
        $opts = getopt(
            implode('', $this->cliParamsDefinition['shortOpts']),
            $this->cliParamsDefinition['longOpts'],
        );


        foreach ($opts as $opt => $value) {
            switch ($opt) {
                case 'keep-soft-deleted-artefacts':
                    $this->setKeepSoftDeletedAretefacts(true);
                    break;

                case 'refresh-remote-all-cache':
                case 'refresh-remote-images-cache':
                case 'refresh-remote-documents-cache':
                    $valueList = is_array($value) ? $value : explode(',', strval($value));

                    $cachesToRefresh = $value === false ? ['all'] : $valueList;
                    $refreshAllCaches = in_array('all', $cachesToRefresh, true);

                    $remoteCachesToRefresh = array_reduce(
                        $this->supportedCachesKeys,
                        function ($arr, $key) use ($refreshAllCaches, $cachesToRefresh) {
                            $arr[$key] = $refreshAllCaches || in_array($key, $cachesToRefresh, true);
                            return $arr;
                        },
                        [],
                    );

                    switch ($opt) {
                        case 'refresh-remote-all-cache':
                            $this->setRemoteImagesCachesToRefresh($remoteCachesToRefresh);
                            $this->setRemoteDocumentsCachesToRefresh($remoteCachesToRefresh);
                            break;

                        case 'refresh-remote-images-cache':
                            $this->setRemoteImagesCachesToRefresh($remoteCachesToRefresh);
                            break;

                        case 'refresh-remote-documents-cache':
                            $this->setRemoteDocumentsCachesToRefresh($remoteCachesToRefresh);
                            break;
                    }
                    break;

                case 'refresh-all-remote-caches':
                    $this->setRemoteImagesCachesToRefresh(array_fill_keys($this->supportedCachesKeys, true));
                    $this->setRemoteDocumentsCachesToRefresh(array_fill_keys($this->supportedCachesKeys, true));
                    break;

                case 'use-export':
                    $value = is_array($value) ? implode(',', $value) : strval($value);
                    $this->setSelectedExportId($value);
                    break;

                default:
            }
        }
    }
}
