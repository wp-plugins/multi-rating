<?php

/**
 * Top Rating Results Widget for Multi Rating plugin
 */
class Top_Rating_Results_Widget extends WP_Widget {
	
	function __construct() {
		
		$widget_ops = array( 'classname' => 'top-rating-results-widget', 'description' => __( 'Displays the Top Rating Results.', 'multi-rating' ) );
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( 'top_rating_results_widget', __('Top Rating Results Widget', 'multi-rating' ), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		
		extract($args);
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = empty( $instance['limit'] ) ? 10 : intval($instance['limit']);
		
		$category_id = 0;
		if (!empty( $instance['category_id'] ) && is_numeric($instance['category_id'])) {
			$category_id = intval($instance['category_id']);
		}
		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		
		echo $before_widget;
		
		Multi_Rating_API::display_top_rating_results( array(
				'limit' => $limit,
				'title' => $title,
				'show_category_filter' => $show_category_filter,
				'category_id' => $category_id,
				'class' => 'widget'
		) );
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['limit'] = intval($new_instance['limit']);
		$instance['category_id'] = 0;
		if (!empty($new_instance['category_id']) && is_numeric($new_instance['category_id'])) {
			$instance['category_id'] = intval($new_instance['category_id']);
		}
		$instance['show_category_filter'] = false;
		if (isset($new_instance['show_category_filter']) && ($new_instance['show_category_filter'] == "true")) {
			$instance['show_category_filter'] = true;
		}
		
		return $instance;
	}

	function form( $instance ) {
		
		$custom_text_settings = (array) get_option( Multi_Rating::CUSTOM_TEXT_SETTINGS );
		$instance = wp_parse_args( (array) $instance, array( 'title' => $custom_text_settings[Multi_Rating::TOP_RATING_RESULTS_TITLE_TEXT_OPTION], 'limit' => 10 ) );
		
		$title = strip_tags($instance['title']);
		$limit = intval($instance['limit']);
		$category_id = 0;
		if (!empty( $instance['category_id'] ) && is_numeric($instance['category_id'])) {
			$category_id = intval($instance['category_id']);
		}
		
		$show_category_filter = empty( $instance['show_category_filter'] ) ? false : $instance['show_category_filter'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'multi-rating' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit', 'multi-rating' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo esc_attr($limit); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('category_id'); ?>"><?php _e('Category', 'multi-rating' ); ?></label>
			<?php wp_dropdown_categories(array('true' => false, 'class' => 'widefat', 'name' => $this->get_field_name('category_id'), 'id' => $this->get_field_id('category_id'), 'selected' => $category_id, 'show_option_all' => 'All')); ?>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('show_category_filter'); ?>" name="<?php echo $this->get_field_name('show_category_filter'); ?>" type="checkbox" value="true" <?php checked(true, $show_category_filter, true); ?>/>
			<label for="<?php echo $this->get_field_id('show_category_filter'); ?>"><?php _e('Show category filter', 'multi-rating' ); ?></label>
		</p>
		<?php
	}
}


function mr_register_widgets() {
	register_widget( 'Top_Rating_Results_Widget' );
}
add_action( 'widgets_init', 'mr_register_widgets' );
?>