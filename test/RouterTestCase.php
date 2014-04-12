<?php
class TestRouterServeView extends AeFramework\Router
{
	public $served_view;
	
	public function serveView(AeFramework\View $view)
	{
		$this->served_view = $view;
	}
}

class RouterTestCase extends PHPUnit_Framework_TestCase
{
	private $router;
	private $test_view1;
	private $test_view2;
	
	protected function setUp()
	{
		$this->router = new TestRouterServeView;
		$this->test_view1 = new AeFramework\TextView('test_view1');
		$this->test_view2 = new AeFramework\TextView('test_view2');
	}
	
	public function testRouterRoute()
	{
		$this->router->route(new AeFramework\StringMapper('/testing/', $this->test_view1));
		
		$_SERVER['REQUEST_URI'] = '/testing/';
		$this->router->despatch();
		
		$this->assertSame($this->router->served_view, $this->test_view1);
	}
	
	public function testRouterNotFoundRoute()
	{
		$this->router->route(new AeFramework\StringMapper('/testing/', $this->test_view1));
		$this->router->error(AeFramework\HttpCode::NotFound, $this->test_view2);
		
		$_SERVER['REQUEST_URI'] = '/not-found/';
		$this->router->despatch();
		
		$this->assertSame($this->router->served_view, $this->test_view2);
	}
}