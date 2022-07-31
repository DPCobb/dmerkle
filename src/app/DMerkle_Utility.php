<?php
declare(strict_types=1);
namespace DMerkle;

/**
 * Utility class for methods used globally
 */
class DMerkle_Utility
{

	/**
	 * Hash transactions
	 *
	 * @param  mixed $transaction The transaction to hash
	 * @return string
	 */
	public function hashTransaction($transaction): string {
		if (!is_string($transaction)) {
			$transaction = json_encode($transaction);
		}
		return hash('sha256', hash('sha256', $transaction));
	}

	/**
	 * Make sure our level always has pairs
	 *
	 * @param  array $transactions The transactions to check for pairs
	 * @return array
	 */
	public function getLevelPairs(array $transactions): array {
		if (count($transactions) % 2 !== 0) {
			$last_transaction                   = count($transactions) - 1;
			$transactions[count($transactions)] = $transactions[$last_transaction];
		}
		return $transactions;
	}
}
