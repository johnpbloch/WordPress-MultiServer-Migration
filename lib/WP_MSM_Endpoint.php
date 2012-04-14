<?php

class WP_MSM_Endpoint
{

	private static $actions = array(
	);

	/**
	 * Parses the endpoint url for the plugin. Returns a boolean indicating
	 * the success or failure of the endpoint. Failure can be a result of
	 * providing an invalid or empty action, or from an error internal to the
	 * endpoint's own functionality. Endpoint actions may either handle their
	 * own errors, or throw an exception. If an uncaught exception is thrown,
	 * parse will catch it and return false. Otherwise, it will return true.
	 * 
	 * If it returns true, an HTTP code of 200 will be sent and the script killed.
	 * If it returns false an HTTP code of 403 will be sent and the script killed.
	 * 
	 * If an endpoint action needs to be more descriptive or needs to respond with
	 * a body, that action method should implement its own headers and should also
	 * kill the script execution when it is finished.
	 * 
	 * @param string $endpointAction The request from the URI
	 * @return boolean Whether the request was completed successfully.
	 */
	public static function parse( $endpointAction )
	{
		$endpointAction = trim( $endpointAction, '/ ' );
		if( empty( $endpointAction ) )
			return false;
		$actionParts = explode( '/', $endpointAction );
		$action = array_shift( $actionParts );
		if( !in_array( $action, self::$actions ) || !method_exists( __CLASS__, $action ) )
			return false;
		if( empty( $actionParts ) )
			$actionParts = array( );
		try
		{
			call_user_func_array( array( __CLASS__, $action ), $actionParts );
		}
		catch( Exception $e )
		{
			return false;
		}
		return true;
	}

}
