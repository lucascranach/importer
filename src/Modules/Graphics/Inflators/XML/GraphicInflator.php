<?php

namespace CranachDigitalArchive\Importer\Modules\Graphics\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Graphic;
use CranachDigitalArchive\Importer\Modules\Graphics\Entities\Classification;

use CranachDigitalArchive\Importer\Modules\Main\Entities\Person;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonName;
use CranachDigitalArchive\Importer\Modules\Main\Entities\PersonNameDetail;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Title;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Dating;
use CranachDigitalArchive\Importer\Modules\Main\Entities\HistoricEventInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\ObjectReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\AdditionalTextInformation;
use CranachDigitalArchive\Importer\Modules\Main\Entities\Publication;
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;

/**
 * Graphics inflator used to inflate german and english graphic instances
 *    by traversing the xml element node and extracting the data in a structured way
 */
class GraphicInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';
    private static $langSplitChar = '#';

    private static $additionalTextLanguageTypes = [
        Language::DE => 'Beschreibung/ Interpretation/ Kommentare',
        Language::EN => 'Description/ Interpretation/ Comments',
        'not_assigned' => '(not assigned)',
    ];

    private static $locationLanguageTypes = [
        Language::DE => 'Standort Cranach Objekt',
        Language::EN => 'Location Cranach Object',
        'not_assigned' => '(not assigned)',
    ];

    private static $titlesLanguageTypes = [
        Language::DE => 'GERMAN',
        Language::EN => 'ENGLISH',
        'not_assigned' => '(not assigned)',
    ];

    private static $repositoryTypes = [
        Language::DE => 'Besitzer',
        Language::EN => 'Repository',
    ];

    private static $ownerTypes = [
        Language::DE => 'Eigentümer',
        Language::EN => 'Owner',
    ];

    private static $referenceTypeValues = [
        'reprint' => 'Abzug A',
        'relatedWork' => 'Teil eines Werkes',
    ];

    private static $translations = [
        /* classification */
        'Druckgrafik' => [
            Language::DE => 'Druckgrafik',
            Language::EN => 'Print',
        ],
        'Zeichnung' => [
            Language::DE => 'Zeichnung',
            Language::EN => 'Drawing',
        ],
    ];

    private static $inventoryNumberReplaceRegExpArr = [
        '/^CDA\./',
        '/^G_/',
    ];

    private static $catalogWrokReferenceReplaceArr = [
        '-Nummer',
    ];

    private static $activeLoggingOfWronglyCategorizedReferences = false;

    private function __construct()
    {
    }

    /**
     * Inflates the passed graphic objects
     *
     * @param SimpleXMLElement $node Current graphics element node
     * @param Graphic $graphicDe Graphic object holding the german informations
     * @param Graphic $graphicEn Graphic object holding the english informations
     *
     * @return void
     */
    public static function inflate(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateInventoryNumber($subNode, $graphicDe, $graphicEn);
        self::inflateInvolvedPersons($subNode, $graphicDe, $graphicEn);
        self::inflatePersonNames($subNode, $graphicDe, $graphicEn);
        self::inflateTitles($subNode, $graphicDe, $graphicEn);
        self::inflateClassification($subNode, $graphicDe, $graphicEn);
        self::inflateObjectMeta($subNode, $graphicDe, $graphicEn);
        self::inflateDimensions($subNode, $graphicDe, $graphicEn);
        self::inflateDating($subNode, $graphicDe, $graphicEn);
        self::inflateDescription($subNode, $graphicDe, $graphicEn);
        self::inflateProvenance($subNode, $graphicDe, $graphicEn);
        self::inflateMedium($subNode, $graphicDe, $graphicEn);
        self::inflateSignature($subNode, $graphicDe, $graphicEn);
        self::inflateInscription($subNode, $graphicDe, $graphicEn);
        self::inflateMarkings($subNode, $graphicDe, $graphicEn);
        self::inflateRelatedWorks($subNode, $graphicDe, $graphicEn);

        /* virtual graphic objects have to have their representative object
            extracted form the exhibitionhistory field */
        if ($graphicDe->getIsVirtual()) {
            self::inflateRepresentativeObject($subNode, $graphicDe, $graphicEn);
        } else {
            self::inflateExhibitionHistory($subNode, $graphicDe, $graphicEn);
        }
        self::inflateBibliography($subNode, $graphicDe, $graphicEn);
        self::inflateReferences($subNode, $graphicDe, $graphicEn);
        self::inflateAdditionalTextInformations($subNode, $graphicDe, $graphicEn);
        self::inflatePublications($subNode, $graphicDe, $graphicEn);
        self::inflateKeywords($subNode, $graphicDe, $graphicEn);
        self::inflateLocations($subNode, $graphicDe, $graphicEn);
        self::inflateRepositoryAndOwner($subNode, $graphicDe, $graphicEn);
        self::inflateSortingNumber($subNode, $graphicDe, $graphicEn);
        self::inflateCatalogWorkReference($subNode, $graphicDe, $graphicEn);
        self::inflateStructuredDimension($subNode, $graphicDe, $graphicEn);
    }

    /* Involved persons */
    private static function inflateInvolvedPersons(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $details = $node->{'Section'}[1]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($details); $i += 2) {
            $personsArr = [
                new Person, // de
                new Person, // en
            ];

            $graphicDe->addPerson($personsArr[0]);
            $graphicEn->addPerson($personsArr[1]);

            for ($j = 0; $j < count($personsArr); $j += 1) {
                $currDetails = $details[$i + $j];

                if (is_null($currDetails)) {
                    continue;
                }

                /* role */
                $roleElement = self::findElementByXPath(
                    $currDetails,
                    'Field[@FieldName="{ROLES.Role}"]/FormattedValue',
                );
                if ($roleElement) {
                    $roleStr = trim(strval($roleElement));
                    $personsArr[$j]->setRole($roleStr);
                }

                /* name */
                $nameElement = self::findElementByXPath(
                    $currDetails,
                    'Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
                );
                if ($nameElement) {
                    $nameStr = trim(strval($nameElement));
                    $personsArr[$j]->setName($nameStr);
                }

                /* prefix */
                $prefixElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="3"]//FormattedValue',
                );
                if ($prefixElement) {
                    $prefixStr = trim(strval($prefixElement));
                    $personsArr[$j]->setPrefix($prefixStr);
                }

                /* suffix */
                $suffixElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="4"]//FormattedValue',
                );
                if ($suffixElement) {
                    $suffixStr = trim(strval($suffixElement));
                    $personsArr[$j]->setSuffix($suffixStr);
                }

                /* role of unknown person */
                $unknownPersonRoleElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="6"]//FormattedValue',
                );
                if ($unknownPersonRoleElement) {
                    /* with a role set for an unknown person,
                        we can mark the person as 'unknown' */
                    $personsArr[$j]->setIsUnknown(true);

                    $unknownPersonRoleStr = trim(strval($unknownPersonRoleElement));
                    $personsArr[$j]->setRole($unknownPersonRoleStr);
                }

                /* prefix of unknown person */
                $unknownPersonPrefixElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="7"]//FormattedValue',
                );
                if ($unknownPersonPrefixElement) {
                    $unknownPersonPrefixStr = trim(strval($unknownPersonPrefixElement));
                    $personsArr[$j]->setPrefix($unknownPersonPrefixStr);
                }

                /* suffix of unknown person */
                $unknownPersonSuffixElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="8"]//FormattedValue',
                );
                if ($unknownPersonSuffixElement) {
                    $unknownPersonSuffixStr = trim(strval($unknownPersonSuffixElement));
                    $personsArr[$j]->setSuffix($unknownPersonSuffixStr);
                }

                /* name type */
                $nameTypeElement = self::findElementByXPath(
                    $currDetails,
                    'Field[@FieldName="{@Nametype}"]/FormattedValue',
                );
                if ($nameTypeElement) {
                    $nameTypeStr = trim(strval($nameTypeElement));
                    $personsArr[$j]->setNameType($nameTypeStr);
                }

                /* alternative name */
                $alternativeNameElement = self::findElementByXPath(
                    $currDetails,
                    'Field[@FieldName="{@AndererName}"]/FormattedValue',
                );
                if ($alternativeNameElement) {
                    $alternativeNameStr = trim(strval($alternativeNameElement));
                    $personsArr[$j]->setAlternativeName($alternativeNameStr);
                }

                /* remarks */
                $remarksElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="11"]//FormattedValue',
                );
                if ($remarksElement) {
                    $remarksNameStr = trim(strval($remarksElement));
                    $personsArr[$j]->setRemarks($remarksNameStr);
                }

                /* date */
                $dateElement = self::findElementByXPath(
                    $currDetails,
                    'Section[@SectionNumber="12"]//FormattedValue',
                );
                if ($dateElement) {
                    $dateStr = trim(strval($dateElement));
                    $personsArr[$j]->setDate($dateStr);
                }
            }
        }
    }


    /* Person names */
    private static function inflatePersonNames(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $groups = $node->{'Section'}[2]->{'Subreport'}->{'Group'};

        foreach ($groups as $group) {
            $personName = new PersonName;

            $graphicDe->addPersonName($personName);
            $graphicEn->addPersonName($personName);

            /* constituent id */
            $constituentIdElement = self::findElementByXPath(
                $group,
                'Field[@FieldName="GroupName ({CONALTNAMES.ConstituentID})"]/FormattedValue',
            );
            if ($constituentIdElement) {
                $constituentIdStr = trim(strval($constituentIdElement));
                $personName->setConstituentId($constituentIdStr);
            }

            $nameDetailGroups = self::findElementsByXPath(
                $group,
                'Group[@Level="2"]',
            );

            if (!$nameDetailGroups) {
                continue;
            }

            foreach ($nameDetailGroups as $nameDetailGroup) {
                $personDetailName = new PersonNameDetail;
                $personName->addDetail($personDetailName);

                /* name */
                $detailNameElement = self::findElementByXPath(
                    $nameDetailGroup,
                    'Field[@FieldName="GroupName ({CONALTNAMES.DisplayName})"]/FormattedValue',
                );
                if ($detailNameElement) {
                    $detailNameStr = trim(strval($detailNameElement));
                    $personDetailName->setName($detailNameStr);
                }

                /* type */
                $detailNameTypeElement = self::findElementByXPath(
                    $nameDetailGroup,
                    'Field[@FieldName="GroupName ({CONALTNAMES.NameType})"]/FormattedValue',
                );
                if ($detailNameTypeElement) {
                    $detailNameTypeStr = trim(strval($detailNameTypeElement));
                    $personDetailName->setNameType($detailNameTypeStr);
                }
            }
        }
    }


    /* Titles */
    private static function inflateTitles(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $titleDetailElements = $node->{'Section'}[3]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($titleDetailElements); $i += 1) {
            $titleDetailElement = $titleDetailElements[$i];

            if (is_null($titleDetailElement)) {
                continue;
            }

            $title = new Title;

            /* title language */
            $langElement = self::findElementByXPath(
                $titleDetailElement,
                'Field[@FieldName="{LANGUAGES.Language}"]/FormattedValue',
            );
            if ($langElement) {
                $langStr = trim(strval($langElement));

                if (self::$titlesLanguageTypes[Language::DE] === $langStr) {
                    $graphicDe->addTitle($title);
                } elseif (self::$titlesLanguageTypes[Language::EN] === $langStr) {
                    $graphicEn->addTitle($title);
                } elseif (self::$titlesLanguageTypes['not_assigned'] === $langStr) {
                    echo '  Unassigned title lang for object ' . $graphicDe->getInventoryNumber() . "\n";
                } else {
                    echo '  Unknown title lang: ' . $langStr . ' for object \'' . $graphicDe->getInventoryNumber() . "\'\n";
                    /* Bind title to both languages to prevent loss */
                    $graphicDe->addTitle($title);
                    $graphicEn->addTitle($title);
                }
            } else {
                /* Bind title to both languages to prevent loss */
                $graphicDe->addTitle($title);
                $graphicEn->addTitle($title);
            }

            /* title type */
            $typeElement = self::findElementByXPath(
                $titleDetailElement,
                'Field[@FieldName="{TITLETYPES.TitleType}"]/FormattedValue',
            );
            if ($typeElement) {
                $typeStr = trim(strval($typeElement));
                $title->setType($typeStr);
            }

            /* title */
            $titleElement = self::findElementByXPath(
                $titleDetailElement,
                'Field[@FieldName="{OBJTITLES.Title}"]/FormattedValue',
            );
            if ($titleElement) {
                $titleStr = trim(strval($titleElement));
                $title->setTitle($titleStr);
            }

            /* remark */
            $remarksElement = self::findElementByXPath(
                $titleDetailElement,
                'Field[@FieldName="{OBJTITLES.Remarks}"]/FormattedValue',
            );
            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));
                $title->setRemarks($remarksStr);
            }
        }
    }


    /* Classification */
    private static function inflateClassification(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $classificationSectionElement = $node->{'Section'}[4];

        $classificationDe = new Classification;
        $classificationEn = new Classification;

        $graphicDe->setClassification($classificationDe);
        $graphicEn->setClassification($classificationEn);

        /* classification */
        $classificationElement = self::findElementByXPath(
            $classificationSectionElement,
            'Field[@FieldName="{@Klassifizierung}"]/FormattedValue',
        );
        if ($classificationElement) {
            $classificationStr = trim(strval($classificationElement));

            $classificationDe->setClassification(
                Language::translate($classificationStr, self::$translations, Language::DE)
            );
            $classificationEn->setClassification(
                Language::translate($classificationStr, self::$translations, Language::EN)
            );
        }

        /* condition */
        $stateElement = self::findElementByXPath(
            $classificationSectionElement,
            'Field[@FieldName="{@Druckzustand}"]/FormattedValue',
        );
        if ($stateElement) {
            $stateStr = trim(strval($stateElement));

            $splitStateStr = self::splitLanguageString($stateStr);

            if (isset($splitStateStr[0])) {
                $classificationDe->setCondition($splitStateStr[0]);
            }

            if (isset($splitStateStr[1])) {
                $classificationEn->setCondition($splitStateStr[1]);
            }
        }

        /* PrintProcess */
        $objectNameSectionElement = $node->{'Section'}[5];

        $objectNameElement = self::findElementByXPath(
            $objectNameSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
        );
        if ($objectNameElement) {
            $objectNameStr = trim(strval($objectNameElement));

            $splitObjectNameStr = self::splitLanguageString($objectNameStr);

            if (isset($splitObjectNameStr[0])) {
                $classificationDe->setPrintProcess($splitObjectNameStr[0]);
            }

            if (isset($splitObjectNameStr[1])) {
                $classificationEn->setPrintProcess($splitObjectNameStr[1]);
            }
        }
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
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

            /* Using single german value for both language objects */
            $graphicDe->setInventoryNumber($cleanInventoryNumberStr);
            $graphicEn->setInventoryNumber($cleanInventoryNumberStr);
        }
    }


    /* Object id & virtual (meta) */
    private static function inflateObjectMeta(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $metaSectionElement = $node->{'Section'}[7];

        /* object id */
        $objectIdElement = self::findElementByXPath(
            $metaSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectID}"]/Value',
        );
        if ($objectIdElement) {
            $objectIdStr = intval(trim(strval($objectIdElement)));

            /* Using single german value for both language objects */
            $graphicDe->setObjectId($objectIdStr);
            $graphicEn->setObjectId($objectIdStr);
        }

        /* virtual*/
        $virtualElement = self::findElementByXPath(
            $metaSectionElement,
            'Field[@FieldName="{OBJECTS.IsVirtual}"]/FormattedValue',
        );
        if ($virtualElement) {
            $virtualStr = trim(strval($virtualElement));

            $isVirtual = ($virtualStr === '1');

            /* Using single german value for both language objects */
            $graphicDe->setIsVirtual($isVirtual);
            $graphicEn->setIsVirtual($isVirtual);
        }
    }


    /* Dimensions */
    private static function inflateDimensions(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $metaSectionElement = $node->{'Section'}[8];

        /* object id */
        $dimensionsElement = self::findElementByXPath(
            $metaSectionElement,
            'Field[@FieldName="{OBJECTS.Dimensions}"]/FormattedValue',
        );
        if ($dimensionsElement) {
            $dimensionsStr = trim(strval($dimensionsElement));

            $splitDimensionsStr = self::splitLanguageString($dimensionsStr);

            if (isset($splitDimensionsStr[0])) {
                $graphicDe->setDimensions($splitDimensionsStr[0]);
            }

            if (isset($splitDimensionsStr[1])) {
                $graphicEn->setDimensions($splitDimensionsStr[1]);
            }
        }
    }


    /* Dating */
    private static function inflateDating(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $datingDe = new Dating;
        $datingEn = new Dating;

        /* Using single german value for both language objects */
        $graphicDe->setDating($datingDe);
        $graphicEn->setDating($datingEn);

        /* Dated (string) */
        $datedSectionElement = $node->{'Section'}[9];

        $datedElement = self::findElementByXPath(
            $datedSectionElement,
            'Field[@FieldName="{OBJECTS.Dated}"]/FormattedValue',
        );
        if ($datedElement) {
            $datedDateStr = trim(strval($datedElement));

            $splitStateStr = self::splitLanguageString($datedDateStr);

            if (isset($splitStateStr[0])) {
                $datingDe->setDated($splitStateStr[0]);
            }

            if (isset($splitStateStr[1])) {
                $datingEn->setDated($splitStateStr[1]);
            }
        }

        /* Date begin */
        $dateBeginSectionElement = $node->{'Section'}[10];

        $dateBeginElement = self::findElementByXPath(
            $dateBeginSectionElement,
            'Field[@FieldName="{OBJECTS.DateBegin}"]/FormattedValue',
        );
        if ($dateBeginElement) {
            $dateBeginStr = intval(trim(strval($dateBeginElement)));

            $datingDe->setBegin($dateBeginStr);
            $datingEn->setBegin($dateBeginStr);
        }

        /* Date end */
        $dateEndSectionElement = $node->{'Section'}[11];

        $dateEndElement = self::findElementByXPath(
            $dateEndSectionElement,
            'Field[@FieldName="{OBJECTS.DateEnd}"]/FormattedValue',
        );
        if ($dateEndElement) {
            $dateEndStr = intval(trim(strval($dateEndElement)));

            $datingDe->setEnd($dateEndStr);
            $datingEn->setEnd($dateEndStr);
        }

        /* Remarks */
        $remarksSectionElement = $node->{'Section'}[12];

        $remarksElement = self::findElementByXPath(
            $remarksSectionElement,
            'Field[@FieldName="{OBJECTS.DateRemarks}"]/FormattedValue',
        );
        if ($remarksElement) {
            $remarksStr = trim(strval($remarksElement));

            $splitRemarksStr = self::splitLanguageString($remarksStr);

            if (isset($splitRemarksStr[0])) {
                $datingDe->setRemarks($splitRemarksStr[0]);
            }

            if (isset($splitRemarksStr[1])) {
                $datingEn->setRemarks($splitRemarksStr[1]);
            }
        }

        /* HistoricEventInformation */
        $historicEventDetailElements = $node->{'Section'}[13]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($historicEventDetailElements); $i += 2) {
            $historicEventArr = [];

            // de
            $detailDeElement = $historicEventDetailElements[$i];
            if (!is_null($detailDeElement) && $detailDeElement->count() > 0) {
                $historicEventInformation = new HistoricEventInformation;
                $historicEventArr[] = $historicEventInformation;
                $datingDe->addHistoricEventInformation($historicEventInformation);
            }

            // en
            $detailEnElement = $historicEventDetailElements[$i + 1];
            if (!is_null($detailEnElement) && $detailEnElement->count() > 0) {
                $historicEventInformation = new HistoricEventInformation;
                $historicEventArr[] = $historicEventInformation;
                $datingEn->addHistoricEventInformation($historicEventInformation);
            }

            for ($j = 0; $j < count($historicEventArr); $j += 1) {
                $historicEventDetailElement = $historicEventDetailElements[$i + $j];

                if (is_null($historicEventDetailElement) || !isset($historicEventArr[$j])) {
                    continue;
                }

                /* event type */
                $eventTypeElement = self::findElementByXPath(
                    $historicEventDetailElement,
                    'Field[@FieldName="{OBJDATES.EventType}"]/FormattedValue',
                );
                if ($eventTypeElement) {
                    $eventTypeStr = trim(strval($eventTypeElement));
                    $historicEventArr[$j]->setEventType($eventTypeStr);
                }

                /* date text */
                $dateTextElement = self::findElementByXPath(
                    $historicEventDetailElement,
                    'Field[@FieldName="{OBJDATES.DateText}"]/FormattedValue',
                );
                if ($dateTextElement) {
                    $dateTextStr = trim(strval($dateTextElement));
                    $historicEventArr[$j]->setText($dateTextStr);
                }

                /* begin date */
                $dateBeginElement = self::findElementByXPath(
                    $historicEventDetailElement,
                    'Field[@FieldName="{@Anfangsdatum}"]/FormattedValue',
                );
                if ($dateBeginElement) {
                    $dateBeginNumber = intval(trim(strval($dateBeginElement)));
                    $historicEventArr[$j]->setBegin($dateBeginNumber);
                }

                /* end date */
                $dateEndElement = self::findElementByXPath(
                    $historicEventDetailElement,
                    'Field[@FieldName="{@Enddatum }"]/FormattedValue',
                );
                if ($dateEndElement) {
                    $dateEndNumber = intval(trim(strval($dateEndElement)));
                    $historicEventArr[$j]->setEnd($dateEndNumber);
                }

                /* remarks */
                $dateRemarksElement = self::findElementByXPath(
                    $historicEventDetailElement,
                    'Field[@FieldName="{OBJDATES.Remarks}"]/FormattedValue',
                );
                if ($dateRemarksElement) {
                    $dateRemarksNumber = trim(strval($dateRemarksElement));
                    $historicEventArr[$j]->setRemarks($dateRemarksNumber);
                }
            }
        }
    }


    /* Description */
    private static function inflateDescription(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $descriptionDeSectionElement = $node->{'Section'}[14];
        $descriptionElement = self::findElementByXPath(
            $descriptionDeSectionElement,
            'Field[@FieldName="{OBJECTS.Description}"]/FormattedValue',
        );
        if ($descriptionElement) {
            $descriptionStr = trim(strval($descriptionElement));
            $graphicDe->setDescription($descriptionStr);
        }

        /* en */
        $descriptionEnSectionElement = $node->{'Section'}[15];
        $descriptionElement = self::findElementByXPath(
            $descriptionEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText3}"]/FormattedValue',
        );
        if ($descriptionElement) {
            $descriptionStr = trim(strval($descriptionElement));
            $graphicEn->setDescription($descriptionStr);
        }
    }


    /* Provenance */
    private static function inflateProvenance(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $provenanceDeSectionElement = $node->{'Section'}[16];
        $provenanceElement = self::findElementByXPath(
            $provenanceDeSectionElement,
            'Field[@FieldName="{OBJECTS.Provenance}"]/FormattedValue',
        );
        if ($provenanceElement) {
            $provenanceStr = trim(strval($provenanceElement));
            $graphicDe->setProvenance($provenanceStr);
        }

        /* en */
        $provenanceEnSectionElement = $node->{'Section'}[17];
        $provenanceElement = self::findElementByXPath(
            $provenanceEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText5}"]/FormattedValue',
        );
        if ($provenanceElement) {
            $provenanceStr = trim(strval($provenanceElement));
            $graphicEn->setProvenance($provenanceStr);
        }
    }


    /* Medium */
    private static function inflateMedium(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $mediumDeSectionElement = $node->{'Section'}[18];
        $mediumElement = self::findElementByXPath(
            $mediumDeSectionElement,
            'Field[@FieldName="{OBJECTS.Medium}"]/FormattedValue',
        );
        if ($mediumElement) {
            $mediumStr = trim(strval($mediumElement));
            $graphicDe->setMedium($mediumStr);
        }

        /* en */
        $mediumEnSectionElement = $node->{'Section'}[19];
        $mediumElement = self::findElementByXPath(
            $mediumEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText4}"]/FormattedValue',
        );
        if ($mediumElement) {
            $mediumStr = trim(strval($mediumElement));
            $graphicEn->setMedium($mediumStr);
        }
    }


    /* Signature */
    private static function inflateSignature(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $signatureDeSectionElement = $node->{'Section'}[20];
        $signatureElement = self::findElementByXPath(
            $signatureDeSectionElement,
            'Field[@FieldName="{OBJECTS.PaperSupport}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim(strval($signatureElement));
            $graphicDe->setSignature($signatureStr);
        }

        /* en */
        $signatureEnSectionElement = $node->{'Section'}[21];
        $signatureElement = self::findElementByXPath(
            $signatureEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.ShortText6}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim(strval($signatureElement));
            $graphicEn->setSignature($signatureStr);
        }
    }


    /* Inscription */
    private static function inflateInscription(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $inscriptionDeSectionElement = $node->{'Section'}[22];
        $inscriptionElement = self::findElementByXPath(
            $inscriptionDeSectionElement,
            'Field[@FieldName="{OBJECTS.Inscribed}"]/FormattedValue',
        );
        if ($inscriptionElement) {
            $inscriptionStr = trim(strval($inscriptionElement));
            $graphicDe->setInscription($inscriptionStr);
        }

        /* en */
        $inscriptionEnSectionElement = $node->{'Section'}[23];
        $inscriptionElement = self::findElementByXPath(
            $inscriptionEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText7}"]/FormattedValue',
        );
        if ($inscriptionElement) {
            $inscriptionStr = trim(strval($inscriptionElement));
            $graphicEn->setInscription($inscriptionStr);
        }
    }


    /* Markings */
    private static function inflateMarkings(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $markingsDeSectionElement = $node->{'Section'}[24];
        $markingsElement = self::findElementByXPath(
            $markingsDeSectionElement,
            'Field[@FieldName="{OBJECTS.Markings}"]/FormattedValue',
        );
        if ($markingsElement) {
            $markingsStr = trim(strval($markingsElement));
            $graphicDe->setMarkings($markingsStr);
        }

        /* en */
        $markingsEnSectionElement = $node->{'Section'}[25];
        $markingsElement = self::findElementByXPath(
            $markingsEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText9}"]/FormattedValue',
        );
        if ($markingsElement) {
            $markingsStr = trim(strval($markingsElement));
            $graphicEn->setMarkings($markingsStr);
        }
    }


    /* Related works */
    private static function inflateRelatedWorks(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $relatedWorksDeSectionElement = $node->{'Section'}[26];
        $relatedWorksElement = self::findElementByXPath(
            $relatedWorksDeSectionElement,
            'Field[@FieldName="{OBJECTS.RelatedWorks}"]/FormattedValue',
        );
        if ($relatedWorksElement) {
            $relatedWorksStr = trim(strval($relatedWorksElement));
            $graphicDe->setRelatedWorks($relatedWorksStr);
        }

        /* en */
        $relatedWorksEnSectionElement = $node->{'Section'}[27];
        $relatedWorksElement = self::findElementByXPath(
            $relatedWorksEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText6}"]/FormattedValue',
        );
        if ($relatedWorksElement) {
            $relatedWorksStr = trim(strval($relatedWorksElement));
            $graphicEn->setRelatedWorks($relatedWorksStr);
        }
    }


    /* */
    private static function inflateRepresentativeObject(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* For virtual graphics, the representative object is found
            under the exhibitionHistory field */
        $exhibitionHistorySectionElement = $node->{'Section'}[28];
        $exhibitionHistoryElement = self::findElementByXPath(
            $exhibitionHistorySectionElement,
            'Field[@FieldName="{OBJECTS.Exhibitions}"]/FormattedValue',
        );

        if ($exhibitionHistoryElement) {
            $representativeObjectStr = trim(strval($exhibitionHistoryElement));
            $cleanRepresentativeObjectStr = preg_replace(
                self::$inventoryNumberReplaceRegExpArr,
                '',
                $representativeObjectStr,
            );
            $graphicDe->setRepresentativeObject($cleanRepresentativeObjectStr);
            $graphicEn->setRepresentativeObject($cleanRepresentativeObjectStr);
        }
    }


    /* Exhibition history */
    private static function inflateExhibitionHistory(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* de */
        $exhibitionHistoryDeSectionElement = $node->{'Section'}[28];
        $exhibitionHistoryElement = self::findElementByXPath(
            $exhibitionHistoryDeSectionElement,
            'Field[@FieldName="{OBJECTS.Exhibitions}"]/FormattedValue',
        );
        if ($exhibitionHistoryElement) {
            $exhibitionHistoryStr = trim(strval($exhibitionHistoryElement));
            $graphicDe->setExhibitionHistory($exhibitionHistoryStr);
        }

        /* en */
        $exhibitionHistoryEnSectionElement = $node->{'Section'}[29];
        $exhibitionHistoryElement = self::findElementByXPath(
            $exhibitionHistoryEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText8}"]/FormattedValue',
        );
        if ($exhibitionHistoryElement) {
            $exhibitionHistoryStr = trim(strval($exhibitionHistoryElement));
            $graphicEn->setExhibitionHistory($exhibitionHistoryStr);
        }


        /* Use the german exhibition history if none is set for the english one */
        if (empty($graphicEn->getExhibitionHistory())) {
            $graphicEn->setExhibitionHistory($graphicDe->getExhibitionHistory());
        }
    }


    /* Bibliography */
    private static function inflateBibliography(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $bibliographySectionElement = $node->{'Section'}[30];
        $bibliographyElement = self::findElementByXPath(
            $bibliographySectionElement,
            'Field[@FieldName="{OBJECTS.Bibliography}"]/FormattedValue',
        );
        if ($bibliographyElement) {
            $bibliographyStr = trim(strval($bibliographyElement));
            $graphicDe->setBibliography($bibliographyStr);
            $graphicEn->setBibliography($bibliographyStr);
        }
    }


    /* References */
    private static function inflateReferences(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        /* Reprints References */
        $referenceReprintDetailsElements = $node->{'Section'}[31]->{'Subreport'}->{'Details'};


        $reprintReferences = self::getReferencesForDetailElements(
            $referenceReprintDetailsElements,
        );

        /* RelatedWorks References */
        $referenceRelatedWorksDetailsElements = $node->{'Section'}[32]->{'Subreport'}->{'Details'};

        $relatedWorksReferences = self::getReferencesForDetailElements(
            $referenceRelatedWorksDetailsElements,
        );


        $wrongReprintReferences = self::getWronglyCategorizedReferences(
            $reprintReferences,
            self::$referenceTypeValues['relatedWork'],
        );

        $wrongRelatedWorkReferences = self::getWronglyCategorizedReferences(
            $relatedWorksReferences,
            self::$referenceTypeValues['reprint'],
        );

        if (self::$activeLoggingOfWronglyCategorizedReferences) {
            self::logWronglyCategorizedReferences(
                $graphicDe,
                $wrongReprintReferences,
                $wrongRelatedWorkReferences,
            );
        }

        $overallReferences = [];
        $overallReferences = array_merge($overallReferences, $reprintReferences);
        $overallReferences = array_merge($overallReferences, $relatedWorksReferences);

        $filteredReprintReferences = array_values(
            array_filter($overallReferences, function ($reference) {
                return $reference->getText() === self::$referenceTypeValues['reprint'];
            }),
        );

        $filteredRelatedWorkReferences = array_values(
            array_filter($overallReferences, function ($reference) {
                return $reference->getText() === self::$referenceTypeValues['relatedWork'];
            }),
        );

        $graphicDe->setReprintReferences($filteredReprintReferences);
        $graphicEn->setReprintReferences($filteredReprintReferences);

        $graphicDe->setRelatedWorkReferences($filteredRelatedWorkReferences);
        $graphicEn->setRelatedWorkReferences($filteredRelatedWorkReferences);
    }


    /* Helper function for logging graphics with wrongly categorized references */
    private static function logWronglyCategorizedReferences(
        Graphic $graphic,
        array $reprintRefs,
        array $relatedWorkRefs
    ): void {
        if (count($reprintRefs) > 0 || count($relatedWorkRefs) > 0) {
            echo '  > ' . $graphic->getInventoryNumber() . (($graphic->getIsVirtual()) ? ' (isVirtual)' : '') . "\n";

            if (count($reprintRefs) > 0) {
                echo "  wrong reprint refs:\n";
                foreach ($reprintRefs as $ref) {
                    echo "      * " . $ref->getInventoryNumber() . ' (' . $ref->getText() . ')' . "\n";
                }
            }

            if (count($relatedWorkRefs) > 0) {
                echo "  wrong relatedWork refs:\n";
                foreach ($relatedWorkRefs as $ref) {
                    echo "      * " . $ref->getInventoryNumber() . ' (' . $ref->getText() . ')' . "\n";
                }
            }
        }
    }


    /* Helper function for checking wrongly groupe */
    private static function getWronglyCategorizedReferences(
        array $references,
        string $wrongType
    ): array {
        return array_filter($references, function ($ref) use ($wrongType) {
            return $ref->getText() === $wrongType;
        });
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
                $reference->setRemark($remarksStr);
            }

            $references[] = $reference;
        }

        return $references;
    }


    /* Additional text informations */
    private static function inflateAdditionalTextInformations(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $additionalTextsDetailsElements = $node->{'Section'}[33]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($additionalTextsDetailsElements); $i += 1) {
            $additionalTextDetailElement = $additionalTextsDetailsElements[$i];

            if ($additionalTextDetailElement->count() === 0) {
                continue;
            }

            $additionalTextInformation = new AdditionalTextInformation;

            /* Text type */
            $textTypeElement = self::findElementByXPath(
                $additionalTextDetailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{TEXTTYPES.TextType}"]/FormattedValue',
            );

            /* Language determination */
            if ($textTypeElement) {
                $textTypeStr = trim(strval($textTypeElement));
                $additionalTextInformation->setType($textTypeStr);

                if (self::$additionalTextLanguageTypes[Language::DE] === $textTypeStr) {
                    $graphicDe->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes[Language::EN] === $textTypeStr) {
                    $graphicEn->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes['not_assigned'] === $textTypeStr) {
                    echo '  Unassigned additional text type for object \'' . $graphicDe->getInventoryNumber() . "'\n";
                    $graphicDe->addAdditionalTextInformation($additionalTextInformation);
                    $graphicEn->addAdditionalTextInformation($additionalTextInformation);
                } else {
                    echo '  Unknown additional text type: ' . $textTypeStr . ' for object \'' . $graphicDe->getInventoryNumber() . "'\n";
                    $graphicDe->addAdditionalTextInformation($additionalTextInformation);
                    $graphicEn->addAdditionalTextInformation($additionalTextInformation);
                }
            } else {
                $graphicDe->addAdditionalTextInformation($additionalTextInformation);
                $graphicEn->addAdditionalTextInformation($additionalTextInformation);
            }

            /* Text */
            $textElement = self::findElementByXPath(
                $additionalTextDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{TEXTENTRIES.TextEntry}"]/FormattedValue',
            );
            if ($textElement) {
                $textStr = trim(strval($textElement));
                $additionalTextInformation->setText($textStr);
            }

            /* Date */
            $dateElement = self::findElementByXPath(
                $additionalTextDetailElement,
                'Section[@SectionNumber="2"]/Text[@Name="Text21"]/TextValue',
            );
            if ($dateElement) {
                $dateStr = trim(strval($dateElement));
                $additionalTextInformation->setDate($dateStr);
            }

            /* Year */
            $yearElement = self::findElementByXPath(
                $additionalTextDetailElement,
                'Section[@SectionNumber="3"]/Text[@Name="Text1"]/TextValue',
            );
            if ($yearElement) {
                $yearStr = trim(strval($yearElement));
                $year = intval($yearStr);
                $additionalTextInformation->setYear($year);
            }

            /* Author */
            $authorElement = self::findElementByXPath(
                $additionalTextDetailElement,
                'Section[@SectionNumber="4"]/Text[@Name="Text3"]/TextValue',
            );
            if ($authorElement) {
                $authorStr = trim(strval($authorElement));
                $additionalTextInformation->setAuthor($authorStr);
            }
        }
    }


    /* Publications */
    private static function inflatePublications(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $publicationDetailsElements = $node->{'Section'}[34]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($publicationDetailsElements); $i += 1) {
            $publicationDetailElement = $publicationDetailsElements[$i];

            if ($publicationDetailElement->count() === 0) {
                continue;
            }

            $publicationDe = new Publication;
            $publicationEn = new Publication;

            $graphicDe->addPublication($publicationDe);
            $graphicEn->addPublication($publicationEn);

            /* Title */
            $titleElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{REFERENCEMASTER.Heading}"]/FormattedValue',
            );
            if ($titleElement) {
                $titleStr = trim(strval($titleElement));
                $publicationDe->setTitle($titleStr);
                $publicationEn->setTitle($titleStr);
            }

            /* Pagenumber */
            $pageNumberElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PageNumber}"]/FormattedValue',
            );
            if ($pageNumberElement) {
                $pageNumberStr = trim(strval($pageNumberElement));

                $splitPageNumberStr = self::splitLanguageString($pageNumberStr);

                if (isset($splitPageNumberStr[0])) {
                    $publicationDe->setPageNumber($splitPageNumberStr[0]);
                }

                if (isset($splitPageNumberStr[1])) {
                    $publicationEn->setPageNumber($splitPageNumberStr[1]);
                }
            }

            /* Reference */
            $referenceIdElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{REFERENCEMASTER.ReferenceID}"]/FormattedValue',
            );
            if ($referenceIdElement) {
                $referenceIdStr = trim(strval($referenceIdElement));
                $publicationDe->setReferenceId($referenceIdStr);
                $publicationEn->setReferenceId($referenceIdStr);
            }
        }
    }


    /* Keywords */
    private static function inflateKeywords(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $keywordDetailsElements = $node->{'Section'}[35]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($keywordDetailsElements); $i += 1) {
            $keywordDetailElement = $keywordDetailsElements[$i];

            if ($keywordDetailElement->count() === 0) {
                continue;
            }

            $metaReference = new MetaReference;

            /* Type */
            $keywordTypeElement = self::findElementByXPath(
                $keywordDetailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{THESXREFTYPES.ThesXrefType}"]/FormattedValue',
            );
            if ($keywordTypeElement) {
                $keywordTypeStr = trim(strval($keywordTypeElement));
                $metaReference->setType($keywordTypeStr);
            }

            /* Term */
            $keywordTermElement = self::findElementByXPath(
                $keywordDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{TERMS.Term}"]/FormattedValue',
            );
            if ($keywordTermElement) {
                $keywordTermStr = trim(strval($keywordTermElement));
                $metaReference->setTerm($keywordTermStr);
            }

            /* Path */
            $keywordPathElement = self::findElementByXPath(
                $keywordDetailElement,
                'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH1.Path}"]/FormattedValue',
            );
            if ($keywordPathElement) {
                $keywordPathStr = trim(strval($keywordPathElement));
                $metaReference->setPath($keywordPathStr);
            }


            /* Decide if keyword is valid */
            if (!empty($metaReference->getTerm())) {
                $graphicDe->addKeyword($metaReference);
                $graphicEn->addKeyword($metaReference);
            }
        }
    }


    /* Locations */
    private static function inflateLocations(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
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
                    $graphicDe->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes[Language::EN] === $locationTypeStr) {
                    $graphicEn->addLocation($metaReference);
                } elseif (self::$locationLanguageTypes['not_assigned'] === $locationTypeStr) {
                    echo '  Unassigned location type for object ' . $graphicDe->getInventoryNumber() . "\n";
                    $graphicDe->addLocation($metaReference);
                    $graphicEn->addLocation($metaReference);
                } else {
                    echo '  Unknown location type: ' . $locationTypeStr . ' for object ' . $graphicDe->getInventoryNumber() . "\n";
                    $graphicDe->addLocation($metaReference);
                    $graphicEn->addLocation($metaReference);
                }
            } else {
                $graphicDe->addLocation($metaReference);
                $graphicEn->addLocation($metaReference);
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


    /* Repository and Owner */
    private static function inflateRepositoryAndOwner(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
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
            $isRepository = false;
            $isOwner = false;

            try {
                $isRepository = self::inflateRepository($detail, $roleName, $graphicDe, $graphicEn);
            } catch (Error $e) {
                echo '  ' . $e->getMessage() . "\n";
            }

            try {
                $isOwner = self::inflateOwner($detail, $roleName, $graphicDe, $graphicEn);
            } catch (Error $e) {
                echo '  ' . $e->getMessage() . "\n";
            }

            if (!$isRepository && !$isOwner) {
                echo '  Item is neither a repository or an owner: ' . $roleName . "\n";
            }
        }
    }


    /* Repository */
    private static function inflateRepository(
        SimpleXMLElement $detail,
        string $roleName,
        Graphic $graphicDe,
        Graphic $graphicEn
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
                $graphicDe->setRepository($repositoryStr);
                break;

            case self::$repositoryTypes[Language::EN]:
                /* en */
                $graphicEn->setRepository($repositoryStr);
                break;

            default:
                return false;
        }

        return true;
    }


    /* Owner */
    private static function inflateOwner(
        SimpleXMLElement $detail,
        string $roleName,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): bool {
        $ownerElement = self::findElementByXPath(
            $detail,
            'Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
        );

        if (!$ownerElement) {
            throw new Error('Missing element with owner name!');
        }

        $ownerStr = trim(strval($ownerElement));

        switch ($roleName) {
            case self::$ownerTypes[Language::DE]:
                /* de */
                $graphicDe->setOwner($ownerStr);
                break;

            case self::$ownerTypes[Language::EN]:
                /* en */
                $graphicEn->setOwner($ownerStr);
                break;

            default:
                return false;
        }

        return true;
    }


    /* Sorting number */
    private static function inflateSortingNumber(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $sortingNumberSubreport = $node->{'Section'}[38];

        $sortingNumberElement = self::findElementByXPath(
            $sortingNumberSubreport,
            'Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
        );
        if ($sortingNumberElement) {
            $sortingNumberStr = trim(strval($sortingNumberElement));

            $graphicDe->setSortingNumber($sortingNumberStr);
            $graphicEn->setSortingNumber($sortingNumberStr);
        }
    }


    /* Catalog work reference */
    private static function inflateCatalogWorkReference(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic $graphicEn
    ): void {
        $catalogWorkReferenceDetailsElements = $node->{'Section'}[39]->{'Subreport'}->{'Details'};

        foreach ($catalogWorkReferenceDetailsElements as $detailElement) {
            if ($detailElement->count() === 0) {
                continue;
            }

            $catalogWorkReference = new CatalogWorkReference;

            /* Description */
            $descriptionElement = self::findElementByXPath(
                $detailElement,
                'Field[@FieldName="{AltNumDescriptions.AltNumDescription}"]/FormattedValue',
            );
            if ($descriptionElement) {
                $descriptionStr = trim(strval($descriptionElement));

                $cleanDescriptionStr = str_ireplace(
                    self::$catalogWrokReferenceReplaceArr,
                    '',
                    $descriptionStr,
                );

                $catalogWorkReference->setDescription($cleanDescriptionStr);
            }

            /* Reference number */
            $referenceNumberElement = self::findElementByXPath(
                $detailElement,
                'Field[@FieldName="{AltNums.AltNum}"]/FormattedValue',
            );
            if ($referenceNumberElement) {
                $referenceNumberStr = trim(strval($referenceNumberElement));

                $cleanReferenceNumberStr = preg_replace(
                    self::$inventoryNumberReplaceRegExpArr,
                    '',
                    $referenceNumberStr,
                );

                $catalogWorkReference->setReferenceNumber($cleanReferenceNumberStr);
            }

            /* Remarks */
            $remarksElement = self::findElementByXPath(
                $detailElement,
                'Field[@FieldName="{AltNums.Remarks}"]/FormattedValue',
            );
            if ($remarksElement) {
                $remarksStr = trim(strval($remarksElement));

                $catalogWorkReference->setRemarks($remarksStr);
            }


            /* Decide if reference should be added */
            if (!empty($catalogWorkReference->getReferenceNumber())) {
                $graphicDe->addCatalogWorkReference($catalogWorkReference);
                $graphicEn->addCatalogWorkReference($catalogWorkReference);
            }
        }
    }


    /* Structured dimension */
    private static function inflateStructuredDimension(
        SimpleXMLElement $node,
        Graphic $graphicDe,
        Graphic &$graphicEn
    ): void {
        $catalogWorkReferenceSubreport = $node->{'Section'}[40]->{'Subreport'};

        $structuredDimension = new StructuredDimension;

        $graphicDe->setStructuredDimension($structuredDimension);
        $graphicEn->setStructuredDimension($structuredDimension);

        /* element */
        $elementElement = self::findElementByXPath(
            $catalogWorkReferenceSubreport,
            'Field[@FieldName="{DIMENSIONELEMENTS.Element}"]/FormattedValue',
        );

        if ($elementElement) {
            $elementStr = trim(strval($elementElement));

            $structuredDimension->setElement($elementStr);
        }


        /* Details elements */
        $detailsElements = self::findElementsByXPath(
            $catalogWorkReferenceSubreport,
            'Details',
        );
        if (is_array($detailsElements) && count($detailsElements) === 2) {
            /* height */
            $heightElement = self::findElementByXPath(
                $detailsElements[0],
                'Field[@FieldName="{DIMENSIONS.Dimension}"]/Value',
            );
            if ($heightElement) {
                $heightNumber = trim(strval($heightElement));

                $structuredDimension->setHeight($heightNumber);
            }

            /* width */
            $widthElement = self::findElementByXPath(
                $detailsElements[1],
                'Field[@FieldName="{DIMENSIONS.Dimension}"]/Value',
            );
            if ($widthElement) {
                $widthNumber = trim(strval($widthElement));

                $structuredDimension->setWidth($widthNumber);
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
