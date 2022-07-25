<?php
declare(strict_types=1);

namespace DMerkle;

use DMerkle\DMerkle_Exception;

class DMerkle_Block
{

    public array $block_data;

    public function __construct(array $block_data)
    {
        if (empty($block_data)) {
            throw new DMerkle_Exception('Block data is empty!');
        }

        $this->block_data = $block_data;
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
     * Make sure our transaction is part of the block. This creates the base level data then validates up tree
     *
     * @param string $hash_to_check
     * @param string $root_hash
     * @return boolean
     */
    public function transactionIsPartOfBlock(string $hash_to_check, string $root_hash): bool
    {
        // check if hash is in level 0
        if (!in_array($hash_to_check, $this->block_data['full_tree'][0])) {
            return false;
        }

        $hash_position_in_base = array_search($hash_to_check, $this->block_data['full_tree'][0]);
        $is_left_sibling = $hash_position_in_base % 2 === 0;

        if ($is_left_sibling) {
            $hash_sibling = $this->block_data['full_tree'][0][$hash_position_in_base + 1];
            $next_hash_raw = $hash_to_check . $hash_sibling;
        } else {
            $hash_sibling = $this->block_data['full_tree'][0][$hash_position_in_base - 1];
            $next_hash_raw = $hash_sibling . $hash_to_check;
        }

        $next_hash = $this->hashTransaction($next_hash_raw);

        // check hashes up to root

        return $this->validateUpTree($next_hash, $root_hash);
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
     * Validates our transaction to the root node by finding siblings
     *
     * @param string $hash
     * @param string $root_hash
     * @return boolean
     */
    public function validateUpTree(string $hash, string $root_hash): bool
    {
        $level = 1;
        while (count($this->block_data['full_tree'][$level]) > 1) {
            $row = $this->getLevelPairs($this->block_data['full_tree'][$level]);
            if (!in_array($hash, $row)) {
                return false;
            }
            $hash_position_in_base = array_search($hash, $row);
            $is_left_sibling = $hash_position_in_base % 2 === 0;
        
            if ($is_left_sibling) {
                $hash_sibling = $row[$hash_position_in_base + 1];
                $next_hash_raw = $hash . $hash_sibling;
            } else {
                $hash_sibling = $row[$hash_position_in_base - 1];
                $next_hash_raw = $hash_sibling . $hash;
            }

            $hash = $this->hashTransaction($next_hash_raw);

            $level++;
        }
        $row = $this->block_data['full_tree'][$level];

        return hash_equals($row[0], $root_hash);
    }
}
