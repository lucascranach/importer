<?php

/*
$litCollector = LiteratureCollector::withLoader(
	LiteratureLoader::withSourceAt('../00_rawContent/lit.xml'),
);
$thesCollector = ThesaurusCollector::withLoader(
	ThesaurusLoader::withSourceAt('../00_rawContent/thesaurus.xml'),
);
*/

$extendedGraphics = GraphicsLoader::withSourceAt('../00_rawContent/graphics.xml')->pipe(
	GraphicExternalImageExtenderOp::withCacheAt('./cache'),
);


$extendedGraphics->pipe(
	GraphicsOutput::withDestinationAt('../output/graphics.simple.json'),
);


$extendedGraphics->pipe(
	// GraphicExampleExtendOp::withCollectors($litCollector, $thesCollector);
	GraphicsOutput::withDestinationAt('../output/graphics.extended.json'),
);

$extendedGraphics->pull();

$litCollector.release();
$thesCollector.release();