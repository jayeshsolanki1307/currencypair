<?php

if ( !class_exists('WP_List_Table') ) {
    require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * List table class
 */
class Table_class extends WP_List_Table {

    function __construct() {
       
        parent::__construct( [
			'singular' => __( 'currency-pair', 'currency-pair' ), //singular name of the listed records
			'plural'   => __( 'currency-pairs', 'currency-pair' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
        $screen = get_current_screen();
    }

    /** Get all post **/  
    function wp_get_all_post( $args = array()  )  {  
        global $wpdb; 
        $table_name = $wpdb->prefix . 'posts';
        $defaults = array(  'number'  =>  20,  'offset'  =>  0,  'orderby'  =>  'id',  'order'  =>  'ASC',  ); 
        $args = wp_parse_args( $args, $defaults ); 
        $cache_key =  'post-all'; 
        $items = wp_cache_get( $cache_key,  'currency_pair'  );  

        $sql = "SELECT * FROM $table_name WHERE post_type = 'cpt_currency_pair' AND post_status = 'publish' ORDER BY  ".  $args['orderby'] ." ". $args['order']  . " LIMIT ". $args['offset']  .  ", "  . $args['number'];
        
        if  (  false  === $items )  { 
            $items = $wpdb->get_results(  $sql ); 
            wp_cache_set( $cache_key, $items,  'currency_pair'  );  
        }  
        return $items;  
    }

    /**
    * Delete a record.
    *
    * @param int $id post ID
    */
    public static function delete_post( $id ) {

		global $wpdb;
        
        $postTitle = strtolower( str_replace( '/', '_', get_the_title( $id ) ) );
        $post_type_query  = new WP_Query(  
            array (  
                'post_type'      => 'cpt_currency_pair',  
                'posts_per_page' => -1  
            )  
        );
        $posts_array = $post_type_query->posts;
        $allPostTitles = wp_list_pluck( $posts_array, 'post_title' );

        $count = 0;
        foreach( $allPostTitles as $index => $title ) {
            if( $title == get_the_title( $id ) ) {
                ++$count;
            }
        }

        if( $count <= 1 ) {
            delete_option( 'currencypair_fx_exchange_rate_data_' . $postTitle );
            delete_option( 'currencypair_fx_monthly_data_' . $postTitle );
			delete_option( 'currencypair_fx_weekly_data_' . $postTitle );
			delete_option( 'currencypair_fx_sixmonth_data_' . $postTitle );
        }

		$wpdb->delete(
			"{$wpdb->prefix}posts",
			[ 'ID' => $id ],
			[ '%d' ]
		);
        
	}    

    /**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
    function wp_get_post_count()  
    {  
        global $wpdb;  
        $table_name = $wpdb->prefix . 'posts';
        return  (int) $wpdb->get_var(  'SELECT COUNT(*) FROM '  . $table_name.' where post_type ="cpt_currency_pair" and post_status = "publish"');  
    }  

    /** Text displayed when no customer data is available */
    function no_items() {
        _e( 'No post found', 'currency-pair' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        $item_id      = $item->ID;
        $delete_nonce = wp_create_nonce( 'delete_nonce' );

        // Delete Action Link
        $action_delete = sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item_id ), $delete_nonce );
        
        // Edit Action Link
        $action_edit = sprintf( '<a href="javascript:void(0)" class="edit_cp_post" data-id=%s>Edit</a>', absint( $item_id ) );

        $row_action = '<div class="row-actions">
            <span class="edit">'.$action_edit.' | </span>
            <span class="trash">'.$action_delete.'</span>
        </div>';
        $post_title   = get_the_title( $item_id ). $row_action;
        $cp_shortcode = '[currency-pair id="'.$item_id.'"]';
        switch ( $column_name ) {
            case 'cp_title':
                return $post_title;

            case 'cp_shortcode':               
                $col_html = "<a id='copy_".$item_id."' class='copy_btn btn btn-primary' data-clipboard-text='".$cp_shortcode."'>".$cp_shortcode."</a>";
                return $col_html;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }
    
    /**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	*/
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%d" />', $item->ID
        );
    }
    
    /**
	 *  Associative array of columns
	 *
	 * @return array
	*/
    function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'cp_title' => __( 'Title', 'currency-pair' ),
            'cp_shortcode'   => __( 'Shortcode', 'currency-pair' ),
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
    function get_bulk_actions() {
        $actions = array(
            'bulk-delete'  => __( 'Delete', 'currency-pair' ),
        );
        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    function prepare_items() {        
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        /** Process bulk action */
        $this->process_bulk_action();
        
        //$per_page            = $this->get_items_per_page('cp_posts_per_page', 5);
        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $this->items  = self::wp_get_all_post( $args );

        $this->set_pagination_args( array(
            'total_items' => self::wp_get_post_count(),
            'per_page'    => $per_page
        ) );

    }

    /** process the bulk actions **/
    public function process_bulk_action() {    
            
        //Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
            
            // security check!, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );  
			if ( ! wp_verify_nonce( $nonce, 'delete_nonce' ) ) {
				die( 'something wrong' );
			}
			else {
				self::delete_post( absint( $_REQUEST['id'] ) );
            }
		}

        // If the delete bulk action is triggered
        if ( 
            ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || 
            ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
            $delete_ids = esc_sql( $_POST['id'] );
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );     

            foreach ( $delete_ids as $id ) {
                self::delete_post( $id );
            }    
        }
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }    

}
/** Fetch a single post from database ***/  
function wp_get_post( $id =  0  )  {  
    global $wpdb;  return $wpdb->get_row( $wpdb->prepare(  'SELECT * FROM '  . $wpdb->prefix .  'posts WHERE id = %d', $id )  );  
}
?>
<div class="wrap">   
    <?php $url = admin_url( 'admin.php?page=currency-pairs' ); ?>
    <form method="post">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>">
        <?php
            $list_table = new Table_class(); 
            $list_table->prepare_items(); 
            //$list_table->search_box(  'search',  'search_id'  ); 
            $list_table->display();  
        ?>
    </form>
</div> 

