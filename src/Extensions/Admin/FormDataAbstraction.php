<?php
namespace Carbo\Extensions\Admin;

use \Doctrine\DBAL\Types\Type;

class FormDataAbstraction
{
	private $database;

	public function __construct(DatabaseAbstraction $database)
	{
		$this->database = $database;
	}
	
	public function update(TableInformation $table, $input, $files, $primary_key, $primary_value, $link_data = [])
	{
		$data = [];
		foreach ($table->columns as $column)
		{
			if ($column->isPrimary)
				continue;
			
			if ($column->isAutoIncrement)
				continue;
			
			if (!$column->isNotNull and $column->isForeign and !isset($input[$column->name]))
				continue;
			
			if ($column->type !== Type::BLOB)
				$data[$column->name] = $this->getColumnInputData($column, @$input[$column->name]);
			
			if (isset($files['tmp_name'][$column->name]) and $files['error'][$column->name] === UPLOAD_ERR_OK)
				$data[$column->name] = $this->getColumnFileData($column, $files['tmp_name'][$column->name]);
		}
		
		$this->database->update($table, $data, [$primary_key => $primary_value]);
		
		foreach ($table->links as $link)
		{
			# If there's data set, use it, otherwise use an empty array
			$link_table_data = isset($link_data[$link->table->name]) ? $link_data[$link->table->name] : [];
			
			$this->database->addLinks($link, $primary_value, $link_table_data);
		}
	}
	
	public function insert(TableInformation $table, $input, $files, $link_data = [])
	{
		$data = [];
		foreach ($table->columns as $column)
		{
			if ($column->isPrimary)
				continue;
			
			if ($column->isAutoIncrement)
				continue;
			
			if (!$column->isNotNull && $column->isForeign)
				continue;
		
			if ($column->type !== Type::BLOB)
				$data[$column->name] = $this->getColumnInputData($column, @$input[$column->name]);
			
			if (isset($files['tmp_name'][$column->name]) and $files['error'][$column->name] === UPLOAD_ERR_OK)
				$data[$column->name] = $this->getColumnFileData($column, $files['tmp_name'][$column->name]);
		}
		
		$this->database->insert($table, $data);
		
		$insertId = $this->database->lastInsertId($table);
		
		foreach ($table->links as $link)
		{
			if (isset($link_data[$link->table->name]))
			{
				$this->database->addLinks($link, $insertId, $link_data[$link->table->name]);
			}
		}
	}
	
	private function getColumnInputData($column, $input)
	{
		if ($column->type == Type::BOOLEAN)
			return ($input === 'on');
		
		if ($column->isForeign)
			return empty($input) ? null : $input;
		
		return $input;
	}
	
	private function getColumnFileData($column, $file)
	{
		if ($column->type == Type::BLOB)
			return file_get_contents($file);
	}
}