<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\GraphicInfoLanguageCollection;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
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

    private static $repositoryTypes = [
        Language::DE => 'Besitzer*in',
        Language::EN => 'Repository',
    ];


    private function __construct()
    {
    }

    /**
     * Inflates the passed graphic objects
     *
     * @param SimpleXMLElement $node Current graphics element node
     * @param GraphicInfoLanguageCollection $graphicInfoCollection Graphic collection
     *
     * @return void
     */
    public static function inflate(
        SimpleXMLElement $node,
        GraphicInfoLanguageCollection $graphicInfoCollection,
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateInventoryNumber($subNode, $graphicInfoCollection);
        self::inflateObjectMeta($subNode, $graphicInfoCollection);

        if ($graphicInfoCollection->getIsVirtual()) {
            self::inflateReprintReferences($subNode, $graphicInfoCollection);
        }

        self::inflateLocations($subNode, $graphicInfoCollection);

        if (!$graphicInfoCollection->getIsVirtual()) {
            self::inflateRepository($subNode, $graphicInfoCollection);
        }
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        GraphicInfoLanguageCollection $graphicInfoCollection,
    ): void {
        $inventoryNumberSectionElement = $node->{'Section'}[6];

        $inventoryNumberElement = self::findElementByXPath(
            $inventoryNumberSectionElement,
            'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
        );
        if ($inventoryNumberElement) {
            $inventoryNumberStr = trim(strval($inventoryNumberElement));

            /* Using single german value for all language objects in the collection */
            $graphicInfoCollection->setInventoryNumber($inventoryNumberStr);
        }
    }


    /* Object id & virtual (meta) */
    private static function inflateObjectMeta(
        SimpleXMLElement $node,
        GraphicInfoLanguageCollection $graphicInfoCollection,
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

            /* Using single german value for all language objects in the collection */
            $graphicInfoCollection->setIsVirtual($isVirtual);
        }
    }


    /* Reprint references */
    private static function inflateReprintReferences(
        SimpleXMLElement $node,
        GraphicInfoLanguageCollection $graphicInfoCollection,
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

        $graphicInfoCollection->setReprintReferences($filteredReprintReferences);
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
        GraphicInfoLanguageCollection $graphicInfoCollection,
    ): void {
        $locationDetailsElements = $node->{'Section'}[36]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($locationDetailsElements); $i += 1) {
            $locationDetailElement = $locationDetailsElements[$i];

            if ($locationDetailElement->count() === 0) {
                continue;
            }

            $metaReference = new MetaLocationReference;

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
                    $graphicInfoCollection->get(Language::DE)->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes[Language::EN] === $locationTypeStr) {
                    $graphicInfoCollection->get(Language::EN)->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes['not_assigned'] === $locationTypeStr) {
                    echo '  Unassigned location type for object ' . $graphicInfoCollection->getInventoryNumber() . "\n";
                    $graphicInfoCollection->addLocation($metaReference);
                } else {
                    echo '  Unknown location type: ' . $locationTypeStr . ' for object ' . $graphicInfoCollection->getInventoryNumber() . "\n";
                    $graphicInfoCollection->addLocation($metaReference);
                }
            } else {
                $graphicInfoCollection->addLocation($metaReference);
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


    /* Repository */
    private static function inflateRepository(
        SimpleXMLElement $node,
        GraphicInfoLanguageCollection $graphicInfoCollection,
    ): void {
        $repositoryAndOwnerDetailsSubreport = $node->{'Section'}[37]->{'Subreport'};
        $details = $repositoryAndOwnerDetailsSubreport->{'Details'};

        foreach ($details as $detail) {
            /* We have to extract the role */
            $roleElement = self::findElementByXPath(
                $detail,
                'Section[@SectionNumber="1"]/Field[@FieldName="{@Rolle}"]/FormattedValue',
            );

            if (!$roleElement) {
                continue;
            }

            $roleName = trim(strval($roleElement));

            /* Passing the roleName to the infaltors for themself to decide if they are
              responsible for further value extraction */
            try {
                self::inflateRepositorySub($detail, $roleName, $graphicInfoCollection, );
            } catch (Error $e) {
                echo '  ' . $e->getMessage() . "\n";
            }
        }
    }


    /* Repository - Section */
    private static function inflateRepositorySub(
        SimpleXMLElement $detail,
        string $roleName,
        GraphicInfoLanguageCollection $graphicInfoCollection,
    ): bool {
        $repositoryElement = self::findElementByXPath(
            $detail,
            'Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
        );

        if (!$repositoryElement) {
            throw new Error('Missing element with repository name!');
        }

        $repositoryStr = trim(strval($repositoryElement));

        switch ($roleName) {
            case self::$repositoryTypes[Language::DE]:
                /* de */
                $graphicInfoCollection->get(Language::DE)->setRepository($repositoryStr);
                break;

            case self::$repositoryTypes[Language::EN]:
                /* en */
                $graphicInfoCollection->get(Language::EN)->setRepository($repositoryStr);
                break;

            default:
                return false;
        }

        return true;
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
