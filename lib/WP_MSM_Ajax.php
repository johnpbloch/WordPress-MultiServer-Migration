<?php

class WP_MSM_Ajax {

	private static $actions = array( );
	private static $public_actions = array( );

	public static function init() {
		foreach( self::$actions as $action ) {
			if( method_exists( get_class(), "_$action" ) )
				add_action( "wp_ajax_$action", array( get_class(), $action ) );
		}
		foreach( self::$public_actions as $action ) {
			if( method_exists( get_class(), "_$action" ) )
				add_action( "wp_ajax_nopriv_$action", array( get_class(), $action ) );
		}
	}

}
