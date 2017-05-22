<?php

require_once('vendor/autoload.php');

$cliArguments = $argv;
$scriptName = array_shift($cliArguments);

$options = [];
$inputs = [];
// loop over the given arguments, splitting options into a separate array
while (count($cliArguments)) {
    $value = array_shift($cliArguments);

    if (substr($value, 0, 2) == '--') {
        $options[] = $value;
    } else {
        $inputs[] = $value;
    }
}

$downloader = new ModPackDownloader\Downloader();

$downloader->download();
