<?php

/**
 * Top Rating Results widget for Multi Rating plugin
 */
class Top_Rating_Results_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'top-rating-results-widget', 'description' => __('Displays the top rating results from the Multi Rating plugin and includes the 5 star rating.'));
		$control_ops = array('width' => 400, 'height' => 350);

		parent::__construct('top_rating_results_widget', __('Top Rating Results Widget'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
		$count = empty( $instance['count'] ) ? 10 : intval($instance['count']);

		echo $before_widget;
		
		Multi_Rating_API::display_top_rating_results(array('count' => $count, 'title' => $title));
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = intval($new_instance['count']);
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		$count = intval($instance['count']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
		</p>
		<?php
	}
}

function mr_register_widgets() {
	register_widget( 'Top_Rating_Results_Widget' );
}
add_action( 'widgets_init', 'mr_register_widgets' );
?>