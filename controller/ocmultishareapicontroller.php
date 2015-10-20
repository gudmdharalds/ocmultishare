<?php

namespace OCA\ocmultishare\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use \OCA\ocmultishare\Db\SharingDAO;

class OcmultishareApiController extends ApiController {
	private $db;

	public function __construct($AppName, IRequest $request, IDb $db){
		parent::__construct($AppName, $request);
		$this->db = $db;
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param int $seconds
	 */

	public function create($id, $seconds) {
		/*
		 * Check if all parameters are fine.
		 * Note that both $id and $seconds should be positive integers.
		 */

		if (($id === NULL) || ((is_numeric($id)) === FALSE) || ($id < 0)) {
			return new DataResponse(array("error" => "Invalid usage (id parameter malformed)"));
		} 

		if (($seconds === NULL) || ((is_numeric($seconds)) === FALSE) || ($seconds < 0)) {
			return new DataResponse(array("error" => "Invalid usage (seconds parameter malformed)"));
		} 

		/* Get an instance of our sharing-class */	
		$sharingdao = new SharingDAO($this->db);

		try {
			/* 
			 * Make sure that the owner of the share is the same as the user
			 * making the current request.
			 */

			if ($sharingdao->getShareOwner($id) !== (string) \OC_User::getUser()) {
				return new DataResponse(array("error" => "Permission denied"));
			}

			/* Try to create a duplicate */
			$share_info = $sharingdao->duplicateShareWithNewToken((int) $id, (int) $seconds);
		}

		catch (\Exception $e) {
			return new DataResponse(array("error" => $e->getMessage()));
		}

		/* All good, return the blessed token */
		return new DataResponse(array("token" => $share_info["token"] ));
	}
}
