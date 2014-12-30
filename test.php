<?php  


namespace test;

class Product 
{

	private function __construct()
	{
		spl_autoload_register(array($this,loader));
	}
	
	private function loader($className)
	{
		echo $className;
	}
	public function factory($classObj)
	{
		if(empty($classObj))
		{
			return false;
		}
		return new $classObj();
		
	}
}