<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Multi_Rating_Table class
 * @author dpowney
 *
 */
class Multi_Rating_Table extends WP_List_Table {

	const
	DESCRIPTION_COLUMN = 'description',
	MAX_RATING_VALUE_COLUMN = 'max_rating_value',
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	RATING_ID_COLUMN = 'rating_id',
	DEFAULT_RATING_VALUE_COLUMN = "default_rating_value",
	WEIGHT_COLUMN = "weight",
	SINGULAR_LABEL = "Rating Item Criteria",
	PLURAL_LABEL = 'Rating Item Criteria',
	DESCRIPTION_LABEL = 'Description',
	MAX_RATING_VALUE_LABEL = "Maximum Rating Value",
	RATING_ITEM_ID_LABEL = "ID",
	DEFAULT_RATING_VALUE_LABEL = "Default rating value",
	WEIGHT_LABEL = "Weight",
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( array(
				'singular'=> Multi_Rating_Table::SINGULAR_LABEL,
				'plural' => Multi_Rating_Table::PLURAL_LABEL,
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			echo "";
		}
		if ( $which == "bottom" ){
			echo "";
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		return $columns= array(
				Multi_Rating_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				Multi_Rating_Table::RATING_ITEM_ID_COLUMN =>__(Multi_Rating_Table::RATING_ITEM_ID_LABEL),
				Multi_Rating_Table::RATING_ID_COLUMN => __(''),
				Multi_Rating_Table::DESCRIPTION_COLUMN =>__(Multi_Rating_Table::DESCRIPTION_LABEL),
				Multi_Rating_Table::MAX_RATING_VALUE_COLUMN	=>__(Multi_Rating_Table::MAX_RATING_VALUE_LABEL),
				Multi_Rating_Table::WEIGHT_COLUMN	=>__(Multi_Rating_Table::WEIGHT_LABEL),
				Multi_Rating_Table::DEFAULT_RATING_VALUE_COLUMN => __(Multi_Rating_Table::DEFAULT_RATING_VALUE_LABEL)
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
		$hidden = array( Multi_Rating_Table::RATING_ID_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		// get table data
		$query = "SELECT * FROM ".$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME;
		
		// pagination
		$item_count = $wpdb->query( $query ); //return the total number of affected rows
		$items_per_page = 10;
		$page_num = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
		if ( empty( $page_num ) || !is_numeric( $page_num ) || $page_num <= 0 ) {
			$page_num = 1;
		}
		$total_pages = ceil( $item_count / $items_per_page );
		// adjust the query to take pagination into account
		if ( !empty( $page_num ) && !empty( $items_per_page ) ) {
			$offset=($page_num-1)*$items_per_page;
			$query .= ' LIMIT ' .(int) $offset. ',' .(int) $items_per_page;
		}
		$this->set_pagination_args( array( "total_items" => $item_count, "total_pages" => $total_pages, "per_page" => $items_per_page ) );
		
		$this->items = $wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case Multi_Rating_Table::CHECKBOX_COLUMN :
			case Multi_Rating_Table::RATING_ITEM_ID_COLUMN :
			case Multi_Rating_Table::RATING_ID_COLUMN :
				return $item[ $column_name ];
				break;
			case Multi_Rating_Table::WEIGHT_COLUMN:
			case Multi_Rating_Table::DESCRIPTION_COLUMN :
			case Multi_Rating_Table::MAX_RATING_VALUE_COLUMN:
			case Multi_Rating_Table::DEFAULT_RATING_VALUE_COLUMN:
				$this->column_actions( $item, $column_name );
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
	function column_cb($item) {
		return sprintf(
				'<input type="checkbox" name="'.Multi_Rating_Table::DELETE_CHECKBOX.'" value="%s" />', $item[Multi_Rating_Table::RATING_ITEM_ID_COLUMN]
		);
	}

	function column_actions($item, $column_name) {
		$row_id = $item[Multi_Rating_Table::RATING_ITEM_ID_COLUMN];
		$row_value = stripslashes($item[$column_name]);
		$edit_btn_id = 'edit-'.$column_name.'-'.$row_id;
		$save_btn_id = 'save-'.$column_name.'-'.$row_id;
		$view_section_id = 'viewSection-'. $column_name . '-'. $row_id;
		$edit_section_id = 'editSection-'. $column_name . '-'. $row_id;
		$input_id = 'input-'. $column_name . '-'. $row_id;
		$text_id = 'text-'. $column_name . '-'. $row_id;
		echo '<div id="' .$view_section_id.'"><div id="'.$text_id.'">'.$row_value.'</div><div class="row-actions"><a href="#" id="'.$edit_btn_id.'">Edit</a></div></div>';
		echo '<div id="'.$edit_section_id.'" style="display: none;"><input type="text" id="'.$input_id.'" value="'.$row_value.'" style="width: 100%;" /><div class="row-actions"><a href="#" id="'.$save_btn_id.'">Save</a></div></div>';	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		$bulk_actions = array(
				'delete'    => 'Delete'
		);
		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		if ($this->current_action() ==='delete') {
			global $wpdb;
			
			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );
			
			foreach($checked as $id) {
				// TODO set acvtive column to 0 instead of deleting row
				$query = "DELETE FROM ". $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . " WHERE " .  Multi_Rating_Table::RATING_ITEM_ID_COLUMN . " = " . $id;
				$results = $wpdb->query($query);
				
			}
			
			echo '<div class="updated"><p>Delete rating items bulk action processed successfully</p></div>';
		}
	}
}

