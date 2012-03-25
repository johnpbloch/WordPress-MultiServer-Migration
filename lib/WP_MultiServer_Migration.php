<?php

class WP_MultiServer_Migration {

	public static function bootstrap() {
		add_action( 'init', array( __CLASS__, 'init' ), 11 );
		add_action( 'parse_request', array( __CLASS__, 'maybeDoManifest' ), 1 );
	}

	/**
	 * @global WP $wp 
	 */
	public static function init() {
		global $wp;
		add_rewrite_rule( 'wpmsm-manifest\.json$', 'index.php?wpmsmm=1', 'top' );
		$wp->add_query_var('wpmsmm');
	}

	public static function maybeDoManifest( WP &$wp ) {
		if( empty( $wp->query_vars['wpmsmm'] ) ) {
			return;
		}
		header( 'Content-type: application/json' );
		require( WP_MULTISERVER_VAR . '/manifest.php' );
		exit;
	}

}
