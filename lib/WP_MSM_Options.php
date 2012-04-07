<?php

class WP_MSM_Options extends JPB_Options
{

	protected $_option_name = 'WP_MultiServer_Migrations_options';
	private static $_instance = null;
	public $version = '0.1.0.2';
	public $publicSignature = '';
	public $privateSignature = '';
	public $acceptsConnections = 0;

	protected function after_setup()
	{
		$this->acceptsConnections = (bool)$this->acceptsConnections;
	}

	protected function _sanitize()
	{
		$this->acceptsConnections = $this->acceptsConnections ? 1 : 0;
	}

	/**
	 * (create and) Fetch the pseudo-singleton
	 * @return WP_MSM_Options
	 */
	public static function instance()
	{
		if( empty( self::$_instance ) )
			self::$_instance = new WP_MSM_Options;
		return self::$_instance;
	}

	protected function _install()
	{
		$keys = WP_MSM_OpenSSL::generate_keys( false );
		$this->publicSignature = $keys['public_key'];
		$this->privateSignature = $keys['private_key'];
		$this->update();
		flush_rewrite_rules();
	}

	protected function _upgrade( array $options )
	{
		if( empty( $options['version'] ) )
			return $this->_install();
		switch( $options['version'] )
		{
			default:
				$keys = WP_MSM_OpenSSL::generate_keys( false );
				$this->publicSignature = $keys['public_key'];
				$this->privateSignature = $keys['private_key'];
				break;
		}
		$this->update();
	}

}
