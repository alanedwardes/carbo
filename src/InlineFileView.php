<?php
namespace AeFramework;

class InlineFileView implements IView
{
	public $file;
	public $content_type;
	
	public function __construct($file, $content_type)
	{
		if (!file_exists($file))
			throw new \Exception(sprintf('File "%s" does not exist.', $file));
		
		$this->file = $file;
		$this->content_type = $content_type;
	}
	
	public function map($params = [])
	{
	}
	
	public function code()
	{
		return HttpCode::Ok;
	}
	
	public function headers()
	{
		return ['Content-Type' => $this->content_type];
	}
	
	public function body()
	{
		return readfile($this->file);
	}
}