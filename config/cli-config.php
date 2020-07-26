<?php

use App\Kernel;

try {
    return (new Kernel())->cli()->run();
} catch (Exception $e) {
    echo $e->getMessage();
}