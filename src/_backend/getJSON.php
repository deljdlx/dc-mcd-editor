<?php

namespace JDLX\DrawioConverter;

use JDLX\DrawioConverter\JSON\Converter;



$graph = require __DIR__.'/_bootstrap.php';



$converter = new Converter($graph);


$data = [
    'code' => $converter->getJSON()
];

header('Content-type: application/json');
echo json_encode($data);

