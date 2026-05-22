<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\SotkMaster::where('unit_kantor', 'Divisi Manajemen Sumber Daya Insani')->get();
foreach ($rows as $row) {
    echo "Lvl: {$row->level_jabatan} | Jabatan: {$row->jabatan} | Penempatan: {$row->penempatan}\n";
}
