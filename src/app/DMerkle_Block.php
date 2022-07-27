<?php
declare(strict_types=1);
namespace DMerkle;

use DMerkle\DMerkle_Exception;
use DMerkle\DMerkle_Utility;

class DMerkle_Block
{
    /**
     * block_data
     *
     * @var array
     */
    public array $block_data;

    /**
     * DMerkle_Utility
     *
     * @var DMerkle_Utility
     */
    public DMerkle_Utility $DMerkle_Utility;

    public function __construct(array $block_data)
    {
        if (empty($block_data)) {
            throw new DMerkle_Exception('Block data is empty!');
        }

        $this->block_data      = $block_data;
        $this->DMerkle_Utility = new DMerkle_Utility;
    }

    /**
     * Make sure our transaction is part of the block. This creates the base level data then validates up tree
     *
     * @param  array   $transaction The transaction information we are validating
     * @param  string  $root_hash   The root hash of the block
     * @return boolean
     */
    public function transactionIsPartOfBlock(array $transaction, string $root_hash): bool
    {
        $hash_to_check = $this->DMerkle_Utility->hashTransaction($transaction);

        if (!in_array($hash_to_check, $this->block_data['full_tree'][0])) {
            return false;
        }

        $hash_position_in_base = array_search($hash_to_check, $this->block_data['full_tree'][0]);
        $is_left_sibling       = $hash_position_in_base % 2 === 0;

        if ($is_left_sibling) {
            $hash_sibling  = $this->block_data['full_tree'][0][$hash_position_in_base + 1];
            $next_hash_raw = $hash_to_check . $hash_sibling;
        } else {
            $hash_sibling  = $this->block_data['full_tree'][0][$hash_position_in_base - 1];
            $next_hash_raw = $hash_sibling . $hash_to_check;
        }

        $next_hash = $this->DMerkle_Utility->hashTransaction($next_hash_raw);

        // check hashes up to root

        return $this->validateUpTree($next_hash, $root_hash);
    }

    /**
     * Validates our transaction to the root node by finding siblings
     *
     * @param  string  $hash      The hashed value of the transaction we are validating
     * @param  string  $root_hash The root hash of the block
     * @return boolean
     */
    public function validateUpTree(string $hash, string $root_hash): bool
    {
        $level = 1;
        while (count($this->block_data['full_tree'][$level]) > 1) {
            $row = $this->DMerkle_Utility->getLevelPairs($this->block_data['full_tree'][$level]);
            if (!in_array($hash, $row)) {
                return false;
            }
            $hash_position_in_base = array_search($hash, $row);
            $is_left_sibling       = $hash_position_in_base % 2 === 0;

            if ($is_left_sibling) {
                $hash_sibling  = $row[$hash_position_in_base + 1];
                $next_hash_raw = $hash . $hash_sibling;
            } else {
                $hash_sibling  = $row[$hash_position_in_base - 1];
                $next_hash_raw = $hash_sibling . $hash;
            }

            $hash = $this->DMerkle_Utility->hashTransaction($next_hash_raw);

            $level++;
        }
        $row = $this->block_data['full_tree'][$level];

        return hash_equals($row[0], $root_hash);
    }

    /**
     * Get the hash of this block
     *
     * @return string
     */
    public function getBlockHash():string
    {
        return $this->DMerkle_Utility->hashTransaction($this->block_data);
    }

    /**
     * Check if a given previous hash is valid
     *
     * @param  string  $previous_block_unknown    The untested hash of the previous block
     * @param  string  $current_block_stored_hash The stored and known hash of the current block
     * @return boolean
     */
    public function previousBlockHashIsValid(string $previous_block_unknown, string $current_block_stored_hash): bool
    {
        $test_block_data                             = $this->block_data;
        $test_block_data['header']['previous_block'] = $previous_block_unknown;

        $test_block_hash = $this->DMerkle_Utility->hashTransaction($test_block_data);

        return hash_equals($current_block_stored_hash, $test_block_hash);
    }
}
