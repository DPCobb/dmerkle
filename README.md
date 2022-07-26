# dmerkle

![License](https://img.shields.io/badge/license-The%20Unlicense-blue)
![WIP](https://img.shields.io/badge/Work%20In%20Progress-red)
![Version](https://img.shields.io/badge/Version:%201.0.0-green)
![PHP](https://img.shields.io/badge/PHP%20^7.4-777BB4?logo=php&logoColor=white)
![PHP](https://img.shields.io/badge/PHPUnit:%20Passing-777BB4?logo=php&logoColor=white)

This library is a work In Progress.

## Description

A PHP implementation of a Merkle Hash tree. Completed hashes are returned in blocks and proof of inclusion can be run on blocks to check if transactions are included in that block.

## Usage

You should do the following store your transactions in a database with a `block_id` field and `root_hash` field. Process your transactions via a first in first out queue. You will also need a table to store block data to make it possible to validate transactions.

You will need to pass these transactions into the hashing function in groups based on the block size you set (defaults to 100 transactions per block). For example if your block size was 2 you would pass something similar to this:

```php
$transactions_to_hash = [
    ['foo'],
    ['bar']
];
```

Data passed into the hashing function should be an array of arrays no larger than the max block size, a `DMerkle_Exception` is thrown if this size is surpassed.

### Using the DMerkle.php file

After building your queue and storing your transactions you can begin building data to pass to the hashing method.

```php
use DMerkle\DMerkle;

$previous_block_hash = '123456bcdef';
$DMerkle = new DMerkle($previous_block_hash);
// Data from your queue in groups equal to your max block size
$transactions = [ ... ];

// Change the default block size
$DMerkle->max_block_size = 50;
// Pass a block of no more than 50 transactions
$DMerkle->setBlockData($transactions);
// Run the hashes
$DMerkle->runBlockCalculation();
// Get block data
$block_data = $DMerkle->createBlockData();
```

The `createBlockData` method will return the structured data of the block, including the complete hash tree. You will need to store this block information to validate blocks! It will also return the block hash for you to use in your next block. This will allow you to validate blocks and should also be saved with your block data! Passing the previous blocks hash into your current block means that if anything changes in any block prior to the current one your current block will not have the same block hash. This will allow us to validate a block is unchanged.

Example block data:

```php
array(2) {
  ["block_data"]=>
  array(3) {
    ["header"]=>
    array(4) {
      ["block_id"]=>
      string(64) "5534f1313ea84c84faff9c99a766fb942864c86e53ce42be94f6a9bc7c322d43"
      ["root_hash"]=>
      string(64) "c12ab12acc2b1567e0c58809005d4e99b84f5a6c640dc3b6285fd70177242fc4"
      ["completed"]=>
      string(19) "2022-07-25 23:14:35"
      ["previous_block"]=>
      string(0) ""
    }
    ["base"]=>
    array(1) {
      [0]=>
      array(6) {
        [0]=>
        string(64) "31a76d7c1c7e8caffd45978dd1550716fded6121fe304172df75182b5888a49d"
        [1]=>
        string(64) "0b61a45894993b25785ea2dc0ff419db8c07eb626d05bf7c02268f536868f36c"
        [2]=>
        string(64) "ff87ae9ef3c9a0280c910142a2b7bde3413ffd0b21746f6e46359aa6ed67baea"
        [3]=>
        string(64) "a9b820c175525dd527b2076bb1be3c303ec2d05a7ab0f84c6af90d040ceb3230"
        [4]=>
        string(64) "80fe5885c37f06b5a8dc501323dc68299df99dda00ff3b04f2c9a9b7a37477bc"
        [5]=>
        string(64) "64755db4b6bdde855a6ddc641fb7b9e8d3238a7303da2cc25a6a4631304bd81b"
      }
    }
    ["full_tree"]=>
    array(4) {
      [0]=>
      array(6) {
        [0]=>
        string(64) "31a76d7c1c7e8caffd45978dd1550716fded6121fe304172df75182b5888a49d"
        [1]=>
        string(64) "0b61a45894993b25785ea2dc0ff419db8c07eb626d05bf7c02268f536868f36c"
        [2]=>
        string(64) "ff87ae9ef3c9a0280c910142a2b7bde3413ffd0b21746f6e46359aa6ed67baea"
        [3]=>
        string(64) "a9b820c175525dd527b2076bb1be3c303ec2d05a7ab0f84c6af90d040ceb3230"
        [4]=>
        string(64) "80fe5885c37f06b5a8dc501323dc68299df99dda00ff3b04f2c9a9b7a37477bc"
        [5]=>
        string(64) "64755db4b6bdde855a6ddc641fb7b9e8d3238a7303da2cc25a6a4631304bd81b"
      }
      [1]=>
      array(3) {
        [0]=>
        string(64) "56f1e19e51877ee8e398af8e83167390dec0552ef4449baf1de717bb7b22ab5d"
        [1]=>
        string(64) "33129c523c53ec276c6470284c94b417fc6af47d867fe3028bba05d7d4e2eb9c"
        [2]=>
        string(64) "1b54ea0fc30fd68b12e94fc8a8cf53a6fd15894feeea485c6f6a1c8496f5dbc9"
      }
      [2]=>
      array(2) {
        [0]=>
        string(64) "8afdae9451bde20d23ad26911acc7378d207c31fc430896c362e105b823c667e"
        [1]=>
        string(64) "c18204abc5b4e9bf5cc92c3c832ffc72a8d33afa5be72110969530ae142eacd6"
      }
      [3]=>
      array(1) {
        [0]=>
        string(64) "c12ab12acc2b1567e0c58809005d4e99b84f5a6c640dc3b6285fd70177242fc4"
      }
    }
  }
  ["block_hash"]=>
  string(64) "e1a822184c2f1909fcf50cc86706442c6705a57186a849cbb86559046c38b0be"
}
```

This data should be stored and the `block_id` and `root_hash` should be saved to each transaction used to create this block.

### Validate a transaction

To validate a transaction you need to pass the transaction data used when creating blocks and the `root_hash` of that block. You will also need to load the complete block data returned from the hashing method into `DMerkle_Block::class`.

```php
use DMerkle\DMerkle_Block;
$block_data_from_hashing = [ ... ]; // loaded from your db somewhere
$transaction_data = [ ... ]; // loaded from your db, data used when creating blocks
$root_hash = '...'; // loaded from your db


$DMerkle_Block = new DMerkle_Block($block_data_from_hashing);

$transaction_is_valid = $block->transactionIsPartOfBlock($transaction_data, $root_hash);
```
The `transactionIsPartOfBlock` method will return `true | false` and will validate the transaction from the base level up to the root hash by hashing it and the siblings it was hashed with up to the root hash level. This means if the hash is changed at the base level there is still no way the root hash would be the same unless the entire block was compromised and hashed again.

### Validate a block

It is possible to validate a block is unchanged by either recreaing or rehashing that block and using the `DMerkle_Block` class to validation the hash. To do this the previous block hash should always be passed into the current block when hashing transactions into a block. This technique means that any change in a block prior to the current one would create a different hash for the current block.

```php
use DMerkle\DMerkle_Block;

$old_block = new DMerkle_Block($old_block_data);
$old_block_hash = $old_block->getBlockHash();

$current_block = new DMerkle_Block($current_block_data);

$old_block_hash_is_valid = $current_block->previousBlockHashIsValid($old_block_hash, $current_block_stored_hash)

```
Since the old block hash is hashed as part of the new block if that old hash is ever changed it would cause the current blocks hash to be different thant what is stored.