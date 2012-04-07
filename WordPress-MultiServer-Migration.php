<?php

/*
 * Plugin Name: WordPress Multi-Server Migration Tool
 * Version: 0.1-alpha
 */

// Check dependencies

if( !function_exists( 'openssl_pkey_new' ) )
{

	function _no_openssl()
	{
		deactivate_plugins( plugin_basename( __FILE__ ), true );
		add_settings_error( '', 'no-openssl', 'OpenSSL is required for MultiServer Migration and is not installed on this server. Please install it before activating.' );
	}

	function _maybe_do_errors()
	{
		global $current_screen;
		if( false === strpos( $current_screen->parent_base, 'option' ) )
		{
			settings_errors();
		}
	}

	add_action( 'admin_notices', '_maybe_do_errors' );
	add_action( 'admin_init', '_no_openssl' );
	return;
}

define( 'WP_MULTISERVER_DIR', dirname( __FILE__ ) . '/' );
define( 'WP_MULTISERVER_VAR', WP_MULTISERVER_DIR . 'var/' );

add_action( 'plugins_loaded', '_wp_multiserver_i18n', 1 );

function _wp_multiserver_i18n()
{
	load_plugin_textdomain( 'WordPress-MultiServer-Migration', false, dirname( __FILE__ ) . '/languages' );
}

if( function_exists( 'spl_autoload_register' ) )
{

	function _wp_multiserver_autoloader( $name )
	{
		static $files = null;
		if( null === $files )
		{
			$files = array( );
			$tempFiles = include( WP_MULTISERVER_VAR . 'files.php' );
			foreach( $tempFiles as $file_type => $files_list )
			{
				foreach( $files_list as $file )
				{
					$files[$file] = "$file_type/$file.php";
				}
			}
		}
		if( is_array( $files ) && !empty( $files[$name] ) )
			require( WP_MULTISERVER_DIR . $files[$name] );
	}

	spl_autoload_register( '_wp_multiserver_autoloader' );
} else
{
	include( WP_MULTISERVER_VAR . 'files.php' );
	foreach( $files_list as $wpm_file_type )
		foreach( $wpm_file_type as $wpm_dir => $wpm_file )
			require( WP_MULTISERVER_DIR . "$wpm_dir/$wpm_file.php" );
	unset( $files_list, $wpm_file_type, $wpm_dir, $wpm_file );
}

WP_MultiServer_Migration::bootstrap();
