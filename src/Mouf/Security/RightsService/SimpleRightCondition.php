<?php
namespace Mouf\Security\RightsService;

/**
 * This class represents a simple right condition.
 * The condition is true if the current user has the appropriate right, and is false if the current
 * user does not have the appropriate right.
 * 
 * @Component
 */
class SimpleRightCondition implements ConditionInterface {
	protected $restrictedRight;
	protected $restrictedRightScope;
	/**
	 * @var RightsServiceInterface
	 */
	protected $rightsService;
	
	/**
	 * This property is the service that will be used to decide the rights of the user.
	 *
	 * @Property
	 * @param RightsServiceInterface $right
	 */
	public function setRightsService(RightsServiceInterface $rightsService) {
		$this->rightsService = $rightsService;
	}
	
	/**
	 * This is the right required for this condition to be validated.
	 *
	 * @Property
	 * @param string $right
	 */
	public function setRight($right) {
		$this->restrictedRight = $right;
	}
	
	/**
	 * This is the scope of the right used to restrict the access.
	 * The scope is optional.
	 *
	 * @Property 
	 * @param string $rightScope
	 */
	public function setRightScope($rightScope) {
		$this->restrictedRightScope = $rightScope;
	}
	
	/**
	 * Returns true if the current user has the appropriate right, false otherwise.
	 *
	 * @param mixed $caller The condition caller. Optional, and not used by this class.
	 * @return bool
	 */
	function isOk($caller = null) {
		return $this->rightsService->isAllowed($this->restrictedRight, $this->restrictedRightScope);
	}
}
?>