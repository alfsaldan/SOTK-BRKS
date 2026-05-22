<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

try {
    $headings = Excel::toArray(new HeadingRowImport, 'public/Data-Excel-sotk-BRKS.xlsx');
    print_r($headings);
    
    $rawData = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray, \Maatwebsite\Excel\Concerns\WithHeadingRow {
        public function array(array $array) {
            return $array;
        }
    }, 'public/Data-Excel-sotk-BRKS.xlsx');
    
    echo "First row data with HeadingRow:\n";
    print_r($rawData[0][0] ?? []);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
