<?php

namespace CranachDigitalArchive\Importer\Modules\LiteratureReferences\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\LiteratureReferences\Entities\LiteratureReference;
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

    private const PUBLICATION_TYPE_ARTICLE_ARTICLE = 'article';
    private const PUBLICATION_TYPE_ARTICLE_ARTICLES = 'articles';
    private const PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGS = 'auction catalogs';
    private const PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUE = 'auction catalogue';
    private const PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUES = 'auction catalogues';
    private const PUBLICATION_TYPE_ARTICLE_CATALOGUE = 'catalogue';
    private const PUBLICATION_TYPE_ARTICLE_CATALOGUES = 'catalogues';
    private const PUBLICATION_TYPE_ARTICLE_CATALOGUES__EXHIBITION = 'catalogues, exhibition';
    private const PUBLICATION_TYPE_ARTICLE_CONFERENCE_PROCEEDINGS = 'conference proceedings';
    private const PUBLICATION_TYPE_ARTICLE_DISSERTATION = 'dissertation';
    private const PUBLICATION_TYPE_ARTICLE_DISSERTATIONS = 'dissertations';
    private const PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGS = 'exhibition catalogs';
    private const PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUE = 'exhibition catalogue';
    private const PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUES = 'exhibition catalogues';
    private const PUBLICATION_TYPE_ARTICLE_FESTSCHRIFT = 'festschrift';
    private const PUBLICATION_TYPE_ARTICLE_GREY_LITERATURE = 'grey literature';
    private const PUBLICATION_TYPE_ARTICLE_GUIDEBOOK = 'guidebook';
    private const PUBLICATION_TYPE_ARTICLE_GUIDEBOOKS = 'guidebooks';
    private const PUBLICATION_TYPE_ARTICLE_LEXICON = 'lexicon';
    private const PUBLICATION_TYPE_ARTICLE_LEXICONS = 'lexicons';
    private const PUBLICATION_TYPE_ARTICLE_MANUSCRIPT = 'manuscript';
    private const PUBLICATION_TYPE_ARTICLE_MANUSCRIPTS = 'manuscripts';
    private const PUBLICATION_TYPE_ARTICLE_MONOGRAPH = 'monograph';
    private const PUBLICATION_TYPE_ARTICLE_MONOGRAPHS = 'monographs';
    private const PUBLICATION_TYPE_ARTICLE_NEWSPAPER = 'newspaper';
    private const PUBLICATION_TYPE_ARTICLE_PAMPHLET = 'pamphlet';
    private const PUBLICATION_TYPE_ARTICLE_PRIMARY_SOURCE = 'Primary source';
    private const PUBLICATION_TYPE_ARTICLE_REFERENCE_BOOK = 'reference book';
    private const PUBLICATION_TYPE_ARTICLE_THESIS = 'thesis';
    private const PUBLICATION_TYPE_ARTICLE_UNPUBLISHED_MATERIALS = 'unpublished materials';


    private static $publicationLanguageTypes = [
        Language::DE => [
            self::PUBLICATION_TYPE_ARTICLE_ARTICLE => 'Artikel',
            self::PUBLICATION_TYPE_ARTICLE_ARTICLES => 'Artikel',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGS => 'Auktionskataloge',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUE => 'Auktionskatalog',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUES => 'Auktionskataloge',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUE => 'Katalog',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUES => 'Kataloge',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUES__EXHIBITION => 'Ausstellungskataloge',
            self::PUBLICATION_TYPE_ARTICLE_CONFERENCE_PROCEEDINGS => 'Tagungsband',
            self::PUBLICATION_TYPE_ARTICLE_DISSERTATION => 'Dissertation',
            self::PUBLICATION_TYPE_ARTICLE_DISSERTATIONS => 'Dissertationen',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGS => 'Ausstellungskataloge',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUE => 'Ausstellungskatalog',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUES => 'Ausstellungskataloge',
            self::PUBLICATION_TYPE_ARTICLE_FESTSCHRIFT => 'Festschrift',
            self::PUBLICATION_TYPE_ARTICLE_GREY_LITERATURE => 'Graue Literatur',
            self::PUBLICATION_TYPE_ARTICLE_GUIDEBOOK => 'Reiseführer',
            self::PUBLICATION_TYPE_ARTICLE_GUIDEBOOKS => 'Reiseführer',
            self::PUBLICATION_TYPE_ARTICLE_LEXICON => 'Lexikon',
            self::PUBLICATION_TYPE_ARTICLE_LEXICONS => 'Lexika',
            self::PUBLICATION_TYPE_ARTICLE_MANUSCRIPT => 'Manuskript',
            self::PUBLICATION_TYPE_ARTICLE_MANUSCRIPTS => 'Manuskripte',
            self::PUBLICATION_TYPE_ARTICLE_MONOGRAPH => 'Monografie',
            self::PUBLICATION_TYPE_ARTICLE_MONOGRAPHS => 'Monografien',
            self::PUBLICATION_TYPE_ARTICLE_NEWSPAPER => 'Zeitung',
            self::PUBLICATION_TYPE_ARTICLE_PAMPHLET => 'Pamphlet',
            self::PUBLICATION_TYPE_ARTICLE_PRIMARY_SOURCE => 'Primärliteratur',
            self::PUBLICATION_TYPE_ARTICLE_REFERENCE_BOOK => 'Referenzbuch',
            self::PUBLICATION_TYPE_ARTICLE_THESIS => 'Thesis',
            self::PUBLICATION_TYPE_ARTICLE_UNPUBLISHED_MATERIALS => 'Unveröffentlichtes Material',
        ],
        Language::EN => [
            self::PUBLICATION_TYPE_ARTICLE_ARTICLE => 'Article',
            self::PUBLICATION_TYPE_ARTICLE_ARTICLES => 'Articles',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGS => 'Auction catalogs',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUE => 'Auction catalogue',
            self::PUBLICATION_TYPE_ARTICLE_AUCTION_CATALOGUES => 'Auction catalogues',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUE => 'Catalogue',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUES => 'Catalogues',
            self::PUBLICATION_TYPE_ARTICLE_CATALOGUES__EXHIBITION => 'Catalogues, exhibition',
            self::PUBLICATION_TYPE_ARTICLE_CONFERENCE_PROCEEDINGS => 'Conference proceedings',
            self::PUBLICATION_TYPE_ARTICLE_DISSERTATION => 'Dissertation',
            self::PUBLICATION_TYPE_ARTICLE_DISSERTATIONS => 'Dissertations',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGS => 'Exhibition catalogs',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUE => 'Exhibition catalogue',
            self::PUBLICATION_TYPE_ARTICLE_EXHIBITION_CATALOGUES => 'Exhibition catalogues',
            self::PUBLICATION_TYPE_ARTICLE_FESTSCHRIFT => 'Festschrift',
            self::PUBLICATION_TYPE_ARTICLE_GREY_LITERATURE => 'Grey literature',
            self::PUBLICATION_TYPE_ARTICLE_GUIDEBOOK => 'Guidebook',
            self::PUBLICATION_TYPE_ARTICLE_GUIDEBOOKS => 'Guidebooks',
            self::PUBLICATION_TYPE_ARTICLE_LEXICON => 'Lexicon',
            self::PUBLICATION_TYPE_ARTICLE_LEXICONS => 'Lexicons',
            self::PUBLICATION_TYPE_ARTICLE_MANUSCRIPT => 'Manuscript',
            self::PUBLICATION_TYPE_ARTICLE_MANUSCRIPTS => 'Manuscripts',
            self::PUBLICATION_TYPE_ARTICLE_MONOGRAPH => 'Monograph',
            self::PUBLICATION_TYPE_ARTICLE_MONOGRAPHS => 'Monographs',
            self::PUBLICATION_TYPE_ARTICLE_NEWSPAPER => 'Newspaper',
            self::PUBLICATION_TYPE_ARTICLE_PAMPHLET => 'Pamphlet',
            self::PUBLICATION_TYPE_ARTICLE_PRIMARY_SOURCE => 'Primary source',
            self::PUBLICATION_TYPE_ARTICLE_REFERENCE_BOOK => 'Reference book',
            self::PUBLICATION_TYPE_ARTICLE_THESIS => 'Thesis',
            self::PUBLICATION_TYPE_ARTICLE_UNPUBLISHED_MATERIALS => 'Unpublished materials'
        ],
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement &$node,
        LiteratureReference &$literatureReferenceDe,
        LiteratureReference &$literatureReferenceEn
    ): void {
        $subNode = $node->{'GroupHeader'};
        $connectedObjectsSubNode = $node->{'Group'};

        self::registerXPathNamespace($subNode);

        self::inflateReferenceId(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateReferenceNumber(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateTitle(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateSubtitle(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateShortTitle(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateLongTitle(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateJournal(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateSeries(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateVolume(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateEdition(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflatePublishLocation(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflatePublishDate(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflatePageNumbers(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflateDate(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateEvents(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateCopyright(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflatePersons(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );
        self::inflatePublications(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateAlternateNumbers(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflatePhysicalDescription(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateMention(
            $subNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        self::inflateConnectedObjects(
            $connectedObjectsSubNode,
            $literatureReferenceDe,
            $literatureReferenceEn
        );

        /* we derive the primary source state from the publications (after they are inflated),
            so we only need the literatureReference instances here */
        self::inflatePrimarySource(
            $literatureReferenceDe,
            $literatureReferenceEn
        );
    }


    /* ReferenceId */
    private static function inflateReferenceId(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $idElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{ReferenceMaster.ReferenceID}"]/FormattedValue',
        );
        if ($idElement) {
            $idStr = trim(strval($idElement));
            $literatureReferenceDe->setReferenceId($idStr);
            $literatureReferenceEn->setReferenceId($idStr);
        }
    }


    /* ReferenceNumber */
    private static function inflateReferenceNumber(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $numberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{@Verweisnummer}"]/FormattedValue',
        );
        if ($numberElement) {
            $numberStr = trim(strval($numberElement));
            $literatureReferenceDe->setReferenceNumber($numberStr);
            $literatureReferenceEn->setReferenceNumber($numberStr);
        }
    }


    /* Title */
    private static function inflateTitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $titleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{ReferenceMaster.Title}"]/FormattedValue',
        );
        if ($titleElement) {
            $titleStr = trim(strval($titleElement));
            $literatureReferenceDe->setTitle($titleStr);
            $literatureReferenceEn->setTitle($titleStr);
        }
    }


    /* Subtitle */
    private static function inflateSubtitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $subtitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{ReferenceMaster.SubTitle}"]/FormattedValue',
        );
        if ($subtitleElement) {
            $subtitleStr = trim(strval($subtitleElement));
            $literatureReferenceDe->setSubtitle($subtitleStr);
            $literatureReferenceEn->setSubtitle($subtitleStr);
        }
    }


    /* ShortTitle */
    private static function inflateShortTitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $shortTitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="4"]/Field[@FieldName="{ReferenceMaster.Heading}"]/FormattedValue',
        );
        if ($shortTitleElement) {
            $shortTitleStr = trim(strval($shortTitleElement));
            $literatureReferenceDe->setShortTitle($shortTitleStr);
            $literatureReferenceEn->setShortTitle($shortTitleStr);
        }
    }


    /* ShortTitle */
    private static function inflateLongTitle(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $longTitleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="18"]/Subreport/Details/Section[@SectionNumber="0"]/Field[@FieldName="{TextEntries.TextEntry}"]/FormattedValue',
        );
        if ($longTitleElement) {
            $longTitleStr = trim(strval($longTitleElement));
            $literatureReferenceDe->setLongTitle($longTitleStr);
            $literatureReferenceEn->setLongTitle($longTitleStr);
        }
    }


    /* Journal */
    private static function inflateJournal(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $journalElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Field[@FieldName="{ReferenceMaster.Journal}"]/FormattedValue',
        );

        if ($journalElement) {
            $journalStr = trim(strval($journalElement));
            $literatureReferenceDe->setJournal($journalStr);
            $literatureReferenceEn->setJournal($journalStr);
        }
    }


    /* Series */
    private static function inflateSeries(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $seriesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Field[@FieldName="{ReferenceMaster.Series}"]/FormattedValue',
        );
        if ($seriesElement) {
            $seriesStr = trim(strval($seriesElement));
            $literatureReferenceDe->setSeries($seriesStr);
            $literatureReferenceEn->setSeries($seriesStr);
        }
    }


    /* Volume */
    private static function inflateVolume(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $volumeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{ReferenceMaster.Volume}"]/FormattedValue',
        );
        if ($volumeElement) {
            $volumeStr = trim(strval($volumeElement));
            $literatureReferenceDe->setVolume($volumeStr);
            $literatureReferenceEn->setVolume($volumeStr);
        }
    }


    /* Edition */
    private static function inflateEdition(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $editionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Field[@FieldName="{ReferenceMaster.Edition}"]/FormattedValue',
        );
        if ($editionElement) {
            $editionStr = trim(strval($editionElement));
            $literatureReferenceDe->setEdition($editionStr);
            $literatureReferenceEn->setEdition($editionStr);
        }
    }


    /* PublishLocation */
    private static function inflatePublishLocation(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $publishLocationElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{ReferenceMaster.PlacePublished}"]/FormattedValue',
        );
        if ($publishLocationElement) {
            $publishLocationStr = trim(strval($publishLocationElement));
            $literatureReferenceDe->setPublishLocation($publishLocationStr);
            $literatureReferenceEn->setPublishLocation($publishLocationStr);
        }
    }


    /* PublishDate */
    private static function inflatePublishDate(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $publishDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{ReferenceMaster.YearPublished}"]/FormattedValue',
        );
        if ($publishDateElement) {
            $publishDateStr = trim(strval($publishDateElement));
            $literatureReferenceDe->setPublishDate($publishDateStr);
            $literatureReferenceEn->setPublishDate($publishDateStr);
        }
    }


    /* PageNumbers */
    private static function inflatePageNumbers(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $pageNumbersElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{ReferenceMaster.NumOfPages}"]/FormattedValue',
        );
        if ($pageNumbersElement) {
            $pageNumbersStr = trim(strval($pageNumbersElement));

            $splitPageNumbers = self::splitLanguageString($pageNumbersStr);

            if (isset($splitPageNumbers[0])) {
                $literatureReferenceDe->setPageNumbers($splitPageNumbers[0]);
            }

            if (isset($splitPageNumbers[1])) {
                $literatureReferenceEn->setPageNumbers($splitPageNumbers[1]);
            }
        }
    }


    /* Date */
    private static function inflateDate(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $dateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{ReferenceMaster.DisplayDate}"]/FormattedValue',
        );
        if ($dateElement) {
            $dateStr = trim(strval($dateElement));
            $literatureReferenceDe->setDate($dateStr);
            $literatureReferenceEn->setDate($dateStr);
        }
    }


    /* Events */
    private static function inflateEvents(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
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
                        $literatureReferenceDe->addEvent($event);
                        break;

                    case Language::EN:
                        $literatureReferenceEn->addEvent($event);
                        break;
                }
            } else {
                $literatureReferenceDe->addEvent($event);
                $literatureReferenceEn->addEvent($event);
            }
        }
    }


    /* Copyright */
    private static function inflateCopyright(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ): void {
        $copyrightElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]/Field[@FieldName="{ReferenceMaster.Copyright}"]/FormattedValue',
        );
        if ($copyrightElement) {
            $copyrightStr = trim(strval($copyrightElement));

            $splitCopyrightStr = self::splitLanguageString($copyrightStr);

            if (isset($splitCopyrightStr[0])) {
                $literatureReferenceDe->setCopyright($splitCopyrightStr[0]);
            }

            if (isset($splitCopyrightStr[1])) {
                $literatureReferenceEn->setCopyright($splitCopyrightStr[1]);
            }
        }
    }


    /* Persons */
    private static function inflatePersons(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
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
            $literatureReferenceDe->addPerson($person);
            $literatureReferenceEn->addPerson($person);

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
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
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
                    $publicationDe->setText(self::$publicationLanguageTypes[Language::DE][$typeStr]);
                    $publicationEn->setText(self::$publicationLanguageTypes[Language::EN][$typeStr]);
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
                $literatureReferenceDe->addPublication($publicationDe);
            }

            if (!empty($publicationEn->getType())) {
                $literatureReferenceEn->addPublication($publicationEn);
            }
        }
    }


    /* AlternateNumbers */
    private static function inflateAlternateNumbers(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
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
                $literatureReferenceDe->addAlternateNumber($alternateNumber);
                $literatureReferenceEn->addAlternateNumber($alternateNumber);
            }
        }
    }


    /* Physical Description */
    private static function inflatePhysicalDescription(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ) {
        $physicalDescriptionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="19"]/Field[@FieldName="{ReferenceMaster.PhysDescription}"]/FormattedValue'
        );

        if ($physicalDescriptionElement) {
            $physicalDescriptionStr = trim(strval($physicalDescriptionElement));

            $splitPhysicalDescriptionStr = self::splitLanguageString($physicalDescriptionStr);

            if (isset($splitPhysicalDescriptionStr[0])) {
                $literatureReferenceDe->setPhysicalDescription($splitPhysicalDescriptionStr[0]);
            }

            if (isset($splitPhysicalDescriptionStr[1])) {
                $literatureReferenceEn->setPhysicalDescription($splitPhysicalDescriptionStr[1]);
            }
        }
    }


    /* Mention */
    private static function inflateMention(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ) {
        $mentionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="20"]/Field[@FieldName="{ReferenceMaster.BoilerText}"]/FormattedValue'
        );

        if ($mentionElement) {
            $mentionStr = trim(strval($mentionElement));

            $literatureReferenceDe->setMention($mentionStr);
            $literatureReferenceEn->setMention($mentionStr);
        }
    }


    /* ConnectedObjects */
    private static function inflateConnectedObjects(
        SimpleXMLElement $node,
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
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

            $literatureReferenceDe->addConnectedObject($connectedObjectDe);
            $literatureReferenceEn->addConnectedObject($connectedObjectEn);

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
        LiteratureReference $literatureReferenceDe,
        LiteratureReference $literatureReferenceEn
    ) {
        $isPrimarySource = false;

        /* publications should be the same for each language,
            so we use the publications list of the german literatureReference */
        foreach ($literatureReferenceDe->getPublications() as $publication) {
            if ($isPrimarySource = ($publication->getType() == self::$primarySourceKey)) {
                break;
            }
        }

        $literatureReferenceDe->setIsPrimarySource($isPrimarySource);
        $literatureReferenceEn->setIsPrimarySource($isPrimarySource);
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
