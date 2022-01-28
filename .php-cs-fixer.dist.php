<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        'strict_param' => true,
    ])
   	->setRiskyAllowed(true)
    ->setFinder($finder)
;