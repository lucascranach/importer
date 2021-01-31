<?php

namespace CranachDigitalArchive\Importer\Modules\Restorations\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Survey;
use CranachDigitalArchive\Importer\Modules\Restorations\Entities\Test;

/**
 * Graphics Restoration inflator used to inflate restoration instances
 *    by traversing the xml element node and extracting the data in a structured way
 */
class GraphicsRestorationInflator extends RestorationInflator
{
    private static $keywordSplitChar = '#';
    private static $keywordBasedSplitChar = ',';

    private function __construct()
    {
    }


    /* Survey Tests */
    /**
     * @return void
     */
    protected static function inflateSurveyTests(
        SimpleXMLElement $node,
        Survey $restorationSurvey
    ) {
        $testNodes = static::getSurveyTestNodes($node);

        if (is_null($testNodes)) {
            return;
        }

        foreach ($testNodes as $testNode) {
            $surveyTest = new Test;

            /* Type */
            static::inflateSurveyTestType($testNode, $surveyTest);

            /* Text */
            static::inflateSurveyTestText($testNode, $surveyTest);

            /* Purpose */
            static::inflateSurveyTestPurpose($testNode, $surveyTest);

            /* Keywords -> Graphics-specific */
            static::inflateSurveyTestKeywords($testNode, $surveyTest);

            $restorationSurvey->addTest($surveyTest);
        }
    }


    protected static function inflateSurveyTestKeywords(
        SimpleXMLElement $node,
        Test $surveyTest
    ) {
        /* Keywords are encoded in the remarks-field of a test */
        $testKeywordsElement = static::findElementByXPath(
            $node,
            'Section[@SectionNumber="3"]/Field[@FieldName="{TEXTENTRIES.Remarks}"]/FormattedValue',
        );
        if (!$testKeywordsElement) {
            return;
        }

        $testKeywordsStr = trim(strval($testKeywordsElement));

        $splitKeywords = explode(static::$keywordSplitChar, $testKeywordsStr);
        $trimmedKeywords = array_map('trim', $splitKeywords);

        $keywords = array_map(function ($keyword) {
            $splitKeyword = explode(static::$keywordBasedSplitChar, $keyword);
            return $splitKeyword[0];
        }, $trimmedKeywords);

        $uniqueKeywords = array_unique($keywords);

        foreach ($uniqueKeywords as $keyword) {
            $surveyTest->addKeyword($keyword);
        }
    }
}
