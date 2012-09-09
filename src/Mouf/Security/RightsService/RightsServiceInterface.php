<?php
namespace Mouf\Security\RightsService;

/**
 * Services implementing this interface can be used to decide whether a user (or the current user) has the rights to do an action.
 * A right can be anything from displaying a button to accessing a web-page, etc...
 * Rights can optionally have a scope. If they have a scope, the user can be have that right
 * only in that scope. For instance, in a project management application, there can be several project, 
 * and maybe the user as rights to manage only one project. So the "AdminProject" right might be restricted to
 * that particular project. 
 *
 */
interface RightsServiceInterface {
	
	/**
	 * Returns true if the current user has the right passed in parameter.
	 * A scope can be optionnally passed.
	 * A scope can be anything from a string to an object. If it is an object,
	 * it must be serializable (because it will be stored in the session).
	 *
	 * @param string $right
	 * @param mixed $scope
	 */
	public function isAllowed($right, $scope = null);
	
	/**
	 * Returns true if the user whose id is $user_id has the $right.
	 * A scope can be optionnally passed.
	 * A scope can be anything from a string to an object. If it is an object,
	 * it must be serializable (because it will be stored in the session).
	 *
	 * @param string $user_id
	 * @param string $right
	 * @param mixed $scope
	 */
	public function isUserAllowed($user_id, $right, $scope = null);

	/**
	 * Rights are cached in session, this function will purge the rights in session.
	 * This can be useful if you know the rights previously fetched for
	 * the current user will change.
	 *
	 */
	public function flushRightsCache();
	
	/**
	 * If the user has not the requested right, this function will
	 * redirect the user to an error page (or a login page...)
	 *
	 * @param string $right
	 * @param mixed $scope
	 */
	public function redirectNotAuthorized($right, $scope = null);
}
?>