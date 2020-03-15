<?php

namespace CranachImport\Jobs\XML\Inflators;

require_once 'IGraphicInflator.php';
require_once 'entities/Graphic.php';

require_once 'entities/main/Person.php';
require_once 'entities/main/PersonName.php';
require_once 'entities/main/PersonNameDetail.php';
require_once 'entities/main/Title.php';
require_once 'entities/main/Dating.php';
require_once 'entities/main/HistoricEventInformation.php';
require_once 'entities/main/ObjectReference.php';
require_once 'entities/main/AdditionalTextInformation.php';
require_once 'entities/main/Publication.php';
require_once 'entities/main/MetaReference.php';
require_once 'entities/main/CatalogWorkReference.php';
require_once 'entities/main/StructuredDimension.php';

require_once 'entities/graphic/Classification.php';


use CranachImport\Entities\Graphic;

use CranachImport\Entities\Main\Person;
use CranachImport\Entities\Main\PersonName;
use CranachImport\Entities\Main\PersonNameDetail;
use CranachImport\Entities\Main\Title;
use CranachImport\Entities\Main\Dating;
use CranachImport\Entities\Main\HistoricEventInformation;
use CranachImport\Entities\Main\ObjectReference;
use CranachImport\Entities\Main\AdditionalTextInformation;
use CranachImport\Entities\Main\Publication;
use CranachImport\Entities\Main\MetaReference;
use CranachImport\Entities\Main\CatalogWorkReference;
use CranachImport\Entities\Main\StructuredDimension;

use CranachImport\Entities\Graphic\Classification;


/**
 * Graphics inflator used to inflate german and english graphic instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class GraphicsInflator implements IGraphicInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';
	private static $langSplitChar = '#';

	private static $additionalTextLanguageTypes = [
		'de' => 'Beschreibung/ Interpretation/ Kommentare',
		'en' => 'Description/ Interpretation/ Comments',
		'not_assigned' => '(not assigned)',
	];

	private static $locationLanguageTypes = [
		'de' => 'Standort Cranach Objekt',
		'en' => 'Location Cranach Object',
		'not_assigned' => '(not assigned)',
	];

	private static $titlesLanguageTypes = [
		'de' => 'GERMAN',
		'en' => 'ENGLISH',
		'not_assigned' => '(not assigned)',
	];

	private static $repositoryTypes = [
		'de' => 'Besitzer',
		'en' => 'Repository',
	];

	private static $ownerTypes = [
		'de' => 'Eigentümer',
		'en' => 'Owner',
	];

	private static $referenceTypeValues = [
		'reprint' => 'Abzug A',
		'relatedWork' => 'Teil eines Werkes',
	];

	private static $inventoryNumberReplaceRegExpArr = [
		'/^CDA\./',
		'/^G_/',
	];

	private static $catalogWrokReferenceReplaceArr = [
		'-Nummer',
	];

	private static $activeLoggingOfWronglyCategorizedReferences = false;

	private function __construct() {}


	public static function inflate(\SimpleXMLElement &$node,
	                               Graphic &$graphicDe,
	                               Graphic &$graphicEn) {
		$subNode = $node->GroupHeader;

		self::registerXPathNamespace($subNode);

		self::inflateInvolvedPersons($subNode, $graphicDe, $graphicEn);
		self::inflatePersonNames($subNode, $graphicDe, $graphicEn);
		self::inflateTitles($subNode, $graphicDe, $graphicEn);
		self::inflateClassification($subNode, $graphicDe, $graphicEn);
		self::inflateObjectName($subNode, $graphicDe, $graphicEn);
		self::inflateInventoryNumber($subNode, $graphicDe, $graphicEn);
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
		self::inflateExhibitionHistory($subNode, $graphicDe, $graphicEn);
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
	private static function inflateInvolvedPersons(\SimpleXMLElement &$node,
	                                               Graphic &$graphicDe,
	                                               Graphic &$graphicEn) {
		$details = $node->Section[1]->Subreport->Details;

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
					$roleStr = trim($roleElement);
					$personsArr[$j]->setRole($roleStr);
				}

				/* name */
				$nameElement = self::findElementByXPath(
					$currDetails,
					'Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
				);
				if ($nameElement) {
					$nameStr = trim($nameElement);
					$personsArr[$j]->setName($nameStr);
				}

				/* prefix */
				$prefixElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="3"]//FormattedValue',
				);
				if ($prefixElement) {
					$prefixStr = trim($prefixElement);
					$personsArr[$j]->setPrefix($prefixStr);
				}

				/* suffix */
				$suffixElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="4"]//FormattedValue',
				);
				if ($suffixElement) {
					$suffixStr = trim($suffixElement);
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

					$unknownPersonRoleStr = trim($unknownPersonRoleElement);
					$personsArr[$j]->setRole($unknownPersonRoleStr);
				}

				/* prefix of unknown person */
				$unknownPersonPrefixElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="7"]//FormattedValue',
				);
				if ($unknownPersonPrefixElement) {
					$unknownPersonPrefixStr = trim($unknownPersonPrefixElement);
					$personsArr[$j]->setPrefix($unknownPersonPrefixStr);
				}

				/* suffix of unknown person */
				$unknownPersonSuffixElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="8"]//FormattedValue',
				);
				if ($unknownPersonSuffixElement) {
					$unknownPersonSuffixStr = trim($unknownPersonSuffixElement);
					$personsArr[$j]->setSuffix($unknownPersonSuffixStr);
				}

				/* name type */
				$nameTypeElement = self::findElementByXPath(
					$currDetails,
					'Field[@FieldName="{@Nametype}"]/FormattedValue',
				);
				if ($nameTypeElement) {
					$nameTypeStr = trim($nameTypeElement);
					$personsArr[$j]->setNameType($nameTypeStr);
				}

				/* alternative name */
				$alternativeNameElement = self::findElementByXPath(
					$currDetails,
					'Field[@FieldName="{@AndererName}"]/FormattedValue',
				);
				if ($alternativeNameElement) {
					$alternativeNameStr = trim($alternativeNameElement);
					$personsArr[$j]->setAlternativeName($alternativeNameStr);
				}

				/* remarks */
				$remarksElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="11"]//FormattedValue',
				);
				if ($remarksElement) {
					$remarksNameStr = trim($remarksElement);
					$personsArr[$j]->setRemarks($remarksNameStr);
				}

				/* date */
				$dateElement = self::findElementByXPath(
					$currDetails,
					'Section[@SectionNumber="12"]//FormattedValue',
				);
				if ($dateElement) {
					$dateStr = trim($dateElement);
					$personsArr[$j]->setDate($dateStr);
				}
			}
		}
	}


	/* Person names */
	private static function inflatePersonNames(\SimpleXMLElement &$node,
	                                           Graphic &$graphicDe,
	                                           Graphic &$graphicEn) {
		$groups = $node->Section[2]->Subreport->Group;

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
				$constituentIdStr = trim($constituentIdElement);
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
					$detailNameStr = trim($detailNameElement);
					$personDetailName->setName($detailNameStr);
				}

				/* type */
				$detailNameTypeElement = self::findElementByXPath(
					$nameDetailGroup,
					'Field[@FieldName="GroupName ({CONALTNAMES.NameType})"]/FormattedValue',
				);
				if ($detailNameTypeElement) {
					$detailNameTypeStr = trim($detailNameTypeElement);
					$personDetailName->setNameType($detailNameTypeStr);
				}
			}
		}
	}


	/* Titles */
	private static function inflateTitles(\SimpleXMLElement &$node,
	                                      Graphic &$graphicDe,
	                                      Graphic &$graphicEn) {
		$titleDetailElements = $node->Section[3]->Subreport->Details;

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
				$langStr = trim($langElement);

				if (self::$titlesLanguageTypes['de'] === $langStr) {
					$graphicDe->addTitle($title);
				} else if (self::$titlesLanguageTypes['en'] === $langStr) {
					$graphicEn->addTitle($title);
				} else if(self::$titlesLanguageTypes['not_assigned'] === $langStr) {
					echo '  Unassigned title lang for object ' . $graphicDe->getInventoryNumber() . "\n";
				} else {
					echo '  Unknown title lang: ' . $langStr . ' for object ' . $graphicDe->getInventoryNumber() . "\n";
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
				$typeStr = trim($typeElement);
				$title->setType($typeStr);
			}

			/* title */
			$titleElement = self::findElementByXPath(
				$titleDetailElement,
				'Field[@FieldName="{OBJTITLES.Title}"]/FormattedValue',
			);
			if ($titleElement) {
				$titleStr = trim($titleElement);
				$title->setTitle($titleStr);
			}

			/* remark */
			$remarksElement = self::findElementByXPath(
				$titleDetailElement,
				'Field[@FieldName="{OBJTITLES.Remarks}"]/FormattedValue',
			);
			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$title->setRemarks($remarksStr);
			}
		}
	}


	/* Classification */
	private static function inflateClassification(\SimpleXMLElement &$node,
	                                              Graphic &$graphicDe,
	                                              Graphic &$graphicEn) {
		$classificationSectionElement = $node->Section[4];

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
			$classificationStr = trim($classificationElement);

			/* Using single german value for both language objects */
			$classificationDe->setClassification($classificationStr);
			$classificationEn->setClassification($classificationStr);
		}

		/* condition */
		$stateElement = self::findElementByXPath(
			$classificationSectionElement,
			'Field[@FieldName="{@Druckzustand}"]/FormattedValue',
		);
		if ($stateElement) {
			$stateStr = trim($stateElement);

			$splitStateStr = self::splitLanguageString($stateStr);

			if (isset($splitStateStr[0])) {
				$classificationDe->setCondition($splitStateStr[0]);
			}

			if (isset($splitStateStr[1])) {
				$classificationEn->setCondition($splitStateStr[1]);
			}
		}
	}

	/* Object name */
	private static function inflateObjectName(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$objectNameSectionElement = $node->Section[5];

		$objectNameElement = self::findElementByXPath(
			$objectNameSectionElement,
			'Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
		);
		if ($objectNameElement) {
			$objectNameStr = trim($objectNameElement);

			/* Using single german value for both language objects */
			$graphicDe->setObjectName($objectNameStr);
			$graphicEn->setObjectName($objectNameStr);
		}
	}


	/* Inventory number */
	private static function inflateInventoryNumber(\SimpleXMLElement &$node,
	                                               Graphic &$graphicDe,
	                                               Graphic &$graphicEn) {
		$inventoryNumberSectionElement = $node->Section[6];

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

			/* Using single german value for both language objects */
			$graphicDe->setInventoryNumber($cleanInventoryNumberStr);
			$graphicEn->setInventoryNumber($cleanInventoryNumberStr);
		}
	}


	/* Object id & virtual (meta) */
	private static function inflateObjectMeta(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$metaSectionElement = $node->Section[7];

		/* object id */
		$objectIdElement = self::findElementByXPath(
			$metaSectionElement,
			'Field[@FieldName="{OBJECTS.ObjectID}"]/Value',
		);
		if ($objectIdElement) {
			$objectIdStr = intval(trim($objectIdElement));

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
			$virtualStr = trim($virtualElement);

			$isVirtual = ($virtualStr === '1');

			/* Using single german value for both language objects */
			$graphicDe->setIsVirtual($isVirtual);
			$graphicEn->setIsVirtual($isVirtual);
		}
	}


	/* Dimensions */
	private static function inflateDimensions(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$metaSectionElement = $node->Section[8];

		/* object id */
		$dimensionsElement = self::findElementByXPath(
			$metaSectionElement,
			'Field[@FieldName="{OBJECTS.Dimensions}"]/FormattedValue',
		);
		if ($dimensionsElement) {
			$dimensionsStr = trim($dimensionsElement);

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
	private static function inflateDating(\SimpleXMLElement &$node,
	                                      Graphic &$graphicDe,
	                                      Graphic &$graphicEn) {
		$datingDe = new Dating;
		$datingEn = new Dating;

		/* Using single german value for both language objects */
		$graphicDe->setDating($datingDe);
		$graphicEn->setDating($datingEn);

		/* Dated (string) */
		$datedSectionElement = $node->Section[9];

		$datedElement = self::findElementByXPath(
			$datedSectionElement,
			'Field[@FieldName="{OBJECTS.Dated}"]/FormattedValue',
		);
		if ($datedElement) {
			$datedDateStr = trim($datedElement);

			$splitStateStr = self::splitLanguageString($datedDateStr);

			if (isset($splitStateStr[0])) {
				$datingDe->setDated($splitStateStr[0]);
			}

			if (isset($splitStateStr[1])) {
				$datingEn->setDated($splitStateStr[1]);
			}
		}

		/* Date begin */
		$dateBeginSectionElement = $node->Section[10];

		$dateBeginElement = self::findElementByXPath(
			$dateBeginSectionElement,
			'Field[@FieldName="{OBJECTS.DateBegin}"]/FormattedValue',
		);
		if ($dateBeginElement) {
			$dateBeginStr = intval(trim($dateBeginElement));

			$datingDe->setBegin($dateBeginStr);
			$datingEn->setBegin($dateBeginStr);
		}

		/* Date end */
		$dateEndSectionElement = $node->Section[11];

		$dateEndElement = self::findElementByXPath(
			$dateEndSectionElement,
			'Field[@FieldName="{OBJECTS.DateEnd}"]/FormattedValue',
		);
		if ($dateEndElement) {
			$dateEndStr = intval(trim($dateEndElement));

			$datingDe->setEnd($dateEndStr);
			$datingEn->setEnd($dateEndStr);
		}

		/* Remarks */
		$remarksSectionElement = $node->Section[12];

		$remarksElement = self::findElementByXPath(
			$remarksSectionElement,
			'Field[@FieldName="{OBJECTS.DateRemarks}"]/FormattedValue',
		);
		if ($remarksElement) {
			$remarksStr = trim($remarksElement);

			$splitRemarksStr = self::splitLanguageString($remarksStr);

			if (isset($splitRemarksStr[0])) {
				$datingDe->setRemarks($splitRemarksStr[0]);
			}

			if (isset($splitRemarksStr[1])) {
				$datingEn->setRemarks($splitRemarksStr[1]);
			}
		}

		/* HistoricEventInformation */
		$historicEventDetailElements = $node->Section[13]->Subreport->Details;

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
					$eventTypeStr = trim($eventTypeElement);
					$historicEventArr[$j]->setEventType($eventTypeStr);
				}

				/* date text */
				$dateTextElement = self::findElementByXPath(
					$historicEventDetailElement,
					'Field[@FieldName="{OBJDATES.DateText}"]/FormattedValue',
				);
				if ($dateTextElement) {
					$dateTextStr = trim($dateTextElement);
					$historicEventArr[$j]->setText($dateTextStr);
				}

				/* begin date */
				$dateBeginElement = self::findElementByXPath(
					$historicEventDetailElement,
					'Field[@FieldName="{@Anfangsdatum}"]/FormattedValue',
				);
				if ($dateBeginElement) {
					$dateBeginNumber = intval(trim($dateBeginElement));
					$historicEventArr[$j]->setBegin($dateBeginNumber);
				}

				/* end date */
				$dateEndElement = self::findElementByXPath(
					$historicEventDetailElement,
					'Field[@FieldName="{@Enddatum }"]/FormattedValue',
				);
				if ($dateEndElement) {
					$dateEndNumber = intval(trim($dateEndElement));
					$historicEventArr[$j]->setEnd($dateEndNumber);
				}

				/* remarks */
				$dateRemarksElement = self::findElementByXPath(
					$historicEventDetailElement,
					'Field[@FieldName="{OBJDATES.Remarks}"]/FormattedValue',
				);
				if ($dateRemarksElement) {
					$dateRemarksNumber = trim($dateRemarksElement);
					$historicEventArr[$j]->setRemarks($dateRemarksNumber);
				}
			}
		}
	}


	/* Description */
	private static function inflateDescription(\SimpleXMLElement &$node,
	                                           Graphic &$graphicDe,
	                                           Graphic &$graphicEn) {
		/* de */
		$descriptionDeSectionElement = $node->Section[14];
		$descriptionElement = self::findElementByXPath(
			$descriptionDeSectionElement,
			'Field[@FieldName="{OBJECTS.Description}"]/FormattedValue',
		);
		if ($descriptionElement) {
			$descriptionStr = trim($descriptionElement);
			$graphicDe->setDescription($descriptionStr);
		}

		/* en */
		$descriptionEnSectionElement = $node->Section[15];
		$descriptionElement = self::findElementByXPath(
			$descriptionEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText3}"]/FormattedValue',
		);
		if ($descriptionElement) {
			$descriptionStr = trim($descriptionElement);
			$graphicEn->setDescription($descriptionStr);
		}
	}


	/* Provenance */
	private static function inflateProvenance(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		/* de */
		$provenanceDeSectionElement = $node->Section[16];
		$provenanceElement = self::findElementByXPath(
			$provenanceDeSectionElement,
			'Field[@FieldName="{OBJECTS.Provenance}"]/FormattedValue',
		);
		if ($provenanceElement) {
			$provenanceStr = trim($provenanceElement);
			$graphicDe->setProvenance($provenanceStr);
		}

		/* en */
		$provenanceEnSectionElement = $node->Section[17];
		$provenanceElement = self::findElementByXPath(
			$provenanceEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText5}"]/FormattedValue',
		);
		if ($provenanceElement) {
			$provenanceStr = trim($provenanceElement);
			$graphicEn->setProvenance($provenanceStr);
		}
	}


	/* Medium */
	private static function inflateMedium(\SimpleXMLElement &$node,
	                                        Graphic &$graphicDe,
	                                        Graphic &$graphicEn) {
		/* de */
		$mediumDeSectionElement = $node->Section[18];
		$mediumElement = self::findElementByXPath(
			$mediumDeSectionElement,
			'Field[@FieldName="{OBJECTS.Medium}"]/FormattedValue',
		);
		if ($mediumElement) {
			$mediumStr = trim($mediumElement);
			$graphicDe->setMedium($mediumStr);
		}

		/* en */
		$mediumEnSectionElement = $node->Section[19];
		$mediumElement = self::findElementByXPath(
			$mediumEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText4}"]/FormattedValue',
		);
		if ($mediumElement) {
			$mediumStr = trim($mediumElement);
			$graphicEn->setMedium($mediumStr);
		}
	}


	/* Signature */
	private static function inflateSignature(\SimpleXMLElement &$node,
	                                         Graphic &$graphicDe,
	                                         Graphic &$graphicEn) {
		/* de */
		$signatureDeSectionElement = $node->Section[20];
		$signatureElement = self::findElementByXPath(
			$signatureDeSectionElement,
			'Field[@FieldName="{OBJECTS.PaperSupport}"]/FormattedValue',
		);
		if ($signatureElement) {
			$signatureStr = trim($signatureElement);
			$graphicDe->setSignature($signatureStr);
		}

		/* en */
		$signatureEnSectionElement = $node->Section[21];
		$signatureElement = self::findElementByXPath(
			$signatureEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.ShortText6}"]/FormattedValue',
		);
		if ($signatureElement) {
			$signatureStr = trim($signatureElement);
			$graphicEn->setSignature($signatureStr);
		}
	}


	/* Inscription */
	private static function inflateInscription(\SimpleXMLElement &$node,
	                                           Graphic &$graphicDe,
	                                           Graphic &$graphicEn) {
		/* de */
		$inscriptionDeSectionElement = $node->Section[22];
		$inscriptionElement = self::findElementByXPath(
			$inscriptionDeSectionElement,
			'Field[@FieldName="{OBJECTS.Inscribed}"]/FormattedValue',
		);
		if ($inscriptionElement) {
			$inscriptionStr = trim($inscriptionElement);
			$graphicDe->setInscription($inscriptionStr);
		}

		/* en */
		$inscriptionEnSectionElement = $node->Section[23];
		$inscriptionElement = self::findElementByXPath(
			$inscriptionEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText7}"]/FormattedValue',
		);
		if ($inscriptionElement) {
			$inscriptionStr = trim($inscriptionElement);
			$graphicEn->setInscription($inscriptionStr);
		}
	}


	/* Markings */
	private static function inflateMarkings(\SimpleXMLElement &$node,
	                                        Graphic &$graphicDe,
	                                        Graphic &$graphicEn) {
		/* de */
		$markingsDeSectionElement = $node->Section[24];
		$markingsElement = self::findElementByXPath(
			$markingsDeSectionElement,
			'Field[@FieldName="{OBJECTS.Markings}"]/FormattedValue',
		);
		if ($markingsElement) {
			$markingsStr = trim($markingsElement);
			$graphicDe->setMarkings($markingsStr);
		}

		/* en */
		$markingsEnSectionElement = $node->Section[25];
		$markingsElement = self::findElementByXPath(
			$markingsEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText9}"]/FormattedValue',
		);
		if ($markingsElement) {
			$markingsStr = trim($markingsElement);
			$graphicEn->setMarkings($markingsStr);
		}
	}


	/* Related works */
	private static function inflateRelatedWorks(\SimpleXMLElement &$node,
	                                            Graphic &$graphicDe,
	                                            Graphic &$graphicEn) {
		/* de */
		$relatedWorksDeSectionElement = $node->Section[26];
		$relatedWorksElement = self::findElementByXPath(
			$relatedWorksDeSectionElement,
			'Field[@FieldName="{OBJECTS.RelatedWorks}"]/FormattedValue',
		);
		if ($relatedWorksElement) {
			$relatedWorksStr = trim($relatedWorksElement);
			$graphicDe->setRelatedWorks($relatedWorksStr);
		}

		/* en */
		$relatedWorksEnSectionElement = $node->Section[27];
		$relatedWorksElement = self::findElementByXPath(
			$relatedWorksEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText6}"]/FormattedValue',
		);
		if ($relatedWorksElement) {
			$relatedWorksStr = trim($relatedWorksElement);
			$graphicEn->setRelatedWorks($relatedWorksStr);
		}
	}


	/* Exhibition history */
	private static function inflateExhibitionHistory(\SimpleXMLElement &$node,
	                                                 Graphic &$graphicDe,
	                                                 Graphic &$graphicEn) {
		/* de */
		$exhibitionHistoryDeSectionElement = $node->Section[28];
		$exhibitionHistoryElement = self::findElementByXPath(
			$exhibitionHistoryDeSectionElement,
			'Field[@FieldName="{OBJECTS.Exhibitions}"]/FormattedValue',
		);
		if ($exhibitionHistoryElement) {
			$exhibitionHistoryStr = trim($exhibitionHistoryElement);
			$cleanExhibitionHistoryStr = preg_replace(
				self::$inventoryNumberReplaceRegExpArr,
				'',
				$exhibitionHistoryStr,
			);
			$graphicDe->setExhibitionHistory($cleanExhibitionHistoryStr);
		}

		/* en */
		$exhibitionHistoryEnSectionElement = $node->Section[29];
		$exhibitionHistoryElement = self::findElementByXPath(
			$exhibitionHistoryEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LongText8}"]/FormattedValue',
		);
		if ($exhibitionHistoryElement) {
			$exhibitionHistoryStr = trim($exhibitionHistoryElement);
			$cleanExhibitionHistoryStr = preg_replace(
				self::$inventoryNumberReplaceRegExpArr,
				'',
				$exhibitionHistoryStr,
			);
			$graphicEn->setExhibitionHistory($cleanExhibitionHistoryStr);
		}


		/* Use the german exhibition history if none is set for the english one */
		if (empty($graphicEn->getExhibitionHistory())) {
			$graphicEn->setExhibitionHistory($graphicDe->getExhibitionHistory());
		}
	}


	/* Bibliography */
	private static function inflateBibliography(\SimpleXMLElement &$node,
	                                            Graphic &$graphicDe,
	                                            Graphic &$graphicEn) {
		$bibliographySectionElement = $node->Section[30];
		$bibliographyElement = self::findElementByXPath(
			$bibliographySectionElement,
			'Field[@FieldName="{OBJECTS.Bibliography}"]/FormattedValue',
		);
		if ($bibliographyElement) {
			$bibliographyStr = trim($bibliographyElement);
			$graphicDe->setBibliography($bibliographyStr);
			$graphicEn->setBibliography($bibliographyStr);
		}
	}


	/* References */
	private static function inflateReferences(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		/* Reprints References */
		$referenceReprintDetailsElements = $node->Section[31]->Subreport->Details;

		
		$reprintReferences = self::getReferencesForDetailElements(
			$referenceReprintDetailsElements,
		);

		/* RelatedWorks References */
		$referenceRelatedWorksDetailsElements = $node->Section[32]->Subreport->Details;

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
			array_filter($overallReferences, function($reference) {
				return $reference->getText() === self::$referenceTypeValues['reprint'];
			}),
		);

		$filteredRelatedWorkReferences = array_values(
			array_filter($overallReferences, function($reference) {
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
		Graphic &$graphic,
		array $reprintRefs,
		array $relatedWorkRefs
	) {
		if (count($reprintRefs) > 0 || count($relatedWorkRefs) > 0) {
			echo '  > ' . $graphic->getInventoryNumber() . (($graphic->getIsVirtual()) ? ' (isVirtual)' : '') . "\n";

			if (count($reprintRefs) > 0) {
				echo "  wrong reprint refs:\n";
				foreach($reprintRefs as $ref) {
					echo "      * " . $ref->getInventoryNumber() . ' (' . $ref->getText() . ')' . "\n";
				}
			}

			if (count($relatedWorkRefs) > 0) {
				echo "  wrong relatedWork refs:\n";
				foreach($relatedWorkRefs as $ref) {
					echo "      * " . $ref->getInventoryNumber() . ' (' . $ref->getText() . ')' . "\n";
				}
			}
		}
	}


	/* Helper function for checking wrongly groupe */
	private static function getWronglyCategorizedReferences(
		array $references,
		string $wrongType
	) {
		return array_filter($references, function ($ref) use($wrongType) {
			return $ref->getText() === $wrongType;
		});
	}


	/* Reusable helper function for extration of reference like elements */
	private static function getReferencesForDetailElements(
		\SimpleXMLElement &$referenceDetailsElements
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
				$textStr = trim($textElement);
				$reference->setText($textStr);
			}

			/* Inventory number */
			$inventoryNumberElement = self::findElementByXPath(
				$referenceDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
			);
			if ($inventoryNumberElement) {
				$inventoryNumberStr = trim($inventoryNumberElement);
				$reference->setInventoryNumber($inventoryNumberStr);
			}

			/* Remarks */
			$remarksElement = self::findElementByXPath(
				$referenceDetailElement,
				'Section[@SectionNumber="2"]/Field[@FieldName="{ASSOCIATIONS.Remarks}"]/FormattedValue',
			);
			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$reference->setRemark($remarksStr);
			}

			$references[] = $reference;
		}

		return $references;
	}


	/* Additional text informations */
	private static function inflateAdditionalTextInformations(\SimpleXMLElement &$node,
	                                                          Graphic &$graphicDe,
	                                                          Graphic &$graphicEn) {
		$additionalTextsDetailsElements = $node->Section[33]->Subreport->Details;

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
				$textTypeStr = trim($textTypeElement);
				$additionalTextInformation->setType($textTypeStr);

				if (self::$additionalTextLanguageTypes['de'] === $textTypeStr) {
					$graphicDe->addAdditionalTextInformation($additionalTextInformation);
				} else if (self::$additionalTextLanguageTypes['en'] === $textTypeStr) {
					$graphicEn->addAdditionalTextInformation($additionalTextInformation);
				} else if(self::$additionalTextLanguageTypes['not_assigned'] === $textTypeStr) {
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
				$textStr = trim($textElement);
				$additionalTextInformation->setText($textStr);
			}

			/* Date */
			$dateElement = self::findElementByXPath(
				$additionalTextDetailElement,
				'Section[@SectionNumber="2"]/Text[@Name="Text21"]/TextValue',
			);
			if ($dateElement) {
				$dateStr = trim($dateElement);
				$additionalTextInformation->setDate($dateStr);
			}

			/* Year */
			$yearElement = self::findElementByXPath(
				$additionalTextDetailElement,
				'Section[@SectionNumber="3"]/Text[@Name="Text1"]/TextValue',
			);
			if ($yearElement) {
				$yearStr = trim($yearElement);
				$additionalTextInformation->setYear($yearStr);
			}

			/* Author */
			$authorElement = self::findElementByXPath(
				$additionalTextDetailElement,
				'Section[@SectionNumber="4"]/Text[@Name="Text3"]/TextValue',
			);
			if ($authorElement) {
				$authorStr = trim($authorElement);
				$additionalTextInformation->setAuthor($authorStr);
			}

		}
	}


	/* Publications */
	private static function inflatePublications(\SimpleXMLElement &$node,
	                                            Graphic &$graphicDe,
	                                            Graphic &$graphicEn) {
		$publicationDetailsElements = $node->Section[34]->Subreport->Details;

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
				$titleStr = trim($titleElement);
				$publicationDe->setTitle($titleStr);
				$publicationEn->setTitle($titleStr);
			}

			/* Pagenumber */
			$pageNumberElement = self::findElementByXPath(
				$publicationDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PageNumber}"]/FormattedValue',
			);
			if ($pageNumberElement) {
				$pageNumberStr = trim($pageNumberElement);

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
				$referenceIdStr = trim($referenceIdElement);
				$publicationDe->setReferenceId($referenceIdStr);
				$publicationEn->setReferenceId($referenceIdStr);
			}
		}
	}


	/* Keywords */
	private static function inflateKeywords(\SimpleXMLElement &$node,
	                                        Graphic &$graphicDe,
	                                        Graphic &$graphicEn) {
		$keywordDetailsElements = $node->Section[35]->Subreport->Details;

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
				$keywordTypeStr = trim($keywordTypeElement);
				$metaReference->setType($keywordTypeStr);
			}

			/* Term */
			$keywordTermElement = self::findElementByXPath(
				$keywordDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{TERMS.Term}"]/FormattedValue',
			);
			if ($keywordTermElement) {
				$keywordTermStr = trim($keywordTermElement);
				$metaReference->setTerm($keywordTermStr);
			}

			/* Path */
			$keywordPathElement = self::findElementByXPath(
				$keywordDetailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH1.Path}"]/FormattedValue',
			);
			if ($keywordPathElement) {
				$keywordPathStr = trim($keywordPathElement);
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
	private static function inflateLocations(\SimpleXMLElement &$node,
	                                         Graphic &$graphicDe,
	                                         Graphic &$graphicEn) {
		$locationDetailsElements = $node->Section[36]->Subreport->Details;

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
				$locationTypeStr = trim($locationTypeElement);
				$metaReference->setType($locationTypeStr);

				if (self::$locationLanguageTypes['de'] === $locationTypeStr) {
					$graphicDe->addLocation($metaReference);
				} else if (self::$locationLanguageTypes['en'] === $locationTypeStr) {
					$graphicEn->addLocation($metaReference);
				} else if(self::$locationLanguageTypes['not_assigned'] === $locationTypeStr) {
					echo '  Unassigned location type for object ' . $graphicDe->getInventoryNumber() . "\n";
					$graphicDe->addLocation($metaReference);
					$graphicEn->addLocation($metaReference);
				} else {
					echo '  Unknown location type: ' . $textTypeStr . ' for object ' . $graphicDe->getInventoryNumber() . "\n";
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
				$locationTermStr = trim($locationTermElement);
				$metaReference->setTerm($locationTermStr);
			}

			/* Path */
			$locationPathElement = self::findElementByXPath(
				$locationDetailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH1.Path}"]/FormattedValue',
			);
			if ($locationPathElement) {
				$locationPathStr = trim($locationPathElement);
				$metaReference->setPath($locationPathStr);
			}
		}
	}


	/* Repository and Owner */
	private static function inflateRepositoryAndOwner(\SimpleXMLElement &$node,
	                                                  Graphic &$graphicDe,
	                                                  Graphic &$graphicEn) {
		$repositoryAndOwnerDetailsSubreport = $node->Section[37]->Subreport;
		$details = $repositoryAndOwnerDetailsSubreport->Details;

		foreach($details as $detail) {
			/* We have to extract the role */
			$roleElement = self::findElementByXPath(
				$detail,
				'Section[@SectionNumber="1"]/Field[@FieldName="{@Rolle}"]/FormattedValue',
			);

			if (!$roleElement) {
				continue;
			}

			$roleName = trim($roleElement);

			/* Passing the roleName to the infaltors for themself to decide if they are
			  responsible for further value extraction */
			$isRepository = false;
			$isOwner = false;

			try {
				$isRepository = self::inflateRepository($detail, $roleName, $graphicDe, $graphicEn);
			} catch (Exception $e) {
				echo '  ' . $e->getMessage() . "\n";
			}

			try {
				$isOwner = self::inflateOwner($detail, $roleName, $graphicDe, $graphicEn);
			} catch (Exception $e) {
				echo '  ' . $e->getMessage() . "\n";
			}

			if (!$isRepository && !$isOwner) {
				echo '  Item is neither a repository or an owner: ' . $roleName . "\n";
			}
		}
	}


	/* Repository */
	private static function inflateRepository(\SimpleXMLElement &$detail,
	                                          string $roleName,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn): bool {
		$repositoryElement = self::findElementByXPath(
			$detail,
			'Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
		);

		if (!$repositoryElement) {
			throw new Exception('Missing element with repository name!');
		}

		$repositoryStr = trim($repositoryElement);

		switch ($roleName) {
			case self::$repositoryTypes['de']:
				/* de */
				$graphicDe->setRepository($repositoryStr);
				break;

			case self::$repositoryTypes['en']:
				/* en */
				$graphicEn->setRepository($repositoryStr);
				break;

			default:
				return FALSE;
		}

		return TRUE;
	}


	/* Owner */
	private static function inflateOwner(\SimpleXMLElement &$detail,
	                                     string $roleName,
	                                     Graphic &$graphicDe,
	                                     Graphic &$graphicEn): bool {
		$ownerElement = self::findElementByXPath(
			$detail,
			'Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DisplayName}"]/FormattedValue',
		);

		if (!$ownerElement) {
			throw new Exception('Missing element with owner name!');
		}

		$ownerStr = trim($ownerElement);

		switch ($roleName) {
			case self::$ownerTypes['de']:
				/* de */
				$graphicDe->setOwner($ownerStr);
				break;

			case self::$ownerTypes['en']:
				/* en */
				$graphicEn->setOwner($ownerStr);
				break;

			default:
				return FALSE;
		}

		return TRUE;
	}


	/* Sorting number */
	private static function inflateSortingNumber(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$sortingNumberSubreport = $node->Section[38];

		$sortingNumberElement = self::findElementByXPath(
			$sortingNumberSubreport,
			'Field[@FieldName="{OBJCONTEXT.Period}"]/FormattedValue',
		);
		if ($sortingNumberElement) {
			$sortingNumberStr = trim($sortingNumberElement);

			$graphicDe->setSortingNumber($sortingNumberStr);
			$graphicEn->setSortingNumber($sortingNumberStr);
		}
	}


	/* Catalog work reference */
	private static function inflateCatalogWorkReference(\SimpleXMLElement &$node,
	                                                    Graphic &$graphicDe,
	                                                    Graphic &$graphicEn) {
		$catalogWorkReferenceDetailsElements = $node->Section[39]->Subreport->Details;

		foreach($catalogWorkReferenceDetailsElements as $detailElement) {
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
				$descriptionStr = trim($descriptionElement);

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
				$referenceNumberStr = trim($referenceNumberElement);

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
				$remarksStr = trim($remarksElement);

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
	private static function inflateStructuredDimension(\SimpleXMLElement &$node,
	                                                   Graphic &$graphicDe,
	                                                   Graphic &$graphicEn) {
		$catalogWorkReferenceSubreport = $node->Section[40]->Subreport;

		$structuredDimension = new StructuredDimension;

		$graphicDe->setStructuredDimension($structuredDimension);
		$graphicEn->setStructuredDimension($structuredDimension);

		/* element */
		$elementElement = self::findElementByXPath(
			$catalogWorkReferenceSubreport,
			'Field[@FieldName="{DIMENSIONELEMENTS.Element}"]/FormattedValue',
		);

		if($elementElement) {
			$elementStr = trim($elementElement);

			$structuredDimension->setElement($elementStr);
		}


		/* Details elements */
		$detailsElements = self::findElementsByXPath(
			$catalogWorkReferenceSubreport,
			'Details',
		);
		if (count($detailsElements) === 2) {
			/* height */
			$heightElement = self::findElementByXPath(
				$detailsElements[0],
				'Field[@FieldName="{DIMENSIONS.Dimension}"]/Value',
			);
			if ($heightElement) {
				$heightNumber = trim($heightElement);

				$structuredDimension->setHeight($heightNumber);
			}

			/* width */
			$widthElement = self::findElementByXPath(
				$detailsElements[1],
				'Field[@FieldName="{DIMENSIONS.Dimension}"]/Value',
			);
			if ($widthElement) {
				$widthNumber = trim($widthElement);

				$structuredDimension->setWidth($widthNumber);
			}
		}

	}


	private static function registerXPathNamespace(\SimpleXMLElement $node) {
		$node->registerXPathNamespace(self::$nsPrefix, self::$ns);
	}


	private static function findElementsByXPath(\SimpleXMLElement $node, string $path) {
		self::registerXPathNamespace($node);

		$splitPath = explode('/', $path);

		$nsPrefix = self::$nsPrefix;
		$xpathStr = './/' . implode('/', array_map(
			function($val) use($nsPrefix) {
				return empty($val) ? $val : $nsPrefix . ':' . $val;
			},
			$splitPath
		));

		return $node->xpath($xpathStr);
	}


	private static function findElementByXPath(\SimpleXMLElement $node, string $path) {
		$result = self::findElementsByXPath($node, $path);

		if (is_array($result) && count($result) > 0) {
			return $result[0];
		}

		return FALSE;
	}


	/*
	  TODO: Move out into helper -> dynamically settable at runtime if possible
	    -> composition over inheritance
	*/
	private static function splitLanguageString(string $langStr): array {
		$splitLangStrs = array_map('trim', explode(self::$langSplitChar, $langStr));
		$cntItems = count($splitLangStrs);

		if ($cntItems == 1) {
			$splitLangStrs[] = $splitLangStrs[0];
		} 

		return $splitLangStrs;
	}

}