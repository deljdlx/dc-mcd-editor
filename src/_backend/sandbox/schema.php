<?php

namespace JDLX\DrawioConverter;

use JDLX\DrawioConverter\SQLExporter\MySQL\Converter;


require __DIR__ . '/../_bootstrap.php';



$source = __DIR__ . '/../demo/cms.drawio';
$raw = file_get_contents($source);



$graph = new Graph();
$graph->loadXML($raw);

$converter = new Converter($graph);

dump($graph->getEntities());
dump($converter->getSQL());







/*
$dropTable = false;
if(array_key_exists('dropTable', $data)) {
    $dropTable = true;
}



$converter = new Converter($graph);


$data = [
    'sql' => $converter->getSQL($dropTable)
];

header('Content-type: application/json');
echo json_encode($data);
*/

