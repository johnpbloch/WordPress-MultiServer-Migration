<?php

class WP_MSM_Profile
{

	public $name = '';
	public $displayName = '';
	public $dbTablesToExclude = array( );
	public $canExport = true;
	public $description = '';
	protected $defaults = array( );

	function __construct( array $settings )
	{
		$defaults = get_object_vars( $this );
		$this->defaults = array_keys( $defaults );
		$settings = array_merge( $defaults, array_intersect_key( $settings, $defaults ) );
		foreach( $settings as $key => $setting )
		{
			$this->$key = $setting;
		}
	}

	/**
	 * Returns the profile settings as an array.
	 * @return array The profile settings
	 */
	public function _toArray()
	{
		$toReturn = array( );
		foreach( $this->defaults as $key )
		{
			$toReturn[$key] = $this->$key;
		}
		return $toReturn;
	}

}
