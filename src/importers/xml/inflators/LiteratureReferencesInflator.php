<?php

namespace CranachImport\Importers\XML\Inflators;

require_once 'ILiteratureReferenceInflator.php';
require_once 'entities/LiteratureReference.php';

require_once 'entities/literatureReference/Event.php';
require_once 'entities/literatureReference/Person.php';
require_once 'entities/literatureReference/Publication.php';
require_once 'entities/literatureReference/ConnectedObject.php';


use CranachImport\Entities\LiteratureReference;

use CranachImport\Entities\LiteratureReference\Event;
use CranachImport\Entities\LiteratureReference\Person;
use CranachImport\Entities\LiteratureReference\Publication;
use CranachImport\Entities\LiteratureReference\ConnectedObject;


/**
 * LiteratureReferences inflator used to inflate literature reference instances
 * 	by traversing the xml element node and extracting the data in a structured way
 */
class LiteratureReferencesInflator implements ILiteratureReferenceInflator {

	private static $nsPrefix = 'ns';
	private static $ns = 'urn:crystal-reports:schemas:report-detail';
	private static $langSplitChar = '#';

	private function __construct() {}


	public static function inflate(\SimpleXMLElement &$node,
	                               LiteratureReference &$literatureReference) {
		$subNode = $node->GroupHeader;
		$connectedObjectsSubNode = $node->Group;

		self::registerXPathNamespace($subNode);

		self::inflateReferenceId($subNode, $literatureReference);
		self::inflateReferenceNumber($subNode, $literatureReference);

		self::inflateTitle($subNode, $literatureReference);
		self::inflateSubtitle($subNode, $literatureReference);
		self::inflateShorttitle($subNode, $literatureReference);

		self::inflateJournal($subNode, $literatureReference);
		self::inflateSeries($subNode, $literatureReference);
		self::inflateVolume($subNode, $literatureReference);
		self::inflateEdition($subNode, $literatureReference);

		self::inflatePublishLocation($subNode, $literatureReference);
		self::inflatePublishDate($subNode, $literatureReference);
		self::inflatePageNumbers($subNode, $literatureReference);
		self::inflateDate($subNode, $literatureReference);

		self::inflateEvents($subNode, $literatureReference);

		self::inflateCopyright($subNode, $literatureReference);

		self::inflatePersons($subNode, $literatureReference);
		self::inflatePublications($subNode, $literatureReference);

		self::inflateId($subNode, $literatureReference);

		self::inflateConnectedObjects($connectedObjectsSubNode, $literatureReference);
	}


	/* ReferenceId */
	private static function inflateReferenceId(\SimpleXMLElement &$node,
	                                           LiteratureReference &$literatureReference) {
		$idElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="0"]/Field[@FieldName="{ReferenceMaster.ReferenceID}"]/FormattedValue',
		);
		if ($idElement) {
			$idStr = trim($idElement);
			$literatureReference->setReferenceId($idStr);
		}
	}


	/* ReferenceNumber */
	private static function inflateReferenceNumber(\SimpleXMLElement &$node,
	                                              LiteratureReference &$literatureReference) {

		$numberElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="1"]/Field[@FieldName="{@Verweisnummer}"]/FormattedValue',
		);
		if ($numberElement) {
			$numberStr = trim($numberElement);
			$literatureReference->setReferenceNumber($numberStr);
		}
	}


	/* Title */
	private static function inflateTitle(\SimpleXMLElement &$node,
	                                     LiteratureReference &$literatureReference) {
		$titleElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="2"]/Field[@FieldName="{ReferenceMaster.Title}"]/FormattedValue',
		);
		if ($titleElement) {
			$titleStr = trim($titleElement);
			$literatureReference->setTitle($titleStr);
		}
	}


	/* Subtitle */
	private static function inflateSubtitle(\SimpleXMLElement &$node,
	                                        LiteratureReference &$literatureReference) {
		$subtitleElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="3"]/Field[@FieldName="{ReferenceMaster.SubTitle}}"]/FormattedValue',
		);
		if ($subtitleElement) {
			$subtitleStr = trim($subtitleElement);
			$literatureReference->setSubtitle($subtitleStr);
		}
	}


	/* Shorttitle */
	private static function inflateShorttitle(\SimpleXMLElement &$node,
	                                          LiteratureReference &$literatureReference) {
		$shorttitleElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="4"]/Field[@FieldName="{ReferenceMaster.Heading}"]/FormattedValue',
		);
		if ($shorttitleElement) {
			$shorttitleStr = trim($shorttitleElement);
			$literatureReference->setShorttitle($shorttitleStr);
		}
	}


	/* Journal */
	private static function inflateJournal(\SimpleXMLElement &$node,
	                                       LiteratureReference &$literatureReference) {
		$journalElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="5"]/Field[@FieldName="{ReferenceMaster.Journal}"]/FormattedValue',
		);

		if ($journalElement) {
			$journalStr = trim($journalElement);
			$literatureReference->setJournal($journalStr);
		}
	}


	/* Series */
	private static function inflateSeries(\SimpleXMLElement &$node,
	                                      LiteratureReference &$literatureReference) {

		$seriesElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="6"]/Field[@FieldName="{ReferenceMaster.Series}"]/FormattedValue',
		);
		if ($seriesElement) {
			$seriesStr = trim($seriesElement);
			$literatureReference->setSeries($seriesStr);
		}
	}


	/* Volume */
	private static function inflateVolume(\SimpleXMLElement &$node,
	                                      LiteratureReference &$literatureReference) {
		$volumeElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="7"]/Field[@FieldName="{ReferenceMaster.Volume}"]/FormattedValue',
		);
		if ($volumeElement) {
			$volumeStr = trim($volumeElement);
			$literatureReference->setVolume($volumeStr);
		}
	}


	/* Edition */
	private static function inflateEdition(\SimpleXMLElement &$node,
	                                       LiteratureReference &$literatureReference) {
		$editionElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="8"]/Field[@FieldName="{ReferenceMaster.Edition}"]/FormattedValue',
		);
		if ($editionElement) {
			$editionStr = trim($editionElement);
			$literatureReference->setEdition($editionStr);
		}
	}


	/* PublishLocation */
	private static function inflatePublishLocation(\SimpleXMLElement &$node,
	                                               LiteratureReference &$literatureReference) {
		$publishLocationElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="9"]/Field[@FieldName="{ReferenceMaster.PlacePublished}"]/FormattedValue',
		);
		if ($publishLocationElement) {
			$publishLocationStr = trim($publishLocationElement);
			$literatureReference->setPublishLocation($publishLocationStr);
		}
	}


	/* PublishDate */
	private static function inflatePublishDate(\SimpleXMLElement &$node,
	                                           LiteratureReference &$literatureReference) {
		$publishDateElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="10"]/Field[@FieldName="{ReferenceMaster.YearPublished}}"]/FormattedValue',
		);
		if ($publishDateElement) {
			$publishDateStr = trim($publishDateElement);
			$literatureReference->setPublishDate($publishDateStr);
		}
	}


	/* PageNumbers */
	private static function inflatePageNumbers(\SimpleXMLElement &$node,
	                                           LiteratureReference &$literatureReference) {
		$pageNumbersElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="11"]/Field[@FieldName="{ReferenceMaster.NumOfPages}"]/FormattedValue',
		);
		if ($pageNumbersElement) {
			$pageNumbersStr = trim($pageNumbersElement);
			$literatureReference->setPageNumbers($pageNumbersStr);
		}
	}


	/* Date */
	private static function inflateDate(\SimpleXMLElement &$node,
	                                    LiteratureReference &$literatureReference) {
		$dateElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="12"]/Field[@FieldName="{ReferenceMaster.DisplayDate}"]/FormattedValue',
		);
		if ($dateElement) {
			$dateStr = trim($dateElement);
			$literatureReference->setDate($dateStr);
		}
	}


	/* Events */
	private static function inflateEvents(\SimpleXMLElement &$node,
	                                      LiteratureReference &$literatureReference) {
		$detailElements = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="13"]/Subreport',
		);

		foreach($detailElements as $detailElement) {
			if ($detailElement->count() === 0) {
				continue;
			}

			$event = new Event;
			$literatureReference->addEvent($event);

			/* EventType */
			$eventTypeElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{RefDates.EventType}"]/FormattedValue',
			);

			if ($eventTypeElement) {
				$eventTypeStr = trim($eventTypeElement);
				$event->setType($eventTypeStr);
			}

			/* DateText */
			$dateTextElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{RefDates.DateText}"]/FormattedValue',
			);

			if ($dateTextElement) {
				$dateTextStr = trim($dateTextElement);
				$event->setDateText($dateTextStr);
			}

			/* DateBegin */
			$dateBeginElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="2"]/Field[@FieldName="{@Anfangsdatum}"]/FormattedValue',
			);

			if ($dateBeginElement) {
				$dateBeginStr = trim($dateBeginElement);
				$event->setDateBegin($dateBeginStr);
			}

			/* DateEnd */
			$dateEndElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{@Enddatum}"]/FormattedValue',
			);

			if ($dateEndElement) {
				$dateEndStr = trim($dateEndElement);
				$event->setDateEnd($dateEndStr);
			}

			/* Remarks */
			$remarksElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="4"]/Field[@FieldName="{RefDates.Remarks}"]/FormattedValue',
			);

			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$event->setRemarks($remarksStr);
			}
		}
	}


	/* Copyright */
	private static function inflateCopyright(\SimpleXMLElement &$node,
	                                         LiteratureReference &$literatureReference) {
		$copyrightElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="14"]/Field[@FieldName="{ReferenceMaster.Copyright}"]/FormattedValue',
		);
		if ($copyrightElement) {
			$copyrightStr = trim($copyrightElement);
			$literatureReference->setCopyright($copyrightStr);
		}
	}


	/* Persons */
	private static function inflatePersons(\SimpleXMLElement &$node,
	                                       LiteratureReference &$literatureReference) {
		$detailElements = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="15"]/Subreport',
		);

		foreach($detailElements as $detailElement) {
			if ($detailElement->count() === 0) {
				continue;
			}

			$person = new Person;
			$literatureReference->addPerson($person);

			/* Role */
			$roleElement = self::findElementByXPath(
				$detailElement,
				'Text[@Name="Text2"]/TextValue',
			);

			if ($roleElement) {
				$roleStr = trim($roleElement);
				$person->setRole($roleStr);
			}

			/* Name */
			$nameElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{@PersonSuffix}"]/FormattedValue',
			);

			if ($nameElement) {
				$nameStr = trim($nameElement);
				$person->setName($nameStr);
			}
		}
	}


	/* Publications */
	private static function inflatePublications(\SimpleXMLElement &$node,
	                                            LiteratureReference &$literatureReference) {
		$detailElements = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="16"]/Subreport',
		);

		foreach($detailElements as $detailElement) {
			if ($detailElement->count() === 0) {
				continue;
			}

			$publication = new Publication;

			/* Type */
			$typeElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{Terms.Term}"]/FormattedValue',
			);

			if ($typeElement) {
				$typeStr = trim($typeElement);
				$publication->setType($typeStr);
			}

			/* Remarks */
			$remarksElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="2"]/Field[@FieldName="{ThesXrefs.Remarks}"]/FormattedValue',
			);

			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$publication->setRemarks($remarksStr);
			}


			if (!empty($publication->getType())) {
				$literatureReference->addPublication($publication);
			}
		}
	}


	/* Id */
	private static function inflateId(\SimpleXMLElement &$node,
	                                  LiteratureReference &$literatureReference) {
		$idElement = self::findElementByXPath(
			$node,
			'Section[@SectionNumber="17"]/Subreport/Details/Section[@SectionNumber="0"]/Field[@FieldName="{@AltNum}"]/FormattedValue',
		);
		if ($idElement) {
			$idStr = trim($idElement);
			$literatureReference->setId($idStr);
		}
	}


	/* ConnectedObjects */
	private static function inflateConnectedObjects(\SimpleXMLElement &$node,
	                                                LiteratureReference &$literatureReference) {
		$detailElements = self::findElementByXPath(
			$node,
			'Details/Section/Subreport',
		);

		$skipElementName = 'ReportHeader';

		foreach($detailElements as $detailElement) {
			if ($detailElement->count() === 0 || $detailElement->getName() === $skipElementName) {
				continue;
			}

			$connectedObject = new ConnectedObject;
			$literatureReference->addConnectedObject($connectedObject);

			/* InventoryNumber */
			$inventoryNumberElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="0"]/Field[@FieldName="{@Inventarnummer}"]/FormattedValue',
			);

			if ($inventoryNumberElement) {
				$inventoryNumberStr = trim($inventoryNumberElement);
				$connectedObject->setInventoryNumber($inventoryNumberStr);
			}

			/* CatalogNumber */
			$catalogNumberElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="1"]/Field[@FieldName="{RefXRefs.CatalogueNumber}"]/FormattedValue',
			);

			if ($catalogNumberElement) {
				$catalogNumberStr = trim($catalogNumberElement);
				$connectedObject->setCatalogNumber($catalogNumberStr);
			}

			/* PageNumber */
			$pageNumberElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="2"]/Field[@FieldName="{RefXRefs.PageNumber}"]/FormattedValue',
			);

			if ($pageNumberElement) {
				$pageNumberStr = trim($pageNumberElement);
				$connectedObject->setPageNumber($pageNumberStr);
			}

			/* FigureNumber */
			$figureNumberElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="3"]/Field[@FieldName="{RefXRefs.Appendage}"]/FormattedValue',
			);

			if ($figureNumberElement) {
				$figureNumberStr = trim($figureNumberElement);
				$connectedObject->setFigureNumber($figureNumberStr);
			}

			/* Remarks */
			$remarksElement = self::findElementByXPath(
				$detailElement,
				'Section[@SectionNumber="4"]/Field[@FieldName="{RefXRefs.Remarks}"]/FormattedValue',
			);

			if ($remarksElement) {
				$remarksStr = trim($remarksElement);
				$connectedObject->setRemarks($remarksStr);
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