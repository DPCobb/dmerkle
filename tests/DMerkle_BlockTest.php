<?php

use PHPUnit\Framework\TestCase;
use DMerkle\DMerkle;
use DMerkle\DMerkle_Block;
use DMerkle\DMerkle_Utility;
use DMerkle\DMerkle_Exception;

/**
 * @covers DMerkle\DMerkle_Block
 */
class DMerkle_BlockTest extends TestCase
{
    /**
     * block_data
     *
     * @var array
     */
    protected array $block_data;

    public function setup(): void
    {
        $this->block_data = [];
        for ($i = 1; $i <= 4; $i++) {
            array_push($this->block_data, [$i]);
        }
    }

    public function testClassSetsUpCorrectly()
    {
        $DMerkle_Block = new class($this->block_data) extends DMerkle_Block {
        };

        $this->assertIsArray($DMerkle_Block->block_data);
        $this->assertInstanceOf(DMerkle_Utility::class, $DMerkle_Block->DMerkle_Utility);
    }

    public function testGetBlockHash()
    {
        $DMerkle_Block = new class($this->block_data) extends DMerkle_Block {
        };

        $this->assertIsString($DMerkle_Block->getBlockHash());
        $this->assertNotEmpty($DMerkle_Block->getBlockHash());
    }

    public function testConstructorFailsIfEmptyBlockData() {
        $this->expectException(DMerkle_Exception::class);
        $this->expectExceptionMessage('Block data is empty!');

        $DMerkle_Block = new class([]) extends DMerkle_Block {
        };
    }

    public function testTransactionIsValid() {
        $DMerkle = new class extends DMerkle {
        };
        $DMerkle->setBlockData($this->block_data);

        $DMerkle->runBlockCalculation();
        $block_data = $DMerkle->createBlockData();

        $DMerkle_Block = new class($block_data['block_data']) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->transactionIsPartOfBlock([1], $block_data['block_data']['header']['root_hash']);

        $this->assertTrue($result);
    }
    public function testTransactionIsValidRightSibling() {
        $DMerkle = new class extends DMerkle {
        };
        $DMerkle->setBlockData($this->block_data);

        $DMerkle->runBlockCalculation();
        $block_data = $DMerkle->createBlockData();

        $DMerkle_Block = new class($block_data['block_data']) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->transactionIsPartOfBlock([2], $block_data['block_data']['header']['root_hash']);

        $this->assertTrue($result);
    }
    public function testTransactionIsNotPartOfTree() {
        $DMerkle = new class extends DMerkle {
        };
        $DMerkle->setBlockData($this->block_data);

        $DMerkle->runBlockCalculation();
        $block_data = $DMerkle->createBlockData();

        $DMerkle_Block = new class($block_data['block_data']) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->transactionIsPartOfBlock([9999], $block_data['block_data']['header']['root_hash']);

        $this->assertFalse($result);
    }
    public function testPreviousBlockIsValid() {
        $DMerkle = new class extends DMerkle {
        };
        $DMerkle->setBlockData($this->block_data);
        $DMerkle->previous_block_hash = '123';
        $DMerkle->runBlockCalculation();
        $block_one = $DMerkle->createBlockData();
        $block_one_hash = $block_one['block_hash'];

        $DMerkle->setBlockData($this->block_data);
        $DMerkle->previous_block_hash = $block_one_hash;
        $DMerkle->runBlockCalculation();
        $block_two = $DMerkle->createBlockData();


        $DMerkle_Block = new class($block_two['block_data']) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->previousBlockHashIsValid($block_one_hash, $block_two['block_hash']);

        $this->assertTrue($result);
    }

    public function testValidateUpTreeIsFalse() {
        $block_data = $this->block_data;
        $block_data['full_tree'][1] = ['ase', 'fgh'];
        $DMerkle_Block = new class($block_data) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->validateUpTree('123', 'abc');

        $this->assertFalse($result);
    }
    public function testValidateUpTreeRightSibling() {
        $block_data = $this->block_data;
        $block_data['full_tree'][1] = ['ase', '123'];
        $block_data['full_tree'][2] = ['abc'];
        $DMerkle_Block = new class($block_data) extends DMerkle_Block {
        };

        $result = $DMerkle_Block->validateUpTree('123', 'abc');

        $this->assertTrue($result);
    }
}
