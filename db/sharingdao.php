<?php

namespace OCA\ocmultishare\Db;

class SharingDAO {
	private $db;

	/**
	 * Constructor.
	 * $db -- Database connection handle.
	 */

	public function __construct(IDb $db) {
		$this->db = $db;
	}

	/** 
	 * @param int $share_id - ID of share
	 *
	 * Returns with username of owner of a particular share.
	 */

	public function getShareOwner($share_id) {
		$stmt = $this->db->prepareQuery('SELECT * FROM `*PREFIX*share` WHERE id = ?');
		$stmt->bindParam(1, $share_id, \PDO::PARAM_INT);
		$stmt->execute();

		$share_info = $stmt->fetch();

		if ($share_info === FALSE) {
			throw new \Exception('Cannot find share ' . intval($share_id) . ' in database');
		}

		else {
			$stmt->closeCursor();

			return $share_info["uid_owner"];
		}
	}

	/**
	 * @param int $share_id ID of existing share
	 * @param int $seconds - Seconds to expiry of new share
	 *
	 * Duplicates an existing share, and assigns a new token to 
	 * the new share.
	 *
	 * Starts by getting an existing share indicated
	 * by $share_id from DB, then creates a new one built
	 * using the existing one, but with new expiry-date
	 * and a new token. 
	 */

	public function duplicateShareWithNewToken($share_id, $seconds) {
		/*
	 	 * Ask for information about share.
		 * Then get it and save in memory.
		 */

		$stmt = $this->db->prepareQuery('SELECT * FROM `*PREFIX*share` WHERE id = ?');
		$stmt->bindParam(1, $share_id, \PDO::PARAM_INT);
		$stmt->execute();

		$share_info_new = $stmt->fetch();
		$stmt->closeCursor();

		if ($share_info_new === FALSE) {
			throw new \Exception('Cannot find share with ID ' . intval($share_id) . ' in database');
		}


		/* 
		 * Insert a full new share into the share-table.
		 * 
		 * This will enable users to access the same file
		 * as in the original share, but with a different token
 		 * that expires at a different time.
		 */

		$stmt = $this->db->prepareQuery("INSERT INTO `*PREFIX*share` (share_type,  uid_owner, item_type,  item_source,  item_target,  file_source,  file_target, permissions, stime,  accepted, expiration, token, mail_send) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		/* Generate new token */
		$share_info_new["token"] = \OC::$server->getSecureRandom()->getMediumStrengthGenerator()->generate(\OC\Share\Share::TOKEN_LENGTH,
						\OCP\Security\ISecureRandom::CHAR_LOWER . \OCP\Security\ISecureRandom::CHAR_UPPER .
						\OCP\Security\ISecureRandom::CHAR_DIGITS
					);

		/* Check if we actually got some token of any worth */
		if (strlen($share_info_new["token"]) <= 5) {
			throw new \Exception('Token was malformed, cannot save new share');
		}

		/* New expiration date (down to the second) */
		$share_info_new["expiration"] = date("o-m-d H:i:s", time() + $seconds);

		/*
		 * Set all parameters - most will be the same as in the original
		 * share we copied.
		 */

		$stmt->bindParam(1, $share_info_new["share_type"], \PDO::PARAM_INT);
		$stmt->bindParam(2, $share_info_new["uid_owner"], \PDO::PARAM_STR);
		$stmt->bindParam(3, $share_info_new["item_type"], \PDO::PARAM_STR);
		$stmt->bindParam(4, $share_info_new["item_source"], \PDO::PARAM_STR);
		$stmt->bindParam(5, $share_info_new["item_target"], \PDO::PARAM_STR);
		$stmt->bindParam(6, $share_info_new["file_source"], \PDO::PARAM_INT);
		$stmt->bindParam(7, $share_info_new["file_target"], \PDO::PARAM_STR);
		$stmt->bindParam(8, $share_info_new["permissions"], \PDO::PARAM_INT);
		$stmt->bindParam(9, $share_info_new["stime"], \PDO::PARAM_INT);
		$stmt->bindParam(10, $share_info_new["accepted"], \PDO::PARAM_INT);
		$stmt->bindParam(11, $share_info_new["expiration"], \PDO::PARAM_STR);
		$stmt->bindParam(12, $share_info_new["token"], \PDO::PARAM_STR);
		$stmt->bindParam(13, $share_info_new["mail_send"], \PDO::PARAM_INT);

		/* Save the thing */
		if ($stmt->execute() === FALSE) {
			throw new \Exception('Cannot save new share in database');
		}

		return $share_info_new;
	}
}

