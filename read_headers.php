<?php
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class HeaderFilter implements IReadFilter {
    public function readCell($columnAddress, $row, $worksheetName = '') {
        return $row === 1; // Only read the first row
    }
}

try {
    $filePath = __DIR__.'/public/Data-Excel-sotk-BRKS.xlsx';
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    $reader->setReadFilter(new HeaderFilter());
    
    $spreadsheet = $reader->load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $headers = [];
    foreach ($worksheet->getRowIterator(1, 1) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $headers[] = $cell->getValue();
        }
    }
    
    echo "Raw Headings:\n";
    print_r($headers);
    
    echo "\nNormalized Headings:\n";
    $normalizedHeadings = array_map(fn($h) => mb_strtolower(trim((string)$h)), $headers);
    print_r($normalizedHeadings);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
