<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\OrgChartBuilderService::class);
$nodes = $service->build(4, 'BRKS Batam');
$nodesCol = collect($nodes);

foreach($nodes as $n) {
    $parent = $nodesCol->firstWhere('id', $n['parentId']);
    echo "[Rank {$n['rank']}] {$n['jabatan']} --> " . ($parent['jabatan'] ?? 'NULL') . "\n";
}
