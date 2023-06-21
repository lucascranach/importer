<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReferenceLanguageCollection;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Event;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\ConnectedObject;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Person;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\AlternateNumber;

/**
 * LiteratureReferences inflator used to inflate literature reference instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class LiteratureReferencesInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';
    private static $langSplitChar = '#';

    private static $inventoryNumberReplaceRegExpArr = [
        '/^GWN_/',
        '/^CDA\./',
        '/^G_G_/',
        '/^G_/',
    ];

    private static $personRolesMapping = [
        'Autor*in' => 'AUTHOR',
        'Herausgeber*in' => 'PUBLISHER',
        'Redaktion' => 'EDITORIAL_STAFF',
        'Bearbeitung' => 'EDITING',
        'Illustrator' => 'ILLUSTRATOR',
        'Verlag' => 'PUBLISHING_HOUSE',
        'unter Mitarbeit' => 'IN_COLLABORATION',
        'Abbildungen' => 'ILLUSTRATIONS',
        'Übersetzung' => 'TRANSLATION',
        'Druck' => 'PRINT',
        'Offizin' => 'OFFICIN',
        'default' => 'UNKNOWN',
    ];

    private static $eventTypesLangMapping = [
        'entstehungszeitraum' => [Language::DE, 'PERIOD_OF_ORIGIN'],
        'period of origin' => [Language::EN, 'PERIOD_OF_ORIGIN'],
        'second edition' => [Language::EN, 'SECOND_EDITION'],
        'revised edition' => [Language::EN, 'REVISED_EDITION'],
    ];

    private static $primarySourceKey = 'Primary source';

    private const PUBLICATION_TYPE_ARTICLE = 'article';
    private const PUBLICATION_TYPE_AUCTION_CATALOGUE = 'auction catalogue';
    private const PUBLICATION_TYPE_COLLECTION_CATALOGUE = 'catalogue';
    private const PUBLICATION_TYPE_CONFERENCE_PROCEEDINGS = 'conference proceedings';
    private const PUBLICATION_TYPE_DISSERTATION = 'dissertation';
    private const PUBLICATION_TYPE_EXHIBITION_CATALOGS = 'exhibition catalogs';
    private const PUBLICATION_TYPE_EXHIBITION_CATALOGUE = 'exhibition catalogue';
    private const PUBLICATION_TYPE_FESTSCHRIFT = 'festschrift';
    private const PUBLICATION_TYPE_GREY_LITERATURE = 'grey literature';
    private const PUBLICATION_TYPE_GUIDEBOOK = 'guidebook';
    private const PUBLICATION_TYPE_GUIDEBOOKS = 'guidebooks';
    private const PUBLICATION_TYPE_MANUSCRIPT = 'manuscript';
    private const PUBLICATION_TYPE_MANUSCRIPT_GENRE = 'manuscript (document genre)';
    private const PUBLICATION_TYPE_MONOGRAPH = 'monograph';
    private const PUBLICATION_TYPE_NEWSPAPER = 'newspaper';
    private const PUBLICATION_TYPE_PAMPHLET = 'pamphlet';
    private const PUBLICATION_TYPE_PRIMARY_SOURCE = 'Primary source';
    private const PUBLICATION_TYPE_REFERENCE_BOOK = 'reference book';
    private const PUBLICATION_TYPE_THESIS = 'thesis';
    private const PUBLICATION_TYPE_UNPUBLISHED_MATERIALS = 'unpublished materials';
    private const PUBLICATION_TYPE_ANTHOLOGY = 'anthology';


    private static $publicationLanguageTypes = [
        Language::DE => [
            self::PUBLICATION_TYPE_ARTICLE => 'Aufsatz',
            self::PUBLICATION_TYPE_AUCTION_CATALOGUE => 'Auktionskatalog',
            self::PUBLICATION_TYPE_COLLECTION_CATALOGUE => 'Bestandskatalog',
            self::PUBLICATION_TYPE_CONFERENCE_PROCEEDINGS => 'Tagungsband',
            self::PUBLICATION_TYPE_DISSERTATION => 'Dissertation',
            self::PUBLICATION_TYPE_EXHIBITION_CATALOGUE => 'Ausstellungskatalog',
            self::PUBLICATION_TYPE_FESTSCHRIFT => 'Festschrift',
            self::PUBLICATION_TYPE_GREY_LITERATURE => 'Graue Literatur',
            self::PUBLICATION_TYPE_GUIDEBOOK => 'Sammlungsführer',
            self::PUBLICATION_TYPE_GUIDEBOOKS => 'Sammlungsführer',
            self::PUBLICATION_TYPE_MANUSCRIPT => 'Manuskript',
            self::PUBLICATION_TYPE_MANUSCRIPT_GENRE => 'Manuskript (Dokumentgenre)',
            self::PUBLICATION_TYPE_MONOGRAPH => 'Monografie',
            self::PUBLICATION_TYPE_NEWSPAPER => 'Zeitungsartikel',
            self::PUBLICATION_TYPE_PRIMARY_SOURCE => 'Primärliteratur',
            self::PUBLICATION_TYPE_THESIS => 'Thesis',
            self::PUBLICATION_TYPE_UNPUBLISHED_MATERIALS => 'Unveröffentlichtes Material',
            self::PUBLICATION_TYPE_ANTHOLOGY => 'Aufsatzsammlung',

            self::PUBLICATION_TYPE_EXHIBITION_CATALOGS => 'Ausstellungskataloge',
            self::PUBLICATION_TYPE_REFERENCE_BOOK => 'Referenzbuch',
            self::PUBLICATION_TYPE_PAMPHLET => 'Pamphlet',
        ],
        Language::EN => [
            self::PUBLICATION_TYPE_ARTICLE => 'Article',
            self::PUBLICATION_TYPE_AUCTION_CATALOGUE => 'Auction catalogue',
            self::PUBLICATION_TYPE_COLLECTION_CATALOGUE => 'Collection catalogue',
            self::PUBLICATION_TYPE_CONFERENCE_PROCEEDINGS => 'Conference proceedings',
            self::PUBLICATION_TYPE_DISSERTATION => 'Dissertation',
            self::PUBLICATION_TYPE_EXHIBITION_CATALOGUE => 'Exhibition catalogue',
            self::PUBLICATION_TYPE_FESTSCHRIFT => 'Festschrift',
            self::PUBLICATION_TYPE_GREY_LITERATURE => 'Grey literature',
            self::PUBLICATION_TYPE_GUIDEBOOK => 'Guidebook',
            self::PUBLICATION_TYPE_GUIDEBOOKS => 'Guidebooks',
            self::PUBLICATION_TYPE_MANUSCRIPT => 'Manuscript',
            self::PUBLICATION_TYPE_MANUSCRIPT_GENRE => 'Manuscript (document genre)',
            self::PUBLICATION_TYPE_MONOGRAPH => 'Monograph',
            self::PUBLICATION_TYPE_NEWSPAPER => 'Newspaper article',
            self::PUBLICATION_TYPE_PRIMARY_SOURCE => 'Primary source',
            self::PUBLICATION_TYPE_THESIS => 'Thesis',
            self::PUBLICATION_TYPE_UNPUBLISHED_MATERIALS => 'Unpublished materials',
            self::PUBLICATION_TYPE_ANTHOLOGY => 'Essay collection',

            self::PUBLICATION_TYPE_EXHIBITION_CATALOGS => 'Exhibition catalogues',
            self::PUBLICATION_TYPE_REFERENCE_BOOK => 'Reference book',
            self::PUBLICATION_TYPE_PAMPHLET => 'Pamphlet',
        ],
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $subNode = $node->{'GroupHeader'};
        $connectedObjectsSubNode = $node->{'Group'};

        self::registerXPathNamespace($subNode);

        self::inflateReferenceId($subNode, $literatureReferenceCollection);
        self::inflateReferenceNumber($subNode, $literatureReferenceCollection);

        self::inflateTitle($subNode, $literatureReferenceCollection);
        self::inflateSubtitle($subNode, $literatureReferenceCollection);
        self::inflateShortTitle($subNode, $literatureReferenceCollection);
        self::inflateLongTitle($subNode, $literatureReferenceCollection);

        self::inflateJournal($subNode, $literatureReferenceCollection);
        self::inflateSeries($subNode, $literatureReferenceCollection);
        self::inflateVolume($subNode, $literatureReferenceCollection);
        self::inflateEdition($subNode, $literatureReferenceCollection);

        self::inflatePublishLocation($subNode, $literatureReferenceCollection);
        self::inflatePublishDate($subNode, $literatureReferenceCollection);
        self::inflatePageNumbers($subNode, $literatureReferenceCollection);
        self::inflateDate($subNode, $literatureReferenceCollection);

        self::inflateEvents($subNode, $literatureReferenceCollection);

        self::inflateCopyright($subNode, $literatureReferenceCollection);

        self::inflatePersons($subNode, $literatureReferenceCollection);
        self::inflatePublications($subNode, $literatureReferenceCollection);

        self::inflateAlternateNumbers($subNode, $literatureReferenceCollection);

        self::inflatePhysicalDescription($subNode, $literatureReferenceCollection);

        self::inflateMention($subNode, $literatureReferenceCollection);

        self::inflateConnectedObjects($connectedObjectsSubNode, $literatureReferenceCollection);

        /* we derive the primary source state from the publications (after they are inflated),
            so we only need the literatureReference instances here */
        self::inflatePrimarySource($literatureReferenceCollection);
    }


    /* ReferenceId */
    private static function inflateReferenceId(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $idElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{ReferenceMaster.ReferenceID}"]/FormattedValue',
        );
        if ($idElement) {
            $idStr = trim(strval($idElement));
            $literatureReferenceCollection->setReferenceId($idStr);
        }
    }


    /* ReferenceNumber */
    private static function inflateReferenceNumber(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $numberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{@Verweisnummer}"]/FormattedValue',
        );
        if ($numberElement) {
            $numberStr = trim(strval($numberElement));
            $literatureReferenceCollection->setReferenceNumber($numberStr);
        }
    }


    /* Title */
    private static function inflateTitle(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $titleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{ReferenceMaster.Title}"]/FormattedValue',
        );
        if ($titleElement) {
            $titleStr = trim(strval($titleElement));
            $literatureReferenceCollection->setTitle($titleStr);
        }
    }


    /* Subtitle */
    private static function inflateSubtitle(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $subtitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{ReferenceMaster.SubTitle}"]/FormattedValue',
        );
        if ($subtitleElement) {
            $subtitleStr = trim(strval($subtitleElement));
            $literatureReferenceCollection->setSubtitle($subtitleStr);
        }
    }


    /* ShortTitle */
    private static function inflateShortTitle(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $shortTitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="4"]/Field[@FieldName="{ReferenceMaster.Heading}"]/FormattedValue',
        );
        if ($shortTitleElement) {
            $shortTitleStr = trim(strval($shortTitleElement));
            $literatureReferenceCollection->setShortTitle($shortTitleStr);
        }
    }


    /* ShortTitle */
    private static function inflateLongTitle(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $longTitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="18"]/Subreport/Details/Section[@SectionNumber="0"]/Field[@FieldName="{TextEntries.TextEntry}"]/FormattedValue',
        );
        if ($longTitleElement) {
            $longTitleStr = trim(strval($longTitleElement));
            $literatureReferenceCollection->setLongTitle($longTitleStr);
        }
    }


    /* Journal */
    private static function inflateJournal(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $journalElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Field[@FieldName="{ReferenceMaster.Journal}"]/FormattedValue',
        );

        if ($journalElement) {
            $journalStr = trim(strval($journalElement));
            $literatureReferenceCollection->setJournal($journalStr);
        }
    }


    /* Series */
    private static function inflateSeries(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $seriesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Field[@FieldName="{ReferenceMaster.Series}"]/FormattedValue',
        );
        if ($seriesElement) {
            $seriesStr = trim(strval($seriesElement));
            $literatureReferenceCollection->setSeries($seriesStr);
        }
    }


    /* Volume */
    private static function inflateVolume(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $volumeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{ReferenceMaster.Volume}"]/FormattedValue',
        );
        if ($volumeElement) {
            $volumeStr = trim(strval($volumeElement));
            $literatureReferenceCollection->setVolume($volumeStr);
        }
    }


    /* Edition */
    private static function inflateEdition(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $editionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Field[@FieldName="{ReferenceMaster.Edition}"]/FormattedValue',
        );
        if ($editionElement) {
            $editionStr = trim(strval($editionElement));
            $literatureReferenceCollection->setEdition($editionStr);
        }
    }


    /* PublishLocation */
    private static function inflatePublishLocation(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $publishLocationElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{ReferenceMaster.PlacePublished}"]/FormattedValue',
        );
        if ($publishLocationElement) {
            $publishLocationStr = trim(strval($publishLocationElement));
            $literatureReferenceCollection->setPublishLocation($publishLocationStr);
        }
    }


    /* PublishDate */
    private static function inflatePublishDate(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $publishDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{ReferenceMaster.YearPublished}"]/FormattedValue',
        );
        if ($publishDateElement) {
            $publishDateStr = trim(strval($publishDateElement));
            $literatureReferenceCollection->setPublishDate($publishDateStr);
        }
    }


    /* PageNumbers */
    private static function inflatePageNumbers(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $pageNumbersElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{ReferenceMaster.NumOfPages}"]/FormattedValue',
        );
        if ($pageNumbersElement) {
            $pageNumbersStr = trim(strval($pageNumbersElement));

            $splitPageNumbers = self::splitLanguageString($pageNumbersStr);

            if (isset($splitPageNumbers[0])) {
                $literatureReferenceCollection->get(Language::DE)->setPageNumbers($splitPageNumbers[0]);
            }

            if (isset($splitPageNumbers[1])) {
                $literatureReferenceCollection->get(Language::EN)->setPageNumbers($splitPageNumbers[1]);
            }
        }
    }


    /* Date */
    private static function inflateDate(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $dateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{ReferenceMaster.DisplayDate}"]/FormattedValue',
        );
        if ($dateElement) {
            $dateStr = trim(strval($dateElement));
            $literatureReferenceCollection->setDate($dateStr);
        }
    }


    /* Events */
    private static function inflateEvents(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
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


            $eventType = strtolower($event->getType());

            if (isset(self::$eventTypesLangMapping[$eventType])) {
                $mappedEventType = self::$eventTypesLangMapping[$eventType];

                $event->setType($mappedEventType[1]);

                switch ($mappedEventType[0]) {
                    case Language::DE:
                        $literatureReferenceCollection->get(Language::DE)->addEvent($event);
                        break;

                    case Language::EN:
                        $literatureReferenceCollection->get(Language::EN)->addEvent($event);
                        break;
                }
            } else {
                $literatureReferenceCollection->addEvent($event);
            }
        }
    }


    /* Copyright */
    private static function inflateCopyright(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $copyrightElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]/Field[@FieldName="{ReferenceMaster.Copyright}"]/FormattedValue',
        );
        if ($copyrightElement) {
            $copyrightStr = trim(strval($copyrightElement));

            $splitCopyrightStr = self::splitLanguageString($copyrightStr);

            if (isset($splitCopyrightStr[0])) {
                $literatureReferenceCollection->get(Language::DE)->setCopyright($splitCopyrightStr[0]);
            }

            if (isset($splitCopyrightStr[1])) {
                $literatureReferenceCollection->get(Language::EN)->setCopyright($splitCopyrightStr[1]);
            }
        }
    }


    /* Persons */
    private static function inflatePersons(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
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
            $literatureReferenceCollection->addPerson($person);

            /* Role */
            $roleElement = self::findElementByXPath(
                $detailElement,
                'Text[@Name="Text2"]/TextValue',
            );

            if ($roleElement) {
                $roleStr = trim(strval($roleElement));

                $mappedRole = isset(self::$personRolesMapping[$roleStr])
                    ? self::$personRolesMapping[$roleStr]
                    : self::$personRolesMapping['default'];

                $person->setRole($mappedRole);
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
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
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

            $publicationDe = new Publication;
            $publicationEn = new Publication;

            /* Type */
            $typeElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{Terms.Term}"]/FormattedValue',
            );

            if ($typeElement) {
                $typeStr = trim(strval($typeElement));
                $publicationDe->setType($typeStr);
                $publicationEn->setType($typeStr);

                if (strlen($typeStr) > 0) {
                    // TODO: Handle missing publication type and translation
                    $deText = $typeStr;
                    if (isset(self::$publicationLanguageTypes[Language::DE][$typeStr])) {
                        $deText = self::$publicationLanguageTypes[Language::DE][$typeStr];
                    } else {
                        echo get_class() . ": Missing mapping for publication type: '" . $typeStr . "'.\n";
                    }

                    $enText = self::$publicationLanguageTypes[Language::EN][$typeStr] ?? $typeStr;
                    if (isset(self::$publicationLanguageTypes[Language::EN][$typeStr])) {
                        $enText = self::$publicationLanguageTypes[Language::EN][$typeStr];
                    } else {
                        echo get_class() . ": Missing mapping for publication type: '" . $typeStr . "'.\n";
                    }

                    $publicationDe->setText($deText);
                    $publicationEn->setText($enText);
                }
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{ThesXrefs.Remarks}"]/FormattedValue',
            );

            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));

                $splitRemarksStr = self::splitLanguageString($remarksStr);

                if (isset($splitRemarksStr[0])) {
                    $publicationDe->setRemarks($splitRemarksStr[0]);
                }

                if (isset($splitRemarksStr[1])) {
                    $publicationEn->setRemarks($splitRemarksStr[1]);
                }
            }


            if (!empty($publicationDe->getType())) {
                $literatureReferenceCollection->get(Language::DE)->addPublication($publicationDe);
            }

            if (!empty($publicationEn->getType())) {
                $literatureReferenceCollection->get(Language::EN)->addPublication($publicationEn);
            }
        }
    }


    /* AlternateNumbers */
    private static function inflateAlternateNumbers(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ): void {
        $detailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="17"]/Subreport'
        );

        if (!$detailElements) {
            return;
        }

        foreach ($detailElements as $detailElement) {
            $alternateNumber = new AlternateNumber();

            /* Description */
            $descriptionElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{AltNumDescriptions.AltNumDescription}"]/FormattedValue',
            );
            if ($descriptionElement) {
                $descriptionStr = trim(strval($descriptionElement));
                $alternateNumber->setDescription($descriptionStr);
            }

            /* Number */
            $numberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{AltNums.AltNum}"]/FormattedValue',
            );
            if ($numberElement) {
                $numberStr = trim(strval($numberElement));
                $alternateNumber->setNumber($numberStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{AltNums.Remarks}"]/FormattedValue',
            );
            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $alternateNumber->setRemarks($remarksStr);
            }

            /* Addition to alternate numbers */
            if (!empty($alternateNumber->getDescription())
                || !empty($alternateNumber->getNumber())
                || !empty($alternateNumber->getRemarks())
            ) {
                $literatureReferenceCollection->addAlternateNumber($alternateNumber);
            }
        }
    }


    /* Physical Description */
    private static function inflatePhysicalDescription(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ) {
        $physicalDescriptionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="19"]/Field[@FieldName="{ReferenceMaster.PhysDescription}"]/FormattedValue'
        );

        if ($physicalDescriptionElement) {
            $physicalDescriptionStr = trim(strval($physicalDescriptionElement));

            $splitPhysicalDescriptionStr = self::splitLanguageString($physicalDescriptionStr);

            if (isset($splitPhysicalDescriptionStr[0])) {
                $literatureReferenceCollection->get(Language::DE)->setPhysicalDescription($splitPhysicalDescriptionStr[0]);
            }

            if (isset($splitPhysicalDescriptionStr[1])) {
                $literatureReferenceCollection->get(Language::EN)->setPhysicalDescription($splitPhysicalDescriptionStr[1]);
            }
        }
    }


    /* Mention */
    private static function inflateMention(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ) {
        $mentionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="20"]/Field[@FieldName="{ReferenceMaster.BoilerText}"]/FormattedValue'
        );

        if ($mentionElement) {
            $mentionStr = trim(strval($mentionElement));

            $literatureReferenceCollection->setMention($mentionStr);
        }
    }


    /* ConnectedObjects */
    private static function inflateConnectedObjects(
        SimpleXMLElement $node,
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
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

            $connectedObjectDe = new ConnectedObject;
            $connectedObjectEn = new ConnectedObject;

            $literatureReferenceCollection->get(Language::DE)->addConnectedObject($connectedObjectDe);
            $literatureReferenceCollection->get(Language::EN)->addConnectedObject($connectedObjectEn);

            /* InventoryNumber */
            $inventoryNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{@Inventanummer}"]/FormattedValue',
            );

            if ($inventoryNumberElement) {
                $inventoryNumberStr = trim(strval($inventoryNumberElement));

                $cleanInventoryNumberStr = preg_replace(
                    self::$inventoryNumberReplaceRegExpArr,
                    '',
                    $inventoryNumberStr,
                );

                $connectedObjectDe->setInventoryNumber($cleanInventoryNumberStr);
                $connectedObjectEn->setInventoryNumber($cleanInventoryNumberStr);
            }

            /* CatalogNumber */
            $catalogNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{RefXRefs.CatalogueNumber}"]/FormattedValue',
            );

            if ($catalogNumberElement) {
                $catalogNumberStr = trim(strval($catalogNumberElement));
                $connectedObjectDe->setCatalogNumber($catalogNumberStr);
                $connectedObjectEn->setCatalogNumber($catalogNumberStr);
            }

            /* PageNumber */
            $pageNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{RefXRefs.PageNumber}"]/FormattedValue',
            );

            if ($pageNumberElement) {
                $pageNumberStr = trim(strval($pageNumberElement));

                $splitPageNumberStr = self::splitLanguageString($pageNumberStr);

                if (isset($splitPageNumberStr[0])) {
                    $connectedObjectDe->setPageNumber($splitPageNumberStr[0]);
                }

                if (isset($splitPageNumberStr[1])) {
                    $connectedObjectEn->setPageNumber($splitPageNumberStr[1]);
                }
            }

            /* FigureNumber */
            $figureNumberElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{RefXRefs.Appendage}"]/FormattedValue',
            );

            if ($figureNumberElement) {
                $figureNumberStr = trim(strval(strval($figureNumberElement)));

                $splitFigureNumber = self::splitLanguageString($figureNumberStr);

                if (isset($splitFigureNumber[0])) {
                    $connectedObjectDe->setFigureNumber($splitFigureNumber[0]);
                }

                if (isset($splitFigureNumber[1])) {
                    $connectedObjectEn->setFigureNumber($splitFigureNumber[1]);
                }
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Section[@SectionNumber="4"]/Field[@FieldName="{RefXRefs.Remarks}"]/FormattedValue',
            );

            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));

                $splitRemarksStr = self::splitLanguageString($remarksStr);

                if (isset($splitRemarksStr[0])) {
                    $connectedObjectDe->setRemarks($splitRemarksStr[0]);
                }

                if (isset($splitRemarksStr[1])) {
                    $connectedObjectEn->setRemarks($splitRemarksStr[1]);
                }
            }
        }
    }


    /* Primary source */
    private static function inflatePrimarySource(
        LiteratureReferenceLanguageCollection $literatureReferenceCollection,
    ) {
        $isPrimarySource = false;

        /* publications should be the same for each language,
            so we use the publications list of the german literatureReference */
        foreach ($literatureReferenceCollection->getPublications() as $publication) {
            if ($isPrimarySource = ($publication->getType() == self::$primarySourceKey)) {
                break;
            }
        }

        $literatureReferenceCollection->setIsPrimarySource($isPrimarySource);
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


    /*
      TODO: Move out into helper -> dynamically settable at runtime if possible
        -> composition over inheritance
    */
    private static function splitLanguageString(string $langStr): array
    {
        $splitLangStrs = array_map('trim', explode(self::$langSplitChar, $langStr));
        $cntItems = count($splitLangStrs);

        if ($cntItems > 0 && $cntItems < 2) {
            $splitLangStrs[] = $splitLangStrs[0];
        }

        return $splitLangStrs;
    }
}
