<?php
declare(strict_types=1);

namespace DMerkle;

use DMerkle\DMerkle_Exception;

class DMerkle
{
    public int $max_block_size = 100;
    public array $transaction_block;
    public array $hash_tree;
    public string $block_unique_id;

    public function __construct()
    {
        $this->block_unique_id = hash('sha256', bin2hex(openssl_random_pseudo_bytes(32)) . microtime());
        $this->checkMaxBlockSizeIsOk();
    }

    public function checkMaxBlockSizeIsOk(): void
    {
        if ($this->max_block_size % 2 !== 0) {
            throw new DMerkle_Exception('Max block size must be divisible by 2');
        }
    }

    /**
     * Set's the given block data
     *
     * @param array $transaction_block[]
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
     * Make sure our level always has pairs
     *
     * @param array $transactions
     * @return array
     */
    public function getLevelPairs(array $transactions): array
    {
        if (count($transactions) % 2 !== 0) {
            $last_transaction = count($transactions) - 1;
            $transactions[count($transactions)] = $transactions[$last_transaction];
        }
        return $transactions;
    }

    /**
     * Calculate the hashes for a block level
     *
     * @param array $transaction_block
     * @param integer $level
     * @return array
     */
    public function calculateBlockHash(array $transaction_block = [], int $level = 0): array
    {
        $hashes_out = [];

        for ($i = 0; $i < count($transaction_block); $i++) {
            $transaction_block = $this->getLevelPairs($transaction_block);
            $left_sibling = is_array($transaction_block[$i]) ? json_encode($transaction_block[$i]) : $transaction_block[$i];
            $i++;
            $right_sibling = is_array($transaction_block[$i]) ? json_encode($transaction_block[$i]) : $transaction_block[$i];

            if ($level === 0) {
                array_push($hashes_out, $this->hashTransaction($left_sibling));
                array_push($hashes_out, $this->hashTransaction($right_sibling));
            } else {
                array_push($hashes_out, $this->hashTransaction($left_sibling . $right_sibling));
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
        $hashes_out = $this->calculateBlockHash($this->transaction_block, 0);
        $this->hash_tree[0] = $hashes_out;
        $level = 1;
        while (count($hashes_out) > 1) {
            $hashes_out = $this->calculateBlockHash($hashes_out, $level);
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
        return [
            'header' => [
                'block_id'  => $this->block_unique_id,
                'root_hash' => array_reverse($this->hash_tree)[0][0],
                'completed' => date('Y-m-d H:i:s'),
            ],
            'base'      => [ $this->hash_tree[0] ],
            'full_tree' => $this->hash_tree
        ];
    }

    /**
     * Hash transactions
     *
     * @param mixed $transaction
     * @return string
     */
    public function hashTransaction($transaction): string
    {
        if (!is_string($transaction)) {
            $transaction = json_encode($transaction);
        }
        return hash('sha256', hash('sha256', $transaction));
    }

    /**
     * Validate a transaction hash belongs to the given block
     *
     * @param string $hash_to_check
     * @param string $root_hash
     * @param array $block_data
     * @return boolean
     */
    public function isHashValid(string $hash_to_check, string $root_hash, array $block_data): bool
    {
        // check if hash is in level 0
        if (!in_array($hash_to_check, $block_data['base'])) {
            return false;
        }
        // if hash exists check if it is a left or right hash (left is always odd)
        $is_left_sibling = array_search($hash_to_check, $block_data['base']);
        var_dump($is_left_sibling);
        // check hashes up to root
        return true;
    }
}
