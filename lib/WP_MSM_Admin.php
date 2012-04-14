<?php

class WP_MSM_Admin
{

	public static function preLoadPage()
	{
		
	}

	public static function render()
	{
		?>
		<div class="wrap">
			<div class="icon32">
				<img src="<?php echo esc_url( plugins_url( 'media/img/servers-icon.png', dirname( __FILE__ ) ) ); ?>" />
			</div>
			<?php
			self::tabs();
			self::renderCurrentScreen();
			?>
		</div>
		<?php
	}

	public static function pageURL( $page = 'manage' )
	{
		$pages = self::getAvailablePages();
		$page = isset( $pages[$page] ) ? $page : 'manage';
		$args = array(
			'page' => 'wpmsm',
		);
		if( $page != 'manage' )
			$args['action'] = $page;
		$url = add_query_arg( $args, admin_url( 'options-general.php' ) );
		return $url;
	}

	private static function getAvailablePages()
	{
		return array(
			'manage' => __( 'Manage Servers', 'WordPress-MultiServer-Migration' ),
			'settings' => __( 'Server Settings', 'WordPress-MultiServer-Migration' ),
		);
	}

	private static function getCurrentPage()
	{
		$pages = self::getAvailablePages();
		$current_page = empty( $_GET['action'] ) || !isset( $pages[$_GET['action']] ) ? 'manage' : $_GET['action'];
		return $current_page;
	}

	private static function tabs()
	{
		$pages = self::getAvailablePages();
		$current_page = self::getCurrentPage();
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $pages as $slug => $page )
		{
			?>
			<a class="nav-tab<?php if( $slug == $current_page ) echo ' nav-tab-active'; ?>" href="<?php echo esc_url( self::pageURL( $slug ) ); ?>">
				<?php echo esc_html( $page ); ?>
			</a>
			<?php
		}
		echo '</h2>';
	}

	private static function renderCurrentScreen()
	{
		$current_page = self::getCurrentPage();
		if( method_exists( __CLASS__, "_render_$current_page" ) )
			call_user_func( array( __CLASS__, "_render_$current_page" ) );
	}

	private static function _render_manage()
	{
		echo 'manage';
	}

	private static function _render_settings()
	{
		echo 'settings';
	}

}
