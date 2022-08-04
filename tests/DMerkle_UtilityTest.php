<?php

use PHPUnit\Framework\TestCase;
use DMerkle\DMerkle_Utility;

/**
 * @covers DMerkle\DMerkle_Utility
 */
class DMerkle_UtilityTest extends TestCase
{
    public function testHashTransactionHashesArray(): void {
        $DMerkle_Utility = new DMerkle_Utility;
        $result = $DMerkle_Utility->hashTransaction([[1], [2], [3], [4]]);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testHashTransactionHashesString(): void {
        $DMerkle_Utility = new DMerkle_Utility;
        $result = $DMerkle_Utility->hashTransaction('Some JSON string');

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGetLevelPairsEvensArray(): void {
        $data = [[1], [2], [3]];

        $DMerkle_Utility = new DMerkle_Utility;

        $result = $DMerkle_Utility->getLevelPairs($data);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals([3], $result[3]);
    }
    public function testGetLevelPairsOnEvenArray(): void {
        $data = [[1], [2], [3], [4]];

        $DMerkle_Utility = new DMerkle_Utility;

        $result = $DMerkle_Utility->getLevelPairs($data);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals([4], $result[3]);
    }
}
