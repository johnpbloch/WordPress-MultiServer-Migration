<?php

class WP_MSM_Profile_List_Table extends WP_List_Table
{

	/**
	 * A profile manager
	 * @var WP_MSM_Profile_Manager
	 */
	protected $profileManager;

	function __construct()
	{
		$this->profileManager = new WP_MSM_Profile_Manager();
		$args = array(
			'plural' => 'Profiles',
			'singular' => 'Profile'
		);
		parent::__construct( $args );
	}

	public function get_columns()
	{
		return array(
			'cb' => (empty( WP_MSM_Options::instance()->customProfiles ) ? '' : '<input type="checkbox" />'),
			'name' => __( 'Name', 'WordPress-MultiServer-Migration' ),
			'description' => __( 'Description', 'WordPress-MultiServer-Migration' ),
			'tables' => __( 'Tables to Exclude', 'WordPress-MultiServer-Migration' ),
		);
	}

	public function prepare_items()
	{
		$profileNames = $this->profileManager->get_all_profile_names();
		$totalItems = count( $profileNames );
		$profilesPerPage = $this->get_items_per_page( 'wpmsm_profiles_per_page', 10 );
		if( $totalItems < $profilesPerPage )
		{
			$page = 1;
			$totalPages = 1;
		}
		else
		{
			$page = $this->get_pagenum();
			$totalPages = ceil( $totalItems / $profilesPerPage );
		}
		if( $page > $totalPages )
			$page = $totalPages;
		$start = ($page - 1) * $profilesPerPage;
		if( $totalPages > 1 )
		{
			$profileNames = array_slice( $profileNames, $start, $profilesPerPage );
		}
		$this->items = array( );
		foreach( $profileNames as $name )
		{
			$this->items[$name] = $this->profileManager->get_profile( $name );
		}
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page' => $profilesPerPage,
		) );
	}

	protected function column_cb( WP_MSM_Profile $profile )
	{
		if( !$this->profileManager->is_default_profile( $profile->name ) )
		{
			?>
			<input type="checkbox" name="checked[]" value="<?php echo esc_attr( $profile->name ); ?>" />
			<?php
		}
	}

	protected function column_name( WP_MSM_Profile $profile )
	{
		echo '<strong>' . esc_html( $profile->displayName ) . '</strong>';
		$urlBase = WP_MSM_Admin::pageURL( 'profiles' );
		$editUrl = add_query_arg( array(
			'action' => 'edit',
			'profile' => $profile->name,
				), $urlBase );
		$duplicateUrl = add_query_arg( array(
			'action' => 'duplicate',
			'profile' => $profile->name,
			'_wpnonce' => wp_create_nonce( 'wpmsm_duplicate_profile_' . $profile->name ),
				), $urlBase );
		$deleteUrl = add_query_arg( array(
			'action' => 'delete',
			'profile' => $profile->name,
			'_wpnonce' => wp_create_nonce( 'wpmsm_delete_profile_' . $profile->name )
				), $urlBase );
		$actions = array(
			'edit' => '<a href="' . esc_url( $editUrl ) . '">' . _x( 'Edit', 'verb', 'WordPress-MultiServer-Migration' ) . '</a>',
			'duplicate' => '<a href="' . esc_url( $duplicateUrl ) . '">' . _x( 'Duplicate', 'verb', 'WordPress-MultiServer-Migration' ) . '</a>',
			'delete' => '<a href="' . esc_url( $deleteUrl ) . '">' . __( 'Delete', 'WordPress-MultiServer-Migration' ) . '</a>',
		);
		if( $this->profileManager->is_default_profile( $profile->name ) )
		{
			unset( $actions['edit'] );
			unset( $actions['delete'] );
		}
		echo $this->row_actions( $actions );
	}

	protected function column_description( WP_MSM_Profile $profile )
	{
		echo esc_html( $profile->description );
	}

	protected function column_tables( WP_MSM_Profile $profile )
	{
		if( !$profile->canExport )
		{
			echo 'Export disabled.';
		}
		elseif( empty( $profile->dbTablesToExclude ) )
		{
			echo 'No tables excluded';
		}
		else
		{
			$tables = implode( ', ', $profile->dbTablesToExclude );
			echo esc_html( $tables );
		}
	}

}
