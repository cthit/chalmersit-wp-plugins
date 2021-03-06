<?php

require_once "functions.php";

class ITNewsMail_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'it_newsmail_widget', 
			'description' => __("Låter användaren styra automatiska mailutskick"));

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );

		parent::__construct('it_newsmail_widget', __("IT Newsmail widget"), $widget_ops);
	}

	function flush_widget_cache() {
		wp_cache_delete('it_newsmail_widget', 'widget');
	}

	public static function init(){
		// Register the widget
		add_action( 'widgets_init',     array( __CLASS__, '__register' ) );
	}

	public static function __register() {
		register_widget( __CLASS__ );
	}

	function widget( $args, $instance) {
		extract($args);

		$cache = wp_cache_get('it_newsmail_widget', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}
		


		$title = apply_filters('widget_title', $instance['title']);
		$desctext = $instance['descriptext'];
		if(is_user_logged_in() && (!$instance['catpage'] || is_category())) :

		global $current_user;
		get_currentuserinfo();

		$cats = get_choices_for_user($current_user->ID);

		// Get categories, find out if user is subscribing, set values in the form
		ob_start();

		echo $before_widget;

		if($title) {
			echo $before_title . $title . $after_title;
		}

		if($desctext){
			echo "<p>".$desctext."</p>";
		}

		?>
		<form method="post" id="newsmail-widget-form" name="newsmail" action="">
			<input type="hidden" name="action" value="it_newsmail" />
		<?php if(!is_category()) : ?>
			<label for="allNews-chkbx">
			<input type="checkbox" id="allNews-chkbx" name="allNews-chkbx" />Alla nyheter</label>
		<?php endif; ?>
			<div class="widget scroll">
				<?php 
				foreach ($cats as $key => $value) { 
					if(!is_category() || is_category($key)){?>
					<label for="itnm<?php echo $key; ?>">
					<input type="checkbox" class="itnm-cat-chkbx" id="itnm<?php echo $key; ?>" name="itnm<?php echo $key; ?>"
					<?php if($value['choice']) echo "checked"; ?>/> <?php echo $value['name']; ?></label>
				<?php 
					}
				} ?>
			</div>
			<input type="submit" value="Spara" />
		</form>
		<?php
		echo $after_widget;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('it_newsmail_widget', $cache, 'widget');
		endif;
	}

	function update($new_instance, $old_instance) {
		// Does not need modification short from changing widget ID under alloptions
		$new_instance = (array) $new_instance;
		$instance = array();
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		foreach($new_instance as $field => $val) {
			$instance[$field] = $val;
		}
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['it_newsmail_widget']) )
			delete_option('it_newsmail_widget');
		
		return $instance;
	}

	function form($instance) {
		$defaults = array(
			"title" => "Maila ut nyheter",
			"catpage" => false,
			"descriptext" => ""
			// Define default key-value pairs
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Get any external data needed for the widget through the form
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Titel"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'descriptext' ); ?>"><?php _e("Beskrivning"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'descriptext' ); ?>" name="<?php echo $this->get_field_name( 'descriptext' ); ?>" value="<?php echo $instance['descriptext']; ?>" class="widefat" />
		</p>
			<label for="<?php echo $this->get_field_id('catpage'); ?>"><?php _e("Visa endast för kategorisidor");?>
			<input type="checkbox" id="<?php echo $this->get_field_id('catpage'); ?>" name="<?php echo $this->get_field_name('catpage'); ?>" value="true" <?if($instance['catpage']) echo "checked"; ?> />
			</label>
		</p>

		<?php
	}
}

?>