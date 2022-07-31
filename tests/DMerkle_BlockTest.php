<?php

use PHPUnit\Framework\TestCase;
use DMerkle\DMerkle;
use DMerkle\DMerkle_Block;
use DMerkle\DMerkle_Utility;
use DMerkle\DMerkle_Exception;

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
}
