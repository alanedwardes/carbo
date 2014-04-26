<?php
use AeFramework\Views as Views;
use AeFramework\Routing as Routing;
use AeFramework\Mapping as Mapping;

class TestNonAuthenticatedView extends Views\View
{
	public function request($verb, array $params = []){}
	public function response(){}
}

class TestAuthenticatedView extends Views\View implements Views\IAuthenticated
{
	public function request($verb, array $params = []){}
	public function response(){}
}

class TestViewThatAuthenticates extends Views\View
{
	public function request($verb, array $params = [])
	{
		$this->authenticator->authenticate('user', 'pass');
	}
	
	public function response(){}
}

class TestAuthenticator implements \AeFramework\Routing\IAuthenticator
{
	private $authenticated = false;
	
	public function authenticate($username, $password)
	{
		return ($this->authenticated = ($username == 'user' and $password == 'pass'));
	}
	
	public function isAuthenticated() { return $this->authenticated; }
}

class AuthenticatedRouterTestCase extends PHPUnit_Framework_TestCase
{
	private $router;
	private $authenticator;
	
	protected function setUp()
	{
		$this->authenticator = new TestAuthenticator;
		$this->router = new Routing\AuthenticatedRouter($this->authenticator);
	}
	
	/**
	 * @expectedException     \AeFramework\HttpCodeException
	 * @expectedExceptionCode 403
	 */
	public function testAuthenticatedRouterAuthenticates()
	{
		$this->router->route(new Mapping\StringMapper('/test/', new TestAuthenticatedView));
		
		$this->router->despatch('/test/');
	}
	
	public function testAuthenticatedRouterServesRegularViews()
	{
		$this->router->route(new Mapping\StringMapper('/auth/', new TestAuthenticatedView));
		$this->router->route(new Mapping\StringMapper('/nonauth/', new TestNonAuthenticatedView));
		
		$this->router->despatch('/nonauth/');
	}
	
	/**
	 * @expectedException     \AeFramework\HttpCodeException
	 * @expectedExceptionCode 404
	 */
	public function testAuthenticatedRouterServesNotFoundViews()
	{
		$this->router->route(new Mapping\StringMapper('/auth/', new TestAuthenticatedView));
		$this->router->route(new Mapping\StringMapper('/nonauth/', new TestNonAuthenticatedView));
		
		$this->router->despatch('/testing/');
	}
	
	public function testAuthentication()
	{
		$this->router->route(new Mapping\StringMapper('/private/', new TestAuthenticatedView));
		
		$this->router->route(new Mapping\StringMapper('/login/', new TestViewThatAuthenticates));
		
		$this->router->despatch('/login/');
		
		$this->router->despatch('/private/');
		
		$this->assertTrue($this->authenticator->isAuthenticated());
	}
}