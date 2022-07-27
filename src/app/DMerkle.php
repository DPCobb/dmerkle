<?php
declare(strict_types=1);
namespace DMerkle;

use DMerkle\DMerkle_Exception;
use DMerkle\DMerkle_Utility;

class DMerkle
{
    /**
     * max_block_size
     *
     * @var integer
     */
    public int $max_block_size = 100;

    /**
     * transaction_block
     *
     * @var array
     */
    public array $transaction_block;

    /**
     * hash_tree
     *
     * @var array
     */
    public array $hash_tree;

    /**
     * block_unique_id
     *
     * @var string
     */
    public string $block_unique_id;

    /**
     * previous_block_hash
     *
     * @var string
     */
    public string $previous_block_hash;

    /**
     * DMerkle_Utility
     *
     * @var DMerkle_Utility
     */
    public DMerkle_Utility $DMerkle_Utility;

    public function __construct($previous_block_hash = '')
    {
        $this->block_unique_id = hash('sha256', bin2hex(openssl_random_pseudo_bytes(32)) . microtime());
        $this->checkMaxBlockSizeIsOk();
        $this->DMerkle_Utility     = new DMerkle_Utility;
        $this->previous_block_hash = $previous_block_hash;
    }

    /**
     * Check that the set block size is allowed
     *
     * @return void
     */
    public function checkMaxBlockSizeIsOk(): void
    {
        if ($this->max_block_size % 2 !== 0) {
            throw new DMerkle_Exception('Max block size must be divisible by 2');
        }
    }

    /**
     * Set's the given block data
     *
     * @param  array $transaction_block[] The block of transactions used to create this block
     * @return void
     */
    public function setBlockData(array $transaction_block):void
    {
        $this->checkMaxBlockSizeIsOk();

        if (empty($transaction_block)) {
            throw new DMerkle_Exception('Transaction Block is empty');
        }

        if (count($transaction_block) > $this->max_block_size) {
            throw new DMerkle_Exception('Block size over max');
        }

        $this->transaction_block = $transaction_block;
    }

    /**
     * Calculate the hashes for a block level
     *
     * @param  array   $transaction_block The block of transactions used
     * @param  integer $level             The tree level we are in
     * @return array
     */
    public function calculateBlockHash(array $transaction_block = [], int $level = 0): array
    {
        $hashes_out = [];

        for ($i = 0; $i < count($transaction_block); $i++) {
            $transaction_block = $this->DMerkle_Utility->getLevelPairs($transaction_block);
            $left_sibling      = is_array($transaction_block[$i]) ? json_encode($transaction_block[$i]) : $transaction_block[$i];
            $i++;
            $right_sibling = is_array($transaction_block[$i]) ? json_encode($transaction_block[$i]) : $transaction_block[$i];

            if ($level === 0) {
                array_push($hashes_out, $this->DMerkle_Utility->hashTransaction($left_sibling));
                array_push($hashes_out, $this->DMerkle_Utility->hashTransaction($right_sibling));
            } else {
                array_push($hashes_out, $this->DMerkle_Utility->hashTransaction($left_sibling . $right_sibling));
            }
        }

        return $hashes_out;
    }

    /**
     * Run all of our calculations on a block
     *
     * @return array
     */
    public function runBlockCalculation():array
    {
        $hashes_out         = $this->calculateBlockHash($this->transaction_block, 0);
        $this->hash_tree[0] = $hashes_out;
        $level              = 1;
        while (count($hashes_out) > 1) {
            $hashes_out              = $this->calculateBlockHash($hashes_out, $level);
            $this->hash_tree[$level] = $hashes_out;
            $level++;
        }

        return $this->hash_tree;
    }

    /**
     * Form the block data for saving
     *
     * @return array
     */
    public function createBlockData(): array
    {
        $block_data = [
            'header' => [
                'block_id'       => $this->block_unique_id,
                'root_hash'      => array_reverse($this->hash_tree)[0][0],
                'completed'      => date('Y-m-d H:i:s'),
                'previous_block' => $this->previous_block_hash,
            ],
            'base'      => [$this->hash_tree[0]],
            'full_tree' => $this->hash_tree,
        ];

        $block_hash = $this->DMerkle_Utility->hashTransaction($block_data);

        return [
            'block_data' => $block_data,
            'block_hash' => $block_hash,
        ];
    }
}
