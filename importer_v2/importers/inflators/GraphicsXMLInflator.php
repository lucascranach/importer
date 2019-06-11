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


use CranachImport\Entities\Graphic;
use CranachImport\Entities\Person;
use CranachImport\Entities\PersonName;
use CranachImport\Entities\PersonNameDetail;
use CranachImport\Entities\Title;
use CranachImport\Entities\Classification;
use CranachImport\Entities\Dating;


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

		/* state */
		$stateElement = self::findElementByXPath(
			$classificationSectionElement,
			'Field[@FieldName="{@Druckzustand}"]/FormattedValue',
		);
		if ($stateElement) {
			$stateStr = trim($stateElement);

			$splitStateStr = self::splitLanguageString($stateStr);

			if (isset($splitStateStr[0])) {
				$classificationDe->setState($splitStateStr[0]);
			}

			if (isset($splitStateStr[1])) {
				$classificationEn->setState($splitStateStr[1]);
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

		/* TODO: HistoricEventInformation */
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