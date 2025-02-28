<?php

use JDLX\DrawioConverter\Graph;

require __DIR__ . '/exporter/core/source/autoload.php';

require __DIR__ . '/exporter/drawio-to-sql/source/autoload.php';
require __DIR__ . '/exporter/drawio-to-json/source/autoload.php';
require __DIR__ . '/exporter/drawio-to-wordpress/source/autoload.php';


ini_set('display_errors', true);
error_reporting(E_ALL);

$graph = new Graph();

$raw = file_get_contents('php://input');

$data = json_decode($raw, true);

if($raw) {
    $xml = $data['xml'];
    $graph = new Graph();
    $graph->loadXML($xml);
    return $graph;
}
else {
    echo 'No data received';
    exit();
}
