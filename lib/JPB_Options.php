<?php

abstract class JPB_Options {

	/**
	 * The database option name value. Must be declared in extending classes.
	 * @var string
	 */
	protected $_option_name = null;

	/**
	 * An array of all public properties on the option class.
	 * 
	 * Will throw an exception if no public option is declared.
	 * @var array
	 */
	protected $_builtin_properties = array( );

	/**
	 * Object constructor. Does magic.
	 * 
	 * Finds class properties, pulls in option from obj cache/db, installs or upgrades as necessary.
	 * @throws Exception Throws an exception if there are no public properties or if the option_name isn't set.
	 */
	final function __construct() {
		// call_user_func lets us grab properties from the global scope, getting only public properties.
		$properties = call_user_func( 'get_class_vars', get_class( $this ) );
		if( empty( $properties ) ) {
			throw new Exception( 'You must define at least one public variable for ' . get_class( $this ) );
			return;
		}
		$this->_builtin_properties = array_keys( $properties );
		if( !$this->_option_name ) {
			throw new Exception( 'You must define the option name in ' . get_class( $this ) . '::$_option_name!' );
			return;
		}
		$option = get_option( $this->_option_name, array( ) );
		if( !$option || !is_array( $option ) ) {
			$this->_install();
		} elseif( !empty( $this->version ) && !empty( $option['version'] ) && version_compare( $this->version, $option['version'], '!=' ) ) {
			$this->_upgrade( $option );
		} else {
			$this->_assign_vars( $option );
		}
	}

	/**
	 * Assign the object properties that are defined and in the passed options array
	 * @param array $options The array of option values.
	 */
	final protected function _assign_vars( array $options ) {
		foreach( $this->_builtin_properties as $option ) {
			if( array_key_exists( $option, $options ) ) {
				$this->{$option} = $options[$option];
			}
		}
	}

	/**
	 * Install the option.
	 * 
	 * Since there's no algorithm for every use case, and options may need to assign dynamic
	 * values to some properties, this is an abstract function. It must be defined in all
	 * extending classes. Extending classes may also want to run $this->_assign_vars to set
	 * the newly installed variables on the object's properties. 
	 */
	abstract protected function _install();

	/**
	 * An upgrade function to upgrade the data structure in the database. 
	 */
	abstract protected function _upgrade( array $option );

	/**
	 * Sanitize the object properties before saving to database.
	 * 
	 * This may be overridden if necessary. If not necessary, the default is a no-op function
	 * so it's fine to leave undefined in extending classes. 
	 */
	protected function _sanitize() {
		
	}

	/**
	 * Update the database values from the object's properties.
	 * 
	 * @uses $this->_sanitize()
	 * @uses update_option()
	 * @return bool False if update failed, true if it succeeded
	 */
	final public function update() {
		$this->_sanitize();
		$updatedOptions = array( );
		foreach( $this->_builtin_properties as $property ) {
			$updatedOptions[$property] = $this->{$property};
		}
		return update_option( $this->_option_name, $updatedOptions );
	}

}
