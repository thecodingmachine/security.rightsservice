<?php
namespace Mouf\Security\RightsService;

/**
 * A typical implementation of the the MoufRightInterface.
 * This class represents a basic right. A right can be anything from displaying a button to accessing a web-page, etc...
 * Rights can optionally have a scope. If they have a scope, the user can have that right
 * only in that scope. For instance, in a project management application, there can be several project, 
 * and maybe the user as rights to manage only one project. So the "AdminProject" right might be restricted to
 * that particular project. 
 *
 */
class MoufRight implements RightInterface {

	private $name;
	private $scopes;
	
	public function __construct($name = null, $scopes = null) {
		$this->name = $name;
		$this->scopes = $scopes;
	}
	
	/**
	 * Returns the name for that right.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Sets the name of this right.
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * Returns an array of scopes this right has.
	 * If null, the right has a global scope on all the application.
	 *
	 * @return array<mixed>
	 */
	public function getScopes() {
		return $this->scopes;
	}
	
	/**
	 * Sets the scopes of this right.
	 *
	 * @param array<mixed> $scopes
	 */
	public function setScopes(array $scopes) {
		$this->scopes = $scopes;
	}
	
	/**
	 * Returns true if the right applies to the scope passed in parameter, false otherwise.
	 *
	 * @return boolean
	 */
	public function hasScope($scope) {
		// No scope = global scope.
		if (empty($this->scopes)) {
			return true;
		}
		
		// If there is a scope list for this right, we are not in the global scope.
		if ($scope == null) {
			return false;
		}
		
		return array_search($scope, $this->scopes) !== false;
	}
}
?>