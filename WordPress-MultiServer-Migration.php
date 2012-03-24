<?php

/*
 * Plugin Name: WordPress Multi-Server Migration Tool
 * Version: 0.1-alpha
 */

define( 'WP_MULTISERVER_DIR', dirname( __FILE__ ) . '/' );
define( 'WP_MULTISERVER_VAR', WP_MULTISERVER_DIR . 'var/' );

add_action( 'init', '_wp_multiserver_i18n' );

function _wp_multiserver_i18n() {
	load_plugin_textdomain( 'WordPress-MultiServer-Migration', false, dirname( __FILE__ ) . '/languages' );
}

if( function_exists( 'spl_autoload_register' ) ) {

	function _wp_multiserver_autoloader( $name ) {
		static $files = null;
		if( null === $files ) {
			$files = array( );
			$tempFiles = include( WP_MULTISERVER_VAR . 'files.php' );
			foreach( $tempFiles as $file_type => $files_list ) {
				foreach( $files_list as $file ) {
					$files[$file] = "$file_type/$file.php";
				}
			}
		}
		if( is_array( $files ) && !empty( $files[$name] ) )
			require( WP_MULTISERVER_DIR . $files[$name] );
	}

	spl_autoload_register( '_wp_multiserver_autoloader' );
} else {
	include( WP_MULTISERVER_VAR . 'files.php' );
	foreach( $files_list as $wpm_file_type )
		foreach( $wpm_file_type as $wpm_dir => $wpm_file )
			require( WP_MULTISERVER_DIR . "$wpm_dir/$wpm_file.php" );
	unset( $files_list, $wpm_file_type, $wpm_dir, $wpm_file );
}
