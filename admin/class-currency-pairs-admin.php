<?php

class Currency_Pairs_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $screen_ids;

    private $toolbar_menus;

    private $submenus;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add Filter for plugin screen
		add_filter('currency_pair_get_screen_ids',array( $this, 'get_screen_ids' ),10);

		//Add CPT for Currency Pair
		add_action( 'init', array( $this, 'currency_pair_register_cpt' ) );
		
		// Add an menu page
		add_action( 'admin_menu', array( $this, 'currency_pair_register_settings_page' ) );

		// Insert Form Data 
		add_action( "wp_ajax_currency_pair_ajax_insert", array( $this,"currency_pair_ajax_insert_handler") ); 

		//Update Form Data
		add_action( "wp_ajax_currency_pair_ajax_update", array( $this,"currency_pair_ajax_update_handler") ); 
	}

	/**
	 * Add plugin screen function
	 */
	public function get_screen_ids( $screen_ids ) {       
        $screen_ids[] = 'toplevel_page_'.$this->plugin_name;
		$screen_ids[] = 'currency-pairs_page_currency-converter';
        return $screen_ids;
    }

	/**
	 * Update data function handler
	 */
	public function currency_pair_ajax_update_handler( ) {     		
		
		if ( 
			isset( $_REQUEST['currency_pair_nonce'] ) && 
			isset( $_REQUEST['post_id'] ) && !empty( $_REQUEST['post_id'] ) && 
			wp_verify_nonce( $_REQUEST['currency_pair_nonce'], 'currency_pair_ajax_nonce' ) 
		) {
			// post id	
			$post_id =  $_REQUEST['post_id'];

			//fetch target url
			$target_url = get_post_meta( $post_id, 'target_url', true );
			if( !is_wp_error( $post_id ) ) {
				$response = array(
					'status' => 1,
					'target_url' => $target_url,
					'message' => __( 'Data Inserted', 'currency-pair' )
				);				
			}else {
				$response = array(
					'status' => 0,
					'message' => __( 'Data Not Inserted', 'currency-pair' )
				);	
			}

			echo json_encode( $response );
		}
		wp_die();
	}

	/**
	 * Add insert data function handler
	 */
	public function currency_pair_ajax_insert_handler( ) {     		
		if ( 
			isset( $_REQUEST['currency_pair_nonce'] ) && 
			isset( $_REQUEST['formdata'] ) && !empty( $_REQUEST['formdata'] ) && 
			isset( $_REQUEST['formType'] ) && !empty( $_REQUEST['formType'] ) && 
			wp_verify_nonce( $_REQUEST['currency_pair_nonce'], 'currency_pair_ajax_nonce' ) 
		) {
			$form_data = array();
			$response = array();
			parse_str( $_REQUEST['formdata'], $form_data );
			$currency1 = $form_data['currency1'];
			$currency2 = $form_data['currency2'];
			$target_url = $form_data['target_url'];
			$formType = $_REQUEST['formType'];
			
			if( $formType=='update' && ( isset( $_REQUEST['post_id'] ) && $_REQUEST['post_id']!='' )){
				
				$post_id = $_REQUEST['post_id'];
				update_post_meta( $_REQUEST['post_id'], 'target_url', $target_url );
				if( !is_wp_error( $post_id ) ) {
					$response = array(
						'status' => 1,
						'message' => __( 'Data Updated', 'currency-pair' )
					);				
				}else {
					$response = array(
						'status' => 0,
						'message' => __( 'Data Not Updated', 'currency-pair' )
					);	
				}
			}elseif ( $formType=='insert' ) {
				$title = $currency1.'/'.$currency2;
				$post_args = array(
					'post_title'    => $title,
					'post_status'   => 'publish',
					'post_type'=> 'cpt_currency_pair',
					'meta_input'   => array(
						'target_url' => $target_url,
					),
				);
	
				// insert the post into the database		
				$post_id =  wp_insert_post( $post_args );
	
				if( !is_wp_error( $post_id ) ) {
					$response = array(
						'status' => 1,
						'message' => __( 'Data Inserted', 'currency-pair' )
					);				
				}else {
					$response = array(
						'status' => 0,
						'message' => __( 'Data Not Inserted', 'currency-pair' )
					);	
				}
			}		

			echo json_encode( $response );
		}
		wp_die();
    }

	/**
	 * Register a custom menu page.
	 */
	public function currency_pair_register_settings_page() {
		add_menu_page(
			__( 'Currency Pairs', 'currency-pairs' ), #page_title
			'Currency Pairs', #menu_title
			'manage_options', #caapability
			'currency-pairs', #menu_slug
			array($this, 'currency_pair_handle_settings_page'), #callback
			'dashicons-chart-area', #icon_url
			null #position
		);

		// Add submenu
		add_submenu_page( 
			"currency-pairs",  #parent_slug
			__("Currency Converter","currency-pairs"), #page_title
			__("Currency Converter","currency-pairs"), #menu_title
			'manage_options', #caapability
			"currency-converter", #submenu_slug
			[ $this, "currency_converter_submenu_page" ] #submenu_callback
		);
	}

	/**
	 * menu callback function
	 */
	public function currency_pair_handle_settings_page() {
		include CURRENCY_PAIRS_ROOT_DIR_ADMIN.'/partials/currency-pairs-admin-display.php';
	}

	/**
	 * submenu callback function
	 */
	public function currency_converter_submenu_page() {		
		include CURRENCY_PAIRS_ROOT_DIR_ADMIN.'/partials/currency-converter-admin-display.php';
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		$this->screen_ids = apply_filters('currency_pair_get_screen_ids',$this->screen_ids);
        if ( in_array( get_current_screen()->id, $this->screen_ids) ) {

			#include bootstrap css
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );

			wp_enqueue_style( 'currency-pairs-admin', plugin_dir_url( __FILE__ ) . 'css/currency-pairs-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$this->screen_ids = apply_filters('currency_pair_get_screen_ids',$this->screen_ids);
        if ( in_array( get_current_screen()->id, $this->screen_ids) ) {
			
			// popper script
			wp_register_script( 'popper', CURRENCY_PAIRS_PLUGIN_DIR_URL . 'js/popper.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'popper' );

			// bootstrap script
			wp_enqueue_script( $this->plugin_name, CURRENCY_PAIRS_PLUGIN_DIR_URL . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );

			// validation script
			wp_register_script( 'validate', CURRENCY_PAIRS_PLUGIN_DIR_URL . '/js/jquery.validate.min.js', array( 'jquery' ), NULL, false );
			wp_enqueue_script( 'validate' );
			
			// admin script
			wp_register_script( 'currency-pairs-admin', CURRENCY_PAIRS_PLUGIN_DIR_URL . '/js/currency-pairs-admin.js', array( 'jquery' ), NULL, false );
			wp_enqueue_script( 'currency-pairs-admin' );
			
			// copy to clipboard script
			wp_register_script( 'copy-clipboard', CURRENCY_PAIRS_PLUGIN_DIR_URL . '/js/clipboard.min.js', array( 'jquery' ), NULL, false );
			wp_enqueue_script( 'copy-clipboard' );

			wp_localize_script( $this->plugin_name, 'currency_pair_ajax_object', array(
				'ajax_url'   => admin_url('admin-ajax.php'), 
				'admin_url'  => admin_url(), 
				'ajax_nonce' => wp_create_nonce('currency_pair_ajax_nonce')
			));
		}
	}
	/**
	 * Create function for CPT
	 */

	public function currency_pair_register_cpt(){
		// Set UI labels for Custom Post Type
		$labels = array(
			'name'                => _x( 'Currency Pair', 'Post Type General Name', 'currency-pair' ),
			'singular_name'       => _x( 'Currency Pair', 'Post Type Singular Name', 'currency-pair' ),
			'menu_name'           => __( 'Currency Pair', 'currency-pair' ),
			'parent_item_colon'   => __( 'Parent Theme', 'currency-pair' ),
			'all_items'           => __( 'All Themes', 'currency-pair' ),
			'view_item'           => __( 'View Theme Details', 'currency-pair' ),
			'add_new_item'        => __( 'Add New Theme Details', 'currency-pair' ),
			'add_new'             => __( 'Add New', 'currency-pair' ),
			'edit_item'           => __( 'Edit Theme Data', 'currency-pair' ),
			'update_item'         => __( 'Update Theme Data', 'currency-pair' ),
			'search_items'        => __( 'Search Theme Data', 'currency-pair' ),
			'not_found'           => __( 'Not Found', 'currency-pair' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'currency-pair' ),
		);

		// Set other options for Custom Post Type
		$args = array(
			'label'               => __( 'Currency Pair', 'currency-pair' ),
			'description'         => __( 'Currency Pair Data', 'currency-pair' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),	     
	        'hierarchical'        => false,
	        'public'              => true,
	        'show_ui'             => false,
	        'show_in_menu'        => true,
	        'show_in_nav_menus'   => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 5,
	        'can_export'          => true,
	        'has_archive'         => true,
	        'exclude_from_search' => false,
	        'publicly_queryable'  => true,
	        'capability_type'     => 'post',
	        'show_in_rest'        => false,
	    );

	    // Registering your Custom Post Type
		register_post_type( 'cpt_currency_pair', $args );
	}
}
