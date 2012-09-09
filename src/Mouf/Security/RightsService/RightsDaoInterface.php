<?php
namespace Mouf\Security\RightsService;

/**
 * Daos implementing this interface can be used to query the database for the list of rights
 * a user has.
 * The Dao will return objects implementing the RightInterface.
 *
 */
interface RightsDaoInterface {

	/**
	 * Returns a list of all the rights for the user passed in parameter.
	 *
	 * @param string $user_id
	 * @return array<RightInterface>
	 */
	public function getRightsForUser($user_id);

	/**
	 * Returns the RightInterface object associated to the user (or null if the
	 * user has no such right).
	 *
	 * @param string $user_id
	 * @param string $right
	 * @return RightInterface
	 */
	public function getRightForUser($user_id, $right);
	
}
?>