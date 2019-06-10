<?php

namespace CranachImport\Importers\Inflators;

require_once 'IInflator.php';
require_once 'entities/Graphic.php';
require_once 'entities/Person.php';
require_once 'entities/PersonName.php';
require_once 'entities/PersonNameDetail.php';

use CranachImport\Entities\Graphic;
use CranachImport\Entities\Person;
use CranachImport\Entities\PersonName;
use CranachImport\Entities\PersonNameDetail;


class GraphicsXMLInflator implements IInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';


	private function __construct() {}


	public static function inflate(\SimpleXMLElement &$node,
	                               Graphic &$graphicDe,
	                               Graphic &$graphicEn) {
		$subNode = $node->GroupHeader;

		self::registerXPathNamespace($subNode);

		self::inflateInvolvedPersons($subNode, $graphicDe, $graphicEn);
		self::inflatePersonNames($subNode, $graphicDe, $graphicEn);
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
				$roleElement = self::findElementByXPath($currDetails, 'Field[@FieldName="{ROLES.ROLE}"]/FormattedValue');
				if ($roleElement) {
					$roleStr = trim($roleElement);
					$personsArr[$j]->setRole($roleStr);
				}

				/* name */
				$nameElement = self::findElementByXPath($currDetails, 'Field[@FieldName="{CONALTNAMES.DISPLAYNAME}"]/FormattedValue');
				if ($nameElement) {
					$nameStr = trim($nameElement);
					$personsArr[$j]->setName($nameStr);
				}

				/* prefix */
				$prefixElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="3"]//FormattedValue');
				if ($prefixElement) {
					$prefixStr = trim($prefixElement);
					$personsArr[$j]->setPrefix($prefixStr);
				}

				/* suffix */
				$suffixElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="4"]//FormattedValue');
				if ($suffixElement) {
					$suffixStr = trim($suffixElement);
					$personsArr[$j]->setSuffix($suffixStr);
				}

				/* role of unknown person */
				$unknownPersonRoleElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="6"]//FormattedValue');
				if ($unknownPersonRoleElement) {
					/* with a role set for an unknown person, we can mark the person as 'unknown' */
					$personsArr[$j]->setIsUnknown(true);

					$unknownPersonRoleStr = trim($unknownPersonRoleElement);
					$personsArr[$j]->setRole($unknownPersonRoleStr);
				}

				/* prefix of unknown person */
				$unknownPersonPrefixElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="7"]//FormattedValue');
				if ($unknownPersonPrefixElement) {
					$unknownPersonPrefixStr = trim($unknownPersonPrefixElement);
					$personsArr[$j]->setPrefix($unknownPersonPrefixStr);
				}

				/* suffix of unknown person */
				$unknownPersonSuffixElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="8"]//FormattedValue');
				if ($unknownPersonSuffixElement) {
					$unknownPersonSuffixStr = trim($unknownPersonSuffixElement);
					$personsArr[$j]->setSuffix($unknownPersonSuffixStr);
				}

				/* name type */
				$nameTypeElement = self::findElementByXPath($currDetails, 'Field[@FieldName="{@Nametype}"]/FormattedValue');
				if ($nameTypeElement) {
					$nameTypeStr = trim($nameTypeElement);
					$personsArr[$j]->setNameType($nameTypeStr);
				}

				/* alternative name */
				$alternativeNameElement = self::findElementByXPath($currDetails, 'Field[@FieldName="{@AndererName}"]/FormattedValue');
				if ($alternativeNameElement) {
					$alternativeNameStr = trim($alternativeNameElement);
					$personsArr[$j]->setAlternativeName($alternativeNameStr);
				}

				/* remarks */
				$remarksElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="11"]//FormattedValue');
				if ($remarksElement) {
					$remarksNameStr = trim($remarksElement);
					$personsArr[$j]->setRemarks($remarksNameStr);
				}

				/* date */
				$dateElement = self::findElementByXPath($currDetails, 'Section[@SectionNumber="12"]//FormattedValue');
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
			$constituentIdElement = self::findElementByXPath($group, 'Field[@FieldName="GroupName ({CONALTNAMES.CONSTITUENTID})"]/FormattedValue');
			if ($constituentIdElement) {
				$constituentIdStr = trim($constituentIdElement);
				$personName->setConstituentId($constituentIdStr);
			}

			$nameDetailGroups = self::findElementsByXPath($group, 'Group[@Level="2"]');

			if (!$nameDetailGroups) {
				continue;
			}

			foreach ($nameDetailGroups as $nameDetailGroup) {
				$personDetailName = new PersonNameDetail;
				$personName->addDetail($personDetailName);

				/* name */
				$detailNameElement = self::findElementByXPath($nameDetailGroup, 'Field[@FieldName="GroupName ({CONALTNAMES.DISPLAYNAME})"]/FormattedValue');
				if ($detailNameElement) {
					$detailNameStr = trim($detailNameElement);
					$personDetailName->setName($detailNameStr);
				}

				/* type */
				$detailNameTypeElement = self::findElementByXPath($nameDetailGroup, 'Field[@FieldName="GroupName ({CONALTNAMES.NAMETYPE})"]/FormattedValue');
				if ($detailNameTypeElement) {
					$detailNameTypeStr = trim($detailNameTypeElement);
					$personDetailName->setNameType($detailNameTypeStr);
				}
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

}