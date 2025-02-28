<?php

use JDLX\DrawioConverter\Graph;

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/exporter/core/source/autoload.php';

require __DIR__ . '/exporter/drawio-to-sql/source/autoload.php';
require __DIR__ . '/exporter/drawio-to-json/source/autoload.php';
require __DIR__ . '/exporter/drawio-to-wordpress/source/autoload.php';


ini_set('display_errors', true);
error_reporting(E_ALL);


