<?php
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = __DIR__.'/public/Data-Excel-sotk-BRKS.xlsx';
    
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    
    echo "Loading worksheet info...\n";
    $info = $reader->listWorksheetInfo($filePath);
    print_r($info);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
