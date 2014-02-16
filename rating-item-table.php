<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Rating_Item_Table class
 * @author dpowney
 *
 */
class Rating_Item_Table extends WP_List_Table {

	const
	DESCRIPTION_COLUMN = 'description',
	MAX_OPTION_VALUE_COLUMN = 'max_option_value',
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	RATING_ID_COLUMN = 'rating_id',
	DEFAULT_OPTION_VALUE_COLUMN = 'default_option_value',
	WEIGHT_COLUMN = 'weight',
	SINGULAR_LABEL = 'Rating Item',
	PLURAL_LABEL = 'Rating Items',
	DESCRIPTION_LABEL = 'Description',
	RATING_ITEM_ID_LABEL = 'Rating Item Id',
	DEFAULT_OPTION_VALUE_LABEL = 'Default option value',
	WEIGHT_LABEL = 'Weight',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( array(
				'singular'=> Rating_Item_Table::SINGULAR_LABEL,
				'plural' => Rating_Item_Table::PLURAL_LABEL,
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
				Rating_Item_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				Rating_Item_Table::RATING_ITEM_ID_COLUMN =>__(Rating_Item_Table::RATING_ITEM_ID_LABEL),
				Rating_Item_Table::RATING_ID_COLUMN => __(''),
				Rating_Item_Table::DESCRIPTION_COLUMN =>__(Rating_Item_Table::DESCRIPTION_LABEL),
				Rating_Item_Table::WEIGHT_COLUMN	=>__(Rating_Item_Table::WEIGHT_LABEL),
				Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN => __(Rating_Item_Table::DEFAULT_OPTION_VALUE_LABEL),
				Rating_Item_Table::MAX_OPTION_VALUE_COLUMN => __('Max option value')
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
		$hidden = array( Rating_Item_Table::RATING_ID_COLUMN );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$query = "SELECT * FROM ".$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME;
		
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
			case Rating_Item_Table::CHECKBOX_COLUMN :
			case Rating_Item_Table::RATING_ITEM_ID_COLUMN :
			case Rating_Item_Table::RATING_ID_COLUMN :
				return $item[ $column_name ];
				break;
			case Rating_Item_Table::WEIGHT_COLUMN:
			case Rating_Item_Table::DESCRIPTION_COLUMN :
			case Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN:
			case Rating_Item_Table::MAX_OPTION_VALUE_COLUMN:
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
				'<input type="checkbox" name="'.Rating_Item_Table::DELETE_CHECKBOX.'" value="%s" />', $item[Rating_Item_Table::RATING_ITEM_ID_COLUMN]
		);
	}

	function column_actions($item, $column_name) {
		$row_id = $item[Rating_Item_Table::RATING_ITEM_ID_COLUMN];
		$row_value = stripslashes($item[$column_name]);
		$edit_btn_id = 'edit-'.$column_name.'-'.$row_id;
		$save_btn_id = 'save-'.$column_name.'-'.$row_id;
		$view_section_id = 'view-section-'. $column_name . '-'. $row_id;
		$edit_section_id = 'edit-section-'. $column_name . '-'. $row_id;
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
				$query = "DELETE FROM ". $wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME . " WHERE " .  Rating_Item_Table::RATING_ITEM_ID_COLUMN . " = " . $id;
				$results = $wpdb->query($query);
				
			}
			
			echo '<div class="updated"><p>Delete rating items bulk action processed successfully</p></div>';
		}
	}
	
	/**
	 * Saves column edit in rating item table
	 *
	 * @since 1.0
	 */
	public static function save_rating_item_table_column() {
		
		global $wpdb;
	
		$ajax_nonce = $_POST['nonce'];
		if (wp_verify_nonce($ajax_nonce, Multi_Rating::ID.'-nonce')) {
			$column = $_POST['column'];
				
			// prevent SQL injection
			if (! ( $column == Rating_Item_Table::DESCRIPTION_COLUMN || $column == Rating_Item_Table::MAX_OPTION_VALUE_COLUMN
					|| $column == Rating_Item_Table::DEFAULT_OPTION_VALUE_COLUMN || $column == Rating_Item_Table::WEIGHT_COLUMN ) ) {
				echo 'An error occured';
				die();
			}
				
			$value = isset($_POST['value']) ? addslashes($_POST['value']) : '';
			$rating_item_id = isset($_POST['ratingItemId']) ? $_POST['ratingItemId'] : '';
			$result = $wpdb->query('UPDATE '.$wpdb->prefix.Multi_Rating::RATING_ITEM_TBL_NAME.' SET '. $column . ' = "' . $value . '" WHERE ' . Rating_Item_Table::RATING_ITEM_ID_COLUMN .' = ' .$rating_item_id) ;
			if ($result === FALSE) {
				echo "An error occured";
			}
			echo $result;
		}
		die();
	}
}