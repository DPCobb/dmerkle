<?php

use PHPUnit\Framework\TestCase;
use DMerkle\DMerkle;
use DMerkle\DMerkle_Exception;

class DMerkleTest extends TestCase
{
    public function setup(): void {
        $this->block_data = [];
        for ($i = 1; $i <= 4; $i++) {
            array_push($this->block_data, [$i]);
        }
    }

    public function testMaxBlockSizeIsOkThrowsException() {
        $DMerkle = new class extends DMerkle {};
        $DMerkle->max_block_size = 3;
        $this->expectException(DMerkle_Exception::class);

        $DMerkle->checkMaXBlockSizeIsOk();
    }

    public function testTransactionBlockIsSet() {
        $DMerkle = new class extends DMerkle {};
        $DMerkle->setBlockData($this->block_data);

        $this->assertEquals($this->block_data, $DMerkle->transaction_block);
    }

    public function testTransactionBlockThrowsExceptionOnEmptyBlock() {
        $DMerkle = new class extends DMerkle {};
        $this->expectException(DMerkle_Exception::class);
        $DMerkle->setBlockData([]);
    }

    public function testTransactionBlockTooLargeThrowsException(){
        $DMerkle = new class extends DMerkle {
            public int $max_block_size = 2;
        };
        $this->expectException(DMerkle_Exception::class);
        $DMerkle->setBlockData($this->block_data);
    }

    public function testRunBlockCalculationReturnsHashTree() {
        $DMerkle = new class extends DMerkle {};
        $DMerkle->setBlockData($this->block_data);

        $hash_tree = $DMerkle->runBlockCalculation();

        $this->assertIsArray($hash_tree);
        $this->assertEquals(3, count($hash_tree));
    }

    public function testTwoIdenticalBlocksProduceSameRootHash() {
        $DMerkle = new class extends DMerkle {};
        $DMerkle->setBlockData($this->block_data);

        $hash_tree = $DMerkle->runBlockCalculation();

        $DMerkle_Two = new class extends DMerkle {};
        $DMerkle_Two->setBlockData($this->block_data);

        $hash_tree_two = $DMerkle->runBlockCalculation();

        $this->assertSame($hash_tree, $hash_tree_two);
        $this->assertSame($hash_tree[2][0], $hash_tree_two[2][0]);
    }

    public function testBlockDataCreates() {
        $DMerkle = new class extends DMerkle {};
        $DMerkle->setBlockData($this->block_data);

        $DMerkle->runBlockCalculation();
        $block_data = $DMerkle->createBlockData();

        $this->assertIsArray($block_data);
        $this->assertEquals(2, count($block_data));
        $this->assertIsString($block_data['block_hash']);
        $this->assertIsArray($block_data['block_data']);
        $this->assertArrayHasKey('header', $block_data['block_data']);
        $this->assertArrayHasKey('base', $block_data['block_data']);
        $this->assertArrayHasKey('full_tree', $block_data['block_data']);
    }
}
