<?php

class WP_MSM_Profile
{

	public $name = '';
	public $displayName = '';
	public $dbTablesToExclude = array( );
	public $canExport = true;
	public $description = '';

	function __construct( array $settings )
	{
		$defaults = get_object_vars( $this );
		$settings = array_merge( $defaults, array_intersect_key( $settings, $defaults ) );
		foreach( $settings as $key => $setting )
		{
			$this->$key = $setting;
		}
	}

}
