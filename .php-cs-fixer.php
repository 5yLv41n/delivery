<?php

$excludes = [
    'bin',
    'config',
    'resources',
    'var',
    'vendor',
];

$finder = (new PhpCsFixer\Finder())->in(__DIR__)->exclude($excludes);

return (new PhpCsFixer\Config())->setFinder($finder);
