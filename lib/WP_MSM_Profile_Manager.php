<?php

class WP_MSM_Profile_Manager
{

	/**
	 * A container for the profiles
	 * 
	 * @var array 
	 */
	private static $profiles = array( );

	/**
	 * The default profile names
	 * 
	 * @var array 
	 */
	private static $defaultProfiles = array(
		'testing',
		'staging',
		'production',
	);

	/**
	 * Constructor 
	 */
	function __construct()
	{
		$this->createDefaultProfiles();
		$options = WP_MSM_Options::instance();
		foreach( $options->customProfiles as $name => $profile )
		{
			$profile = new WP_MSM_Profile( $profile );
			$this->add_profile( $name, $profile );
		}
	}

	/**
	 * Create the default profiles (production, staging, and testing) 
	 */
	private function createDefaultProfiles()
	{
		$productionArgs = array(
			'name' => 'production',
			'displayName' => _x( 'Production', 'A production server', 'WordPress-MultiServer-Migration' ),
			'description' => __( 'A profile for production servers. Exports everything but users.', 'WordPress-MultiServer-Migration' ),
			'canExport' => true,
			'dbTablesToExclude' => array(
				'users',
				'usermeta'
			),
		);
		$production = new WP_MSM_Profile( $productionArgs );
		$this->add_profile( $production->name, $production );
		$stagingArgs = array(
			'name' => 'staging',
			'displayName' => _x( 'Staging', 'A staging server', 'WordPress-MultiServer-Migration' ),
			'description' => __( 'A profile for staging servers. Exports everything but users and comments.', 'WordPress-MultiServer-Migration' ),
			'canExport' => true,
			'dbTablesToExclude' => array(
				'users',
				'usermeta',
				'comments',
				'commentmeta',
			),
		);
		$staging = new WP_MSM_Profile( $stagingArgs );
		$this->add_profile( $staging->name, $staging );
		$testArgs = array(
			'name' => 'testing',
			'displayName' => _x( 'Testing', 'A testing server', 'WordPress-MultiServer-Migration' ),
			'description' => __( 'A profile for testing servers. Export is turned off.', 'WordPress-MultiServer-Migration' ),
			'canExport' => false,
		);
		$testing = new WP_MSM_Profile( $testArgs );
		$this->add_profile( $testing->name, $testing );
	}

	/**
	 * Add a profile to the manager.
	 * 
	 * @param type string
	 * @param WP_MSM_Profile $profile
	 */
	public function add_profile( $name, WP_MSM_Profile $profile )
	{
		$name = (string)$name;
		if( isset( self::$profiles[$name] ) && self::$profiles[$name] instanceof WP_MSM_Profile )
			return;
		self::$profiles[$name] = $profile;
		$options = WP_MSM_Options::instance();
		if( !isset( $options->customProfiles[$name] ) )
		{
			$options->customProfiles[$name] = $profile->_toArray();
			$options->update();
		}
	}

	/**
	 * Remove a profile from the manager.
	 * 
	 * @param string $name
	 */
	public function delete_profile( $name )
	{
		if( in_array( $name, self::$defaultProfiles ) )
			return;
		if( isset( self::$profiles[$name] ) )
			unset( self::$profiles[$name] );
		$options = WP_MSM_Options::instance();
		if( isset( $options->customProfiles[$name] ) )
		{
			unset( $options->customProfiles[$name] );
			$options->update();
		}
	}

	/**
	 * Update a profile in the manager.
	 * 
	 * If the profile doesn't exist, it'll update it. If the profile is a default profile
	 * it will not update.
	 * 
	 * @param string $name
	 * @param WP_MSM_Profile $profile
	 */
	public function update_profile( $name, WP_MSM_Profile $profile )
	{
		if( in_array( $name, self::$defaultProfiles ) )
			return;
		if( isset( self::$profiles[$name] ) )
			unset( self::$profiles[$name] );
		self::$profiles[$name] = $profile;
		$options = WP_MSM_Options::instance();
		if( isset( $options->customProfiles[$name] ) )
		{
			$options->customProfiles[$name] = $profile->_toArray();
			$options->update();
		}
	}

	/**
	 * Get a profile from the manager.
	 * 
	 * Returns false if the profile doesn't exist.
	 * 
	 * @param string $name
	 * @return bool|WP_MSM_Profile The profile or false if it doesn't exist.
	 */
	public function get_profile( $name )
	{
		if( isset( self::$profiles[$name] ) )
			return self::$profiles[$name];
		return false;
	}

	/**
	 * Get all the names of the registered profiles.
	 * 
	 * @return array All profile names.
	 */
	public function get_all_profile_names()
	{
		return array_keys( self::$profiles );
	}

	/**
	 * Check whether a profile is a default profile or not
	 * 
	 * @param string The profile name to check
	 * @return boolean Whether the profile is a default one. 
	 */
	public function is_default_profile( $name )
	{
		return in_array( $name, self::$defaultProfiles );
	}

}
