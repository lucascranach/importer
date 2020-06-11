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

// Try 1
$thesaurusCollector = ThesaurusLoader::withSourceAt('../00_rawContent/thesaurus.xml')->pipe(
    ThesaurusCleanupOp::new(),
    ThesaurusCollector::new(),
);

$graphicsCollector = GraphicsLoader::withSourceAt('../00_rawContent/graphics.xml')->pipe(
    GraphicConditionDeterminerOp::new(),
    GraphicExternalImageExtenderOp::withCacheAt('./cache'),
    GraphicsCollector::new(),
);

// $extendedGraphics = mergeCollectors($graphicsCollector, $thesaurusCollector)->pipe(GraphicExtenderOp::new());

$graphicsExtender = GraphicExtenderOp::new();
$graphicsCollector->pipe($graphicsExtender);
$thesaurusCollector->pipe($graphicsExtender);

$graphicsExtender->pipe(
    GraphicsOutput::withDestinationAt('../output/graphics.simple.json'),
);

$graphicsExtender->pipe(
    GraphicsOutput::withDestinationAt('../output/graphics.extended.json'),
);

$thesaurusCollector->pipe(
    ThesaurusOutput::withDestinationAt('../output/thesaurus.json'),
);

$thesaurus->pull();
$graphics->pull();



// Try 2
$graphicsLoader = GraphicsLoader::withSourceAt('../00_rawContent/graphics.xml');
$thesaurusLoader = ThesaurusLoader::withSourceAt('../00_rawContent/thesaurus.xml');
$graphicConditionDeterminer = GraphicConditionDeterminer::new();
$graphicsCollector = GraphicsCollector::new();

$graphicsCollector->setGraphicStreamSink($graphicsLoader);

$graphicConditionDeterminer->setGraphicCollectorSink($graphicsCollector);
$graphicConditionDeterminer->setThesaurusCollectorSink($graphicsLoader);

$pipeline = Pipeline::new()->addMany(
    $graphicsLoader,
    $thesaurusLoader,
    $graphicConditionDeterminer,
);


$graphicsLoader->subscribe($graphicConditionDeterminer);

$graphicsLoader->notify($graphicConditionDeterminer, GraphicConditionDeterminer::GraphicsInput);
$thesaurusLoader->notify($graphicConditionDeterminer, GraphicConditionDeterminer::ThesaurusInput);

$pipeline->start();




// Try 3
$thesaurusCollector =  ThesaurusLoader::withSourceAt('../00_rawContent/thesaurus.xml')->pipe(
    ThesaurusCleanupOp::new(),
    ThesaurusCollector::new(),
);


$graphicsCollector = GraphicsLoader::withSourceAt('../00_rawContent/graphics.xml')->pipe(
    GraphicConditionDeterminerOp::new(),
    GraphicExternalImageExtenderOp::withCacheAt('./cache'),
    GraphicsCollector::new()
);

$graphicsExtender = mergeProviders($thesaurusCollector, $graphicsCollector)->pipe(
    GraphicsExtenderOp::new(),
);

$graphicsExtender->pipe(
    GraphicsOutput::withDestinationAt('../output/graphics.simple.json'),
);


$graphicsExtender->pipe(
    GraphicsOutput::withDestinationAt('../output/graphics.extended.json'),
);
