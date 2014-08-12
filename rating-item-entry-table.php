<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Rating_Item_Entry_Table class
 * @author dpowney
 *
 */
class Rating_Item_Entry_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN 				= 'cb',
	RATING_ITEM_ENTRY_ID_COLUMN 	= 'rating_item_entry_id',
	POST_ID_COLUMN 					= 'post_id',
	ENTRY_DATE_COLUMN 				= 'entry_date',
	IP_ADDRESS_COLUMN 				= 'ip_address',
	USERNAME_COLUMN 				= 'username',
	RATING_RESULT_COLUMN 			= 'rating_result',
	SHORTCODE_COLUMN 				= 'shortcode',
	ACTION_COLUMN 					= 'action',
	DELETE_CHECKBOX 				= 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular' => __( 'Rating Result', 'multi-rating' ),
				'plural' => __( 'Rating Results', 'multi-rating' ),
				'ajax'	=> false
		) );
		
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == "top" ) {
			echo '';
		}
		
		if ( $which == "bottom" ) {
			echo '';
		}
		
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		
		return array(
				Rating_Item_Entry_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN =>__( 'Entry Id', 'multi-rating' ),
				Rating_Item_Entry_Table::POST_ID_COLUMN => __( 'Post Id', 'multi-rating' ),
				Rating_Item_Entry_Table::ENTRY_DATE_COLUMN =>__( 'Entry Date', 'multi-rating' ),
				Rating_Item_Entry_Table::IP_ADDRESS_COLUMN	=>__( 'IP Address', 'multi-rating' ),
				Rating_Item_Entry_Table::USERNAME_COLUMN => __( 'Username', 'multi-rating' ),
				Rating_Item_Entry_Table::RATING_RESULT_COLUMN => __( 'Rating Result', 'multi-rating' ),
				Rating_Item_Entry_Table::ACTION_COLUMN => __( 'Action', 'multi-rating' ),
				Rating_Item_Entry_Table::SHORTCODE_COLUMN => __( 'Shortcode', 'multi-rating' )
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		
		global $wpdb;
		
		// Process any bulk actions first
		$this->process_bulk_action();

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array( );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// get table data
		$query = "SELECT * FROM ".$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;;
		
		$query .= ' ORDER BY entry_date DESC';
		
		// pagination
		$item_count = $wpdb->query( $query ); //return the total number of affected rows
		$items_per_page = 10;
		$page_num = ! empty( $_GET[ "paged" ] ) ? mysql_real_escape_string( $_GET["paged"] ) : '';
		if ( empty( $page_num ) || !is_numeric( $page_num ) || $page_num <= 0 ) {
			$page_num = 1;
		}
		$total_pages = ceil( $item_count / $items_per_page );
		// adjust the query to take pagination into account
		if ( !empty( $page_num ) && !empty( $items_per_page ) ) {
			$offset = ( $page_num -1 ) * $items_per_page;
			$query .= ' LIMIT ' .(int) $offset. ',' . (int) $items_per_page;
		}
		$this->set_pagination_args( array( "total_items" => $item_count, "total_pages" => $total_pages, "per_page" => $items_per_page ) );
		
		$this->items = $wpdb->get_results( $query, ARRAY_A );
		
	}

	/**
	 * Column default
	 * 
	 * @param $item
	 * @param $column_name
	 * @return 
	 */
	function column_default( $item, $column_name ) {
		
		switch( $column_name ) {
			
			case Rating_Item_Entry_Table::SHORTCODE_COLUMN :
				echo '[display_rating_result post_id="' . $item[ Rating_Item_Entry_Table::POST_ID_COLUMN ] . '"]';
				break;
				
			case Rating_Item_Entry_Table::ENTRY_DATE_COLUMN :
				echo date( 'F j, Y, g:i a', strtotime( $item[ $column_name ] ) );
				break;
				
			case Rating_Item_Entry_Table::CHECKBOX_COLUMN :
				return $item[ $column_name ];
				break;
				
			case Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN :
			case Rating_Item_Entry_Table::POST_ID_COLUMN :
			case Rating_Item_Entry_Table::IP_ADDRESS_COLUMN :
			case Rating_Item_Entry_Table::USERNAME_COLUMN :
				echo $item[ $column_name ];
				break;
				
			case Rating_Item_Entry_Table::RATING_RESULT_COLUMN :
				$rating_result = Multi_Rating_API::calculate_rating_item_entry_result( $item[ Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN ] );
				
				$star_rating_result = sprintf( __( 'Star Rating: %d/5', 'multi-rating' ), $rating_result[ 'adjusted_star_result' ]);
				$score_rating_result = sprintf( __( 'Score: %d/%d', 'multi-rating' ), $rating_result[ 'adjusted_score_result' ], $rating_result[ 'total_max_option_value' ]);
				$percentage_rating_result = sprintf( __( 'Percentage: %d%%', 'multi-rating' ), $rating_result[ 'adjusted_percentage_result' ] );
				
				echo $star_rating_result . '<br />' . $score_rating_result . '<br />' . $percentage_rating_result;
				break;
				
			case Rating_Item_Entry_Table::ACTION_COLUMN :
				?>
				<a class="view-rating-item-entry-values-anchor" href="?page=<?php echo Multi_Rating::RATING_RESULTS_PAGE_SLUG; ?>&tab=<?php echo Multi_Rating::ENTRY_VALUES_TAB; ?>&rating-item-entry-id=<?php echo $item[ Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN ]; ?>"><?php _e('View Entry Values', 'multi-rating'); ?></a>
				<?php
				break;
				
			default:
				return print_r( $item, true ) ;
		}
		
	}
	
	/**
	 * checkbox column
	 * @param unknown_type $item
	 * @return string
	 */
	function column_cb( $item ) {
		
		return sprintf( '<input type="checkbox" name="' . Rating_Item_Entry_Table::DELETE_CHECKBOX . '" value="%s" />', $item[ Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN ] );
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		
		$bulk_actions = array( 'delete' => __('Delete', 'multi-rating') );
		
		return $bulk_actions;
		
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		
		if ( $this->current_action() === 'delete') {
			
			global $wpdb;
			
			$checked = ( is_array( $_REQUEST[ 'delete' ] ) ) ? $_REQUEST[ 'delete' ] : array( $_REQUEST[ 'delete' ] );
			
			foreach( $checked as $id ) {
				// TODO set acvtive column to 0 instead of deleting row
				$query = 'DELETE FROM ' . $wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE ' .  Rating_Item_Entry_Table::RATING_ITEM_ENTRY_ID_COLUMN . ' = ' . $id;
				$results = $wpdb->query($query);
				
			}
			
			echo '<div class="updated"><p>' . _e('Entries deleted successfully', 'multi-rating') . '</p></div>';
		}
	}
	
}