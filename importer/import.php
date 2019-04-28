<?php


/* Fragen:

- Unterschied zwischen GR-Daten und GR-Rest
- Klassifizierung der Daten: Was sind Werknormdaten, wie sind die Daten verbunden.
- Bitte mal Bilder schicken

*/

$xmlFile = "./import-file/20190328/CDA-GR_DatenÅbersicht_20190329.xml";

class Collection
{
    public $items = array();

    public function addItem($item)
    {
        array_push($this->items, $item);
    }

    public function doStore($fn, $data)
    {
        file_put_contents($fn, json_encode($data));
    }

    public function store()
    {
        $this->doStore("cda-graphics.json", $this);
    }

    private function getVirtualObjects()
    {
        $virtualObjects = array();

        foreach ($this->items as $item) {
            if ($item->Type == "virtual") {
                array_push($virtualObjects, $item);
            }
        }
        return $virtualObjects;
    }

    public function storeVirtualObjects()
    {
        $this->doStore("cda-graphics-virtual-objects.json", $this->getVirtualObjects());
    }
}

class Graphic
{

    public function setType(string $newType)
    {
        $this->Type = $newType;
    }

    public function setOid(int $newOid)
    {
        $this->Oid = $newOid;
    }

    public function setOobjectname(string $OBJECTNAME)
    {
        $this->ObjectName = $OBJECTNAME;
    }

    public function setInventarnummer(string $Inventarnummer)
    {
        $this->Inventarnummer = $Inventarnummer;
    }

    public function setDated(int $DATED)
    {
        $this->Dated = $DATED;
    }

    public function setTitle(string $langkey = "de", string $newTitle)
    {
        $this->Title[$langkey] = $newTitle;
    }

    public function setRemarks(string $langkey = "de", string $newRemark)
    {
        $this->Remarks[$langkey] = $newRemark;
    }

    public function setClassification(string $langkey = "de", string $newClassification)
    {
        $this->Classification[$langkey] = $newClassification;
    }

    public function setCondition(string $langkey = "de", string $newCondition)
    {
        $this->Condition[$langkey] = $newCondition;
    }

    public function setDimensions(string $langkey = "de", string $DIMENSIONS)
    {
        $this->Dimensions[$langkey] = $DIMENSIONS;
    }

    public function setLongtext(string $langkey = "de", string $LONGTEXT)
    {
        $this->Longtext[$langkey] = $LONGTEXT;
    }

    /* Sprachbezogen? */
    public function setRelatedWorks(string $langkey = "de", string $RELATEDWORKS)
    {
        $this->RelatedWorks[$langkey] = $RELATEDWORKS;
    }

    public function store()
    {
        var_dump($this);
    }


    

}

function searchFieldForData($node, string $fieldname, $needle = false, $returnValue = false)
{

    foreach ($node->Field as $field) {
        if ($field["Name"] == $fieldname) {
            if ($needle && $field->FormattedValue == $needle) {
                return $returnValue ? $returnValue : $field->FormattedValue;
            } else {
                return $field->FormattedValue;
            }
        }
    }
}

function getLangContent(string $lang, string $content)
{
    if (!preg_match("=#=", $content)) {return $content;}
    $c = explode("#", $content);

    if ($lang == "de") {return $c[0];} else {
        return $c[1];
    }
}

function parseGroup($group, $graphic, $collection)
{
    $exit = 0;

    $graphic->setType((string) searchFieldForData($group->GroupHeader->Section[7], "ISVIRTUAL1", "1", "virtual"));
    $graphic->setOid((int) searchFieldForData($group->GroupHeader->Section[7], "OBJECTID1"));
    $graphic->setOobjectname((string) searchFieldForData($group->GroupHeader->Section[5], "OBJECTNAME1"));
    $graphic->setInventarnummer((string) searchFieldForData($group->GroupHeader->Section[6], "Inventarnummer1"));
    $graphic->setDated((int) searchFieldForData($group->GroupHeader->Section[9], "DATED1"));

    $graphic->setLongtext("de", (string) searchFieldForData($group->GroupHeader->Section[14], "DESCRIPTION1"));
    $graphic->setLongtext("en", (string) searchFieldForData($group->GroupHeader->Section[15], "LONGTEXT31"));
    $graphic->setTitle("de", (string) searchFieldForData($group->GroupHeader->Section[3]->Subreport->Details[0]->Section[3], "TITLE1"));
    $graphic->setRemarks("de", (string) searchFieldForData($group->GroupHeader->Section[3]->Subreport->Details[0]->Section[3], "REMARKS1"));
    $graphic->setRelatedWorks("de", (string) searchFieldForData($group->GroupHeader->Section[26], "RELATEDWORKS1"));

    $classification = (string) searchFieldForData($group->GroupHeader->Section[4], "Klassifizierung1");
    $graphic->setClassification("de", getLangContent("de", $classification));
    $graphic->setClassification("en", getLangContent("en", $classification));

    $condition = (string) searchFieldForData($group->GroupHeader->Section[4], "Druckzustand1");
    $graphic->setCondition("de", getLangContent("de", $condition));
    $graphic->setCondition("en", getLangContent("en", $condition));

    $DIMENSIONS = (string) searchFieldForData($group->GroupHeader->Section[8], "Feld2");
    $graphic->setDimensions("de", getLangContent("de", $DIMENSIONS));
    $graphic->setDimensions("en", getLangContent("en", $DIMENSIONS));

    //$graphic->store();

    $collection->addItem($graphic);
}

function iterateGroups($xml, $collection)
{
    foreach ($xml->Group as $group) {
        $graphic = new Graphic;
        //$exit++;if ($exit > 1) {die;}
        parseGroup($group, $graphic, $collection);
    }

}

$exit = 0;
$collection = new Collection;

if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    iterateGroups($xml, $collection);
    $collection->storeVirtualObjects();
    $collection->store();
} else {
    exit("Datei $xmlFile kann nicht geöffnet werden.");
}
