<?php
$composerJsonPath = __DIR__ . '/composer.json';
$composerJson = file_get_contents($composerJsonPath);
$composerProject = json_decode($composerJson);
$currentYear = date('Y');

$projectHeader = <<<HEADER

@author forecho <caizhenghai@gmail.com>
@link https://cashwarden.com/
@copyright Copyright (c) 2020-{$currentYear} forecho
@license https://github.com/cashwarden/api/blob/master/LICENSE.md
@version {$composerProject->version}
HEADER;

use plumthedev\PhpCsFixer\Config;

$csConfig = Config::create();

// CS Config setup
$csConfig->mergeRules([
    'header_comment' => [
        'header' => $projectHeader,
        'commentType' => 'PHPDoc',
        'separate' => 'bottom',
    ]
]);

// CS Finder setup
$csConfigFinder = $csConfig->getFinder();
$csConfigFinder->in(__DIR__); // set current project directory
$csConfig->setFinder($csConfigFinder);

return $csConfig;