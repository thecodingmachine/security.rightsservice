<?php
namespace Mouf\Security\RightsService;

/**
 * This class can be used to decide whether a user (or the current user) has the rights to do an action.
 * A right can be anything from displaying a button to accessing a web-page, etc...
 * Rights can optionally have a scope. If they have a scope, the user can have a right
 * only in that scope. For instance, in a project management application, there can be several project, 
 * and maybe the user as rights to manage only one project. So the "AdminProject" right might be restricted to
 * that particular project. 
 *
 * @Component
 */
class MoufRightService implements RightsServiceInterface, AuthenticationListenerInterface {

	private static $RIGHTS_SESSION_NAME="MoufRights";
	
	/**
	 * The userService that will be used to retrieve the current user.
	 *
	 * @Property
	 * @Compulsory
	 * @var UserServiceInterface
	 */
	public $userService;
	
	/**
	 * The Dao that will return all rights for a user.
	 *
	 * @Property
	 * @Compulsory
	 * @var RightsDaoInterface
	 */
	public $rightsDao;
		
	/**
	 * The logger for this service
	 *
	 * @Property
	 * @Compulsory
	 * @var LogInterface
	 */
	public $log;
	
	/**
	 * The path to the error page that will be displayed if the
	 * user does not have the rights. It should be a 403 page.
	 * The URL is relative to the root of the application.
	 * It should not start with a "/" and should not end with a "/".
	 *
	 * @Property
	 * @Compulsory
	 * @var string
	 */
	public $errorPageUrl;
	
	/**
	 * When the user is redirected to a 403 page, the URL he tried to access
	 * is appended to the 403 page. You can customize the 
	 * name of the URL parameter for the redirect.
	 * 
	 * For instance, if $redirectParameter = "redir", then your
	 * redirection URL might look like:
	 * 	http://[myserver]/[myapp]/403.php?redir=%2F[myapp]%2F[my]%2F[page]%2F
	 * 
	 * @Property
	 * @Compulsory
	 * @var string
	 */
	public $redirectParameter = "redirect";
	
	/**
	 * The rights for the current user (mapping the $_SESSION rights), but unserialized for better performance access.
	 * 
	 * @var array<string, RightInterface>
	 */
	public $currentUserRights = array();
	
	/**
	 * In case you have several Mouf applications using the RightService running on the same server, in the same domain, you
	 * should use a different session prefix for each application in order to avoid "melting" the sessions.
	 * 
	 * @Property
	 * @var string
	 */
	public $sessionPrefix;
	
	/**
	 * Stores the rights of the current user in session.
	 */
	private function storeRightsInSession() {
		$rights = $this->rightsDao->getRightsForUser($this->userService->getUserId());
		$rightsAssoc = array();
		foreach ($rights as $right) {
			// Note: we store the serialized version of the right instead of the right itself in session.
			// This is because in some cases (e.g. Drupal integration), the session will be loaded
			// before the class is loaded.
			$rightsAssoc[$right->getName()] = serialize($right);
			$this->currentUserRights[$right->getName()] = $right;
		}
		$_SESSION[$this->sessionPrefix.self::$RIGHTS_SESSION_NAME] = $rightsAssoc;
	}
	
	/**
	 * Loads the rights from the session.
	 */
	private function loadRightsFromSession() {
		foreach ($_SESSION[$this->sessionPrefix.self::$RIGHTS_SESSION_NAME] as $name=>$serializedRight) {
			$this->currentUserRights[$name] = unserialize($serializedRight);
		}
	}
	
	/**
	 * Returns true if the current user has the right passed in parameter.
	 * A scope can be optionnally passed.
	 * A scope can be anything from a string to an object. If it is an object,
	 * it must be serializable (because it will be stored in the session).
	 *
	 * @param string $right
	 * @param mixed $scope
	 */
	public function isAllowed($right, $scope = null) {
		// First, a user must be logged.
		
		if (!$this->userService->isLogged()) {
			return false;
		}
		
		if (!isset($_SESSION[$this->sessionPrefix.self::$RIGHTS_SESSION_NAME])) {			
			$this->storeRightsInSession();
		} else {
			$this->loadRightsFromSession();
		}

		if (isset($this->currentUserRights[$right])) {
			if ($this->currentUserRights[$right]->hasScope($scope)) {
				return true;
			}
		}
		return false;
	}
	
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
	public function isUserAllowed($user_id, $right, $scope = null) {
		$right = $this->rightsDao->getRightForUser($user_id, $right);
		if ($right != null) {
			if ($right->hasScope($scope)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Rights are cached in session, this function will purge the rights in session.
	 * This can be useful if you know the rights previously fetched for
	 * the current user will change.
	 *
	 */
	public function flushRightsCache() {
		unset($_SESSION[$this->sessionPrefix.self::$RIGHTS_SESSION_NAME]);
		$this->currentUserRights = array();
	}
	
	/**
	 * This method is called just after a log-in occurs.
	 *
	 * @param UserServiceInterface $userService The service that performed the log-in
	 */
	public function afterLogIn(UserServiceInterface $userService) {
		$this->storeRightsInSession();
	}
	
	/**
	 * This method is called just before the current user logs out.
	 *
	 * @param UserServiceInterface $userService The service that performed the log-out
	 */
	public function beforeLogOut(UserServiceInterface $userService) {
		$this->flushRightsCache();
	}
	
	/**
	 * If the user has not the requested right, this function will
	 * redirect the user to an error page (or a login page...)
	 *
	 * @param string $right
	 * @param mixed $scope
	 */
	public function redirectNotAuthorized($right, $scope = null) {
		if (!$this->isAllowed($right, $scope)) {
			if ($scope == null) {
				$this->log->info("User ".$this->userService->getUserLogin()." was denied access because he does not have the right ".$right.".");
			} else {
				$this->log->info("User ".$this->userService->getUserLogin()." was denied access because he does not have the right ".$right." on the required scope.");
			}
			header("Location:".ROOT_URL.$this->errorPageUrl."?".$this->redirectParameter."=".urlencode($_SERVER['REQUEST_URI']));
			exit;
		}
	}
}
?>