<?php

namespace CranachDigitalArchive\Importer\Modules\Archivals\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Archivals\Entities\Archival;

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
        Language::DE => 'GERMAN',
        Language::EN => 'ENGLISH',
        'not_assigned' => '(not assigned)',
    ];

    private static $repositoryTypes = [
        Language::DE => 'Besitzer',
        Language::EN => 'Repository',
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateReferenceId($subNode, $archivalDe, $archivalEn);
        self::inflateDating($subNode, $archivalDe, $archivalEn);
        self::inflateSummary($subNode, $archivalDe, $archivalEn);
        self::inflateTranscription($subNode, $archivalDe, $archivalEn);
        self::inflateLocationAndDate($subNode, $archivalDe, $archivalEn);
        self::inflateRepository($subNode, $archivalDe, $archivalEn);
        self::inflateSignature($subNode, $archivalDe, $archivalEn);
        self::inflateComments($subNode, $archivalDe, $archivalEn);
        self::inflateTranscribedBy($subNode, $archivalDe, $archivalEn);
        self::inflateTranscriptionDate($subNode, $archivalDe, $archivalEn);
        self::inflateTranscriptionAccordingTo($subNode, $archivalDe, $archivalEn);
        self::inflateVerification($subNode, $archivalDe, $archivalEn);
        self::inflateScans($subNode, $archivalDe, $archivalEn);
        self::inflateDocuments($subNode, $archivalDe, $archivalEn);
        self::inflateScanNames($subNode, $archivalDe, $archivalEn);
        self::inflatePeriod($subNode, $archivalDe, $archivalEn);
        self::inflatePublications($subNode, $archivalDe, $archivalEn);
    }


    /* InventoryNumber */
    private static function inflateReferenceId(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $inventoryNumberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim($inventoryNumberElement);
            $archivalDe->setInventoryNumber($inventoryNumberStr);
            $archivalEn->setInventoryNumber($inventoryNumberStr);
        }
    }


    /* Dating */
    private static function inflateDating(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $datingDe = new Dating;
        $datingEn = new Dating;

        $archivalDe->setDating($datingDe);
        $archivalEn->setDating($datingEn);

        /* Dated */
        $datedElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{OBJECTS.Dated}"]/FormattedValue',
        );
        if ($datedElement) {
            $datedStr = trim($datedElement);

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
            $dateBeginStr = intval(trim($dateBeginElement));

            $datingDe->setBegin($dateBeginStr);
            $datingEn->setBegin($dateBeginStr);
        }

        /* Date end */
        $dateEndElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="4"]/Field[@FieldName="{OBJECTS.DateEnd}"]/FormattedValue',
        );
        if ($dateEndElement) {
            $dateEndStr = intval(trim($dateEndElement));

            $datingDe->setEnd($dateEndStr);
            $datingEn->setEnd($dateEndStr);
        }
    }


    /* Summary */
    private static function inflateSummary(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
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
                $langStr = trim($langElement);
                $summaryStr = trim($summaryElement);

                if (self::$titlesLanguageTypes[Language::DE] === $langStr) {
                    $archivalDe->addSummary($summaryStr);
                } elseif (self::$titlesLanguageTypes[Language::EN] === $langStr) {
                    $archivalEn->addSummary($summaryStr);
                } elseif (self::$titlesLanguageTypes['not_assigned'] === $langStr) {
                    echo '  Unassigned summary lang for object ' . $archivalDe->getInventoryNumber() . "\n";
                } else {
                    echo '  Unknown summary lang: ' . $langStr . ' for object ' . $archivalDe->getInventoryNumber() . "\n";
                    /* Bind title to both languages to prevent loss */
                    $archivalDe->addSummary($summaryStr);
                    $archivalEn->addSummary($summaryStr);
                }
            }
        }
    }


    /* Transcription */
    private static function inflateTranscription(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $transcriptionElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Field[@FieldName="{OBJECTS.Description}"]/FormattedValue',
        );
        if ($transcriptionElement) {
            $transcriptionStr = trim($transcriptionElement);
            $archivalDe->setTranscription($transcriptionStr);
            $archivalEn->setTranscription($transcriptionStr);
        }
    }


    /* Location and Date */
    private static function inflateLocationAndDate(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $locationAndDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{OBJECTS.PaperSupport}"]/FormattedValue',
        );
        if ($locationAndDateElement) {
            $locationAndDateStr = trim($locationAndDateElement);

            $splitLocationAndDateStr = self::splitLanguageString($locationAndDateStr);

            if (isset($splitLocationAndDateStr[0])) {
                $archivalDe->setLocationAndDate($splitLocationAndDateStr[0]);
            }

            if (isset($splitLocationAndDateStr[1])) {
                $archivalEn->setLocationAndDate($splitLocationAndDateStr[1]);
            }
        }
    }


    /* Repository */
    private static function inflateRepository(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
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

            $roleName = trim($roleEl);

            $repositoryEl = self::findElementByXPath(
                $repositoryDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
            );

            if (!$repositoryEl) {
                continue;
            }

            $repositoryStr = trim($repositoryEl);

            switch ($roleName) {
                case self::$repositoryTypes[Language::DE]:
                    /* de */
                    $archivalDe->setRepository($repositoryStr);
                    break;

                case self::$repositoryTypes[Language::EN]:
                    /* en */
                    $archivalEn->setRepository($repositoryStr);
                    break;

                default:
                    $archivalDe->setRepository($repositoryStr);
                    $archivalEn->setRepository($repositoryStr);
                    return ;
            }
        }

        if (empty($archivalEn->getRepository())) {
            $archivalEn->setRepository($archivalDe->getRepository());
        }
    }


    /* Signature */
    private static function inflateSignature(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $signatureElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{OBJECTS.Signed}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim($signatureElement);
            $archivalDe->setSignature($signatureStr);
            $archivalEn->setSignature($signatureStr);
        }
    }


    /* Comments */
    private static function inflateComments(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $commentsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{OBJECTS.Notes}"]/FormattedValue',
        );
        if ($commentsElement) {
            $commentsStr = trim($commentsElement);

            $splitCommentsStr = self::splitLanguageString($commentsStr);

            if (isset($splitCommentsStr[0])) {
                $archivalDe->setComments($splitCommentsStr[0]);
            }

            if (isset($splitCommentsStr[1])) {
                $archivalEn->setComments($splitCommentsStr[1]);
            }
        }
    }


    /* Transcribed by */
    private static function inflateTranscribedBy(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $transcribedByElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{OBJCONTEXT.Culture}"]/FormattedValue',
        );
        if ($transcribedByElement) {
            $transcribedByStr = trim($transcribedByElement);

            $archivalDe->setTranscribedBy($transcribedByStr);
            $archivalEn->setTranscribedBy($transcribedByStr);
        }
    }


    /* Transcription date */
    private static function inflateTranscriptionDate(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $transcriptionDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
        );
        if ($transcriptionDateElement) {
            $transcriptionDateStr = trim($transcriptionDateElement);

            $archivalDe->setTranscriptionDate($transcriptionDateStr);
            $archivalEn->setTranscriptionDate($transcriptionDateStr);
        }
    }


    /* Transcription according to */
    private static function inflateTranscriptionAccordingTo(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $transcriptionAccordingToElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="13"]/Field[@FieldName="{OBJECTS.CatRais}"]/FormattedValue',
        );
        if ($transcriptionAccordingToElement) {
            $transcriptionAccordingToStr = trim($transcriptionAccordingToElement);

            $archivalDe->setTranscriptionDate($transcriptionAccordingToStr);
            $archivalEn->setTranscriptionDate($transcriptionAccordingToStr);
        }
    }


    /* Verification */
    private static function inflateVerification(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $verificationElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]/Field[@FieldName="{OBJECTS.Bibliography}"]/FormattedValue',
        );
        if ($verificationElement) {
            $verificationStr = trim($verificationElement);

            $archivalDe->setVerification($verificationStr);
            $archivalEn->setVerification($verificationStr);
        }
    }


    /* Scans */
    private static function inflateScans(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $scansElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="15"]/Field[@FieldName="{OBJECTS.Markings}"]/FormattedValue',
        );
        if ($scansElement) {
            $scansStr = trim($scansElement);

            $archivalDe->setScans($scansStr);
            $archivalEn->setScans($scansStr);
        }
    }


    /* Documents */
    private static function inflateDocuments(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $documentsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="16"]/Field[@FieldName="{OBJECTS.Inscribed}"]/FormattedValue',
        );
        if ($documentsElement) {
            $documentsStr = trim($documentsElement);

            $archivalDe->setDocuments($documentsStr);
            $archivalEn->setDocuments($documentsStr);
        }
    }


    /* Scan names */
    private static function inflateScanNames(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $scanNamesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="17"]/Field[@FieldName="{OBJECTS.Medium}"]/FormattedValue',
        );
        if ($scanNamesElement) {
            $scanNamesStr = trim($scanNamesElement);

            $scanNamesArr = array_map('trim', explode(self::$splitChar, $scanNamesStr));

            $archivalDe->setScanNames($scanNamesArr);
            $archivalEn->setScanNames($scanNamesArr);
        }
    }


    /* Period */
    private static function inflatePeriod(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $periodElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="18"]/Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
        );
        if ($periodElement) {
            $periodStr = trim($periodElement);

            $archivalDe->setPeriod($periodStr);
            $archivalEn->setPeriod($periodStr);
        }
    }


    /* Publications */
    private static function inflatePublications(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $publicationDetailElements = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="19"]/Subreport',
        );

        if (!$publicationDetailElements) {
            return;
        }

        foreach ($publicationDetailElements as $publicationDetailElement) {
            self::inflatePublication($publicationDetailElement, $archivalDe, $archivalEn);
        }
    }

    /* Publication */
    private static function inflatePublication(
        SimpleXMLElement $node,
        Archival $archivalDe,
        Archival $archivalEn
    ) {
        $publication = new Publication;
        $wasInflated = false;

        /* Title */
        $titleElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{REFERENCEMASTER.Heading}"]/FormattedValue',
        );

        if ($titleElement) {
            $publication->setTitle(trim($titleElement));
            $wasInflated = true;
        }

        /* PageNumber */
        $pageNumberElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PageNumber}"]/FormattedValue',
        );

        if ($pageNumberElement) {
            $publication->setPageNumber(trim($pageNumberElement));
            $wasInflated = true;
        }

        /* Reference ID */
        $referenceElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{REFERENCEMASTER.ReferenceID}"]/FormattedValue',
        );

        if ($referenceElement) {
            $publication->setReferenceId(trim($referenceElement));
            $wasInflated = true;
        }


        if ($wasInflated) {
            $archivalDe->addPublication($publication);
            $archivalEn->addPublication($publication);
        }
    }


    private static function registerXPathNamespace(SimpleXMLElement $node)
    {
        $node->registerXPathNamespace(self::$nsPrefix, self::$ns);
    }


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
