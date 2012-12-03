<?php
namespace Mouf\Security\RightsService;

/**
 * Objects implementing this interface represent a basic right a user has.
 * For instance: right to display a button, right to access a webpage, right to perform some action...
 * A right can have a scope associated.
 * A scope can be anything from a string to an object, but it must be serializable (because
 * some services can cache it, for instance in the Session).
 *
 */
namespace Mouf\Security\RightsService;


interface RightInterface {
	
	/**
	 * Returns the name for that right.
	 *
	 * @return string
	 */
	public function getName();
	
	/**
	 * Returns an array of scopes this right has.
	 * If null, the right has a global scope on all the application.
	 *
	 * @return array<mixed>
	 */
	public function getScopes();
	
	/**
	 * Returns true if the right applies to the scope passed in parameter, false otherwise.
	 *
	 * @return boolean
	 */
	public function hasScope($scope);
}
?>