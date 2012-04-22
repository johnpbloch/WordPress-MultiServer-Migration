<?php

class WP_MSM_Admin
{

	/**
	 * The list table for profiles.
	 * 
	 * @var WP_MSM_Profile_List_Table 
	 */
	protected static $profileListTable;

	public static function preLoadPage()
	{
		if( !empty( $_GET['subpage'] ) && $_GET['subpage'] == 'profiles' )
		{
			$action = empty( $_GET['action'] ) ? '' : stripslashes( $_GET['action'] );
			switch( $action )
			{
				case 'delete':
					$profile = empty( $_GET['profile'] ) ? '' : $_GET['profile'];
					check_admin_referer( "wpmsm_delete_profile_$profile" );
					$profileManager = new WP_MSM_Profile_Manager();
					if( !$profileManager->get_profile( $profile ) )
					{
						wp_die( __( 'That profile does not exist.', 'WordPress-MultiServer-Migration' ) );
					}
					elseif( $profileManager->is_default_profile( $profile ) )
					{
						wp_die( __( 'Cannot delete default profiles.', 'WordPress-MultiServer-Migration' ) );
					}
					$profileManager->delete_profile( $profile );
					$redirectUrl = add_query_arg( array(
						'action' => 'deleted',
							), self::pageURL( 'profiles' ) );
					wp_redirect( $redirectUrl );
					exit;
					break;
				case 'duplicate':
					$profile = empty( $_GET['profile'] ) ? '' : $_GET['profile'];
					check_admin_referer( "wpmsm_duplicate_profile_$profile" );
					$profileManager = new WP_MSM_Profile_Manager();
					$theProfile = $profileManager->get_profile( $profile );
					if( !$theProfile )
					{
						wp_die( __( "That profile does not exist.", 'WordPress-MultiServer-Migration' ) );
					}
					$newProfile = $theProfile->_toArray();
					$counter = 2;
					while( $profileManager->get_profile( $theProfile->name . $counter ) )
					{
						$counter++;
					}
					$newProfile['name'] = $theProfile->name . $counter;
					$newProfile['displayName'] = $theProfile->displayName . " $counter";
					$newProfile = new WP_MSM_Profile( $newProfile );
					$profileManager->add_profile( $newProfile->name, $newProfile );
					$redirectUrl = add_query_arg( array(
						'action' => 'edit',
						'profile' => $newProfile->name,
							), self::pageURL( 'profiles' ) );
					wp_redirect( $redirectUrl );
					exit;
					break;
				case 'deleted':
					// Add a settings error and then intentionally fall through to the default listing action.
					add_settings_error( '', 'profile-deleted', __( 'The profile was successfully deleted.', 'WordPress-MultiServer-Migration' ), 'updated' );
				default:
					self::$profileListTable = new WP_MSM_Profile_List_Table();
					self::$profileListTable->prepare_items();
			}
		}
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
			$args['subpage'] = $page;
		$url = add_query_arg( $args, admin_url( 'options-general.php' ) );
		return $url;
	}

	private static function getAvailablePages()
	{
		return array(
			'manage' => __( 'Manage Servers', 'WordPress-MultiServer-Migration' ),
			'settings' => __( 'Server Settings', 'WordPress-MultiServer-Migration' ),
			'profiles' => __( 'Manage Profiles', 'WordPress-MultiServer-Migration' ),
		);
	}

	private static function getCurrentPage()
	{
		$pages = self::getAvailablePages();
		$current_page = empty( $_GET['subpage'] ) || !isset( $pages[$_GET['subpage']] ) ? 'manage' : $_GET['subpage'];
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
		
	}

	private static function _render_settings()
	{
		
	}

	private static function _render_profiles()
	{
		$action = empty( $_GET['action'] ) ? '' : stripslashes( $_GET['action'] );
		switch( $action )
		{
			case 'edit':
				$profileManager = new WP_MSM_Profile_Manager();
				$profile = empty( $_GET['profile'] ) ? '' : $_GET['profile'];
				$profile = $profileManager->get_profile( $profile );
				if( !$profile )
				{
					wp_die( 'That profile does not exist!' );
				}
				?>
				<form method="post" action="">
					<?php wp_nonce_field( 'wpmsm_edit_profile_' . $profile->name ); ?>
				</form>
				<?php
				break;
			default:
				self::$profileListTable->views();
				?>
				<form method="get" action="">
					<br />
					<?php self::$profileListTable->search_box( __( 'Search Profiles', 'WordPress-MultiServer-Migration' ), 'profiles' ); ?>
				</form>
				<form method="post" action="">
					<input type="hidden" name="paged" value="<?php echo esc_attr( self::$profileListTable->get_pagenum() ) ?>" />
					<style>
						.wp-list-table th#name {
							width: 15%;
						}
					</style>
					<?php self::$profileListTable->display(); ?>
				</form>
			<?php
		}
	}

}
