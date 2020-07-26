<?php

use App\Kernel;

require __DIR__ . '/../vendor/autoload.php';

try {
    (new Kernel())->web()->run();
} catch (Exception $e) {
    echo $e->getMessage();
}