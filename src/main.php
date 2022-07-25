<?php
require_once 'vendor/autoload.php';
// Tes

use DMerkle\DMerkle;

$m = new DMerkle;
$transactions = [];

for ($i = 1; $i <= 100; $i++) {
    array_push($transactions, [$i]);
}

$m->setBlockData($transactions);
$m->runBlockCalculation();
$block_data = $m->createBlockData();

var_dump($block_data);

$check = $m->hashTransaction([1]);

var_dump($m->isHashValid($check, $block_data['header']['root_hash'], $block_data));
