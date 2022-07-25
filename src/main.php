<?php
require_once 'vendor/autoload.php';
// Tes

use DMerkle\DMerkle;
use DMerkle\DMerkle_Block;

$m = new DMerkle;
$transactions = [];

for ($i = 1; $i <= 6; $i++) {
    array_push($transactions, [$i]);
}

$m->setBlockData($transactions);
$m->runBlockCalculation();
$block_data = $m->createBlockData();

var_dump($block_data);

$block = new DMerkle_Block($block_data);

var_dump($block->transactionIsPartOfBlock([1], $block_data['header']['root_hash']));
