<?php
namespace Controller;

class Redirect extends Base
{

	public function __construct()
	{
		
	}
	
	public function filter (\Base $fw, $params)
	{
		if ( empty($params['a']) )  $fw->reroute("/redirect/{$params['b']}/{$params['c']}", false);

		$query = explode ( "&", $params['b'] );
		foreach ( $query as $q )
		{
			$item = explode("=", $q);
			$old_data[$item[0]] = $item[1];
		}

		if ( $params['a']=="viewstory" )
		{
			if ( isset($old_data['sid']) && is_numeric($old_data['sid']) )
			{
				$redirect = "/story/read/".$old_data['sid'];
				if ( isset($old_data['chapter']) && is_numeric($old_data['chapter']) )
					$redirect .= ",".$old_data['chapter'];
			}
			else $redirect = "/";
		}

		$fw->reroute($redirect, false);
	}

}