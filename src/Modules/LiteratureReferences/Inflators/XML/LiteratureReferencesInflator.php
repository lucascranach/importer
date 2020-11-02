<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Event;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\ConnectedObject;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;

/**
 * LiteratureReferences inflator used to inflate literature reference instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class LiteratureReferencesInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement &$node,
        LiteratureReference &$literatureReference
    ): void {
        $subNode = $node->{'GroupHeader'};
        $connectedObjectsSubNode = $node->{'Group'};

        self::registerXPathNamespace($subNode);

        self::inflateReferenceId($subNode, $literatureReference);
        self::inflateReferenceNumber($subNode, $literatureReference);

        self::inflateTitle($subNode, $literatureReference);
        self::inflateSubtitle($subNode, $literatureReference);
        self::inflateShorttitle($subNode, $literatureReference);

        self::inflateJournal($subNode, $literatureReference);
        self::inflateSeries($subNode, $literatureReference);
        self::inflateVolume($subNode, $literatureReference);
        self::inflateEdition($subNode, $literatureReference);

        self::inflatePublishLocation($subNode, $literatureReference);
        self::inflatePublishDate($subNode, $literatureReference);
        self::inflatePageNumbers($subNode, $literatureReference);
        self::inflateDate($subNode, $literatureReference);

        self::inflateEvents($subNode, $literatureReference);

        self::inflateCopyright($subNode, $literatureReference);

        self::inflatePersons($subNode, $literatureReference);
        self::inflatePublications($subNode, $literatureReference);

        self::inflateId($subNode, $literatureReference);

        self::inflateConnectedObjects($connectedObjectsSubNode, $literatureReference);
    }


    /* ReferenceId */
    private static function inflateReferenceId(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $idElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{ReferenceMaster.ReferenceID}"]/FormattedValue',
        );
        if ($idElement) {
            $idStr = trim(strval($idElement));
            $literatureReference->setReferenceId($idStr);
        }
    }


    /* ReferenceNumber */
    private static function inflateReferenceNumber(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $numberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{@Verweisnummer}"]/FormattedValue',
        );
        if ($numberElement) {
            $numberStr = trim(strval($numberElement));
            $literatureReference->setReferenceNumber($numberStr);
        }
    }


    /* Title */
    private static function inflateTitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $titleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{ReferenceMaster.Title}"]/FormattedValue',
        );
        if ($titleElement) {
            $titleStr = trim(strval($titleElement));
            $literatureReference->setTitle($titleStr);
        }
    }


    /* Subtitle */
    private static function inflateSubtitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $subtitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{ReferenceMaster.SubTitle}}"]/FormattedValue',
        );
        if ($subtitleElement) {
            $subtitleStr = trim(strval($subtitleElement));
            $literatureReference->setSubtitle($subtitleStr);
        }
    }


    /* Shorttitle */
    private static function inflateShorttitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $shorttitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="4"]/Field[@FieldName="{ReferenceMaster.Heading}"]/FormattedValue',
        );
        if ($shorttitleElement) {
            $shorttitleStr = trim(strval($shorttitleElement));
            $literatureReference->setShorttitle($shorttitleStr);
        }
    }


    /* Journal */
    private static function inflateJournal(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $journalElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Field[@FieldName="{ReferenceMaster.Journal}"]/FormattedValue',
        );

        if ($journalElement) {
            $journalStr = trim(strval($journalElement));
            $literatureReference->setJournal($journalStr);
        }
    }


    /* Series */
    private static function inflateSeries(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $seriesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Field[@FieldName="{ReferenceMaster.Series}"]/FormattedValue',
        );
        if ($seriesElement) {
            $seriesStr = trim(strval($seriesElement));
            $literatureReference->setSeries($seriesStr);
        }
    }


    /* Volume */
    private static function inflateVolume(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $volumeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{ReferenceMaster.Volume}"]/FormattedValue',
        );
        if ($volumeElement) {
            $volumeStr = trim(strval($volumeElement));
            $literatureReference->setVolume($volumeStr);
        }
    }


    /* Edition */
    private static function inflateEdition(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $editionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Field[@FieldName="{ReferenceMaster.Edition}"]/FormattedValue',
        );
        if ($editionElement) {
            $editionStr = trim(strval($editionElement));
            $literatureReference->setEdition($editionStr);
        }
    }


    /* PublishLocation */
    private static function inflatePublishLocation(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $publishLocationElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{ReferenceMaster.PlacePublished}"]/FormattedValue',
        );
        if ($publishLocationElement) {
            $publishLocationStr = trim(strval($publishLocationElement));
            $literatureReference->setPublishLocation($publishLocationStr);
        }
    }


    /* PublishDate */
    private static function inflatePublishDate(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $publishDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{ReferenceMaster.YearPublished}}"]/FormattedValue',
        );
        if ($publishDateElement) {
            $publishDateStr = trim(strval($publishDateElement));
            $literatureReference->setPublishDate($publishDateStr);
        }
    }


    /* PageNumbers */
    private static function inflatePageNumbers(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $pageNumbersElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{ReferenceMaster.NumOfPages}"]/FormattedValue',
        );
        if ($pageNumbersElement) {
            $pageNumbersStr = trim(strval($pageNumbersElement));
            $literatureReference->setPageNumbers($pageNumbersStr);
        }
    }


    /* Date */
    private static function inflateDate(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $dateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{ReferenceMaster.DisplayDate}"]/FormattedValue',
        );
        if ($dateElement) {
            $dateStr = trim(strval($dateElement));
            $literatureReference->setDate($dateStr);
        }
    }


    /* Events */
    private static function inflateEvents(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $detailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="13"]/Subreport',
        );

        if (!$detailElements) {
            return;
        }

        foreach ($detailElements as $detailElement) {
            if ($detailElement->count() === 0) {
                continue;
            }

            $event = new Event;
            $literatureReference->addEvent($event);

            /* EventType */
            $eventTypeElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{RefDates.EventType}"]/FormattedValue',
            );

            if ($eventTypeElement) {
                $eventTypeStr = trim(strval($eventTypeElement));
                $event->setType($eventTypeStr);
            }

            /* DateText */
            $dateTextElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{RefDates.DateText}"]/FormattedValue',
            );

            if ($dateTextElement) {
                $dateTextStr = trim(strval($dateTextElement));
                $event->setDateText($dateTextStr);
            }

            /* DateBegin */
            $dateBeginElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{@Anfangsdatum}"]/FormattedValue',
            );

            if ($dateBeginElement) {
                $dateBeginStr = trim(strval($dateBeginElement));
                $event->setDateBegin($dateBeginStr);
            }

            /* DateEnd */
            $dateEndElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{@Enddatum}"]/FormattedValue',
            );

            if ($dateEndElement) {
                $dateEndStr = trim(strval($dateEndElement));
                $event->setDateEnd($dateEndStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="4"]/Field[@FieldName="{RefDates.Remarks}"]/FormattedValue',
            );

            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $event->setRemarks($remarksStr);
            }
        }
    }


    /* Copyright */
    private static function inflateCopyright(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $copyrightElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]/Field[@FieldName="{ReferenceMaster.Copyright}"]/FormattedValue',
        );
        if ($copyrightElement) {
            $copyrightStr = trim(strval($copyrightElement));
            $literatureReference->setCopyright($copyrightStr);
        }
    }


    /* Persons */
    private static function inflatePersons(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $detailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="15"]/Subreport',
        );

        if (!$detailElements) {
            return;
        }

        foreach ($detailElements as $detailElement) {
            if ($detailElement->count() === 0) {
                continue;
            }

            $person = new Person;
            $literatureReference->addPerson($person);

            /* Role */
            $roleElement = self::findElementByXPath(
                $detailElement,
                'Text[@Name="Text2"]/TextValue',
            );

            if ($roleElement) {
                $roleStr = trim(strval($roleElement));
                $person->setRole($roleStr);
            }

            /* Name */
            $nameElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{@PersonSuffix}"]/FormattedValue',
            );

            if ($nameElement) {
                $nameStr = trim(strval($nameElement));
                $person->setName($nameStr);
            }
        }
    }


    /* Publications */
    private static function inflatePublications(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $detailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="16"]/Subreport',
        );

        if (!$detailElements) {
            return;
        }

        foreach ($detailElements as $detailElement) {
            if ($detailElement->count() === 0) {
                continue;
            }

            $publication = new Publication;

            /* Type */
            $typeElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{Terms.Term}"]/FormattedValue',
            );

            if ($typeElement) {
                $typeStr = trim(strval($typeElement));
                $publication->setType($typeStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{ThesXrefs.Remarks}"]/FormattedValue',
            );

            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $publication->setRemarks($remarksStr);
            }


            if (!empty($publication->getType())) {
                $literatureReference->addPublication($publication);
            }
        }
    }


    /* Id */
    private static function inflateId(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $idElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="17"]/Subreport/Details/Section[@SectionNumber="0"]/Field[@FieldName="{@AltNum}"]/FormattedValue',
        );
        if ($idElement) {
            $idStr = trim(strval($idElement));
            $literatureReference->setId($idStr);
        }
    }


    /* ConnectedObjects */
    private static function inflateConnectedObjects(
        SimpleXMLElement $node,
        LiteratureReference $literatureReference
    ): void {
        $detailElements = self::findElementByXPath(
            $node,
            'Details/Section/Subreport',
        );

        if (!$detailElements) {
            return;
        }

        $skipElementName = 'ReportHeader';

        foreach ($detailElements as $detailElement) {
            if ($detailElement->count() === 0 || $detailElement->getName() === $skipElementName) {
                continue;
            }

            $connectedObject = new ConnectedObject;
            $literatureReference->addConnectedObject($connectedObject);

            /* InventoryNumber */
            $inventoryNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{@Inventanummer}"]/FormattedValue',
            );

            if ($inventoryNumberElement) {
                $inventoryNumberStr = trim(strval($inventoryNumberElement));
                $connectedObject->setInventoryNumber($inventoryNumberStr);
            }

            /* CatalogNumber */
            $catalogNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{RefXRefs.CatalogueNumber}"]/FormattedValue',
            );

            if ($catalogNumberElement) {
                $catalogNumberStr = trim(strval($catalogNumberElement));
                $connectedObject->setCatalogNumber($catalogNumberStr);
            }

            /* PageNumber */
            $pageNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{RefXRefs.PageNumber}"]/FormattedValue',
            );

            if ($pageNumberElement) {
                $pageNumberStr = trim(strval($pageNumberElement));
                $connectedObject->setPageNumber($pageNumberStr);
            }

            /* FigureNumber */
            $figureNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{RefXRefs.Appendage}"]/FormattedValue',
            );

            if ($figureNumberElement) {
                $figureNumberStr = trim(strval(strval($figureNumberElement)));
                $connectedObject->setFigureNumber($figureNumberStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="4"]/Field[@FieldName="{RefXRefs.Remarks}"]/FormattedValue',
            );

            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $connectedObject->setRemarks($remarksStr);
            }
        }
    }


    private static function registerXPathNamespace(SimpleXMLElement $node): void
    {
        $node->registerXPathNamespace(self::$nsPrefix, self::$ns);
    }


    /**
     * @return SimpleXMLElement[]|false
     *
     * @psalm-return array<array-key, SimpleXMLElement>|false
     */
    private static function findElementsByXPath(SimpleXMLElement $node, string $path)
    {
        self::registerXPathNamespace($node);

        $splitPath = explode('/', $path);

        $nsPrefix = self::$nsPrefix;
        $xpathStr = './/' . implode('/', array_map(
            function ($val) use ($nsPrefix) {
                return empty($val) ? $val : $nsPrefix . ':' . $val;
            },
            $splitPath
        ));

        return $node->xpath($xpathStr);
    }


    /**
     * @return SimpleXMLElement|false
     */
    private static function findElementByXPath(SimpleXMLElement $node, string $path)
    {
        $result = self::findElementsByXPath($node, $path);

        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return false;
    }
}
