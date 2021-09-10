<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ImageFileReference;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Restoration;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Survey;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Test;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\ProcessingDates;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Signature;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Keyword;

/**
 * Restoration inflator used to inflate restoration instances
 *    by traversing the xml element node and extracting the data in a structured way
 */
class RestorationInflator implements IInflator
{
    private const ART_TECH_EXAMINATION = 'ArtTechExamination';
    private const CONDITION_REPORT = 'ConditionReport';
    private const CONSERVATION_REPORT = 'ConservationReport';
    private const UNCATEGORIZED_SURVEY = 'UncategorizedSurvey';

    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';
    private static $splitChar = '#';
    private static $keywordBasedSplitChar = ',';
    private static $kindOrderRegExp = '/(\d{1,}\.\d{1,})(\s|$)/';

    private static $surveyTypesLanguageTypes = [
        'Material/Technik' => Language::DE,
        'Zustandsprotokoll' => Language::DE,
        'Restaurierungsdokumentation' => Language::DE,

        'Material/Technique' => Language::EN,
        'Condition Report' => Language::EN,
        'Conservation Report' => Language::EN,
    ];

    private static $surveyTypesCategoryTypes = [
        'Material/Technik' => self::ART_TECH_EXAMINATION,
        'Zustandsprotokoll' => self::CONDITION_REPORT,
        'Restaurierungsdokumentation' => self::CONSERVATION_REPORT,

        'Material/Technique' => self::ART_TECH_EXAMINATION,
        'Condition Report' => self::CONDITION_REPORT,
        'Conservation Report' => self::CONSERVATION_REPORT,
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ): void {
        $headerNode = $node->{'GroupHeader'};
        $detailsNodes = $node->{'Details'};

        self::registerXPathNamespace($headerNode);
        self::registerXPathNamespace($detailsNodes);

        self::inflateInventoryNumber($headerNode, $restorationDe, $restorationEn);
        self::inflateObjectId($headerNode, $restorationDe, $restorationEn);

        self::inflateSurveys($detailsNodes, $restorationDe, $restorationEn);
    }


    /* Inventory number */
    protected static function inflateInventoryNumber(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ): void {
        $inventoryNumberSectionElement = $node->{'Section'}[0];

        $inventoryNumberElement = self::findElementByXPath(
            $inventoryNumberSectionElement,
            'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim(strval($inventoryNumberElement));

            $restorationDe->setInventoryNumber($inventoryNumberStr);
            $restorationEn->setInventoryNumber($inventoryNumberStr);
        }
    }


    /* Object Id */
    protected static function inflateObjectId(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ): void {
        $objectIdSectionElement = $node->{'Section'}[1];

        $objectIdElement = self::findElementByXPath(
            $objectIdSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectID}"]/FormattedValue',
        );
        if ($objectIdElement) {
            $objectIdNumberInt = intval(trim(strval($objectIdElement)));

            $restorationDe->setObjectId($objectIdNumberInt);
            $restorationEn->setObjectId($objectIdNumberInt);
        }
    }


    /* Surveys */
    protected static function inflateSurveys(
        SimpleXMLElement $nodes,
        Restoration $restorationDe,
        Restoration $restorationEn
    ): void {
        foreach ($nodes as $node) {
            self::inflateSurvey($node, $restorationDe, $restorationEn);
        }
    }


    /* Survey */
    protected static function inflateSurvey(
        SimpleXMLElement $node,
        Restoration $restorationDe,
        Restoration $restorationEn
    ): void {
        $survey = new Survey();

        /* Type */
        self::inflateSurveyType($node, $survey);

        /* Project */
        self::inflateSurveyProject($node, $survey);

        /* OverallAnalysis */
        self::inflateSurveyOverallAnalysis($node, $survey);

        /* Remarks */
        self::inflateSurveyRemarks($node, $survey);

        /* Skipping unknown fourth section element */

        /* Tests */
        self::inflateSurveyTests($node, $survey);

        /* Persons */
        self::inflateSurveyPersons($node, $survey);

        /* ProcessingDates */
        self::inflateSurveyProcessingDates($node, $survey);

        /* Signature */
        self::inflateSurveySignature($node, $survey);

        /* Filenames */
        self::inflateSurveyFilenames($node, $survey);

        $surveyType = $survey->getType();

        /* Determining the language of the survey
            to assign it to the correct restoration */
        $lang = isset(self::$surveyTypesLanguageTypes[$surveyType])
            ? self::$surveyTypesLanguageTypes[$surveyType]
            : 'unknown';

        $selectedRestorations = [];

        switch ($lang) {
            case Language::DE:
                $selectedRestorations[] = $restorationDe;
                break;

            case Language::EN:
                $selectedRestorations[] = $restorationEn;
                break;

            default:
                $selectedRestorations[] = $restorationDe;
                $selectedRestorations[] = $restorationEn;
        }

        $surveyCategory = isset(self::$surveyTypesCategoryTypes[$surveyType])
            ? self::$surveyTypesCategoryTypes[$surveyType]
            : self::UNCATEGORIZED_SURVEY;

        /* Overwriting the type with a language-independend value */
        $survey->setType($surveyCategory);

        foreach ($selectedRestorations as $selectedRestoration) {
            $selectedRestoration->addSurvey($survey);
        }
    }


    protected static function inflateSurveyType(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $typeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{SURVEYTYPES.SurveyType}"]/FormattedValue',
        );
        if ($typeElement) {
            $typeStr = trim(strval($typeElement));

            $survey->setType($typeStr);
        }
    }


    protected static function inflateSurveyProject(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $projectElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{CONDITIONS.Project}"]/FormattedValue',
        );
        if ($projectElement) {
            $projectStr = trim(strval($projectElement));

            $survey->setProject($projectStr);
        }
    }


    protected static function inflateSurveyOverallAnalysis(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $overallAnalysisElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{CONDITIONS.OverallAnalysis}"]/FormattedValue',
        );
        if ($overallAnalysisElement) {
            $overallAnalysisStr = trim(strval($overallAnalysisElement));

            $survey->setOverallAnalysis($overallAnalysisStr);
        }
    }


    protected static function inflateSurveyRemarks(
        SimpleXMLElement $node,
        Survey $survey
    ) {
        $remarksElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{CONDITIONS.Remarks}"]/FormattedValue',
        );
        if ($remarksElement) {
            $remarksStr = trim(strval($remarksElement));

            $survey->setRemarks($remarksStr);
        }
    }


    protected static function getSurveyTestNodes(
        SimpleXMLElement $node
    ): ?SimpleXMLElement {
        $testsSubreportNode = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="5"]/Subreport',
        );

        if (!$testsSubreportNode) {
            return null;
        }

        $testNodes = $testsSubreportNode->{'Details'};

        if (is_null($testNodes) || $testNodes->children()->count() === 0) {
            return null;
        }

        return $testNodes;
    }

    /* Survey Tests */
    /**
     * @return void
     */
    protected static function inflateSurveyTests(
        SimpleXMLElement $node,
        Survey $restorationSurvey
    ) {
        $testNodes = self::getSurveyTestNodes($node);

        if (is_null($testNodes)) {
            return;
        }

        foreach ($testNodes as $testNode) {
            $surveyTest = new Test;

            /* Type */
            self::inflateSurveyTestType($testNode, $surveyTest);

            /* Text */
            self::inflateSurveyTestText($testNode, $surveyTest);

            /* Purpose */
            self::inflateSurveyTestPurpose($testNode, $surveyTest);

            /* Keywords */
            self::inflateSurveyTestKeywords($testNode, $surveyTest);

            $restorationSurvey->addTest($surveyTest);
        }
    }


    protected static function inflateSurveyTestType(
        SimpleXMLElement $node,
        Test $surveyTest
    ) {
        $testKindElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="0"]/Field[@FieldName="{TEXTTYPES.TextType}"]/FormattedValue',
        );
        if ($testKindElement) {
            $testKindStr = trim(strval($testKindElement));

            $order = 0;

            $matches = [];
            if (preg_match(self::$kindOrderRegExp, $testKindStr, $matches)) {
                $order = intval(floatval($matches[1]) * 100);
            }

            $testKindStr = preg_replace(self::$kindOrderRegExp, '', $testKindStr);

            $surveyTest->setOrder($order);
            $surveyTest->setKind($testKindStr);
        }
    }


    protected static function inflateSurveyTestText(
        SimpleXMLElement $node,
        Test $surveyTest
    ) {
        $testTextElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="1"]/Field[@FieldName="{TEXTENTRIES.TextEntry}"]/FormattedValue',
        );
        if ($testTextElement) {
            $testTextStr = trim(strval($testTextElement));

            $surveyTest->setText($testTextStr);
        }
    }


    protected static function inflateSurveyTestPurpose(
        SimpleXMLElement $node,
        Test $surveyTest
    ) {
        $testPurposeElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="2"]/Field[@FieldName="{TEXTENTRIES.Purpose}"]/FormattedValue',
        );
        if ($testPurposeElement) {
            $testPurposeStr = trim(strval($testPurposeElement));

            $surveyTest->setPurpose($testPurposeStr);
        }
    }


    protected static function inflateSurveyTestKeywords(
        SimpleXMLElement $node,
        Test $surveyTest
    ) {
        /* Keywords are encoded in the remarks-field of a test */
        $testKeywordsElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{TEXTENTRIES.Remarks}"]/FormattedValue',
        );
        if (!$testKeywordsElement) {
            return;
        }

        $testKeywordsStr = trim(strval($testKeywordsElement));

        $splitKeywords = explode(self::$splitChar, $testKeywordsStr);
        $trimmedKeywords = array_map('trim', $splitKeywords);
        $cleanedKeywords = array_filter($trimmedKeywords);

        foreach ($cleanedKeywords as $cleanedKeyword) {
            $splitKeyword = explode(self::$keywordBasedSplitChar, $cleanedKeyword);

            $keyword = new Keyword();
            $keyword->setName($splitKeyword[0]);

            if (isset($splitKeyword[1])) {
                $keyword->setAdditional($splitKeyword[1]);
            }

            $surveyTest->addKeyword($keyword);
        }
    }


    /* Survey Persons */
    /**
     * @return void
     */
    protected static function inflateSurveyPersons(
        SimpleXMLElement $node,
        Survey $restorationSurvey
    ) {
        $personsSubreportNode = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="6"]/Subreport',
        );
        if (!$personsSubreportNode) {
            return;
        }

        $personNodes = $personsSubreportNode->{'Details'};

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
                $personRoleStr = trim(strval($personRoleElement));

                $surveyPerson->setRole($personRoleStr);
            }

            /* Name */
            $personNameElement = self::findElementByXPath(
                $personNode,
                'Section[@SectionNumber="1"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
            );
            if ($personNameElement) {
                $personNameStr = trim(strval($personNameElement));

                $surveyPerson->setName($personNameStr);
            }

            $restorationSurvey->addInvolvedPerson($surveyPerson);
        }
    }


    /* Survey ProcessingDates */
    protected static function inflateSurveyProcessingDates(
        SimpleXMLElement $node,
        Survey $survey
    ): void {
        $processingDates = new ProcessingDates;

        /* BeginDate */
        $beginDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="7"]/Field[@FieldName="{@BearbeitungsdatumTrue}"]/Value',
        );
        if ($beginDateElement) {
            $beginDateStr = trim(strval($beginDateElement));

            $processingDates->setBeginDate($beginDateStr);
        }

        /* BeginYear */
        $beginYearElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="8"]/Field[@FieldName="{@BearbeitungsdatumFalse}"]/Value',
        );
        if ($beginYearElement) {
            $beginYearInt = intval(trim(strval($beginYearElement)));

            $processingDates->setBeginYear($beginYearInt);
        }

        /* EndDate */
        $endDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="9"]/Field[@FieldName="{@BearbeitungsdatEndTrue}"]/Value',
        );
        if ($endDateElement) {
            $endDateStr = trim(strval($endDateElement));

            $processingDates->setEndDate($endDateStr);
        }

        /* EndYear */
        $endYearElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="10"]/Field[@FieldName="{@BearbeitungsdatEndFalse}"]/Value',
        );
        if ($endYearElement) {
            $endYearInt = intval(trim(strval($endYearElement)));

            $processingDates->setEndYear($endYearInt);
        }


        /* Overwrite Begin- and End-Date, because of newly introduced date section-elements (2020-09-11) */

        /* New BeginYear */
        $beginDateNewElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="11"]/Field[@FieldName="{@BearbeitungsdatumNeu}"]/Value',
        );
        if ($beginDateNewElement) {
            $beginDateNew = trim(strval($beginDateNewElement));

            $processingDates->setBeginDate($beginDateNew);
        }

        /* New EndYear */
        $endDateNewElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="12"]/Field[@FieldName="{@BearbeitungsdatEndNeu}"]/Value',
        );
        if ($endDateNewElement) {
            $endDateNew = trim(strval($endDateNewElement));

            $processingDates->setEndDate($endDateNew);
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
    protected static function inflateSurveySignature(
        SimpleXMLElement $node,
        Survey $survey
    ): void {
        $signature = new Signature;

        /* Signature Date */
        $signatureDateElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="13"]/Text[@Name="Text31"]/TextValue',
        );
        if ($signatureDateElement) {
            $signatureDateStr = trim(strval($signatureDateElement));

            $signature->setDate($signatureDateStr);
        }

        /* Signature Name */
        $signatureNameElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="13"]/Field[@Name]/FormattedValue',
        );
        if ($signatureNameElement) {
            $signatureNameStr = trim(strval($signatureNameElement));

            $signature->setName($signatureNameStr);
        }

        if (!empty($signature->getDate()) && !empty($signature->getName())) {
            $survey->setSignature($signature);
        }
    }


    protected static function inflateSurveyFilenames(
        SimpleXMLElement $node,
        Survey $survey
    ): void {
        $filenamesElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="14"]'
            . '/Subreport[@Name="Subreport3"]'
            . '/Details[@Level="1"]'
            . '/Section[@SectionNumber="1"]'
            . '/Field[@FieldName="{TEXTENTRIES.TextEntry}"]'
            . '/Value',
        );

        if ($filenamesElement) {
            $filenamesStr = strval($filenamesElement);

            $filenames = explode(self::$splitChar, $filenamesStr);
            $trimmedFilenames = array_map('trim', $filenames);

            foreach ($trimmedFilenames as $trimmedFilename) {
                $basename = basename($trimmedFilename);

                $type = self::determineFileType($trimmedFilename);

                $id = $basename;

                $dotPos = strrpos($id, '.');
                if ($dotPos !== false) {
                    $id = substr($id, 0, $dotPos);
                }

                $fileRef = new ImageFileReference();
                $fileRef->setType($type);
                $fileRef->setId($id);

                $survey->addFileReference($fileRef);
            }
        }
    }


    protected static function determineFileType($filepath): string
    {
        $dirname = explode('/', $filepath)[0];
        $imageType = strtolower(preg_replace('/\d+_/', '', $dirname));
        return str_replace(['-'], '_', $imageType);
    }


    protected static function registerXPathNamespace(SimpleXMLElement $node): void
    {
        $node->registerXPathNamespace(self::$nsPrefix, self::$ns);
    }


    /**
     * @return SimpleXMLElement[]|false
     *
     * @psalm-return array<array-key, SimpleXMLElement>|false
     */
    protected static function findElementsByXPath(SimpleXMLElement $node, string $path)
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
    protected static function findElementByXPath(SimpleXMLElement $node, string $path)
    {
        $result = self::findElementsByXPath($node, $path);

        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return false;
    }
}
