<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Survey;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Test;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\ProcessingDates;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Signature;

/**
 * Restoration inflator used to inflate restoration instances
 *    by traversing the xml element node and extracting the data in a structured way
 */
class RestorationInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';

    private static $inventoryNumberReplaceRegExpArr = [
        '/^CDA\./',
        '/^G_/',
    ];

    private static $surveyTypesLanguageTypes = [
        'Material/Technik' => Language::DE,
        'Zustandsprotokoll' => Language::DE,
        'Restaurierungsdokumentation' => Language::DE,

        'Material/Technique' => Language::EN,
        'Condition Report' => Language::EN,
        'Conservation Report' => Language::EN,
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ) {
        $headerNode = $node->GroupHeader;
        $detailsNodes = $node->Details;

        self::registerXPathNamespace($headerNode);
        self::registerXPathNamespace($detailsNodes);

        self::inflateInventoryNumber($headerNode, $restorationDe, $restorationEn);
        self::inflateObjectId($headerNode, $restorationDe, $restorationEn);

        self::inflateSurveys($detailsNodes, $restorationDe, $restorationEn);
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ) {
        $inventoryNumberSectionElement = $node->Section[0];

        $inventoryNumberElement = self::findElementByXPath(
            $inventoryNumberSectionElement,
            'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim($inventoryNumberElement);

            $cleanInventoryNumberStr = preg_replace(
                self::$inventoryNumberReplaceRegExpArr,
                '',
                $inventoryNumberStr,
            );

            $restorationDe->setInventoryNumber($cleanInventoryNumberStr);
        }
    }


    /* Object Id */
    private static function inflateObjectId(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ) {
        $objectIdSectionElement = $node->Section[1];

        $objectIdElement = self::findElementByXPath(
            $objectIdSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectID}"]/FormattedValue',
        );
        if ($objectIdElement) {
            $objectIdNumberInt = intval(trim($objectIdElement));

            $restorationDe->setObjectId($objectIdNumberInt);
        }
    }


    /* Surveys */
    private static function inflateSurveys(
        SimpleXMLElement $nodes,
        Restoration $restorationDe,
        Restoration $restorationEn
    ) {
        foreach ($nodes as $node) {
            self::inflateSurvey($node, $restorationDe, $restorationEn);
        }
    }


    /* Survey */
    private static function inflateSurvey(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ) {
        $survey = new Survey();

        /* Type */
        $typeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{SURVEYTYPES.SurveyType}"]/FormattedValue',
        );
        if ($typeElement) {
            $typeStr = trim($typeElement);

            $survey->setType($typeStr);
        }

        /* Project */
        $projectElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{CONDITIONS.Project}"]/FormattedValue',
        );
        if ($projectElement) {
            $projectStr = trim($projectElement);

            $survey->setProject($projectStr);
        }

        /* OverallAnalysis */
        $overallAnalysisElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{CONDITIONS.OverallAnalysis}"]/FormattedValue',
        );
        if ($overallAnalysisElement) {
            $overallAnalysisStr = trim($overallAnalysisElement);

            $survey->setOverallAnalysis($overallAnalysisStr);
        }

        /* Remarks */
        $remarksElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{CONDITIONS.Remarks}"]/FormattedValue',
        );
        if ($remarksElement) {
            $remarksStr = trim($remarksElement);

            $survey->setRemarks($remarksStr);
        }

        /* Skipping unknown fourth section element */

        /* Tests */
        $testsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Subreport',
        );
        if ($testsElement) {
            self::inflateSurveyTests($testsElement, $survey);
        }

        /* Persons */
        $personsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Subreport',
        );
        if ($personsElement) {
            self::inflateSurveyPersons($personsElement, $survey);
        }

        /* ProcessingDates */
        self::inflateSurveyProcessingDates($node, $survey);

        /* Signature */
        self::inflateSurveySignature($node, $survey);

        $surveyType = $survey->getType();

        $lang = isset(self::$surveyTypesLanguageTypes[$surveyType])
            ? self::$surveyTypesLanguageTypes[$surveyType]
            : 'unknown' ;

        switch ($lang) {
            case Language::DE:
                $restorationDe->addSurvey($survey);
                break;

            case Language::EN:
                $restorationEn->addSurvey($survey);
                break;

            default:
                $restorationDe->addSurvey($survey);
                $restorationEn->addSurvey($survey);
        }
    }


    /* Survey Tests */
    private static function inflateSurveyTests(
        SimpleXMLElement $node,
        Survey $restorationSurvey
    ) {
        $testNodes = $node->Details;

        if (is_null($testNodes) || $testNodes->children()->count() === 0) {
            return;
        }

        foreach ($testNodes as $testNode) {
            $surveyTest = new Test;

            /* Type */
            $testKindElement = self::findElementByXPath(
                $testNode,
                'Section[@SectionNumber="0"]/Field[@FieldName="{@testart}"]/FormattedValue',
            );
            if ($testKindElement) {
                $testKindStr = trim($testKindElement);

                $surveyTest->setKind($testKindStr);
            }

            /* Text */
            $testTextElement = self::findElementByXPath(
                $testNode,
                'Section[@SectionNumber="1"]/Field[@FieldName="{TEXTENTRIES.TextEntry}"]/FormattedValue',
            );
            if ($testTextElement) {
                $testTextStr = trim($testTextElement);

                $surveyTest->setText($testTextStr);
            }

            /* Purpose */
            $testPurposeElement = self::findElementByXPath(
                $testNode,
                'Section[@SectionNumber="2"]/Field[@FieldName="{TEXTENTRIES.Purpose}"]/FormattedValue',
            );
            if ($testPurposeElement) {
                $testPurposeStr = trim($testPurposeElement);

                $surveyTest->setPurpose($testPurposeStr);
            }

            /* Remarks */
            $testRemarksElement = self::findElementByXPath(
                $testNode,
                'Section[@SectionNumber="3"]/Field[@FieldName="{TEXTENTRIES.Remarks}"]/FormattedValue',
            );
            if ($testRemarksElement) {
                $testRemarksStr = trim($testRemarksElement);

                $surveyTest->setRemarks($testRemarksStr);
            }

            $restorationSurvey->addTest($surveyTest);
        }
    }


    /* Survey Persons */
    private static function inflateSurveyPersons(
        SimpleXMLElement $node,
        Survey $restorationSurvey
    ) {
        $personNodes = $node->Details;

        if (is_null($personNodes) || $personNodes->children()->count() === 0) {
            return;
        }

        foreach ($personNodes as $personNode) {
            $surveyPerson = new Person;

            /* Role */
            $personRoleElement = self::findElementByXPath(
                $personNode,
                'Section[@SectionNumber="0"]/Field[@FieldName="{ROLES.Role}"]/FormattedValue',
            );
            if ($personRoleElement) {
                $personRoleStr = trim($personRoleElement);

                $surveyPerson->setRole($personRoleStr);
            }

            /* Name */
            $personNameElement = self::findElementByXPath(
                $personNode,
                'Section[@SectionNumber="1"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
            );
            if ($personNameElement) {
                $personNameStr = trim($personNameElement);

                $surveyPerson->setName($personNameStr);
            }

            $restorationSurvey->addInvolvedPerson($surveyPerson);
        }
    }


    /* Survey ProcessingDates */
    private static function inflateSurveyProcessingDates(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $processingDates = new ProcessingDates;

        /* BeginDate */
        $beginDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{@BearbeitungsdatumTrue}"]/Value',
        );
        if ($beginDateElement) {
            $beginDateStr = trim($beginDateElement);

            $processingDates->setBeginDate($beginDateStr);
        }

        /* BeginYear */
        $beginYearElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Field[@FieldName="{@BearbeitungsdatumFalse}"]/Value',
        );
        if ($beginYearElement) {
            $beginYearInt = intval(trim($beginYearElement));

            $processingDates->setBeginYear($beginYearInt);
        }

        /* EndDate */
        $endDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{@BearbeitungsdatEndTrue}"]/Value',
        );
        if ($endDateElement) {
            $endDateStr = trim($endDateElement);

            $processingDates->setEndDate($endDateStr);
        }

        /* EndYear */
        $endYearElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{@BearbeitungsdatEndFalse}"]/Value',
        );
        if ($endYearElement) {
            $endYearInt = intval(trim($endYearElement));

            $processingDates->setEndYear($endYearInt);
        }

        /* Decide if we should use the processingDates instance */
        $hasValidBeginData = !empty($processingDates->getBeginDate())
            || !is_null($processingDates->getBeginYear());
        $hasValidEndData = !empty($processingDates->getEndDate())
            || !is_null($processingDates->getEndYear());

        if ($hasValidBeginData || $hasValidEndData) {
            $survey->setProcessingDates($processingDates);
        }
    }


    /* Survey Signature */
    private static function inflateSurveySignature(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $signature = new Signature;

        /* Signature Date */
        $signatureDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Text[@Name="Text31"]/TextValue',
        );
        if ($signatureDateElement) {
            $signatureDateStr = trim($signatureDateElement);

            $signature->setDate($signatureDateStr);
        }

        /* Signature Name */
        $signatureNameElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@Name]/FormattedValue',
        );
        if ($signatureNameElement) {
            $signatureNameStr = trim($signatureNameElement);

            $signature->setName($signatureNameStr);
        }

        if (!empty($signature->getDate()) && !empty($signature->getName())) {
            $survey->setSignature($signature);
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
}
