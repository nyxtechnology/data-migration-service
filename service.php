<?php

require __DIR__ . '/vendor/autoload.php';
require "src/DataMigration/DataMigration.php";


use DataMigration\DataMigration;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


// Get json configuration
$settings = json_decode(file_get_contents("config.json"));

// Get data from start service

$dataMigration = new DataMigration();
if ($settings->from->auth)
    $importedData = $dataMigration->getData($settings->from->uri, $settings->from->auth, $settings->from->auth_key);
else
    $importedData = $dataMigration->getData($settings->from->uri);

echo $importedData;
// TODO: Loop in the data read and save in the new service
//foreach ($importedData as $data) {
//    // TODO: Save data in new service
//    $postData = $dataMigration->postData($settings->from->uri, $settings->from->body, $settings->from->auth, $settings->from->auth_key);
//}


// TODO: save where stop
// TODO: log information about service operation