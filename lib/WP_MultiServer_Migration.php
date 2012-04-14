
<?php

class WP_MultiServer_Migration
{

	public static function bootstrap()
	{
		add_action( 'init', array( __CLASS__, 'init' ), 11 );
		add_action( 'parse_request', array( __CLASS__, 'maybeDoManifest' ), 1 );
		add_action( 'parse_request', array( __CLASS__, 'maybeDoEndpoint' ), 1 );
		add_action( 'wpmsm_create_new_keys', array( 'WP_MSM_OpenSSL', 'generate_keys' ) );
		add_action( 'cron_schedules', array( __CLASS__, 'create_monthly_schedule' ) );
		if( is_admin() )
		{
			add_action( 'load-settings_page_wpmsm', array( 'WP_MSM_Admin', 'preLoadPage' ) );
			add_action( 'admin_menu', array( __CLASS__, 'adminMenu' ) );
		}
	}

	/**
	 * @global WP $wp 
	 */
	public static function init()
	{
		global $wp;
		add_rewrite_rule( 'wpmsm-manifest\.json$', 'index.php?wpmsmm=1', 'top' );
		add_rewrite_tag( '%wpmsmaction%', '(.+)' );
		add_permastruct( 'wpmsmendpoint', '/wpmsm-endpoint/%wpmsmaction%', false );
		$wp->add_query_var( 'wpmsmm' );
		if( !wp_next_scheduled( 'wpmsm_create_new_keys' ) )
		{
			wp_schedule_event( time(), 'monthly', 'wpmsm_create_new_keys' );
		}
	}

	public static function maybeDoManifest( WP &$wp )
	{
		if( empty( $wp->query_vars['wpmsmm'] ) )
		{
			return;
		}
		header( 'Content-type: application/json' );
		require( WP_MULTISERVER_VAR . '/manifest.php' );
		exit;
	}

	public static function maybeDoEndpoint( WP &$wp )
	{
		if( empty( $wp->query_vars['wpmsmaction'] ) )
		{
			return;
		}
		$request = WP_MSM_Endpoint::parse( $wp->query_vars['wpmsmaction'] );
		if( $request )
		{
			header( 'HTTP/1.0 200 OK' );
			exit;
		}
		else
		{
			header( 'HTTP/1.0 403 Forbidden' );
			exit;
		}
	}

	public static function create_monthly_schedule( array $schedules )
	{
		if( empty( $schedules['monthly'] ) )
		{
			$schedules['monthly'] = array(
				'interval' => (60 * 60 * 24 * 30),
				'display' => __( 'Monthly', 'WordPress-MultiServer-Migration' ),
			);
		}
		return $schedules;
	}

	public static function adminMenu()
	{
		add_options_page( __( 'Manage Servers', 'WordPress-MultiServer-Migration' ), __( 'Manage Servers', 'WordPress-MultiServer-Migration' ), 'manage_options', 'wpmsm', array( 'WP_MSM_Admin', 'render' ) );
	}

}
