<?php

class WP_MSM_Options extends JPB_Options {

	protected $_option_name = 'WP_MultiServer_Migrations_options';
	private static $_instance = null;
	public $version = '0.1.0.1';
	public $publicSignature = '';
	public $privateSignature = '';
	public $acceptsConnections = 0;

	protected function after_setup() {
		$this->acceptsConnections = (bool)$this->acceptsConnections;
	}

	protected function _sanitize() {
		$this->acceptsConnections = $this->acceptsConnections ? 1 : 0;
	}

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
		$this->publicSignature = wp_generate_password( 24, true, true );
		$this->privateSignature = wp_generate_password( 32, true, true );
		$this->update();
		flush_rewrite_rules();
	}

	protected function _upgrade( array $options ) {
		if( empty( $options['version'] ) )
			return $this->_install();
		switch( $options['version'] ) {
			case '0.1' :
				$this->publicSignature = wp_generate_password( 24, true, true );
				break;
			case '0.1.0.1' :
				$this->privateSignature = wp_generate_password( 32, true, true );
				break;
			default:
				break;
		}
		$this->update();
	}

}
