Managing rights with Mouf
=========================

The Mouf framework is only an IOC framework. As such, it does not provide any means for managing access rights for users. Hopefully,
the Mouf team provides this "rightsservice" package that can do help you with user management. This package does rely on the
"userservice" package for providing users management and will add access rights management to it.

The "rightsservice" package

This package can be found in the "security" directory.
This package provides components you can use to implement user authorization. This is not an "out-of-the-box" package.
In order to use this package, you will have to develop some components on your side. This package will provide utility functions
to know if a user has the rights to perform some action or not, .... This package does not provide any way to add or remove
rights for a user. You will have to take care of this in your developments.
The package contains these classes and interfaces:

- A <b>MoufRightService</b> class: this is the main class. It can be used to know if the current logged user (or a particular
  user has some rights or not), etc...
- A <b>RightsServiceInterface</b> interface: most libraries relying on the "userservice" will rely on this interface. If the default
  <b>MoufRightService</b> class does not meet your requirements, you can develop you own "rightsservice" instance that will implement the <b>RightsServiceInterface</b>
  interface.
- The <b>MoufRightService</b> class will require a Data Access Object to access your database. The DAO is not part of this package,
  therefore, you will have to provide it. You DAO will need to extend the <b>RightsDaoInterface</b> interface.
- Finally, objects returned by your <b>RightsDao</b> class will implement the <b>RightInterface</b> interface. A sample implementation
  is provided in the <b>MoufRight</b> class.


The one thing you must remember when using the "rightsservice" package is this: You provide the "rightsservice" package with a DAO that will help 
it know the rights for a user, and the userservice will help you manage the rights and access those efficiently by caching them in session.

Note: if a user is not logged, we will have no rights. This package does not allow rights for unlogged people.

In Mouf, a right can be anything from displaying a button to accessing a web-page, etc...
Rights can optionally have a scope. If they have a scope, the user can have a right
only in that scope. For instance, in a project management application, there can be several project, 
and maybe the user as rights to manage only one project. So the "AdminProject" right might be restricted to
that particular project.

<h2>The <b>MoufRightService</b> component</h2>

This component has 3 required properties that must be wired using the Mouf User Interface:

- <b>userService</b>: This is a pointer to the userService component that will be used to retrieve the ID of the current user. Please note
  that when wiring the rightsService to the userService, the wiring must be done <b>both ways!</b> The rightsService references the userService
  throught the "userService" property, and in the userService, <b>the rightsService must be referenced in the "authenticationListeners" property</b>.
- <b>rightsDao</b>: This is the Data Access Object that will query your database to know what rights are associated to the user.
- <b>log</b>: The logger used to log messages.
- <b>errorPageUrl</b>: This is the page containing the error message if the user does not have the requested rights (the 403 error page).


The <b>MoufRightService</b> contains these methods:

```php
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
```

You will use the <em>isAllowed</em> method to know if the current user has rights or not.
You can use the <em>isUserAllowed</em> variant if you want to know the rights of a user that is not the current user.
The <em>flushRightsCache</em> method will let you flush the rights cache for the current user (in order to
save on database calls, the list of rights for a user is cached in session). Finally, the <em>redirectNotAuthorized</em> functions will 
check if the user has the right authorization, and will redirect the user to the 403 page ($errorPageUrl) if he is not allowed
to view the page.

For the RightsService to work, you will need to provide a RightsDao implementing this interface:

```php
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
	 * @return array&lt;RightInterface&gt;
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
```

The RightsDao provides return objects representing Rights.
A right is an object that has a name (the right's name) and a an array of scopes the right applies to.
A scope can be anything from a string to an object, but it must be serializable (to be stored in session).
These "Right" objects will need to implement the RightInterface interface:

```php
/**
 * Objects implementing this interface represent a basic right a user has.
 * For instance: right to display a button, right to access a webpage, right to perform some action...
 * A right can have a scope associated.
 * A scope can be anything from a string to an object, but it must be serializable (because
 * some services can cache it, for instance in the Session).
 *
 */
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
```

The "rightsservice" package provides the <b>MoufRight</b> class that is a default implementation of the RightInterface.
You can use it by making sure the RightsDao you will code returns objects from the <b>MoufRight</b> class.