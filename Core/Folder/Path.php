<?php
namespace Core\Folder;
	
class Path{
	
	const CONFIG = ROOT.'Config'.DS;
	const LOGS = ROOT.'Logs'.DS;
	const CACHE = ROOT.'Cache'.DS;
    const CONTROLLERS = 'App'.DS.'Controllers'.DS;
    const MODELS = ROOT.'App'.DS.'Models'.DS;
    const VIEWS  = ROOT.'App'.DS.'Views'.DS;
	
	public static function session() : string 
	{
		return realpath("../Session");
	}
}