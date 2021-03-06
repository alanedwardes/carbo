<?php
namespace Carbo\Auth;

use Carbo\Sessions\SessionHandler;

class ArrayAuthenticator implements IAuthenticator, IPasswordAuthenticator
{
	private $credentials = [];
	private $session = null;
	
	public function session() { return $this->session; }
	
	public function __construct(array $credentials, SessionHandler $session)
	{
		$this->credentials = $credentials;
		$this->session = $session;
	}
	
	public function authenticate($username, $password)
	{
		if (isset($this->credentials[$username]))
		{
			if ($this->credentials[$username] === $password)
			{
				$this->session->username = $username;
				$this->session->valid_host = $_SERVER['HTTP_HOST'];
				$this->session->authenticated = true;
				return true;
			}
		}
		
		return false;
	}
	
	public function isAuthenticated()
	{
		return ($this->session->authenticated === true && in_array($this->session->username, array_keys($this->credentials)) && $this->session->valid_host === $_SERVER['HTTP_HOST']);
	}
}