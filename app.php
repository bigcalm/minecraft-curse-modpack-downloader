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

$modpackPath = $inputs[0];
$instancePath = $inputs[1];

$downloader = new ModPackDownloader\Downloader();


if (isset($inputs[0])) {
    $downloader->setManifestFile($modpackPath . DIRECTORY_SEPARATOR . 'manifest.json');
}

if (isset($inputs[1])) {
    $downloader->setModsDirectory($instancePath . DIRECTORY_SEPARATOR . 'mods');
}

$downloader->download();

$this->out('Copying overrides directory into instance.');
copy($modpackPath . DIRECTORY_SEPARATOR . 'overrides', $instancePath);

$this->out('');
$this->out('Download Finished.');
