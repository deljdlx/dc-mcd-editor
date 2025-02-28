<?php

namespace JDLX\DrawioConverter;

use JDLX\DrawioConverter\SQLExporter\MySQL\Converter;


$graph = require __DIR__.'/_get-graph.php';

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

