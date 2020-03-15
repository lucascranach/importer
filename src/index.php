<?php

/*
	@TODO: Introduce Pipeline-Class to add Input, Ops and Destinations to; process can only be started through the pipeline
	@TODO: Use src and sink as Input- and Output-Fields?
*/


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