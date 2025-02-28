<?php

namespace JDLX\DrawioMCDConverter;


require __DIR__ . '/source/autoload.php';

// $file = __DIR__ . '/demo/butler.drawio';
// $file = __DIR__ . '/demo/ecommerce.drawio';
$file = __DIR__ . '/demo/mcd-xml.drawio';


$converter = new Graph($file);

echo '<div style="border: solid 2px #F00">';
    echo '<pre style="background-color: rgba(255,255,255, 0.8);">';
    print_r($converter->getSQL(false));
    echo '</pre>';
echo '</div>';


