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
				case 'edit':
					$submitted = empty( $_POST['wpmsm'] ) ? false : $_POST['wpmsm'];
					if( empty( $_POST['_wpnonce'] ) || !$submitted )
					{
						break;
					}
					check_admin_referer( "wpmsm_edit_profile_{$_GET['profile']}" );
					$submitted = stripslashes_deep( $submitted );
					$profileManager = new WP_MSM_Profile_Manager();
					$profile = $profileManager->get_profile( $_GET['profile'] );
					if( !$profile )
					{
						wp_die( __( 'That profile does not exist.', 'WordPress-MultiServer-Migration' ) );
					}
					$slugName = $submitted['name'];
					$slugName = sanitize_title_with_dashes( $slugName );
					if( $slugName != $profile->name && $profileManager->get_profile( $slugName ) )
					{
						add_settings_error( '', 'name-collision', __( 'Invalid name. Name already exists.', 'WordPress-MultiServer-Migration' ) );
						break;
					}
					$profile->name = $slugName;
					$profile->displayName = $submitted['name'];
					$profile->description = $submitted['description'];
					$profile->canExport = 'no' === $submitted['disable_export'];
					$profile->dbTablesToExclude = $submitted['exclude_table'];
					$profileManager->update_profile( $_GET['profile'], $profile );
					if( $_GET['profile'] != $profile->name )
					{
						$redirectUrl = add_query_arg( array(
							'action' => 'edit',
							'profile' => $profile->name,
								), self::pageURL( 'profiles' ) );
						wp_redirect( $redirectUrl );
						exit;
					}
					break;
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
		<style>
			.wrap div.updated, .wrap div.error {
				margin-bottom: 0;
			}
		</style>
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
		global $wpdb;
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
				<form style="max-width:500px;" method="post" action="">
					<h2><?php printf( __( 'Edit Profile: %s', '' ), $profile->displayName ); ?></h2>
					<?php wp_nonce_field( 'wpmsm_edit_profile_' . $profile->name ); ?>
					<p>
						<label for="wpmsm_name"><strong><?php _e( 'Profile Name', 'WordPress-MultiServer-Migration' ); ?></strong></label><br />
						<input type="text" class="widefat" name="wpmsm[name]" id="wpmsm_name" value="<?php echo esc_attr( $profile->displayName ); ?>" />
					</p>
					<p>
						<label for="wpmsm_description"><strong><?php _e( 'Description', 'WordPress-MultiServer-Migration' ); ?></strong></label><br />
						<textarea name="wpmsm[description]" class="widefat" id="wpmsm_description"><?php echo esc_textarea( $profile->description ); ?></textarea>
					</p>
					<p>
						<strong><?php _e( 'Disable export?', 'WordPress-MultiServer-Migration' ); ?></strong><br />
						<input type="radio" name="wpmsm[disable_export]" value="yes" id="wpmsm_disable_export_yes"<?php checked( !$profile->canExport ); ?> />
						<label for="wpmsm_disable_export_yes"> Yes </label>
						<input type="radio" name="wpmsm[disable_export]" value="no" id="wpmsm_disable_export_no"<?php checked( $profile->canExport ); ?> />
						<label for="wpmsm_disable_export_no"> No</label>
					</p>
					<?php
					$tables = $wpdb->get_col( "SHOW TABLES LIKE '$wpdb->prefix%';" );
					foreach( $tables as &$table )
						$table = preg_replace( '@^' . preg_quote( $wpdb->prefix, '@' ) . '@', '', $table );
					unset( $table );
					$table_names = array(
						'commentmeta' => __( 'Comment Meta', 'WordPress-MultiServer-Migration' ),
						'comments' => __( 'Comments', 'WordPress-MultiServer-Migration' ),
						'links' => __( 'Links', 'WordPress-MultiServer-Migration' ),
						'options' => __( 'Options', 'WordPress-MultiServer-Migration' ),
						'postmeta' => __( 'Post Meta', 'WordPress-MultiServer-Migration' ),
						'posts' => __( 'Posts', 'WordPress-MultiServer-Migration' ),
						'term_relationships' => __( 'Term Relationships', 'WordPress-MultiServer-Migration' ),
						'term_taxonomy' => __( 'Term Taxonomy', 'WordPress-MultiServer-Migration' ),
						'terms' => __( 'Terms', 'WordPress-MultiServer-Migration' ),
						'usermeta' => __( 'User Meta', 'WordPress-MultiServer-Migration' ),
						'users' => __( 'Users', 'WordPress-MultiServer-Migration' )
					);
					$counter = 0;
					echo '<h4>Database Tables to exclude from exports</h4><p>';
					foreach( $tables as $table )
					{
						$label = empty( $table_names[$table] ) ? $table : $table_names[$table];
						$excluded = in_array( $table, $profile->dbTablesToExclude );
						?>
						<div style="width:30%;margin-right:3%;float:left;">
							<label>
								<input type="checkbox" name="wpmsm[exclude_table][]" value="<?php echo esc_attr( $table ); ?>"<?php checked( $excluded ); ?> />
								<?php echo esc_html( $label ); ?></label>
						</div>
						<?php
						if( !(++$counter % 3 ) )
						{
							echo '<div class="clear"></div></p><p>';
						}
					}
					echo '<div class="clear"></div></p>';
					submit_button( __( 'Update Profile', 'WordPress-MultiServer-Migration' ) );
					?>
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
