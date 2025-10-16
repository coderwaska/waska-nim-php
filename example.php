<?php

use Wastukancana\Nim;
use Wastukancana\Provider\PDDikti;

require __DIR__.'/vendor/autoload.php';

try {
    $nim = new Nim('91151001', PDDikti::class);

    var_dump($nim->dump());
} catch (\Exception $e) {
    echo $e->getMessage();
}
