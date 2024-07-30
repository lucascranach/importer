<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingInfo;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingInfoLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

/**
 * Drawingss inflator used to inflate german and english drawing instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class DrawingPreInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';

    private static $inventoryNumberReplaceRegExpArr = [
        '/^CDA\./',
    ];

    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        DrawingInfoLanguageCollection $drawingInfoCollection,
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateInventoryNumber($subNode, $drawingInfoCollection);
        self::inflateReferences($subNode, $drawingInfoCollection);
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        DrawingInfoLanguageCollection $drawingInfoCollection,
    ): void {
        $inventoryNumberSectionElement = $node->{'Section'}[6];

        $inventoryNumberElement = self::findElementByXPath(
            $inventoryNumberSectionElement,
            'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim(strval($inventoryNumberElement));
            $cleanInventoryNumberStr = preg_replace(
                self::$inventoryNumberReplaceRegExpArr,
                '',
                $inventoryNumberStr,
            );

            /* Using single german value for all items in the collection */
            $drawingInfoCollection->setInventoryNumber($cleanInventoryNumberStr);
        }
    }


    /* References */
    private static function inflateReferences(
        SimpleXMLElement $node,
        DrawingInfoLanguageCollection $drawingInfoCollection,
    ): void {
        $referenceDetailsElements = $node->{'Section'}[31]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($referenceDetailsElements); $i += 1) {
            $referenceDetailElement = $referenceDetailsElements[$i];

            if ($referenceDetailElement->count() === 0) {
                continue;
            }

            $referenceDe = new ObjectReference;
            $referenceEn = new ObjectReference;

            $drawingInfoCollection->get(Language::DE)->addReference($referenceDe);
            $drawingInfoCollection->get(Language::EN)->addReference($referenceEn);

            /* Text */
            $textElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="0"]/Text[@Name="Text5"]/TextValue',
            );
            if ($textElement) {
                $textStr = trim(strval($textElement));
                $referenceDe->setText($textStr);
                $referenceEn->setText($textStr);

                $kind = ObjectReference::getKindFromText($textStr);

                if ($kind !== false) {
                    $referenceDe->setKind($kind);
                    $referenceEn->setKind($kind);
                } else {
                    echo 'DrawingInflator: Unknown text for kind determination "' . $textStr . '"\n';
                }
            }

            /* Inventory number */
            $inventoryNumberElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
            );
            if ($inventoryNumberElement) {
                $inventoryNumberStr = trim(strval($inventoryNumberElement));
                $referenceDe->setInventoryNumber($inventoryNumberStr);
                $referenceEn->setInventoryNumber($inventoryNumberStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{ASSOCIATIONS.Remarks}"]/FormattedValue',
            );
            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));

                self::inflateReferenceRemarks(
                    $drawingInfoCollection->get(Language::DE)->getInventoryNumber(),
                    $remarksStr,
                    $referenceDe,
                    $referenceEn,
                );
            }
        }
    }


    private static function inflateReferenceRemarks(
        string $inventoryNumber,
        string $value,
        ObjectReference $referenceDe,
        ObjectReference $referenceEn
    ): void {
        $splitValues = array_values(array_filter(array_map('trim', explode('#', $value))));

        for ($i = 0; $i < count($splitValues); $i++) {
            $currVal = $splitValues[$i];
            $nextVal = isset($splitValues[$i + 1]) ? $splitValues[$i + 1] : false;
            $nextNextVal = isset($splitValues[$i + 2]) ? $splitValues[$i + 2] : false;

            if (is_numeric($currVal)) {
                if ($currVal === '00') {
                    if ($nextVal) {
                        $referenceDe->addRemark($nextVal);
                    }

                    if ($nextNextVal) {
                        $referenceEn->addRemark($nextNextVal);
                        $i = $i + 2;
                    } elseif ($nextVal) {
                        $referenceEn->addRemark($nextVal);
                        $i++;
                    }
                } else {
                    $remark = ObjectReference::getRemarkMappingForLangAndCode(Language::DE, $currVal);

                    if ($remark === false) {
                        echo 'DrawingInflator: Unknown reference remark code "' . $currVal . '" for "' . $inventoryNumber . '"' . "\n";
                        $remark = $currVal;
                    }
                    $referenceDe->addRemark($remark);

                    $remark = ObjectReference::getRemarkMappingForLangAndCode(Language::EN, $currVal);

                    if ($remark === false) {
                        echo 'DrawingInflator: Unknown reference remark code "' . $currVal . '" for "' . $inventoryNumber . '"' . "\n";
                        $remark = $currVal;
                    }
                    $referenceEn->addRemark($remark);
                }
            } else {
                $referenceDe->addRemark($currVal);

                if ($nextVal) {
                    $referenceEn->addRemark($nextVal);
                    $i += 1;
                } else {
                    $referenceEn->addRemark($currVal);
                }
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
