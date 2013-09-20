<?php

/**
 * Adds Multi rating form widget.
 */
class Multi_Rating_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'multi_rating_widget', 'description' => __('A widget to display a multi rating form'));
		$control_ops = array('width' => 400, 'height' => 350);

		parent::__construct('multi_rating_widget', __('Multi Rating Widget'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
		
		echo $before_widget;
		
		// multi-rating widget shortcode
		echo display_rating_form( array(null, $title, $before_title,  $after_title) );
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php
	}
}

/**
 * Adds Multi rating top results widget.
 */
class Multi_Rating_Top_Results_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'multi_rating_top_results_widget', 'description' => __('A widget to display multi rating top results'));
		$control_ops = array('width' => 400, 'height' => 350);

		parent::__construct('multi_rating_top_results_widget', __('Multi Rating Top Results Widget'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		echo display_rating_top_results( array(null, $title, $before_title,  $after_title) );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php
	}
}