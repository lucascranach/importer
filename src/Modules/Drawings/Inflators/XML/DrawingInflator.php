<?php

namespace CranachDigitalArchive\Importer\Modules\Drawings\Inflators\XML;

use Error;
use SimpleXMLElement;
use CranachDigitalArchive\Importer\Language;
use CranachDigitalArchive\Importer\Interfaces\Inflators\IInflator;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\Classification;
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
use CranachDigitalArchive\Importer\Modules\Main\Entities\MetaLocationReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\CatalogWorkReference;
use CranachDigitalArchive\Importer\Modules\Main\Entities\StructuredDimension;
use CranachDigitalArchive\Importer\Modules\Drawings\Entities\DrawingLanguageCollection;

/**
 * Drawingss inflator used to inflate german and english drawing instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class DrawingInflator implements IInflator
{
    private static $nsPrefix = 'ns';
    private static $ns = 'urn:crystal-reports:schemas:report-detail';
    private static $langSplitChar = '#';

    private static $additionalTextLanguageTypes = [
        Language::DE => 'Beschreibung/ Interpretation/ Kommentare',
        Language::EN => 'Description/ Interpretation/ Comments',
        'author' => 'Autor', /* TODO: To be checked; has german values? */
        'letter' => 'Briefumschrift', /* TODO: To be checked; has english values? */
        'not_assigned' => '(not assigned)',
    ];

    private static $locationLanguageTypes = [
        Language::DE => 'Standort Cranach Objekt',
        Language::EN => 'Location Cranach Object',
        'not_assigned' => '(not assigned)',
    ];

    private static $titlesLanguageTypes = [
        Language::DE => 'German',
        Language::EN => 'English',
        'not_assigned' => '(not assigned)',
    ];

    private static $repositoryTypes = [
        Language::DE => 'Besitzer*in',
        Language::EN => 'Repository',
    ];

    private static $ownerTypes = [
        Language::DE => 'Eigentümer*in',
        Language::EN => 'Owner',
    ];

    private static $historicEventTypesLangMapping = [
        'datierung' => [Language::DE, 'DATING'],
        'dating' => [Language::EN, 'DATING'],
        'auflage' => [Language::DE, 'EDITION'],
        'edition' => [Language::EN, 'EDITION'],
    ];

    private static $historicEventTypeNotEntered = '[not entered]';

    private static $inventoryNumberReplaceRegExpArr = [
        '/^CDA\./',
    ];

    private static $isPublishedString = 'CDA Online-Freigabe';

    private static $sortingNumberFallbackValue = '?';


    private function __construct()
    {
    }


    public static function inflate(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $subNode = $node->{'GroupHeader'};

        self::registerXPathNamespace($subNode);

        self::inflateInventoryNumber($subNode, $drawingCollection);
        self::inflateInvolvedPersons($subNode, $drawingCollection);
        self::inflatePersonNames($subNode, $drawingCollection);
        self::inflateTitles($subNode, $drawingCollection);
        self::inflateClassification($subNode, $drawingCollection);
        self::inflateObjectName($subNode, $drawingCollection);
        self::inflateObjectMeta($subNode, $drawingCollection);
        self::inflateDimensions($subNode, $drawingCollection);
        self::inflateDating($subNode, $drawingCollection);
        self::inflateDescription($subNode, $drawingCollection);
        self::inflateProvenance($subNode, $drawingCollection);
        self::inflateMedium($subNode, $drawingCollection);
        self::inflateSignature($subNode, $drawingCollection);
        self::inflateInscription($subNode, $drawingCollection);
        self::inflateMarkings($subNode, $drawingCollection);
        self::inflateRelatedWorks($subNode, $drawingCollection);
        self::inflateExhibitionHistory($subNode, $drawingCollection);
        self::inflateBibliography($subNode, $drawingCollection);
        self::inflateReferences($subNode, $drawingCollection);
        self::inflateSecondaryReferences($subNode, $drawingCollection);
        self::inflateAdditionalTextInformations($subNode, $drawingCollection);
        self::inflatePublications($subNode, $drawingCollection);
        self::inflateKeywords($subNode, $drawingCollection);
        self::inflateLocations($subNode, $drawingCollection);
        self::inflateRepositoryAndOwner($subNode, $drawingCollection);
        self::inflateSortingNumber($subNode, $drawingCollection);
        self::inflateCatalogWorkReference($subNode, $drawingCollection);
        self::inflateStructuredDimension($subNode, $drawingCollection);
        self::inflateIsBestOf($subNode, $drawingCollection);
        self::inflateIsPublished($subNode, $drawingCollection);
    }


    /* Involved persons */
    private static function inflateInvolvedPersons(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $details = $node->{'Section'}[1]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($details); $i += 2) {
            $personsArr = [
                new Person, // de
                new Person, // en
            ];

            $drawingCollection->get(Language::DE)->addPerson($personsArr[0]);
            $drawingCollection->get(Language::EN)->addPerson($personsArr[1]);

            for ($j = 0; $j < count($personsArr); $j += 1) {
                $currDetails = $details[$i + $j];

                if (is_null($currDetails)) {
                    continue;
                }

                /* DisplayOrder */
                $displayOrderElement = self::findElementByXPath(
                    $currDetails,
                    'Field[@FieldName="{CONXREFS.DisplayOrder}"]/FormattedValue',
                );
                if ($displayOrderElement) {
                    $displayOrder = intval(strval($displayOrderElement));
                    $personsArr[$j]->setDisplayOrder($displayOrder);
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
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $groups = $node->{'Section'}[2]->{'Subreport'}->{'Group'};

        foreach ($groups as $group) {
            $personName = new PersonName;

            $drawingCollection->addPersonName($personName);

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
        DrawingLanguageCollection $drawingCollection,
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
                    $drawingCollection->get(Language::DE)->addTitle($title);
                } elseif (self::$titlesLanguageTypes[Language::EN] === $langStr) {
                    $drawingCollection->get(Language::EN)->addTitle($title);
                } elseif (self::$titlesLanguageTypes['not_assigned'] === $langStr) {
                    echo '  Unassigned title lang for object ' . $drawingCollection->get(Language::DE)->getInventoryNumber() . "\n";
                } else {
                    echo '  Unknown title lang: ' . $langStr . ' for object \'' . $drawingCollection->get(Language::DE)->getInventoryNumber() . "'\n";
                    /* Bind the title to all drawings in the collection to prevent loss */
                    $drawingCollection->addTitle($title);
                }
            } else {
                /* Bind the title to all drawings in the collection to prevent loss */
                $drawingCollection->addTitle($title);
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
                'Field[@FieldName="{@ObjTitlesRemarks}"]/FormattedValue',
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
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $classificationSectionElement = $node->{'Section'}[4];

        $classificationDe = new Classification;
        $classificationEn = new Classification;

        $drawingCollection->get(Language::DE)->setClassification($classificationDe);
        $drawingCollection->get(Language::EN)->setClassification($classificationEn);

        /* classification */
        $classificationElement = self::findElementByXPath(
            $classificationSectionElement,
            'Field[@FieldName="{@Klassifizierung}"]/FormattedValue',
        );
        if ($classificationElement) {
            $classificationStr = trim(strval($classificationElement));

            /* Using single german value for both language objects */
            $classificationDe->setClassification($classificationStr);
            $classificationEn->setClassification('Drawing');
        }
    }


    /* Object name */
    private static function inflateObjectName(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $objectNameSectionElement = $node->{'Section'}[5];

        $objectNameElement = self::findElementByXPath(
            $objectNameSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
        );
        if ($objectNameElement) {
            $objectNameStr = trim(strval($objectNameElement));

            /* Using single german value for all language drawings in the collection */
            $drawingCollection->setObjectName($objectNameStr);
        }
    }


    /* Inventory number */
    private static function inflateInventoryNumber(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
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

            /* Using single german value for all language drawings in the collection */
            $drawingCollection->setInventoryNumber($cleanInventoryNumberStr);
        }
    }


    /* Object id & virtual (meta) */
    private static function inflateObjectMeta(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $metaSectionElement = $node->{'Section'}[7];

        /* object id */
        $objectIdElement = self::findElementByXPath(
            $metaSectionElement,
            'Field[@FieldName="{OBJECTS.ObjectID}"]/Value',
        );
        if ($objectIdElement) {
            $objectIdStr = intval(trim(strval($objectIdElement)));

            /* Using single german value for all language drawings in the collection */
            $drawingCollection->setObjectId($objectIdStr);
        }
    }


    /* Dimensions */
    private static function inflateDimensions(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
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
                $drawingCollection->get(Language::DE)->setDimensions($splitDimensionsStr[0]);
            }

            if (isset($splitDimensionsStr[1])) {
                $drawingCollection->get(Language::EN)->setDimensions($splitDimensionsStr[1]);
            }
        }
    }


    /* Dating */
    private static function inflateDating(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $datingDe = new Dating;
        $datingEn = new Dating;

        /* Using single german value for both language objects */
        $drawingCollection->get(Language::DE)->setDating($datingDe);
        $drawingCollection->get(Language::EN)->setDating($datingEn);

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

        foreach ($historicEventDetailElements as $historicEventDetailElement) {
            $historicEventInformation = new HistoricEventInformation;

            /* event type */
            $eventTypeElement = self::findElementByXPath(
                $historicEventDetailElement,
                'Field[@FieldName="{OBJDATES.EventType}"]/FormattedValue',
            );
            if ($eventTypeElement) {
                $eventTypeStr = trim(strval($eventTypeElement));
                $historicEventInformation->setEventType($eventTypeStr);
            }

            /* date text */
            $dateTextElement = self::findElementByXPath(
                $historicEventDetailElement,
                'Field[@FieldName="{OBJDATES.DateText}"]/FormattedValue',
            );
            if ($dateTextElement) {
                $dateTextStr = trim(strval($dateTextElement));
                $historicEventInformation->setText($dateTextStr);
            }

            /* begin date */
            $dateBeginElement = self::findElementByXPath(
                $historicEventDetailElement,
                'Field[@FieldName="{@Anfangsdatum}"]/FormattedValue',
            );
            if ($dateBeginElement) {
                $dateBeginNumber = intval($dateBeginElement);
                $historicEventInformation->setBegin($dateBeginNumber);
            }

            /* end date */
            $dateEndElement = self::findElementByXPath(
                $historicEventDetailElement,
                'Field[@FieldName="{@Enddatum }"]/FormattedValue',
            );
            if ($dateEndElement) {
                $dateEndNumber = intval($dateEndElement);
                $historicEventInformation->setEnd($dateEndNumber);
            }

            /* remarks */
            $dateRemarksElement = self::findElementByXPath(
                $historicEventDetailElement,
                'Field[@FieldName="{OBJDATES.Remarks}"]/FormattedValue',
            );
            if ($dateRemarksElement) {
                $dateRemarksNumber = trim(strval($dateRemarksElement));
                $historicEventInformation->setRemarks($dateRemarksNumber);
            }


            $eventType = strtolower($historicEventInformation->getEventType());

            if (empty($eventType) || $eventType === self::$historicEventTypeNotEntered) {
                continue;
            }

            if (isset(self::$historicEventTypesLangMapping[$eventType])) {
                $mappedEventType = self::$historicEventTypesLangMapping[$eventType];

                $historicEventInformation->setEventType($mappedEventType[1]);

                switch ($mappedEventType[0]) {
                    case Language::DE:
                        $datingDe->addHistoricEventInformation($historicEventInformation);
                        break;

                    case Language::EN:
                        $datingEn->addHistoricEventInformation($historicEventInformation);
                        break;
                }
            } else {
                $datingDe->addHistoricEventInformation($historicEventInformation);
                $datingEn->addHistoricEventInformation($historicEventInformation);
            }
        }

        /* IsDated */
        if (!empty($remarks = $datingDe->getRemarks())) {
            $datingDe->setIsDated(Dating::determineIfIsDated($remarks));
        }

        if (!empty($remarks = $datingEn->getRemarks())) {
            $datingEn->setIsDated(Dating::determineIfIsDated($remarks));
        }
    }


    /* Description */
    private static function inflateDescription(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $descriptionDeSectionElement = $node->{'Section'}[14];
        $descriptionElement = self::findElementByXPath(
            $descriptionDeSectionElement,
            'Field[@FieldName="{OBJECTS.Description}"]/FormattedValue',
        );
        if ($descriptionElement) {
            $descriptionStr = trim(strval($descriptionElement));
            $drawingCollection->get(Language::DE)->setDescription($descriptionStr);
        }

        /* en */
        $descriptionEnSectionElement = $node->{'Section'}[15];
        $descriptionElement = self::findElementByXPath(
            $descriptionEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText3}"]/FormattedValue',
        );
        if ($descriptionElement) {
            $descriptionStr = trim(strval($descriptionElement));
            $drawingCollection->get(Language::EN)->setDescription($descriptionStr);
        }
    }


    /* Provenance */
    private static function inflateProvenance(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $provenanceDeSectionElement = $node->{'Section'}[16];
        $provenanceElement = self::findElementByXPath(
            $provenanceDeSectionElement,
            'Field[@FieldName="{OBJECTS.Provenance}"]/FormattedValue',
        );
        if ($provenanceElement) {
            $provenanceStr = trim(strval($provenanceElement));
            $drawingCollection->get(Language::DE)->setProvenance($provenanceStr);
        }

        /* en */
        $provenanceEnSectionElement = $node->{'Section'}[17];
        $provenanceElement = self::findElementByXPath(
            $provenanceEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText5}"]/FormattedValue',
        );
        if ($provenanceElement) {
            $provenanceStr = trim(strval($provenanceElement));
            $drawingCollection->get(Language::EN)->setProvenance($provenanceStr);
        }
    }


    /* Medium */
    private static function inflateMedium(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $mediumDeSectionElement = $node->{'Section'}[18];
        $mediumElement = self::findElementByXPath(
            $mediumDeSectionElement,
            'Field[@FieldName="{OBJECTS.Medium}"]/FormattedValue',
        );
        if ($mediumElement) {
            $mediumStr = trim(strval($mediumElement));
            $drawingCollection->get(Language::DE)->setMedium($mediumStr);
        }

        /* en */
        $mediumEnSectionElement = $node->{'Section'}[19];
        $mediumElement = self::findElementByXPath(
            $mediumEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText4}"]/FormattedValue',
        );
        if ($mediumElement) {
            $mediumStr = trim(strval($mediumElement));
            $drawingCollection->get(Language::EN)->setMedium($mediumStr);
        }
    }


    /* Signature */
    private static function inflateSignature(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $signatureDeSectionElement = $node->{'Section'}[20];
        $signatureElement = self::findElementByXPath(
            $signatureDeSectionElement,
            'Field[@FieldName="{OBJECTS.PaperSupport}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim(strval($signatureElement));
            $drawingCollection->get(Language::DE)->setSignature($signatureStr);
        }

        /* en */
        $signatureEnSectionElement = $node->{'Section'}[21];
        $signatureElement = self::findElementByXPath(
            $signatureEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.ShortText6}"]/FormattedValue',
        );
        if ($signatureElement) {
            $signatureStr = trim(strval($signatureElement));
            $drawingCollection->get(Language::EN)->setSignature($signatureStr);
        }
    }


    /* Inscription */
    private static function inflateInscription(
        SimpleXMLElement &$node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $inscriptionDeSectionElement = $node->{'Section'}[22];
        $inscriptionElement = self::findElementByXPath(
            $inscriptionDeSectionElement,
            'Field[@FieldName="{OBJECTS.Inscribed}"]/FormattedValue',
        );
        if ($inscriptionElement) {
            $inscriptionStr = trim(strval($inscriptionElement));
            $drawingCollection->get(Language::DE)->setInscription($inscriptionStr);
        }

        /* en */
        $inscriptionEnSectionElement = $node->{'Section'}[23];
        $inscriptionElement = self::findElementByXPath(
            $inscriptionEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText7}"]/FormattedValue',
        );
        if ($inscriptionElement) {
            $inscriptionStr = trim(strval($inscriptionElement));
            $drawingCollection->get(Language::EN)->setInscription($inscriptionStr);
        }
    }


    /* Markings */
    private static function inflateMarkings(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $markingsDeSectionElement = $node->{'Section'}[24];
        $markingsElement = self::findElementByXPath(
            $markingsDeSectionElement,
            'Field[@FieldName="{OBJECTS.Markings}"]/FormattedValue',
        );
        if ($markingsElement) {
            $markingsStr = trim(strval($markingsElement));
            $drawingCollection->get(Language::DE)->setMarkings($markingsStr);
        }

        /* en */
        $markingsEnSectionElement = $node->{'Section'}[25];
        $markingsElement = self::findElementByXPath(
            $markingsEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText9}"]/FormattedValue',
        );
        if ($markingsElement) {
            $markingsStr = trim(strval($markingsElement));
            $drawingCollection->get(Language::EN)->setMarkings($markingsStr);
        }
    }


    /* Related works */
    private static function inflateRelatedWorks(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $relatedWorksDeSectionElement = $node->{'Section'}[26];
        $relatedWorksElement = self::findElementByXPath(
            $relatedWorksDeSectionElement,
            'Field[@FieldName="{OBJECTS.RelatedWorks}"]/FormattedValue',
        );
        if ($relatedWorksElement) {
            $relatedWorksStr = trim(strval($relatedWorksElement));
            $drawingCollection->get(Language::DE)->setRelatedWorks($relatedWorksStr);
        }

        /* en */
        $relatedWorksEnSectionElement = $node->{'Section'}[27];
        $relatedWorksElement = self::findElementByXPath(
            $relatedWorksEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText6}"]/FormattedValue',
        );
        if ($relatedWorksElement) {
            $relatedWorksStr = trim(strval($relatedWorksElement));
            $drawingCollection->get(Language::EN)->setRelatedWorks($relatedWorksStr);
        }
    }


    /* Exhibition history */
    private static function inflateExhibitionHistory(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        /* de */
        $exhibitionHistoryDeSectionElement = $node->{'Section'}[28];
        $exhibitionHistoryElement = self::findElementByXPath(
            $exhibitionHistoryDeSectionElement,
            'Field[@FieldName="{OBJECTS.Exhibitions}"]/FormattedValue',
        );
        if ($exhibitionHistoryElement) {
            $exhibitionHistoryStr = trim(strval($exhibitionHistoryElement));
            $cleanExhibitionHistoryStr = preg_replace(
                self::$inventoryNumberReplaceRegExpArr,
                '',
                $exhibitionHistoryStr,
            );
            $drawingCollection->get(Language::DE)->setExhibitionHistory($cleanExhibitionHistoryStr);
        }

        /* en */
        $exhibitionHistoryEnSectionElement = $node->{'Section'}[29];
        $exhibitionHistoryElement = self::findElementByXPath(
            $exhibitionHistoryEnSectionElement,
            'Field[@FieldName="{OBJCONTEXT.LongText8}"]/FormattedValue',
        );
        if ($exhibitionHistoryElement) {
            $exhibitionHistoryStr = trim(strval($exhibitionHistoryElement));
            $cleanExhibitionHistoryStr = preg_replace(
                self::$inventoryNumberReplaceRegExpArr,
                '',
                $exhibitionHistoryStr,
            );
            $drawingCollection->get(Language::EN)->setExhibitionHistory($cleanExhibitionHistoryStr);
        }
    }


    /* Bibliography */
    private static function inflateBibliography(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $bibliographySectionElement = $node->{'Section'}[30];
        $bibliographyElement = self::findElementByXPath(
            $bibliographySectionElement,
            'Field[@FieldName="{OBJECTS.Bibliography}"]/FormattedValue',
        );
        if ($bibliographyElement) {
            $bibliographyStr = trim(strval($bibliographyElement));

            $drawingCollection->setBibliography($bibliographyStr);
        }
    }


    /* References */
    private static function inflateReferences(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $referenceDetailsElements = $node->{'Section'}[31]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($referenceDetailsElements); $i += 1) {
            $referenceDetailElement = $referenceDetailsElements[$i];

            if ($referenceDetailElement->count() === 0) {
                continue;
            }

            $referenceDe = new ObjectReference;
            $referenceEn = new ObjectReference;

            $drawingCollection->get(Language::DE)->addReference($referenceDe);
            $drawingCollection->get(Language::EN)->addReference($referenceEn);

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
                    $drawingCollection->get(Language::DE)->getInventoryNumber(),
                    $remarksStr,
                    $referenceDe,
                    $referenceEn,
                );
            }
        }
    }


    /* Secondary References */
    private static function inflateSecondaryReferences(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $referenceDetailsElements = $node->{'Section'}[32]->{'Subreport'}->{':Details'};

        for ($i = 0; $i < count($referenceDetailsElements); $i += 1) {
            $referenceDetailElement = $referenceDetailsElements[$i];

            if ($referenceDetailElement->count() === 0) {
                continue;
            }

            $referenceDe = new ObjectReference;
            $referenceEn = new ObjectReference;

            $drawingCollection->get(Language::DE)->addReference($referenceDe);
            $drawingCollection->get(Language::EN)->addReference($referenceEn);

            /* Text */
            $textElement = self::findElementByXPath(
                $referenceDetailElement,
                'Section[@SectionNumber="0"]/Text[@Name="Text5"]/TextValue',
            );
            if ($textElement) {
                $textStr = trim(strval($textElement));
                $referenceDe->setText($textStr);
                $referenceEn->setText($textStr);
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
                    $drawingCollection->get(Language::DE)->getInventoryNumber(),
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
        ObjectReference $referenceEn,
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


    /* Additional text informations */
    private static function inflateAdditionalTextInformations(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $drawingDe = $drawingCollection->get(Language::DE);
        $drawingEn = $drawingCollection->get(Language::EN);

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
                    $drawingDe->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes[Language::EN] === $textTypeStr) {
                    $drawingEn->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes['author'] === $textTypeStr) {
                    $drawingDe->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes['letter'] === $textTypeStr) {
                    $drawingEn->addAdditionalTextInformation($additionalTextInformation);
                } elseif (self::$additionalTextLanguageTypes['not_assigned'] === $textTypeStr) {
                    echo '  Unassigned additional text type for object \'' . $drawingDe->getInventoryNumber() . "'\n";
                    $drawingDe->addAdditionalTextInformation($additionalTextInformation);
                    $drawingEn->addAdditionalTextInformation($additionalTextInformation);
                } else {
                    echo '  Unknown additional text type: ' . $textTypeStr . ' for object ' . $drawingDe->getInventoryNumber() . "\n";
                    $drawingCollection->addAdditionalTextInformation($additionalTextInformation);
                }
            } else {
                $drawingCollection->addAdditionalTextInformation($additionalTextInformation);
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
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $drawingDe = $drawingCollection->get(Language::DE);
        $drawingEn = $drawingCollection->get(Language::EN);

        $publicationDetailsElements = $node->{'Section'}[34]->{'Subreport'}->{'Details'};

        for ($i = 0; $i < count($publicationDetailsElements); $i += 1) {
            $publicationDetailElement = $publicationDetailsElements[$i];

            if ($publicationDetailElement->count() === 0) {
                continue;
            }

            $publication = new Publication;

            $drawingDe->addPublication($publication);
            $drawingEn->addPublication($publication);

            /* Title */
            $titleElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="0"]/Field[@FieldName="{REFERENCEMASTER.Heading}"]/FormattedValue',
            );
            if ($titleElement) {
                $titleStr = trim(strval($titleElement));
                $publication->setTitle($titleStr);
            }

            /* Pagenumber */
            $pageNumberElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PageNumber}"]/FormattedValue',
            );
            if ($pageNumberElement) {
                $pageNumberStr = trim(strval($pageNumberElement));
                $publication->setPageNumber($pageNumberStr);
            }

            /* Reference */
            $referenceIdElement = self::findElementByXPath(
                $publicationDetailElement,
                'Section[@SectionNumber="2"]/Field[@FieldName="{REFERENCEMASTER.ReferenceID}"]/FormattedValue',
            );
            if ($referenceIdElement) {
                $referenceIdStr = trim(strval($referenceIdElement));
                $publication->setReferenceId($referenceIdStr);
            }
        }
    }


    /* Keywords */
    private static function inflateKeywords(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
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
            $fieldnames = ['{THESXREFSPATH1.Path}', '{THESXREFSPATH2.Path}'];
            foreach ($fieldnames as $fieldname) {
                $keywordPathElement = self::findElementByXPath(
                    $keywordDetailElement,
                    'Section[@SectionNumber="3"]/Field[@FieldName="' . $fieldname . '"]/FormattedValue',
                );
                if ($keywordPathElement) {
                    $keywordPathStr = trim(strval($keywordPathElement));
                    $metaReference->setPath($keywordPathStr);
                }
            }

            /* Decide if keyword is valid */
            if (!empty($metaReference->getTerm())) {
                $drawingCollection->addKeyword($metaReference);
            }
        }
    }


    /* Locations */
    private static function inflateLocations(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $drawingDe = $drawingCollection->get(Language::DE);
        $drawingEn = $drawingCollection->get(Language::EN);

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

            /* Language determination */
            if ($locationTypeElement) {
                $locationTypeStr = trim(strval($locationTypeElement));
                $metaReference->setType($locationTypeStr);

                if (!empty($locationTypeStr)) {
                    if (self::$locationLanguageTypes[Language::DE] === $locationTypeStr) {
                        $drawingDe->addLocation($metaReference);
                    } elseif (self::$locationLanguageTypes[Language::EN] === $locationTypeStr) {
                        $drawingEn->addLocation($metaReference);
                    } elseif (self::$locationLanguageTypes['not_assigned'] === $locationTypeStr) {
                        echo '  Unassigned location type for object ' . $drawingDe->getInventoryNumber() . "\n";
                        $drawingCollection->addLocation($metaReference);
                    } else {
                        echo '  Unknown location type: ' . $locationTypeStr . ' for object ' . $drawingDe->getInventoryNumber() . "\n";
                        $drawingCollection->addLocation($metaReference);
                    }
                }
            }
        }
    }


    /* Repository and Owner */
    private static function inflateRepositoryAndOwner(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
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
                $isRepository = self::inflateRepository($detail, $roleName, $drawingCollection);
            } catch (Error $e) {
                echo '  ' . $e->getMessage() . "\n";
            }

            try {
                $isOwner = self::inflateOwner($detail, $roleName, $drawingCollection);
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
        SimpleXMLElement &$detail,
        string $roleName,
        DrawingLanguageCollection $drawingCollection,
    ): bool {
        $drawingDe = $drawingCollection->get(Language::DE);
        $drawingEn = $drawingCollection->get(Language::EN);

        $repositoryElement = self::findElementByXPath(
            $detail,
            'Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
        );

        if (!$repositoryElement) {
            throw new Error('Missing element with repository name!');
        }

        $repositoryStr = trim(strval($repositoryElement));

        switch ($roleName) {
            case self::$repositoryTypes[Language::DE]: /* de */
                $drawingDe->setRepository($repositoryStr);
                break;

            case self::$repositoryTypes[Language::EN]: /* en */
                $drawingEn->setRepository($repositoryStr);
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
        DrawingLanguageCollection $drawingCollection,
    ): bool {
        $drawingDe = $drawingCollection->get(Language::DE);
        $drawingEn = $drawingCollection->get(Language::EN);

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
                $drawingDe->setOwner($ownerStr);
                break;

            case self::$ownerTypes[Language::EN]:
                /* en */
                $drawingEn->setOwner($ownerStr);
                break;

            default:
                return false;
        }

        return true;
    }


    /* Sorting number */
    private static function inflateSortingNumber(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $sortingNumberSubreport = $node->{'Section'}[38];

        $sortingNumberElement = self::findElementByXPath(
            $sortingNumberSubreport,
            'Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
        );
        if ($sortingNumberElement) {
            $sortingNumberStr = trim(strval($sortingNumberElement));

            if (empty($sortingNumberStr)) {
                $sortingNumberStr = self::$sortingNumberFallbackValue;
            }

            $drawingCollection->setSortingNumber($sortingNumberStr);
        }
    }


    /* Catalog work reference */
    private static function inflateCatalogWorkReference(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $catalogWorkReferenceDetailsElements = $node->{'Section'}[39]->{'Subreport'}->{'Details'};

        foreach ($catalogWorkReferenceDetailsElements as $detailElement) {
            $catalogWorkReference = new CatalogWorkReference;

            /* Description */
            $descriptionElement = self::findElementByXPath(
                $detailElement,
                'Field[@FieldName="{AltNumDescriptions.AltNumDescription}"]/FormattedValue',
            );
            if ($descriptionElement) {
                $descriptionStr = trim(strval($descriptionElement));

                $catalogWorkReference->setDescription($descriptionStr);
            }

            /* Reference number */
            $referenceNumberElement = self::findElementByXPath(
                $detailElement,
                'Field[@FieldName="{AltNums.AltNum}"]/FormattedValue',
            );
            if ($referenceNumberElement) {
                $referenceNumberStr = trim(strval($referenceNumberElement));

                $catalogWorkReference->setReferenceNumber($referenceNumberStr);
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
                $drawingCollection->addCatalogWorkReference($catalogWorkReference);
            }
        }
    }


    /* Structured dimension */
    private static function inflateStructuredDimension(
        SimpleXMLElement $node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $catalogWorkReferenceSubreport = $node->{'Section'}[40]->{'Subreport'};

        $structuredDimension = new StructuredDimension;

        $drawingCollection->setStructuredDimension($structuredDimension);

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


    /* Structured dimension */
    private static function inflateIsBestOf(
        SimpleXMLElement &$node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $isBestOfElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="41"]',
        );

        $isBestOf = $isBestOfElement !== false;

        $drawingCollection->setIsBestOf($isBestOf);
    }


    private static function inflateIsPublished(
        SimpleXMLElement &$node,
        DrawingLanguageCollection $drawingCollection,
    ): void {
        $isPublishedElement = self::findElementByXPath(
            $node,
            'Section[@SectionNumber="42"]/Field[@FieldName="{@CDA Online-Freigabe}"]/Value',
        );

        $isPublished = $isPublishedElement !== false && strval($isPublishedElement) === self::$isPublishedString;

        foreach ($drawingCollection as $drawing) {
            $drawing->getMetadata()?->setIsPublished($isPublished);
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
