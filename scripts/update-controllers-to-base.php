<?php

/**
 * Script to help update controllers to extend BaseApiController
 *
 * This script identifies controllers that need to be updated and provides
 * a list of changes needed.
 *
 * Usage: php scripts/update-controllers-to-base.php
 */
$controllersDir = __DIR__.'/../app/Http/Controllers/Api/V1';
$controllers = glob($controllersDir.'/*Controller.php');

$updated = [];
$needsUpdate = [];

foreach ($controllers as $controllerFile) {
    $content = file_get_contents($controllerFile);
    $basename = basename($controllerFile);

    if ($basename === 'BaseApiController.php') {
        continue;
    }

    if (strpos($content, 'extends BaseApiController') !== false) {
        $updated[] = $basename;
    } elseif (strpos($content, 'extends Controller') !== false) {
        $needsUpdate[] = $basename;
    }
}

echo "=== Controller Update Status ===\n\n";
echo '✅ Updated ('.count($updated)."):\n";
foreach ($updated as $controller) {
    echo "  - $controller\n";
}

echo "\n⏳ Needs Update (".count($needsUpdate)."):\n";
foreach ($needsUpdate as $controller) {
    echo "  - $controller\n";
}

echo "\n=== Summary ===\n";
echo 'Total controllers: '.(count($updated) + count($needsUpdate))."\n";
echo 'Updated: '.count($updated).' ('.round(count($updated) / (count($updated) + count($needsUpdate)) * 100)."%)\n";
echo 'Remaining: '.count($needsUpdate)."\n";
