<?php

namespace JDLX\DrawioConverter;



use JDLX\DrawioConverter\Wordpress\Converter;



$graph = require __DIR__.'/_bootstrap.php';

$converter = new Converter($graph);





$data = [
    // 'code' => $converter->getJSON()
    'code' => $converter->getCode()
];

header('Content-type: application/json');
echo json_encode($data);

