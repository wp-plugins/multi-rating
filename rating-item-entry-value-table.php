<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Rating_Item_Entry_Value_Table class
 * @author dpowney
 *
 */
class Rating_Item_Entry_Value_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ENTRY_ID_COLUMN = 'rating_item_entry_id',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	DESCRIPTION_COLUMN = 'description',
	VALUE_COLUMN = 'value',
	MAX_OPTION_VALUE_COLUMN = 'max_option_value',
	ACTION_COLUMN = 'action',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( array(
				'singular'=> __( 'Entry Value', 'multi-rating' ),
				'plural' => __( 'Entry Values', 'multi-rating' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == "top" ){
			$rating_item_entry_id = $this->get_rating_item_entry_id();
			if ($rating_item_entry_id == null) {
				$rating_item_entry_id = '';
			}
			
			echo '<label for="rating-item_entry-id">' . _e('Entry Id', 'multi-rating' ) . '</label>';
			echo '<input type="text" class="regular-text" placeholder="' . __('Enter Entry Id', 'multi-rating' ) . '" name="rating-item-entry-id" value="' . $rating_item_entry_id . '" />';
			echo '<input type="submit" class="button" value="' . __( 'Submit', 'multi-rating' ) . '" />';
		}
		if ( $which == "bottom" ){
			echo '';
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		return array(
				Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN =>__( 'Rating Item Entry Id', 'multi-rating' ),
				Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN => __( 'Rating Item Id', 'multi-rating' ),
				Rating_Item_Entry_Value_Table::DESCRIPTION_COLUMN =>__( 'Description' , 'multi-rating' ),
				Rating_Item_Entry_Value_Table::VALUE_COLUMN	=>__( 'Value', 'multi-rating' ),
				Rating_Item_Entry_Value_Table::MAX_OPTION_VALUE_COLUMN => __( 'Max Option Value', 'multi-rating' )
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array( Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN, Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$rating_item_entry_id = $this->get_rating_item_entry_id();
		if ( $rating_item_entry_id == null ) {
			return;
		}
		
		$query = 'SELECT ri.description AS description, riev.value AS value, ri.max_option_value AS max_option_value, '
		. 'riev.rating_item_entry_id AS rating_item_entry_id, ri.rating_item_id AS rating_item_id ' 
		. 'FROM '.$wpdb->prefix.Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev, '
		. $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . ' AS ri WHERE ri.rating_item_id = riev.rating_item_id '
		. 'AND riev.rating_item_entry_id = "' . $rating_item_entry_id . '"';

		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Default column
	 * @param $item
	 * @param $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case Rating_Item_Entry_Value_Table::RATING_ITEM_ENTRY_ID_COLUMN :
			case Rating_Item_Entry_Value_Table::RATING_ITEM_ID_COLUMN :
			case Rating_Item_Entry_Value_Table::DESCRIPTION_COLUMN :
			case Rating_Item_Entry_Value_Table::VALUE_COLUMN :
			case Rating_Item_Entry_Value_Table::MAX_OPTION_VALUE_COLUMN :
				echo $item[ $column_name ];
				break;
			default:
				return print_r( $item, true ) ;
		}
	}
	
	private function get_rating_item_entry_id() {
		if (isset($_POST['rating-item-entry-id'])) {
			return $_POST['rating-item-entry-id'];
		} else if (isset($_GET['rating-item-entry-id'])) {
			return $_GET['rating-item-entry-id'];
		}
		return null;
	}
}