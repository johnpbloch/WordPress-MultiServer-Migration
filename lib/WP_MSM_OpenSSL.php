<?php

class WP_MSM_OpenSSL
{

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
		$options->update();
	}

}
