<?php

class WP_MSM_OpenSSL
{

	/**
	 * Generates a pair of public and private keys for the plugin to use.
	 * 
	 * Keys can either be returned without saving them to the plugin's options
	 * object or can be saved directly to the plugin's options object. Used
	 * internally. This is probably never going to need to be used directly,
	 * since it's set up at installation and then fresh keys are re-generated
	 * every 30 days.
	 * 
	 * @param bool $saveDirectly Save the keys directly to options. False to return keys.
	 * @return array|bool An array containing keys if $saveDirectly is true, boolean indicating save success otherwise. 
	 */
	public static function generate_keys( $saveDirectly = true )
	{
		$pkey_resource = openssl_pkey_new( array( 'private_key_bits' => 2048 ) );
		$public_key = openssl_pkey_get_details( $pkey_resource );
		$public_key = $public_key['key'];
		openssl_pkey_export( $pkey_resource, $private_key );
		if( !$saveDirectly )
		{
			return compact( 'public_key', 'private_key' );
		}
		$options = WP_MSM_Options::instance();
		$options->publicSignature = $public_key;
		$options->privateSignature = $private_key;
		return $options->update();
	}

}
