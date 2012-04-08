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

	/**
	 * Encrypt arbitrary text.
	 * 
	 * If a key is not provided, it is assumed that this is a private key encryption
	 * and the plugin's internal private key will be used. Otherwise, the provided
	 * key is assumed to be a public key.
	 * 
	 * Any value that will work with openssl_public_encrypt will work for the $key
	 * argument. See http://us3.php.net/manual/en/openssl.certparams.php for more
	 * information.
	 * 
	 * Returns the encrypted text on success, false on failure.
	 * 
	 * @link http://us3.php.net/manual/en/openssl.certparams.php Key parameter details.
	 * @param string $text The text to be encrypted.
	 * @param mixed $key False to use the internal private key, an OpenSSL key value otherwise
	 * @return boolean 
	 */
	public static function encrypt( $text, $key = false )
	{
		if( !$key )
		{
			$key = WP_MSM_Options::instance()->privateSignature;
			$worked = openssl_private_encrypt( $text, $encrypted_text, $key );
			if( $worked )
				return $encrypted_text;
			return false;
		}
		$worked = openssl_public_encrypt( $text, $encrypted_text, $key );
		if( $worked )
			return $encrypted_text;
		return false;
	}

}
