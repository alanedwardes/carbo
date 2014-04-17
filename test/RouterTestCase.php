<?php
require_once 'Helpers/TestRouterServedView.php';

class RouterTestCase extends PHPUnit_Framework_TestCase
{
	private $router;
	private $test_view1;
	private $test_view2;
	
	protected function setUp()
	{
		$this->router = new TestRouterServedView;
		$this->test_view1 = new AeFramework\TextView('test_view1');
		$this->test_view2 = new AeFramework\TextView('test_view2');
	}
	
	public function testRouterRoute()
	{
		$this->router->route(new AeFramework\StringMapper('/testing/', $this->test_view1));
		
		$this->router->despatch('/testing/');
		
		$this->assertSame($this->router->served_view, $this->test_view1);
	}
	
	public function testRouterNotFoundRoute()
	{
		$this->router->route(new AeFramework\StringMapper('/testing/', $this->test_view1));
		$this->router->error(AeFramework\HttpCode::NotFound, $this->test_view2);
		
		$this->router->despatch('/not-found/');
		
		$this->assertSame($this->router->served_view, $this->test_view2);
	}
	
	public function testRouterDeferredViewContruction()
	{
		$this->router->route(new AeFramework\StringMapper('/testing/', ['AeFramework\TextView', ['test_deferred']]));
		
		$this->router->despatch('/testing/');
		
		$this->assertSame($this->router->served_view->body(), 'test_deferred');
	}
}