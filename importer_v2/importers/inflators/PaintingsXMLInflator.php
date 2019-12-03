<?php

namespace CranachImport\Importers\Inflators;

require_once 'IPaintingInflator.php';
require_once 'entities/Painting.php';

require_once 'entities/main/Person.php';
require_once 'entities/main/PersonName.php';
require_once 'entities/main/PersonNameDetail.php';
require_once 'entities/main/Title.php';

require_once 'entities/painting/Classification.php';

use CranachImport\Entities\Painting;

use CranachImport\Entities\Main\Person;
use CranachImport\Entities\Main\PersonName;
use CranachImport\Entities\Main\PersonNameDetail;
use CranachImport\Entities\Main\Title;

use CranachImport\Entities\Painting\Classification;


/**
 * Paintingss inflator used to inflate german and english painting instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class PaintingsXMLInflator implements IPaintingInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';
	private static $langSplitChar = '#';

	private static $titlesLanguageTypes = [
		'de' => 'GERMAN',
		'en' => 'ENGLISH',
		'not_assigned' => '(not assigned)',
	];

	private static $inventoryNumberReplaceArr = [
		'CDA.',
	];

	private function __construct() {}


	public static function inflate(\SimpleXMLElement &$node,
	                               Painting &$paintingDe,
	                               Painting &$paintingEn) {
		$subNode = $node->GroupHeader;

		self::registerXPathNamespace($subNode);
	
		self::inflateInvolvedPersons($subNode, $paintingDe, $paintingEn);
		self::inflatePersonNames($subNode, $paintingDe, $paintingEn);
		self::inflateTitles($subNode, $paintingDe, $paintingEn);
		self::inflateClassification($subNode, $paintingDe, $paintingEn);
		self::inflateObjectName($subNode, $paintingDe, $paintingEn);
		self::inflateInventoryNumber($subNode, $paintingDe, $paintingEn);
		self::inflateObjectMeta($subNode, $paintingDe, $paintingEn);
	}


	/* Involved persons */
	private static function inflateInvolvedPersons(\SimpleXMLElement &$node,
	                                               Painting &$paintingDe,
	                                               Painting &$paintingEn) {
		$details = $node->Section[1]->Subreport->Details;

		for ($i = 0; $i < count($details); $i += 2) {
			$personsArr = [
				new Person, // de
				new Person, // en
			];

			$paintingDe->addPerson($personsArr[0]);
			$paintingEn->addPerson($personsArr[1]);

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
	                                           Painting &$paintingDe,
	                                           Painting &$paintingEn) {
		$groups = $node->Section[2]->Subreport->Group;

		foreach ($groups as $group) {
			$personName = new PersonName;

			$paintingDe->addPersonName($personName);
			$paintingEn->addPersonName($personName);

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
	                                      Painting &$paintingDe,
	                                      Painting &$paintingEn) {
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
					$paintingDe->addTitle($title);
				} else if (self::$titlesLanguageTypes['en'] === $langStr) {
					$paintingEn->addTitle($title);
				} else if(self::$titlesLanguageTypes['not_assigned'] === $langStr) {
					echo 'Unassigned title lang for object ' . $paintingDe->getInventoryNumber() . "\n";
				} else {
					echo 'Unknown title lang: ' . $langStr . ' for object ' . $paintingDe->getInventoryNumber() . "\n";
					/* Bind title to both languages to prevent loss */
					$paintingDe->addTitle($title);
					$paintingEn->addTitle($title);
				}
			} else {
				/* Bind title to both languages to prevent loss */
				$paintingDe->addTitle($title);
				$paintingEn->addTitle($title);
			}

			/* title type */
			$typeElement = self::findElementByXPath(
				$titleDetailElement,
				'Field[@FieldName="{TITLETYPES.TitleType}}"]/FormattedValue',
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
	                                              Painting &$paintingDe,
	                                              Painting &$paintingEn) {
		$classificationSectionElement = $node->Section[4];

		$classificationDe = new Classification;
		$classificationEn = new Classification;

		$paintingDe->setClassification($classificationDe);
		$paintingEn->setClassification($classificationEn);

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
	}


	/* Object name */
	private static function inflateObjectName(\SimpleXMLElement &$node,
	                                          Painting &$paintingDe,
	                                          Painting &$paintingEn) {
		$objectNameSectionElement = $node->Section[5];

		$objectNameElement = self::findElementByXPath(
			$objectNameSectionElement,
			'Field[@FieldName="{OBJECTS.ObjectName}"]/FormattedValue',
		);
		if ($objectNameElement) {
			$objectNameStr = trim($objectNameElement);

			/* Using single german value for both language objects */
			$paintingDe->setObjectName($objectNameStr);
			$paintingEn->setObjectName($objectNameStr);
		}
	}


	/* Inventory number */
	private static function inflateInventoryNumber(\SimpleXMLElement &$node,
	                                               Painting &$paintingDe,
	                                               Painting &$paintingEn) {
		$inventoryNumberSectionElement = $node->Section[6];

		$inventoryNumberElement = self::findElementByXPath(
			$inventoryNumberSectionElement,
			'Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
		);
		if ($inventoryNumberElement) {
			$inventoryNumberStr = trim($inventoryNumberElement);
			$cleanInventoryNumberStr = str_replace(
				self::$inventoryNumberReplaceArr,
				'',
				$inventoryNumberStr,
			);

			/* Using single german value for both language objects */
			$paintingDe->setInventoryNumber($cleanInventoryNumberStr);
			$paintingEn->setInventoryNumber($cleanInventoryNumberStr);
		}
	}


	/* Object id & virtual (meta) */
	private static function inflateObjectMeta(\SimpleXMLElement &$node,
	                                          Painting &$paintingDe,
	                                          Painting &$paintingEn) {
		$metaSectionElement = $node->Section[7];

		/* object id */
		$objectIdElement = self::findElementByXPath(
			$metaSectionElement,
			'Field[@FieldName="{OBJECTS.ObjectID}"]/Value',
		);
		if ($objectIdElement) {
			$objectIdStr = intval(trim($objectIdElement));

			/* Using single german value for both language objects */
			$paintingDe->setObjectId($objectIdStr);
			$paintingEn->setObjectId($objectIdStr);
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