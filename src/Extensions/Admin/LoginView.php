<?php
namespace AeFramework\Extensions\Admin;

class LoginView extends \AeFramework\Views\TemplateView
{
	public function __construct()
	{
		parent::__construct(\AeFramework\Util::joinPath(__DIR__, 'templates/login.html'));
	}
	
	public function request($verb, array $params = [])
	{
		if (isset($_POST['username']) and isset($_POST['password']))
		{
			if ($this->authenticator->authenticate($_POST['username'], $_POST['password']))
			{
				$this->headers['Location'] = '.';
			}
		}
	}
}