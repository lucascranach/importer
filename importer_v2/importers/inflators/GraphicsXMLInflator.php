<?php

namespace CranachImport\Importers\Inflators;

require_once 'IInflator.php';
require_once 'entities/Graphic.php';
require_once 'entities/Person.php';
require_once 'entities/PersonName.php';
require_once 'entities/PersonNameDetail.php';
require_once 'entities/Title.php';
require_once 'entities/Classification.php';
require_once 'entities/Dating.php';
require_once 'entities/HistoricEventInformation.php';
require_once 'entities/GraphicReference.php';
require_once 'entities/AdditionalTextInformation.php';
require_once 'entities/Publication.php';
require_once 'entities/MetaReference.php';
require_once 'entities/CatalogWorkReference.php';
require_once 'entities/StructuredDimension.php';


use CranachImport\Entities\Graphic;
use CranachImport\Entities\Person;
use CranachImport\Entities\PersonName;
use CranachImport\Entities\PersonNameDetail;
use CranachImport\Entities\Title;
use CranachImport\Entities\Classification;
use CranachImport\Entities\Dating;
use CranachImport\Entities\HistoricEventInformation;
use CranachImport\Entities\GraphicReference;
use CranachImport\Entities\AdditionalTextInformation;
use CranachImport\Entities\Publication;
use CranachImport\Entities\MetaReference;
use CranachImport\Entities\CatalogWorkReference;
use CranachImport\Entities\StructuredDimension;


/**
 * Graphics inflator used to inflate german and english graphic instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class GraphicsXMLInflator implements IInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';
	private static $langSplitChar = '#';


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
		self::inflateRepository($subNode, $graphicDe, $graphicEn);
		self::inflateOwner($subNode, $graphicDe, $graphicEn);
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
					'Field[@FieldName="{ROLES.ROLE}"]/FormattedValue',
				);
				if ($roleElement) {
					$roleStr = trim($roleElement);
					$personsArr[$j]->setRole($roleStr);
				}

				/* name */
				$nameElement = self::findElementByXPath(
					$currDetails,
					'Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue',
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
				'Field[@FieldName="GroupName ({CONALTNAMES.CONSTITUENTID})"]/FormattedValue',
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
					'Field[@FieldName="GroupName ({CONALTNAMES.DISPLAYNAME})"]/FormattedValue',
				);
				if ($detailNameElement) {
					$detailNameStr = trim($detailNameElement);
					$personDetailName->setName($detailNameStr);
				}

				/* type */
				$detailNameTypeElement = self::findElementByXPath(
					$nameDetailGroup,
					'Field[@FieldName="GroupName ({CONALTNAMES.NAMETYPE})"]/FormattedValue',
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

		for ($i = 0; $i < count($titleDetailElements); $i += 2) {
			$titlesArr = [
				new Title, // de
				new Title, // en
			];

			$graphicDe->addTitle($titlesArr[0]);
			$graphicEn->addTitle($titlesArr[1]);

			for ($j = 0; $j < count($titlesArr); $j += 1) {
				$titleDetailElement = $titleDetailElements[$i + $j];

				if (is_null($titleDetailElement)) {
					continue;
				}

				/* title type */
				$typeElement = self::findElementByXPath(
					$titleDetailElement,
					'Field[@FieldName="{TITLETYPES.TITLETYPE}"]/FormattedValue',
				);
				if ($typeElement) {
					$typeStr = trim($typeElement);
					$titlesArr[$j]->setType($typeStr);
				}

				/* title */
				$titleElement = self::findElementByXPath(
					$titleDetailElement,
					'Field[@FieldName="{OBJTITLES.TITLE}"]/FormattedValue',
				);
				if ($titleElement) {
					$titleStr = trim($titleElement);
					$titlesArr[$j]->setTitle($titleStr);
				}

				/* remark */
				$remarksElement = self::findElementByXPath(
					$titleDetailElement,
					'Field[@FieldName="{OBJTITLES.REMARKS}"]/FormattedValue',
				);
				if ($remarksElement) {
					$remarksStr = trim($remarksElement);
					$titlesArr[$j]->setRemarks($remarksStr);
				}
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
			'Field[@FieldName="{OBJECTS.OBJECTNAME}"]/FormattedValue',
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

			/* Using single german value for both language objects */
			$graphicDe->setInventoryNumber($inventoryNumberStr);
			$graphicEn->setInventoryNumber($inventoryNumberStr);
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
			'Field[@FieldName="{OBJECTS.OBJECTID}"]/Value',
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
			'Field[@FieldName="{OBJECTS.ISVIRTUAL}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.DIMENSIONS}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.DATED}"]/FormattedValue',
		);
		if ($datedElement) {
			$datedDateStr = trim($datedElement);

			$datingDe->setDated($datedDateStr);
			$datingEn->setDated($datedDateStr);
		}

		/* Date begin */
		$dateBeginSectionElement = $node->Section[10];

		$dateBeginElement = self::findElementByXPath(
			$dateBeginSectionElement,
			'Field[@FieldName="{OBJECTS.DATEBEGIN}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.DATEEND}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.DATEREMARKS}"]/FormattedValue',
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
					'Field[@FieldName="{OBJDATES.EVENTTYPE}"]/FormattedValue',
				);
				if ($eventTypeElement) {
					$eventTypeStr = trim($eventTypeElement);
					$historicEventArr[$j]->setEventType($eventTypeStr);
				}

				/* date text */
				$dateTextElement = self::findElementByXPath(
					$historicEventDetailElement,
					'Field[@FieldName="{OBJDATES.DATETEXT}"]/FormattedValue',
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
					'Field[@FieldName="{OBJDATES.REMARKS}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.DESCRIPTION}"]/FormattedValue',
		);
		if ($descriptionElement) {
			$descriptionStr = trim($descriptionElement);
			$graphicDe->setDescription($descriptionStr);
		}

		/* en */
		$descriptionEnSectionElement = $node->Section[15];
		$descriptionElement = self::findElementByXPath(
			$descriptionEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT3}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.PROVENANCE}"]/FormattedValue',
		);
		if ($provenanceElement) {
			$provenanceStr = trim($provenanceElement);
			$graphicDe->setProvenance($provenanceStr);
		}

		/* en */
		$provenanceEnSectionElement = $node->Section[17];
		$provenanceElement = self::findElementByXPath(
			$provenanceEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT5}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.MEDIUM}"]/FormattedValue',
		);
		if ($mediumElement) {
			$mediumStr = trim($mediumElement);
			$graphicDe->setMedium($mediumStr);
		}

		/* en */
		$mediumEnSectionElement = $node->Section[19];
		$mediumElement = self::findElementByXPath(
			$mediumEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT4}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.PAPERSUPPORT}"]/FormattedValue',
		);
		if ($signatureElement) {
			$signatureStr = trim($signatureElement);
			$graphicDe->setSignature($signatureStr);
		}

		/* en */
		$signatureEnSectionElement = $node->Section[21];
		$signatureElement = self::findElementByXPath(
			$signatureEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.SHORTTEXT6}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.INSCRIBED}"]/FormattedValue',
		);
		if ($inscriptionElement) {
			$inscriptionStr = trim($inscriptionElement);
			$graphicDe->setInscription($inscriptionStr);
		}

		/* en */
		$inscriptionEnSectionElement = $node->Section[23];
		$inscriptionElement = self::findElementByXPath(
			$inscriptionEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT7}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.MARKINGS}"]/FormattedValue',
		);
		if ($markingsElement) {
			$markingsStr = trim($markingsElement);
			$graphicDe->setMarkings($markingsStr);
		}

		/* en */
		$markingsEnSectionElement = $node->Section[25];
		$markingsElement = self::findElementByXPath(
			$markingsEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT9}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.RELATEDWORKS}"]/FormattedValue',
		);
		if ($relatedWorksElement) {
			$relatedWorksStr = trim($relatedWorksElement);
			$graphicDe->setRelatedWorks($relatedWorksStr);
		}

		/* en */
		$relatedWorksEnSectionElement = $node->Section[27];
		$relatedWorksElement = self::findElementByXPath(
			$relatedWorksEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT6}"]/FormattedValue',
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
			'Field[@FieldName="{OBJECTS.EXHIBITIONS}"]/FormattedValue',
		);
		if ($exhibitionHistoryElement) {
			$exhibitionHistoryStr = trim($exhibitionHistoryElement);
			$graphicDe->setExhibitionHistory($exhibitionHistoryStr);
		}

		/* en */
		$exhibitionHistoryEnSectionElement = $node->Section[29];
		$exhibitionHistoryElement = self::findElementByXPath(
			$exhibitionHistoryEnSectionElement,
			'Field[@FieldName="{OBJCONTEXT.LONGTEXT8}"]/FormattedValue',
		);
		if ($exhibitionHistoryElement) {
			$exhibitionHistoryStr = trim($exhibitionHistoryElement);
			$graphicEn->setExhibitionHistory($exhibitionHistoryStr);
		}
	}


	/* Bibliography */
	private static function inflateBibliography(\SimpleXMLElement &$node,
	                                            Graphic &$graphicDe,
	                                            Graphic &$graphicEn) {
		$bibliographySectionElement = $node->Section[30];
		$bibliographyElement = self::findElementByXPath(
			$bibliographySectionElement,
			'Field[@FieldName="{OBJECTS.BIBLIOGRAPHY}"]/FormattedValue',
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
		$referenceDetailsElements = $node->Section[31]->Subreport->Details;

		for ($i = 0; $i < count($referenceDetailsElements); $i += 1) {
			$referenceDetailElement = $referenceDetailsElements[$i];

			if ($referenceDetailElement->count() === 0) {
				continue;
			}

			$reference = new GraphicReference;

			$graphicDe->addReference($reference);
			$graphicEn->addReference($reference);

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
				'Section[@SectionNumber="2"]/Field[@FieldName="{ASSOCIATIONS.REMARKS}"]/FormattedValue',
			);
			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$reference->setRemark($remarksStr);
			}
		}
	}


	/* Additional text informations */
	private static function inflateAdditionalTextInformations(\SimpleXMLElement &$node,
	                                                          Graphic &$graphicDe,
	                                                          Graphic &$graphicEn) {
		$additionalTextsDetailsElements = $node->Section[33]->Subreport->Details;

		for ($i = 0; $i < count($additionalTextsDetailsElements); $i += 1) {
			$additonalTextDetailElement = $additionalTextsDetailsElements[$i];

			if ($additonalTextDetailElement->count() === 0) {
				continue;
			}

			$additionalTextInformation = new AdditionalTextInformation;

			$graphicDe->addAdditionalTextInformation($additionalTextInformation);
			$graphicEn->addAdditionalTextInformation($additionalTextInformation);

			/* Text type */
			$textTypeElement = self::findElementByXPath(
				$additonalTextDetailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{TEXTTYPES.TEXTTYPE}"]/FormattedValue',
			);
			if ($textTypeElement) {
				$textTypeStr = trim($textTypeElement);
				$additionalTextInformation->setType($textTypeStr);
			}

			/* Text */
			$textElement = self::findElementByXPath(
				$additonalTextDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{TEXTENTRIES.TEXTENTRY}"]/FormattedValue',
			);
			if ($textElement) {
				$textStr = trim($textElement);
				$additionalTextInformation->setText($textStr);
			}

			/* Year */
			$yearElement = self::findElementByXPath(
				$additonalTextDetailElement,
				'Section[@SectionNumber="3"]/Text[@Name="Text1"]/TextValue',
			);
			if ($yearElement) {
				$yearStr = trim($yearElement);
				$additionalTextInformation->setYear($yearStr);
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

			$publication = new Publication;

			$graphicDe->addPublication($publication);
			$graphicEn->addPublication($publication);

			/* Title */
			$titleElement = self::findElementByXPath(
				$publicationDetailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{REFERENCEMASTER.HEADING}"]/FormattedValue',
			);
			if ($titleElement) {
				$titleStr = trim($titleElement);
				$publication->setTitle($titleStr);
			}

			/* Seitennummer */
			$pageNumberElement = self::findElementByXPath(
				$publicationDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{REFXREFS.PAGENUMBER}"]/FormattedValue',
			);
			if ($pageNumberElement) {
				$pageNumberStr = trim($pageNumberElement);
				$publication->setPageNumber($pageNumberStr);
			}

			/* Reference */
			$referenceIdElement = self::findElementByXPath(
				$publicationDetailElement,
				'Section[@SectionNumber="2"]/Field[@FieldName="{REFERENCEMASTER.REFERENCEID}"]/FormattedValue',
			);
			if ($referenceIdElement) {
				$referenceIdStr = trim($referenceIdElement);
				$publication->setReferenceId($referenceIdStr);
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

			$graphicDe->addKeyword($metaReference);
			$graphicEn->addKeyword($metaReference);

			/* Type */
			$keywordTypeElement = self::findElementByXPath(
				$keywordDetailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{THESXREFTYPES.THESXREFTYPE}"]/FormattedValue',
			);
			if ($keywordTypeElement) {
				$keywordTypeStr = trim($keywordTypeElement);
				$metaReference->setType($keywordTypeStr);
			}

			/* Term */
			$keywordTermElement = self::findElementByXPath(
				$keywordDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{TERMS.TERM}"]/FormattedValue',
			);
			if ($keywordTermElement) {
				$keywordTermStr = trim($keywordTermElement);
				$metaReference->setTerm($keywordTermStr);
			}

			/* Path */
			$keywordPathElement = self::findElementByXPath(
				$keywordDetailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH1.PATH}"]/FormattedValue',
			);
			if ($keywordPathElement) {
				$keywordPathStr = trim($keywordPathElement);
				$metaReference->setPath($keywordPathStr);
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

			$graphicDe->addLocation($metaReference);
			$graphicEn->addLocation($metaReference);

			/* Type */
			$locationTypeElement = self::findElementByXPath(
				$locationDetailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{THESXREFTYPES.THESXREFTYPE}"]/FormattedValue',
			);
			if ($locationTypeElement) {
				$locationTypeStr = trim($locationTypeElement);
				$metaReference->setType($locationTypeStr);
			}

			/* Term */
			$locationTermElement = self::findElementByXPath(
				$locationDetailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{TERMS.TERM}"]/FormattedValue',
			);
			if ($locationTermElement) {
				$locationTermStr = trim($locationTermElement);
				$metaReference->setTerm($locationTermStr);
			}

			/* Path */
			$locationPathElement = self::findElementByXPath(
				$locationDetailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{THESXREFSPATH1.PATH}"]/FormattedValue',
			);
			if ($locationPathElement) {
				$locationPathStr = trim($locationPathElement);
				$metaReference->setPath($locationPathStr);
			}
		}
	}


	/* Repository */
	private static function inflateRepository(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$repositoryDetailsSubreport = $node->Section[37]->Subreport;

		// de
		$repositoryDeElement = self::findElementByXPath(
			$repositoryDetailsSubreport,
			'Details[1]/Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue',
		);
		if ($repositoryDeElement) {
			$repositoryStr = trim($repositoryDeElement);

			$graphicDe->setRepository($repositoryStr);
		}

		// en
		$repositoryEnElement = self::findElementByXPath(
			$repositoryDetailsSubreport,
			'Details[2]/Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue',
		);
		if ($repositoryEnElement) {
			$repositoryStr = trim($repositoryEnElement);

			$graphicEn->setRepository($repositoryStr);
		}
	}


	/* Owner */
	private static function inflateOwner(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$ownerDetailsSubreport = $node->Section[37]->Subreport;

		// de
		$ownerDeElement = self::findElementByXPath(
			$ownerDetailsSubreport,
			'Details[3]/Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue',
		);
		if ($ownerDeElement) {
			$ownerStr = trim($ownerDeElement);

			$graphicDe->setOwner($ownerStr);
		}

		// en
		$ownerEnElement = self::findElementByXPath(
			$ownerDetailsSubreport,
			'Details[4]/Section[@SectionNumber="3"]/Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue',
		);
		if ($ownerEnElement) {
			$ownerStr = trim($ownerEnElement);

			$graphicEn->setOwner($ownerStr);
		}
	}


	/* Sorting number */
	private static function inflateSortingNumber(\SimpleXMLElement &$node,
	                                          Graphic &$graphicDe,
	                                          Graphic &$graphicEn) {
		$sortingNumberSubreport = $node->Section[38];

		$sortingNumberElement = self::findElementByXPath(
			$sortingNumberSubreport,
			'Field[@FieldName="{OBJCONTEXT.PERIOD}"]/FormattedValue',
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

		for ($i = 0; $i < count($catalogWorkReferenceDetailsElements); $i += 1) {
			$catalogWorkReferenceDetailElement = $catalogWorkReferenceDetailsElements[$i];

			if ($catalogWorkReferenceDetailElement->count() === 0) {
				continue;
			}

			$catalogWorkReference = new CatalogWorkReference;

			$graphicDe->addCatalogWorkReference($catalogWorkReference);
			$graphicEn->addCatalogWorkReference($catalogWorkReference);

			/* Description */
			$descriptionElement = self::findElementByXPath(
				$catalogWorkReferenceDetailElement,
				'Field[@FieldName="{ALTNUMS.DESCRIPTION}"]/FormattedValue',
			);
			if ($descriptionElement) {
				$descriptionStr = trim($descriptionElement);

				$catalogWorkReference->setDescription($descriptionStr);
			}

			/* Reference number */
			$referenceNumberElement = self::findElementByXPath(
				$catalogWorkReferenceDetailElement,
				'Field[@FieldName="{ALTNUMS.ALTNUM}"]/FormattedValue',
			);
			if ($referenceNumberElement) {
				$referenceNumberStr = trim($referenceNumberElement);

				$catalogWorkReference->setReferenceNumber($referenceNumberStr);
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
			'Field[@FieldName="{DIMENSIONELEMENTS.ELEMENT}"]/FormattedValue',
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
				'Field[@FieldName="{DIMENSIONS.DIMENSION}"]/Value',
			);
			if ($heightElement) {
				$heightNumber = trim($heightElement);

				$structuredDimension->setHeight($heightNumber);
			}

			/* width */
			$widthElement = self::findElementByXPath(
				$detailsElements[1],
				'Field[@FieldName="{DIMENSIONS.DIMENSION}"]/Value',
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


	private static function splitLanguageString(string $langStr): array {
		return array_map('trim', explode(self::$langSplitChar, $langStr));
	}

}