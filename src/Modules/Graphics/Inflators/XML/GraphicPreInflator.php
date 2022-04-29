<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML;

use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfo;

use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;

/**
 * Graphics inflator used to inflate german and english graphic instances
 *    by traversing the xml element node and extracting the data in a structured way
 */
class GraphicPreInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';

    private static $locationLanguageTypes = [
        Language::DE => 'Standort Cranach Objekt',
        Language::EN => 'Location Cranach Object',
        'not_assigned' => '(not assigned)',
    ];

    private static $referenceTypeValues = [
        'reprint' => 'Abzug A',
    ];

    private function __construct()
    {
    }

    /**
     * Inflates the passed graphic objects
     *
     * @param SimpleXMLElement $node Current graphics element node
     * @param GraphicInfo $graphicInfoDe Graphic object holding the german informations
     * @param GraphicInfo $graphicInfoEn Graphic object holding the english informations
     *
     * @return void
     */
    public static function inflate(
        SimpleXMLElement $node,
        GraphicInfo $graphicInfoDe,
        GraphicInfo $graphicInfoEn
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateInventoryNumber($subNode, $graphicInfoDe, $graphicInfoEn);
        self::inflateObjectMeta($subNode, $graphicInfoDe, $graphicInfoEn);

        if ($graphicInfoDe->getIsVirtual()) {
            self::inflateReprintReferences($subNode, $graphicInfoDe, $graphicInfoEn);
        }

        self::inflateLocations($subNode, $graphicInfoDe, $graphicInfoEn);
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        GraphicInfo $graphicInfoDe,
        GraphicInfo $graphicInfoEn
    ): void {
        $inventoryNumberSectionElement = $node->{'Section'}[6];

        $inventoryNumberElement = self::findElementByXPath(
            $inventoryNumberSectionElement,
            'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim(strval($inventoryNumberElement));

            /* Using single german value for both language objects */
            $graphicInfoDe->setInventoryNumber($inventoryNumberStr);
            $graphicInfoEn->setInventoryNumber($inventoryNumberStr);
        }
    }


    /* Object id & virtual (meta) */
    private static function inflateObjectMeta(
        SimpleXMLElement $node,
        GraphicInfo $graphicInfoDe,
        GraphicInfo $graphicInfoEn
    ): void {
        $metaSectionElement = $node->{'Section'}[7];

        /* virtual*/
        $virtualElement = self::findElementByXPath(
            $metaSectionElement,
            'Field[@FieldName="{OBJECTS.IsVirtual}"]/FormattedValue',
        );
        if ($virtualElement) {
            $virtualStr = trim(strval($virtualElement));

            $isVirtual = ($virtualStr === '1');

            /* Using single german value for both language objects */
            $graphicInfoDe->setIsVirtual($isVirtual);
            $graphicInfoEn->setIsVirtual($isVirtual);
        }
    }


    /* Reprint references */
    private static function inflateReprintReferences(
        SimpleXMLElement $node,
        GraphicInfo $graphicInfoDe,
        GraphicInfo $graphicInfoEn
    ): void {
        /* Reprints References */
        $referenceReprintDetailsElements = $node->{'Section'}[31]->{'Subreport'}->{'Details'};

        $reprintReferences = self::getReferencesForDetailElements(
            $referenceReprintDetailsElements,
        );

        $filteredReprintReferences = array_values(
            array_filter($reprintReferences, function ($reference) {
                return $reference->getText() === self::$referenceTypeValues['reprint'];
            }),
        );

        $graphicInfoDe->setReprintReferences($filteredReprintReferences);
        $graphicInfoEn->setReprintReferences($filteredReprintReferences);
    }


    /* Reusable helper function for extration of reference like elements */
    private static function getReferencesForDetailElements(
        SimpleXMLElement $referenceDetailsElements
    ): array {
        $references = [];

        for ($i = 0; $i < count($referenceDetailsElements); $i += 1) {
            $referenceDetailElement = $referenceDetailsElements[$i];

            if ($referenceDetailElement->count() === 0) {
                continue;
            }

            $reference = new ObjectReference;

            /* Text */
            $textElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="0"]/Text[@Name="Text5"]/TextValue',
            );
            if ($textElement) {
                $textStr = trim(strval($textElement));
                $reference->setText($textStr);

                $kind = ObjectReference::getKindFromText($textStr);

                if ($kind !== false) {
                    $reference->setKind($kind);
                } else {
                    echo 'PaintingInflator: Unknown text for kind determination "' . $textStr . '"';
                }
            }

            /* Inventory number */
            $inventoryNumberElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
            );
            if ($inventoryNumberElement) {
                $inventoryNumberStr = trim(strval($inventoryNumberElement));
                $reference->setInventoryNumber($inventoryNumberStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{ASSOCIATIONS.Remarks}"]/FormattedValue',
            );
            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $reference->addRemark($remarksStr);
            }

            $references[] = $reference;
        }

        return $references;
    }


    /* Locations */
    private static function inflateLocations(
        SimpleXMLElement $node,
        GraphicInfo $graphicInfoDe,
        GraphicInfo $graphicInfoEn
    ): void {
        $locationDetailsElements = $node->{'Section'}[36]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($locationDetailsElements); $i += 1) {
            $locationDetailElement = $locationDetailsElements[$i];

            if ($locationDetailElement->count() === 0) {
                continue;
            }

            $metaReference = new MetaReference;

            /* Type */
            $locationTypeElement = self::findElementByXPath(
                $locationDetailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{THESXREFTYPES.ThesXrefType}"]/FormattedValue',
            );

            /* Language determination */
            if ($locationTypeElement) {
                $locationTypeStr = trim(strval($locationTypeElement));
                $metaReference->setType($locationTypeStr);

                if (self::$locationLanguageTypes[Language::DE] === $locationTypeStr) {
                    $graphicInfoDe->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes[Language::EN] === $locationTypeStr) {
                    $graphicInfoEn->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes['not_assigned'] === $locationTypeStr) {
                    echo '  Unassigned location type for object ' . $graphicInfoDe->getInventoryNumber() . "\n";
                    $graphicInfoDe->addLocation($metaReference);
                    $graphicInfoEn->addLocation($metaReference);
                } else {
                    echo '  Unknown location type: ' . $locationTypeStr . ' for object ' . $graphicInfoDe->getInventoryNumber() . "\n";
                    $graphicInfoDe->addLocation($metaReference);
                    $graphicInfoEn->addLocation($metaReference);
                }
            } else {
                $graphicInfoDe->addLocation($metaReference);
                $graphicInfoEn->addLocation($metaReference);
            }

            /* Term */
            $locationTermElement = self::findElementByXPath(
                $locationDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{TERMS.Term}"]/FormattedValue',
            );
            if ($locationTermElement) {
                $locationTermStr = trim(strval($locationTermElement));
                $metaReference->setTerm($locationTermStr);
            }

            /* Path */
            $locationPathElement = self::findElementByXPath(
                $locationDetailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH2.Path}"]/FormattedValue',
            );
            if ($locationPathElement) {
                $locationPathStr = trim(strval($locationPathElement));
                $metaReference->setPath($locationPathStr);
            }

            /* URL */
            $locationURLElement = self::findElementByXPath(
                $locationDetailElement,
                'Section[@SectionNumber="4"]/Field[@FieldName="{@URL TGN}"]/FormattedValue',
            );
            if ($locationURLElement) {
                $locationURLStr = trim(strval($locationURLElement));
                $metaReference->setURL($locationURLStr);
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
