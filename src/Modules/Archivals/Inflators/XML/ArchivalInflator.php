<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Archivals\Interfaces\IArchival;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\ArchivalLanguageCollection;

/**
 * Archivals inflator used to inflate archival instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class ArchivalInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';
    private static $langSplitChar = '#';
    private static $splitChar = '#';

    private static $titlesLanguageTypes = [
        Language::DE => 'German',
        Language::EN => 'English',
        'not_assigned' => '(not assigned)',
    ];

    private static $repositoryTypes = [
        Language::DE => 'Besitzer',
        Language::EN => 'Repository',
    ];

    private static $isPublishedString = 'CDA Online-Freigabe';

    private static $sortingNumberFallbackValue = '?';


    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateReferenceId($subNode, $archivalCollection);
        self::inflateDating($subNode, $archivalCollection);
        self::inflateSummary($subNode, $archivalCollection);
        self::inflateTranscription($subNode, $archivalCollection);
        self::inflateLocationAndDate($subNode, $archivalCollection);
        self::inflateRepository($subNode, $archivalCollection);
        self::inflateSignature($subNode, $archivalCollection);
        self::inflateComments($subNode, $archivalCollection);
        self::inflateTranscribedBy($subNode, $archivalCollection);
        self::inflateTranscriptionDate($subNode, $archivalCollection);
        self::inflateTranscriptionAccordingTo($subNode, $archivalCollection);
        self::inflateVerification($subNode, $archivalCollection);
        self::inflateScans($subNode, $archivalCollection);
        self::inflateDocuments($subNode, $archivalCollection);
        self::inflateScanNames($subNode, $archivalCollection);
        self::inflatePeriod($subNode, $archivalCollection);
        self::inflatePublications($subNode, $archivalCollection);
        self::inflateIsPublished($subNode, $archivalCollection);
        self::inflateSortingNumber($subNode, $archivalCollection);
    }


    /* InventoryNumber */
    private static function inflateReferenceId(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $inventoryNumberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim(strval($inventoryNumberElement));
            $archivalCollection->setInventoryNumber($inventoryNumberStr);
        }
    }


    /* Dating */
    private static function inflateDating(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $datingDe = new Dating;
        $datingEn = new Dating;

        $archivalCollection->get(Language::DE)->setDating($datingDe);
        $archivalCollection->get(Language::EN)->setDating($datingEn);

        /* Dated */
        $datedElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{OBJECTS.Dated}"]/FormattedValue',
        );
        if ($datedElement) {
            $datedStr = trim(strval($datedElement));

            $splitDatedStr = self::splitLanguageString($datedStr);

            if (isset($splitDatedStr[0])) {
                $datingDe->setDated($splitDatedStr[0]);
            }

            if (isset($splitDatedStr[1])) {
                $datingEn->setDated($splitDatedStr[1]);
            }
        }

        /* Date begin */
        $dateBeginElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{OBJECTS.DateBegin}"]/FormattedValue',
        );
        if ($dateBeginElement) {
            $dateBeginStr = intval(trim(strval($dateBeginElement)));

            $datingDe->setBegin($dateBeginStr);
            $datingEn->setBegin($dateBeginStr);
        }

        /* Date end */
        $dateEndElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="4"]/Field[@FieldName="{OBJECTS.DateEnd}"]/FormattedValue',
        );
        if ($dateEndElement) {
            $dateEndStr = intval(trim(strval($dateEndElement)));

            $datingDe->setEnd($dateEndStr);
            $datingEn->setEnd($dateEndStr);
        }
    }


    /* Summary */
    /**
     * @return void
     */
    private static function inflateSummary(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ) {
        $summaryDetailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Subreport[@Name="Subreport6"]',
        );

        if (!$summaryDetailElements) {
            return;
        }

        foreach ($summaryDetailElements as $summaryDetailElement) {
            $langElement = self::findElementByXPath(
                $summaryDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{LANGUAGES.Language}"]/FormattedValue',
            );

            $summaryElement = self::findElementByXPath(
                $summaryDetailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{OBJTITLES.Title}"]/FormattedValue',
            );

            if ($langElement && $summaryElement) {
                $langStr = trim(strval($langElement));
                $summaryStr = trim(strval($summaryElement));

                if (self::$titlesLanguageTypes[Language::DE] === $langStr) {
                    $archivalCollection->get(Language::DE)->addSummary($summaryStr);
                } elseif (self::$titlesLanguageTypes[Language::EN] === $langStr) {
                    $archivalCollection->get(Language::EN)->addSummary($summaryStr);
                } elseif (self::$titlesLanguageTypes['not_assigned'] === $langStr) {
                    echo '  Unassigned summary lang for object ' . $archivalCollection->getInventoryNumber() . "\n";
                } else {
                    echo '  Unknown summary lang: ' . $langStr . ' for object ' . $archivalCollection->getInventoryNumber() . "\n";
                    /* Bind title to all languages to prevent loss */
                    $archivalCollection->addSummary($summaryStr);
                }
            }
        }
    }


    /* Transcription */
    private static function inflateTranscription(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $transcriptionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Field[@FieldName="{OBJECTS.Description}"]/FormattedValue',
        );
        if ($transcriptionElement) {
            $transcriptionStr = trim(strval($transcriptionElement));
            $archivalCollection->setTranscription($transcriptionStr);
        }
    }


    /* Location and Date */
    private static function inflateLocationAndDate(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $locationAndDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{OBJECTS.PaperSupport}"]/FormattedValue',
        );
        if ($locationAndDateElement) {
            $locationAndDateStr = trim(strval($locationAndDateElement));

            $splitLocationAndDateStr = self::splitLanguageString($locationAndDateStr);

            if (isset($splitLocationAndDateStr[0])) {
                $archivalCollection->get(Language::DE)->setLocationAndDate($splitLocationAndDateStr[0]);
            }

            if (isset($splitLocationAndDateStr[1])) {
                $archivalCollection->get(Language::EN)->setLocationAndDate($splitLocationAndDateStr[1]);
            }
        }
    }


    /* Repository */
    /**
     * @return void
     */
    private static function inflateRepository(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ) {
        $repositoryDetailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Subreport',
        );

        if (!$repositoryDetailElements) {
            return;
        }

        foreach ($repositoryDetailElements as $repositoryDetailElement) {
            $roleEl = self::findElementByXPath(
                $repositoryDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{@Rolle}"]/FormattedValue',
            );

            $roleName = trim(strval($roleEl));

            $repositoryEl = self::findElementByXPath(
                $repositoryDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
            );

            if (!$repositoryEl) {
                continue;
            }

            $repositoryStr = trim(strval($repositoryEl));

            switch ($roleName) {
                case self::$repositoryTypes[Language::DE]:
                    /* de */
                    $archivalCollection->get(Language::DE)->setRepository($repositoryStr);
                    break;

                case self::$repositoryTypes[Language::EN]:
                    /* en */
                    $archivalCollection->get(Language::EN)->setRepository($repositoryStr);
                    break;

                default:
                    $archivalCollection->setRepository($repositoryStr);
                    return ;
            }
        }

        if (empty($archivalCollection->get(Language::EN)->getRepository())) {
            $archivalCollection->get(Language::EN)->setRepository(
                $archivalCollection->get(Language::DE)->getRepository()
            );
        }
    }


    /* Signature */
    private static function inflateSignature(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $signatureElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{OBJECTS.Signed}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim(strval($signatureElement));
            $archivalCollection->setSignature($signatureStr);
        }
    }


    /* Comments */
    private static function inflateComments(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $commentsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{OBJECTS.Notes}"]/FormattedValue',
        );
        if ($commentsElement) {
            $commentsStr = trim(strval($commentsElement));

            $splitCommentsStr = self::splitLanguageString($commentsStr);

            if (isset($splitCommentsStr[0])) {
                $archivalCollection->get(Language::DE)->setComments($splitCommentsStr[0]);
            }

            if (isset($splitCommentsStr[1])) {
                $archivalCollection->get(Language::EN)->setComments($splitCommentsStr[1]);
            }
        }
    }


    /* Transcribed by */
    private static function inflateTranscribedBy(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $transcribedByElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{OBJCONTEXT.Culture}"]/FormattedValue',
        );
        if ($transcribedByElement) {
            $transcribedByStr = trim(strval($transcribedByElement));

            $archivalCollection->setTranscribedBy($transcribedByStr);
        }
    }


    /* Transcription date */
    private static function inflateTranscriptionDate(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $transcriptionDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
        );
        if ($transcriptionDateElement) {
            $transcriptionDateStr = trim(strval($transcriptionDateElement));

            $archivalCollection->setTranscriptionDate($transcriptionDateStr);
        }
    }


    /* Transcription according to */
    private static function inflateTranscriptionAccordingTo(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $transcriptionAccordingToElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="13"]/Field[@FieldName="{OBJECTS.CatRais}"]/FormattedValue',
        );
        if ($transcriptionAccordingToElement) {
            $transcriptionAccordingToStr = trim(strval($transcriptionAccordingToElement));

            $archivalCollection->setTranscriptionDate($transcriptionAccordingToStr);
        }
    }


    /* Verification */
    private static function inflateVerification(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $verificationElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]/Field[@FieldName="{OBJECTS.Bibliography}"]/FormattedValue',
        );
        if ($verificationElement) {
            $verificationStr = trim(strval($verificationElement));

            $archivalCollection->setVerification($verificationStr);
        }
    }


    /* Scans */
    private static function inflateScans(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $scansElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="15"]/Field[@FieldName="{OBJECTS.Markings}"]/FormattedValue',
        );
        if ($scansElement) {
            $scansStr = trim(strval($scansElement));

            $archivalCollection->setScans($scansStr);
        }
    }


    /* Documents */
    private static function inflateDocuments(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $documentsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="16"]/Field[@FieldName="{OBJECTS.Inscribed}"]/FormattedValue',
        );
        if ($documentsElement) {
            $documentsStr = trim(strval($documentsElement));
            $documentArr = array_map('trim', explode("\n", $documentsStr));

            $archivalCollection->setDocumentReferences($documentArr);
        }
    }


    /* Scan names */
    private static function inflateScanNames(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $scanNamesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="17"]/Field[@FieldName="{OBJECTS.Medium}"]/FormattedValue',
        );
        if ($scanNamesElement) {
            $scanNamesStr = trim(strval($scanNamesElement));

            $scanNamesArr = array_map('trim', explode(self::$splitChar, $scanNamesStr));

            $archivalCollection->setScanNames($scanNamesArr);
        }
    }


    /* Period */
    private static function inflatePeriod(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $periodElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="18"]/Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
        );
        if ($periodElement) {
            $periodStr = trim(strval($periodElement));

            $archivalCollection->setPeriod($periodStr);
            //$archivalCollection->setSortingNumber($periodStr);
        }
    }

    /* Sorting number */
    private static function inflateSortingNumber(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $sortingNumberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="18"]/Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
        );
        if ($sortingNumberElement) {
            $sortingNumberStr = trim(strval($sortingNumberElement));

            if (empty($sortingNumberStr)) {
                $sortingNumberStr = self::$sortingNumberFallbackValue;
            }

            $archivalCollection->setSortingNumber($sortingNumberStr);
        }
    }


    /* Publications */
    /**
     * @return void
     */
    private static function inflatePublications(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ) {
        $publicationDetailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="19"]/Subreport',
        );

        if (!$publicationDetailElements) {
            return;
        }

        foreach ($publicationDetailElements as $publicationDetailElement) {
            self::inflatePublication($publicationDetailElement, $archivalCollection);
        }
    }

    /* Publication */
    private static function inflatePublication(
        SimpleXMLElement $node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $publication = new Publication;
        $wasInflated = false;

        /* Title */
        $titleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{REFERENCEMASTER.Heading}"]/FormattedValue',
        );

        if ($titleElement) {
            $publication->setTitle(trim(strval($titleElement)));
            $wasInflated = true;
        }

        /* PageNumber */
        $pageNumberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PageNumber}"]/FormattedValue',
        );

        if ($pageNumberElement) {
            $publication->setPageNumber(trim(strval($pageNumberElement)));
            $wasInflated = true;
        }

        /* Reference ID */
        $referenceElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{REFERENCEMASTER.ReferenceID}"]/FormattedValue',
        );

        if ($referenceElement) {
            $publication->setReferenceId(trim(strval($referenceElement)));
            $wasInflated = true;
        }


        if ($wasInflated) {
            $archivalCollection->addPublication($publication);
        }
    }


    private static function inflateIsPublished(
        SimpleXMLElement &$node,
        ArchivalLanguageCollection $archivalCollection,
    ): void {
        $isPublishedElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="23"]/Field[@FieldName="{@CDA Online-Freigabe}"]/Value',
        );

        $isPublished = $isPublishedElement !== false && strval($isPublishedElement) === self::$isPublishedString;

        /** @var IArchival */
        foreach ($archivalCollection as $archival) {
            $archival->getMetadata()?->setIsPublished($isPublished);
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
