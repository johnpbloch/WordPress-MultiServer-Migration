<?php

class WP_MSM_Options extends JPB_Options {

	protected $_option_name = 'WP_MultiServer_Migrations_options';
	private static $_instance = null;
	public $version = '0.1';

	/**
	 * (create and) Fetch the pseudo-singleton
	 * @return WP_MSM_Options
	 */
	public static function instance() {
		if( empty( self::$_instance ) )
			self::$_instance = new WP_MSM_Options;
		return self::$_instance;
	}

	protected function _install() {
		$this->update();
	}

	protected function _upgrade( array $options ) {
		
	}

}
