<?php

namespace CranachDigitalArchive\Importer\Modules\Locations\Sources;

use Error;
use Throwable;
use CranachDigitalArchive\Importer\Interfaces\Loaders\IFileLoader;
use CranachDigitalArchive\Importer\Modules\Locations\Entities\Location;

use GuzzleHttp\Client;

class LocationsSource implements IFileLoader
{
    private const GETTY_ID_PATTERN = '/vocab.getty.edu\/page\/tgn\/(\d+)/';
    private const GETTY_SPARQL_URL = 'http://vocab.getty.edu/sparql.json?query=%s';
    private const GETTY_SPARQL_QUERY_BODY = <<<HEREDOC
SELECT ?place ?name ?lat ?lng {
  ?place skos:inScheme tgn:;
    foaf:focus [wgs:lat ?lat;
      wgs:long ?lng];
        gvp:prefLabelGVP [xl:literalForm ?name].
  ?place dc:identifier "%s".
}
LIMIT 1
HEREDOC;

    private $sourceFilePath;
    private $locations;
    private $wasUpdated = false;

    private $client;

    private function __construct()
    {
    }

    public static function withSourceAt(string $sourceFilePath)
    {
        $source = new self;

        $source->sourceFilePath = $sourceFilePath;

        if (!file_exists($sourceFilePath)) {
            throw new Error('Locations source file does not exist: ' . $sourceFilePath);
        }

        $source->client = new Client();

        $source->run();

        return $source;
    }

    public function run()
    {
        echo 'Processing locations source file : ' . $this->sourceFilePath . "\n";

        $locationsSourceContentRaw = file_get_contents($this->sourceFilePath);
        $locationsSourceContent = json_decode($locationsSourceContentRaw, true, 512, JSON_UNESCAPED_UNICODE);

        foreach ($locationsSourceContent as $id => $storedLocationDataset) {
            $this->locations[$id] = Location::fromStoredDataset($storedLocationDataset);
        }
    }

    public function getLocationByURL(string $url): ?Location
    {
        $id = $this->extractIdFromURL($url);

        if (is_null($id)) {
            return null;
        }

        return $this->getLocationById($id);
    }

    public function getLocationById(string $id): ?Location
    {
        if (!isset($this->locations[$id])) {
            $fetchedLocation = $this->getFromAPIById($id);

            if (!is_null($fetchedLocation)) {
                $this->locations[$id] = $fetchedLocation;
                $this->wasUpdated = true;
            } else {
                echo "Missing location resource for ID (vocab.getty.edu): " . $id . "\n";
            }
        }

        return isset($this->locations[$id]) ? $this->locations[$id] : null;
    }

    public function store()
    {
        if (!$this->wasUpdated) {
            return;
        }

        file_put_contents($this->sourceFilePath, json_encode($this->locations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function cleanUp()
    {
        $this->locations = [];
        $this->client = null;
    }

    private function extractIdFromURL(string $url): ?string
    {
        $matches = [];
        if (preg_match(self::GETTY_ID_PATTERN, $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function getFromAPIById($id): ?Location
    {
        $location = null;

        $queryBody = sprintf(self::GETTY_SPARQL_QUERY_BODY, $id);
        $url = sprintf(self::GETTY_SPARQL_URL, $queryBody);

        try {
            $resp = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (Throwable $e) {
            echo $e->getMessage();
            return $location;
        }

        if ($resp->getReasonPhrase() != 'OK') {
            return $location;
        }

        $bodyRaw = $resp->getBody();
        $body = json_decode($bodyRaw, true, 512, JSON_UNESCAPED_UNICODE);

        if (count($body['results']['bindings']) !== 1) {
            return null;
        }

        $bindings = $body['results']['bindings'][0];
        $name = $bindings['name']['value'];
        $latiude = $bindings['lat']['value'];
        $longitude = $bindings['lng']['value'];

        $location = new Location($name, $latiude, $longitude);

        return $location;
    }
}
