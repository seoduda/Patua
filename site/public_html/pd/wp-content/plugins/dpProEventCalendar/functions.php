<?php
function dpProEventCalendar_admin_url( $query = array() ) {
	global $plugin_page;

	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );
}

function dpProEventCalendar_plugin_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder )
			$path = path_join( ltrim( $folder, '/' ), $path );

		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}

function dpProEventCalendar_parse_date( $date ) {
	
	$date = substr($date,0,10);
	if($date == "0000-00-00" || $date == "")
		return '';
	$date_arr = explode("-", $date);
	$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0];
	
	return $date ;
}

function dpProEventCalendar_parse_date_widget( $date, $date_format ) {
	if($date == "0000-00-00" || $date == "")
		return '';
		
	$date_arr = explode("-", substr($date, 0, 10));
	$time_arr = explode(":", substr($date, 11, 5));
	
	switch($date_format) {
		case 0: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;
		case 1: 
			$date = $date_arr[2]."/".$date_arr[1]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;
		case 2: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0];
			break;
		case 3: 
			$date = $date_arr[2]."/".$date_arr[1]."/".$date_arr[0];
			break;
		case 4: 
			$date = substr(dpProEventCalendar_translate_month($date_arr[1]), 0, 3)." ".$date_arr[2].", ".$date_arr[0];
			break;
		case 5: 
			$date = substr(dpProEventCalendar_translate_month($date_arr[1]), 0, 3)." ".$date_arr[2];
			break;
		default: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;	
	}
	
	return $date ;
}

function dpProEventCalendar_translate_month($month) {
	global $dpProEventCalendar;
	
	switch($month) {
		case "01":
			$month_name = $dpProEventCalendar['lang_january'];
			break;
		case "02":
			$month_name = $dpProEventCalendar['lang_february'];
			break;
		case "03":
			$month_name = $dpProEventCalendar['lang_march'];
			break;
		case "04":
			$month_name = $dpProEventCalendar['lang_april'];
			break;
		case "05":
			$month_name = $dpProEventCalendar['lang_may'];
			break;
		case "06":
			$month_name = $dpProEventCalendar['lang_june'];
			break;
		case "07":
			$month_name = $dpProEventCalendar['lang_july'];
			break;
		case "08":
			$month_name = $dpProEventCalendar['lang_august'];
			break;
		case "09":
			$month_name = $dpProEventCalendar['lang_september'];
			break;
		case "10":
			$month_name = $dpProEventCalendar['lang_october'];
			break;
		case "11":
			$month_name = $dpProEventCalendar['lang_november'];
			break;
		case "12":
			$month_name = $dpProEventCalendar['lang_december'];
			break;
		default:
			$month_name = $dpProEventCalendar['lang_january'];
			break;
	}
	
	return $month_name;
}

function dpProEventCalendar_reslash_multi(&$val,$key) 
{
   if (is_array($val)) array_walk($val,'dpProEventCalendar_reslash_multi',$new);
   else {
      $val = dpProEventCalendar_reslash($val);
   }
}


function dpProEventCalendar_reslash($string)
{
   if (!get_magic_quotes_gpc())$string = addslashes($string);
   return $string;
}

function dpProEventCalendar_CutString ($texto, $longitud = 180) { 
	$str_len = function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
	if($str_len > $longitud) { 
		$strpos = function_exists('mb_strpos') ? mb_strpos($texto, ' ', $longitud) : strpos($texto, ' ', $longitud);
		$pos_espacios = $strpos - 1; 
		if($pos_espacios > 0) { 
			$substr1 = function_exists('mb_substr') ? mb_substr($texto, 0, ($pos_espacios + 1)) : substr($texto, 0, ($pos_espacios + 1));
			$caracteres = count_chars($substr1, 1); 
			if ($caracteres[ord('<')] > $caracteres[ord('>')]) { 
				$strpos2 = function_exists('mb_strpos') ? mb_strpos($texto, ">", $pos_espacios) : strpos($texto, ">", $pos_espacios);
				$pos_espacios = $strpos2 - 1; 
			} 
			$substr2 = function_exists('mb_substr') ? mb_substr($texto, 0, ($pos_espacios + 1)) : substr($texto, 0, ($pos_espacios + 1));
			$texto = $substr2.'...'; 
		} 
		if(preg_match_all("|(<([\w]+)[^>]*>)|", $texto, $buffer)) { 
			if(!empty($buffer[1])) { 
				preg_match_all("|</([a-zA-Z]+)>|", $texto, $buffer2); 
				if(count($buffer[2]) != count($buffer2[1])) { 
					$cierrotags = array_diff($buffer[2], $buffer2[1]); 
					$cierrotags = array_reverse($cierrotags); 
					foreach($cierrotags as $tag) { 
							$texto .= '</'.$tag.'>'; 
					} 
				} 
			} 
		} 
 
	} 
	return $texto; 
}

add_action( 'wp_ajax_nopriv_getDate', 'dpProEventCalendar_getDate' );
add_action( 'wp_ajax_getDate', 'dpProEventCalendar_getDate' );
 
function dpProEventCalendar_getDate() {

    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!is_numeric($_POST['date'])) { die(); }
	
	$timestamp = $_POST['date'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	$is_admin = $_POST['is_admin'];
	if ($is_admin && strtolower($is_admin) !== "false") {
      $is_admin = true;
   } else {
      $is_admin = false;
   }
   
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( $is_admin, $calendar, $timestamp, null, '', $category, $event_id, $author );
	
	die($dpProEventCalendar->monthlyCalendarLayout());
}

add_action( 'wp_ajax_nopriv_getDaily', 'dpProEventCalendar_getDaily' );
add_action( 'wp_ajax_getDaily', 'dpProEventCalendar_getDaily' );
 
function dpProEventCalendar_getDaily() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!is_numeric($_POST['date'])) { die(); }
	
	$timestamp = $_POST['date'];
	$currDate = date("Y-m-d", $timestamp);
	
	$calendar = $_POST['calendar'];
	$is_admin = $_POST['is_admin'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	if ($is_admin && strtolower($is_admin) !== "false") {
      $is_admin = true;
   } else {
      $is_admin = false;
   }
   
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( $is_admin, $calendar, $timestamp, null, '', $category, $event_id, $author );
	
	echo "<!--".date_i18n(get_option('date_format'), $timestamp).">!]-->";
	
	die($dpProEventCalendar->dailyCalendarLayout());
}

add_action( 'wp_ajax_nopriv_getWeekly', 'dpProEventCalendar_getWeekly' );
add_action( 'wp_ajax_getWeekly', 'dpProEventCalendar_getWeekly' );
 
function dpProEventCalendar_getWeekly() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!is_numeric($_POST['date'])) { die(); }
	
	$timestamp = $_POST['date'];
	$currDate = date("Y-m-d", $timestamp);
	
	$calendar = $_POST['calendar'];
	$is_admin = $_POST['is_admin'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	if ($is_admin && strtolower($is_admin) !== "false") {
      $is_admin = true;
   } else {
      $is_admin = false;
   }
   
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( $is_admin, $calendar, $timestamp, null, '', $category, $event_id, $author );
	
	if($dpProEventCalendar->calendar_obj->first_day == 1) {
		$weekly_first_date = strtotime('last monday', ($timestamp + (24* 60 * 60)));
		$weekly_last_date = strtotime('next sunday', ($timestamp - (24* 60 * 60)));
	} else {
		$weekly_first_date = strtotime('last sunday', ($timestamp + (24* 60 * 60)));
		$weekly_last_date = strtotime('next saturday', ($timestamp - (24* 60 * 60)));
	}

	$weekly_txt = date_i18n('d F', $weekly_first_date).' - '.date_i18n('d F, Y', $weekly_last_date);
	
	if(date('m', $weekly_first_date) == date('m', $weekly_last_date)) {
	
		$weekly_txt = date('d', $weekly_first_date) . ' - ' . date('d F, Y', $weekly_last_date);
		
	}
	
	if(date('Y', $weekly_first_date) != date('Y', $weekly_last_date)) {
			
		$weekly_txt = date_i18n(get_option('date_format'), $weekly_first_date).' - '.date_i18n(get_option('date_format'), $weekly_last_date);
		
	}

	echo "<!--".$weekly_txt.">!]-->";
	
	die($dpProEventCalendar->weeklyCalendarLayout());
}

add_action( 'wp_ajax_nopriv_getEvents', 'dpProEventCalendar_getEvents' );
add_action( 'wp_ajax_getEvents', 'dpProEventCalendar_getEvents' );
 
function dpProEventCalendar_getEvents() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['date'])) { die(); }
	
	$date = $_POST['date'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	die($dpProEventCalendar->eventsListLayout( $date ));
}

add_action( 'wp_ajax_nopriv_getEvent', 'dpProEventCalendar_getEvent' );
add_action( 'wp_ajax_getEvent', 'dpProEventCalendar_getEvent' );
 
function dpProEventCalendar_getEvent() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['event'])) { die(); }
	
	$event = $_POST['event'];
	$calendar = $_POST['calendar'];
	$date = $_POST['date'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar );
	
	echo '
		<div class="dp_pec_date_event_head dp_pec_date_event_daily dp_pec_isotope">
			<i class="fa fa-calendar"></i>
			<span>'.$dpProEventCalendar->parseMysqlDate($date).'</span><a href="" class="dp_pec_date_event_back"><i class="fa fa-angle-double-left"></i></a>
		</div>';
		
	$result = $dpProEventCalendar->getEventByID($event);
	echo $dpProEventCalendar->singleEventLayout($result, false);
	
	die();
}

add_action( 'wp_ajax_nopriv_getEventsMonth', 'dpProEventCalendar_getEventsMonth' );
add_action( 'wp_ajax_getEventsMonth', 'dpProEventCalendar_getEventsMonth' );
 
function dpProEventCalendar_getEventsMonth() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['month'])) { die(); }
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	$next_month_days = cal_days_in_month(CAL_GREGORIAN, str_pad(($month), 2, "0", STR_PAD_LEFT), $year);
	$month_number = str_pad($month, 2, "0", STR_PAD_LEFT);
	
	echo $dpProEventCalendar->upcomingCalendarLayout( false, 20, '', $year."-".$month_number."-01 00:00:00", $year."-".$month_number."-".$next_month_days." 23:59:59", true );
	die();
}

add_action( 'wp_ajax_nopriv_getEventsMonthList', 'dpProEventCalendar_getEventsMonthList' );
add_action( 'wp_ajax_getEventsMonthList', 'dpProEventCalendar_getEventsMonthList' );
 
function dpProEventCalendar_getEventsMonthList() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!is_numeric($_POST['month'])) { die(); }
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	$next_month_days = cal_days_in_month(CAL_GREGORIAN, str_pad(($month), 2, "0", STR_PAD_LEFT), $year);
	$month_number = str_pad($month, 2, "0", STR_PAD_LEFT);
	
	echo $dpProEventCalendar->eventsMonthList( $year."-".$month_number."-01 00:00:00", $year."-".$month_number."-".$next_month_days." 23:59:59" );
	
	die();
}

add_action( 'wp_ajax_nopriv_submitEvent', 'dpProEventCalendar_submitEvent' );
add_action( 'wp_ajax_submitEvent', 'dpProEventCalendar_submitEvent' );
 
function dpProEventCalendar_submitEvent() {
	
    $nonce = $_POST['postEventsNonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
       die ( 'Error!');
		
	if(!is_numeric($_POST['calendar'])) { die(); }
	
	global $current_user, $dpProEventCalendar_cache, $dpProEventCalendar;
	get_currentuserinfo();
	
	$calendar = $_POST['calendar'];

	require_once('classes/base.class.php');

	$dpProEventCalendar_class = new DpProEventCalendar( false, $calendar );
	$dpProEventCalendar_class->getCalendarData();
	
	if(!is_user_logged_in() && !$dpProEventCalendar_class->calendar_obj->assign_events_admin) { die(); }
	
	$category = array();
	foreach ($_POST as $key => $value) {
		if (strpos($key, 'category-') === 0) {
			$category[] = $value;
		}
	}

	$new_event = array(
	  'post_title'    => $_POST['title'],
	  'post_content'  => $_POST['description'],
	  'post_category'  => $category,
	  'post_status'   => ($dpProEventCalendar_class->calendar_obj->publish_new_event ? 'publish' : 'pending'),
	  'post_type'	  => 'pec-events'
	);
	
	if(!is_user_logged_in() && $dpProEventCalendar_class->calendar_obj->assign_events_admin > 0) {
		$new_event['post_author'] = $dpProEventCalendar_class->calendar_obj->assign_events_admin;
	}
	
	if(is_numeric($_POST['edit_calendar']) && is_numeric($_POST['edit_event'])) {
		$inserted = $_POST['edit_event'];
		
		$event_edit = get_post($inserted);
		if($event_edit->post_author == $current_user->ID) {
			$new_event['ID'] = $inserted;
			$new_event['post_status'] = $event_edit->post_status;
			
			wp_update_post($new_event);
		} else {
			die();	
		}
		
	} else {
		$inserted = wp_insert_post($new_event);
	}
	
	if(!is_numeric($inserted)) { die(); }
	
	wp_set_post_terms($inserted, $category, 'pec_events_category');
	
	if(is_array($dpProEventCalendar['custom_fields_counter'])) {
		$counter = 0;
		
		foreach($dpProEventCalendar['custom_fields_counter'] as $key) {
			update_post_meta($inserted, "pec_custom_".$dpProEventCalendar['custom_fields']['id'][$counter], $_POST['pec_custom_'.$dpProEventCalendar['custom_fields']['id'][$counter]]);
			$counter++;		
		}
	}
	update_post_meta($inserted, "pec_link", $_POST['link']);
	update_post_meta($inserted, "pec_share", $_POST['share']);
	update_post_meta($inserted, "pec_location", $_POST['location']);
	update_post_meta($inserted, "pec_phone", $_POST['phone']);
	update_post_meta($inserted, "pec_map", $_POST['googlemap']);
	update_post_meta($inserted, 'pec_id_calendar', $_POST['calendar']);
	update_post_meta($inserted, 'pec_date', $_POST['date'].' '.$_POST['time_hours'].':'.$_POST['time_minutes'].':00');
	update_post_meta($inserted, 'pec_all_day', $_POST['all_day']);
	update_post_meta($inserted, 'pec_recurring_frecuency', $_POST['recurring_frecuency']);
	update_post_meta($inserted, 'pec_end_date', $_POST['end_date']);
	update_post_meta($inserted, 'pec_end_time_hh', $_POST['end_time_hh']);
	update_post_meta($inserted, 'pec_end_time_mm', $_POST['end_time_mm']);
	update_post_meta($inserted, 'pec_hide_time', $_POST['hide_time']);
	
	update_post_meta( $inserted, 'pec_daily_every', $_POST['pec_daily_every'] );
	update_post_meta( $inserted, 'pec_daily_working_days', $_POST['pec_daily_working_days'] );
	update_post_meta( $inserted, 'pec_weekly_day', $_POST['pec_weekly_day'] );
	update_post_meta( $inserted, 'pec_weekly_every', $_POST['pec_weekly_every'] );
	update_post_meta( $inserted, 'pec_monthly_every', $_POST['pec_monthly_every'] );
	update_post_meta( $inserted, 'pec_monthly_position', $_POST['pec_monthly_position'] );
	update_post_meta( $inserted, 'pec_monthly_day', $_POST['pec_monthly_day'] );
	update_post_meta( $inserted, 'pec_enable_booking', $_POST['booking_enable'] );
	update_post_meta( $inserted, 'pec_booking_limit', $_POST['limit'] );
	update_post_meta( $inserted, 'pec_booking_price', $_POST['price'] );
		
	$image = $_FILES['event_image'];
	$timestamp = time();
	
	$wp_filetype = wp_check_filetype(basename($image['name']), null );
	if(strtolower($wp_filetype['ext']) == "jpeg" || strtolower($wp_filetype['ext']) == "png" || strtolower($wp_filetype['ext']) == "gif" || strtolower($wp_filetype['ext']) == "jpg") {
		$uploads = wp_upload_dir();
		
		if (!copy($image['tmp_name'], $uploads['path']."/".$current_user->ID."_".$timestamp."_".$image['name'])) {
			//echo "Error copying file...<br>";
		} else {
		
			$attachment = array(
			 'guid' => $uploads['path'] . '/'.$current_user->ID."_".$timestamp."_" . basename( $image['name'] ), 
			 'post_mime_type' => $wp_filetype['type'],
			 'post_title' => '',
			 'post_content' => '',
			 'post_excerpt' => '',
			 'post_status' => 'inherit'
			);
			
			$attach_id = wp_insert_attachment( $attachment, $uploads['path'] . '/'.$current_user->ID."_".$timestamp."_" . basename( $image['name'] ) );
			
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $uploads['path'] . '/'.$current_user->ID."_".$timestamp."_" . basename( $image['name'] ) );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			update_post_meta($inserted, "_thumbnail_id", $attach_id);
		}
	}

	if($dpProEventCalendar_class->calendar_obj->email_admin_new_event && !is_numeric($_POST['edit_calendar'])) {
		add_filter( 'wp_mail_from_name', 'dpProEventCalendar_wp_mail_from_name' );
		add_filter( 'wp_mail_from', 'dpProEventCalendar_wp_mail_from' );
		
		$message = "A new event is waiting for approval: ".$_POST['title']." ( ".get_edit_post_link($inserted, '')." )";
		
		$success_email = wp_mail( get_bloginfo('admin_email'), 'New Event', $message );
		
	}

	if(isset($dpProEventCalendar_cache['calendar_id_'.$calendar])) {
	   $dpProEventCalendar_cache['calendar_id_'.$calendar] = array();
	   update_option( 'dpProEventCalendar_cache', $dpProEventCalendar_cache );
   }
	
	die();
}

add_action( 'wp_ajax_nopriv_removeEvent', 'dpProEventCalendar_removeEvent' );
add_action( 'wp_ajax_removeEvent', 'dpProEventCalendar_removeEvent' );
 
function dpProEventCalendar_removeEvent() {
	
    $nonce = $_POST['postEventsNonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
       die ( 'Error!');
		
	if(!is_numeric($_POST['calendar'])) { die(); }
	
	global $current_user;
	get_currentuserinfo();
	
	$calendar = $_POST['calendar'];

	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar );
	$dpProEventCalendar->getCalendarData();
	
	if(!is_user_logged_in()) { die(); }
	
	if(is_numeric($_POST['remove_event_calendar']) && is_numeric($_POST['remove_event'])) {
		$inserted = $_POST['remove_event'];
		
		$event_edit = get_post($inserted);
		if($event_edit->post_author == $current_user->ID && $event_edit->post_type == 'pec-events') {
						
			wp_delete_post($inserted);
		}
		
	}
	die();
}

function dpProEventCalendar_wp_mail_from_name( $original_email_from )
{
	return get_bloginfo('name');
}

function dpProEventCalendar_wp_mail_from( $original_email_address ) {
	global $dpProEventCalendar;
	//Make sure the email is from the same domain 
	//as your website to avoid being marked as spam.
	return ($dpProEventCalendar['wp_mail_from'] != "" ? $dpProEventCalendar['wp_mail_from'] : $original_email_address);
}

add_action( 'wp_ajax_nopriv_getSearchResults', 'dpProEventCalendar_getSearchResults' );
add_action( 'wp_ajax_getSearchResults', 'dpProEventCalendar_getSearchResults' );
 
function dpProEventCalendar_getSearchResults() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['key'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$key = $_POST['key'];
	$type = $_POST['type'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', '', '', $author );
	
	die($dpProEventCalendar->getSearchResults( $key, $type ));
}

add_action( 'wp_ajax_nopriv_bookEvent', 'dpProEventCalendar_bookEvent' );
add_action( 'wp_ajax_bookEvent', 'dpProEventCalendar_bookEvent' );
 
function dpProEventCalendar_bookEvent() {
	global $current_user, $wpdb, $dpProEventCalendar, $table_prefix;
	
	$table_name_booking = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_BOOKING;
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !is_numeric($_POST['event_id']) || !isset($_POST['event_date'])) { die(); }

	$calendar = $_POST['calendar'];
	$comment = $_POST['comment'];
	$quantity = $_POST['quantity'];
	$id_event = $_POST['event_id'];
	$name = $_POST['name'];
	$email = $_POST['email'];
	$event_date = $_POST['event_date'];
	$return_url = $_POST['return_url'];
	
	if(!is_numeric($quantity) || $quantity < 1) {
		$quantity = 1;	
	}
	
	require_once('classes/base.class.php');

	$dpProEventCalendar_class = new DpProEventCalendar( false, $calendar );
	
	if(!is_user_logged_in() && !$dpProEventCalendar_class->calendar_obj->booking_non_logged) {
		die();	
	}
	
	/*
	
	$querystr = "
		SELECT id as id_booking
		FROM ".$table_name_booking."
		WHERE id_event = ".$id_event." AND id_user = ".$current_user->ID." AND event_date = '".$event_date."'
		LIMIT 1
		";
	$bookings_obj = $wpdb->get_results($querystr, OBJECT);
	
	$id_booking = "";
	
	if(!empty($bookings_obj) && is_numeric($bookings_obj[0]->id_booking)) {
		
		$id_booking = $bookings_obj[0]->id_booking;
		
		//$wpdb->delete( $table_name_booking, array( 'id' => $id_booking ) );
		
	} else {
		*/
		$wpdb->insert( 
			$table_name_booking, 
			array( 
				'id_calendar' 	=> $calendar, 
				'booking_date' 	=> date('Y-m-d H:i:s'),
				'event_date'	=> $event_date,
				'id_event'		=> $id_event,
				'id_user'		=> $current_user->ID,
				'comment'		=> $comment,
				'quantity'		=> $quantity,
				'name'			=> $name,
				'email'			=> $email,
				'status'		=> (get_post_meta($id_event, 'pec_booking_price', true) != '' ? 'pending' : '')
			), 
			array( 
				'%d', 
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s'
			) 
		);
		
		$id_booking = $wpdb->insert_id;
		
	//}
	
	if(!get_post_meta($id_event, 'pec_booking_price', true)) {
	
		// Send emails for free bookings
		
		if(is_user_logged_in()) {
			
			$userdata = get_userdata($current_user->ID);
			
		} else {
			
			$userdata = new stdClass();
			$userdata->display_name = $name;
			$userdata->user_email = $email;
				
		}
		
		if($dpProEventCalendar_class->calendar_obj->booking_email_template_user == '') {
			$dpProEventCalendar_class->calendar_obj->booking_email_template_user = "Hi #USERNAME#,\n\nThanks for booking the event:\n\n#EVENT_DETAILS#\n\nPlease contact us if you have questions.\n\nKind Regards.\n#SITE_NAME#";
		}
		
		if($dpProEventCalendar_class->calendar_obj->booking_email_template_admin == '') {
			$dpProEventCalendar_class->calendar_obj->booking_email_template_admin = "The user #USERNAME# (#USEREMAIL#) booked the event:\n\n#EVENT_DETAILS#\n\n#COMMENT#\n\n#SITE_NAME#";
		}
		
		add_filter( 'wp_mail_from_name', 'dpProEventCalendar_wp_mail_from_name' );
		add_filter( 'wp_mail_from', 'dpProEventCalendar_wp_mail_from' );
		
		// Email to User
						
		wp_mail( $userdata->user_email, get_bloginfo('name'), apply_filters('pec_booking_email', $dpProEventCalendar_class->calendar_obj->booking_email_template_user, $id_event, $userdata->display_name, $userdata->user_email, $event_date, $comment, $quantity) );
		
		// Email to Admin

		wp_mail( get_bloginfo('admin_email'), get_bloginfo('name'), apply_filters('pec_booking_email', $dpProEventCalendar_class->calendar_obj->booking_email_template_admin, $id_event, $userdata->display_name, $userdata->user_email, $event_date, $comment, $quantity) );
	}
	
	$return = array(
		array(
			"book_btn" => $dpProEventCalendar_class->translation['TXT_BOOK_EVENT_REMOVE'], 
			"notification" => '<p>'.$dpProEventCalendar_class->translation['TXT_BOOK_EVENT_SAVED'].'</p>',
			"gateway_screen" => apply_filters('pec_receipt_gateways', '', $id_event, $event_date, $id_booking, $return_url, $quantity)
		),
		array(
			"book_btn" => $dpProEventCalendar_class->translation['TXT_BOOK_EVENT'], 
			"notification" => '<p>'.$dpProEventCalendar_class->translation['TXT_BOOK_EVENT_REMOVED'].'</p>'
		)
	);
	
	//die(!$id_booking ? json_encode($return[0]) : json_encode($return[1]));
	die(json_encode($return[0]));
}

add_filter('pec_booking_email', 'dpProEventCalendar_bookingEmail', 10, 7);

function dpProEventCalendar_bookingEmail($template, $event_id, $user_name, $user_email, $event_date, $comment, $quantity) {
	
	$template = str_replace("#USERNAME#", $user_name, $template);
	
	$template = str_replace("#COMMENT#", $comment, $template);
	
	$template = str_replace("#USEREMAIL#", $user_email, $template);
	
	$template = str_replace("#EVENT_DETAILS#", "---------------------------\n\r".get_the_title($event_id)."\n\r".date_i18n(get_option('date_format'), strtotime($event_date)).' - '.date(get_option('time_format'), strtotime(get_post_meta($event_id, 'pec_date', true)))."\n\r".__("Quantity", "dpProEventCalendar").": ".$quantity."\n\r".get_post_meta($event_id, 'pec_location', true)."\n\r---------------------------\n\r", $template);
	
	$template = str_replace("#SITE_NAME#", get_bloginfo('name'), $template);
	
	return html_entity_decode ($template);
	
}

add_filter('pec_new_event_published', 'dpProEventCalendar_eventPublished', 10, 6);

function dpProEventCalendar_eventPublished($template, $event_title, $user_name) {
	
	$template = str_replace("#USERNAME#", $user_name, $template);
	
	$template = str_replace("#EVENT_TITLE#", $event_title, $template);
		
	$template = str_replace("#SITE_NAME#", get_bloginfo('name'), $template);
	
	return html_entity_decode ($template);
	
}

//add_action( 'wp_ajax_nopriv_removeBooking', 'dpProEventCalendar_removeBooking' );
add_action( 'wp_ajax_removeBooking', 'dpProEventCalendar_removeBooking' );
 
function dpProEventCalendar_removeBooking() {
	global $current_user, $wpdb, $dpProEventCalendar, $table_prefix;
	
	$table_name_booking = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_BOOKING;
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
	
	if(!is_user_logged_in()) {
		die();	
	}
	
	if(!is_numeric($_POST['booking_id'])) { die(); }
	
	$booking_id = $_POST['booking_id'];

	$wpdb->delete( $table_name_booking, array( 'id' => $booking_id ) );
		
}

add_action( 'wp_ajax_nopriv_getCategoryResults', 'dpProEventCalendar_getCategoryResults' );
add_action( 'wp_ajax_getCategoryResults', 'dpProEventCalendar_getCategoryResults' );
 
function dpProEventCalendar_getCategoryResults() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['key'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$key = $_POST['key'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar );
	
	die($dpProEventCalendar->getCategoryResults( $key ));
}

add_action( 'wp_ajax_setSpecialDates', 'dpProEventCalendar_setSpecialDates' );
 
function dpProEventCalendar_setSpecialDates() {

    $nonce = $_POST['postEventsNonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
        die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['date'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$sp = $_POST['sp'];
	$date = $_POST['date'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( true, $calendar );
	
	$dpProEventCalendar->setSpecialDates( $sp, $date );
	
	die();
}

function dpProEventCalendar_updateNotice(){
    echo '<div class="updated">
       <p>Updated Succesfully.</p>
    </div>';
}

if(@$_GET['settings-updated'] && 
($_GET['page'] == 'dpProEventCalendar-admin' 
|| $_GET['page'] == 'dpProEventCalendar-events' 
|| $_GET['page'] == 'dpProEventCalendar-special'
|| $_GET['page'] == 'dpProEventCalendar-payments')) {
	add_action('admin_notices', 'dpProEventCalendar_updateNotice');
}

function dpProEventCalendar_pro_event_calendar_init() {
  global $dpProEventCalendar;
  
  $labels = array(
    'name' => __('Pro Event Calendar', 'dpProEventCalendar'),
    'singular_name' => __('Events', 'dpProEventCalendar'),
    'add_new' => __('Add New', 'dpProEventCalendar'),
    'add_new_item' => __('Add New Event', 'dpProEventCalendar'),
    'edit_item' => __('Edit Event', 'dpProEventCalendar'),
    'new_item' => __('New Event', 'dpProEventCalendar'),
    'all_items' => __('All Events', 'dpProEventCalendar'),
    'view_item' => __('View Event', 'dpProEventCalendar'),
    'search_items' => __('Search Events', 'dpProEventCalendar'),
    'not_found' =>  __('No Events Found', 'dpProEventCalendar'),
    'not_found_in_trash' => __('No Events Found in Trash', 'dpProEventCalendar'), 
    'parent_item_colon' => '',
    'menu_name' => __('Events', 'dpProEventCalendar')
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
	'exclude_from_search' => false,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => ( $dpProEventCalendar['events_slug'] != "" ? $dpProEventCalendar['events_slug'] : 'pec-events') ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
	'show_in_menu' => 'dpProEventCalendar-admin',
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'taxonomies' => array('pec_events_category', 'post_tag')
  ); 

  register_post_type( 'pec-events', $args );
  //flush_rewrite_rules();
  
}
add_action( 'init', 'dpProEventCalendar_pro_event_calendar_init' );

add_action( 'init', 'dpProEventCalendar_pro_event_calendar_taxonomies', 0 );

add_action( 'admin_init', 'flush_rewrite_rules' );

function dpProEventCalendar_pro_event_calendar_taxonomies() 
{
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name'                => _x( 'Categories', 'taxonomy general name' ),
    'singular_name'       => _x( 'Category', 'taxonomy singular name' ),
    'search_items'        => __( 'Search Categories' ),
    'all_items'           => __( 'All Categories' ),
    'parent_item'         => __( 'Parent Category' ),
    'parent_item_colon'   => __( 'Parent Category:' ),
    'edit_item'           => __( 'Edit Category' ), 
    'update_item'         => __( 'Update Category' ),
    'add_new_item'        => __( 'Add New Category' ),
    'new_item_name'       => __( 'New Category Name' ),
    'menu_name'           => __( 'Category' )
  ); 	

  $args = array(
    'hierarchical'        => true,
    'labels'              => $labels,
    'show_ui'             => true,
    'show_admin_column'   => true,
    'query_var'           => true,
    'rewrite'             => array( 'slug' => 'pec_events_category' )
  );

  register_taxonomy( 'pec_events_category', array( 'pec-events' ), $args );
}


add_action('admin_footer-edit.php', 'pec_custom_bulk_admin_footer');

function pec_custom_bulk_admin_footer() {

	global $post_type;
	if($post_type == 'pec-events') {
	  echo '
	  <script type="text/javascript">
	  	jQuery(document).ready(function() {
			jQuery("select[name=\'action\']").append("<option value=\'duplicate\'>Duplicate</option>");

		});
	  </script>';
	}
  
}

add_action('load-edit.php', 'pec_custom_bulk_action');

function pec_custom_bulk_action() {

	// 1. get the action
	$wp_list_table = _get_list_table('WP_Posts_List_Table');
	
	$action = $wp_list_table->current_action();
	
	// 2. security check
	if ($action != "" && check_admin_referer('bulk-posts')) {
		check_admin_referer('bulk-posts');
		
		$post_ids = $_GET['post'];
		
		switch($action) {
			
			// 3. Perform the action
			
			case 'duplicate':
							
				$duplicated = 0;
				
				foreach( $post_ids as $post_id ) {
					$my_post = get_post($post_id, "ARRAY_A" );
					unset($my_post['ID']);
					$my_post['post_category'] = array();
					$my_post['post_date'] = date('Y-m-d H:i:s');
					$category = get_the_terms( $post_id, 'pec_events_category' ); 
					if(!empty($category)) {
						foreach ( $category as $cat){
							$my_post['post_category'][] =  $cat->term_id;
						}
					}

					if ( !$inserted = wp_insert_post( $my_post, false ) )
					
					wp_die( __('Error duplicating post.') );
					
					$meta_values = get_post_meta($post_id);
					
					foreach($meta_values as $key => $value) {
						foreach($value as $val) {
							add_post_meta($inserted, $key, $val);
						}
					}
					wp_set_post_terms( $inserted, $my_post['post_category'], 'pec_events_category' );
					$duplicated++;
				
				}
				
				// build the redirect url
				
				$sendback = esc_url_raw(add_query_arg( array( 'post_type' => 'pec-events', 'duplicated' => $duplicated, 'ids' => join(',', $post_ids) ), $sendback ));
				
			break;
			
			default: return;
			
		}
		
		// 4. Redirect client
		
		wp_redirect($sendback);
		
		exit();
	}

}

add_action('admin_notices', 'pec_custom_bulk_admin_notices');

function pec_custom_bulk_admin_notices() {

	global $post_type, $pagenow;
	
	if($pagenow == 'edit.php' && $post_type == 'pec-events' &&
	
		isset($_REQUEST['duplicated']) && (int) $_REQUEST['duplicated']) {
		
		$message = sprintf( _n( 'Post duplicated.', '%s posts duplicated.', $_REQUEST['duplicated'] ), number_format_i18n( $_REQUEST['duplicated'] ) );
		
		echo "
		<div class='updated'><p>{$message}</p></div>
		";
	
	}

}

function pec_truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}

add_action( 'wp_ajax_nopriv_ProEventCalendar_NewSubscriber', 'dpProEventCalendar_ProEventCalendar_NewSubscriber' );
add_action( 'wp_ajax_ProEventCalendar_NewSubscriber', 'dpProEventCalendar_ProEventCalendar_NewSubscriber' );

function dpProEventCalendar_ProEventCalendar_NewSubscriber() {
	global $dpProEventCalendar;
	
	$your_name = stripslashes($_POST['your_name']);
	$your_email = stripslashes($_POST['your_email']);
	$calendar = stripslashes($_POST['calendar']);
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( true, $calendar );
	
	$dpProEventCalendar->calendarSubscription($your_email, $your_name);
	
	die();	
}

add_action( 'wp_ajax_nopriv_ProEventCalendar_RateEvent', 'dpProEventCalendar_ProEventCalendar_RateEvent' );
add_action( 'wp_ajax_ProEventCalendar_RateEvent', 'dpProEventCalendar_ProEventCalendar_RateEvent' );

function dpProEventCalendar_ProEventCalendar_RateEvent() {
	global $dpProEventCalendar;
	
	if(!is_user_logged_in()) {
		die();	
	}
	
	$event_id = stripslashes($_POST['event_id']);
	$rate = stripslashes($_POST['rate']);
	$calendar = stripslashes($_POST['calendar']);
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( true, $calendar );
	
	echo $dpProEventCalendar->rateEvent($event_id, $rate);
	
	die();	
}

add_action('edit_post', 'dpProEventCalendar_editEvent');
add_action('publish_post', 'dpProEventCalendar_editEvent');
add_action('wp_trash_post', 'dpProEventCalendar_editEvent');
add_action('untrash_post', 'dpProEventCalendar_editEvent');
add_action('delete_post', 'dpProEventCalendar_editEvent');

function dpProEventCalendar_editEvent($post_ID) {
	global $current_user, $wpdb, $dpProEventCalendar_cache;
	
	$post_type = get_post_type( $_GET['post'] );

	if('pec-events' != $_POST['post_type'] && 'pec-events' != $post_type) return;

	$calendar_id = explode(",", get_post_meta($post_ID, 'pec_id_calendar', true)); 
	
	$calendar_id = $calendar_id[0];	
	
	if($_POST['hidden_post_status'] == 'pending' && $_POST['post_status'] == 'publish') {
		// Send email to event author
		
		require_once('classes/base.class.php');
		
		$dpProEventCalendar_class = new DpProEventCalendar( false, $calendar_id );
		
		$userdata = get_userdata($_POST['post_author']);
		
		if($dpProEventCalendar_class->calendar_obj->new_event_email_template_published == '') {
			@$dpProEventCalendar_class->calendar_obj->new_event_email_template_published = "Hi #USERNAME#,\n\nThe event #EVENT_TITLE# has been approved.\n\nPlease contact us if you have questions.\n\nKind Regards.\n#SITE_NAME#";
		}
		
		add_filter( 'wp_mail_from_name', 'dpProEventCalendar_wp_mail_from_name' );
		add_filter( 'wp_mail_from', 'dpProEventCalendar_wp_mail_from' );
		
		// Email to User
						
		wp_mail( $userdata->user_email, get_bloginfo('name'), apply_filters('pec_new_event_published', $dpProEventCalendar_class->calendar_obj->new_event_email_template_published, get_the_title($post_ID), $userdata->display_name) );
				
	}
	
	if(isset($dpProEventCalendar_cache['calendar_id_'.$calendar_id])) {
	   $dpProEventCalendar_cache['calendar_id_'.$calendar_id] = array();
	   update_option( 'dpProEventCalendar_cache', $dpProEventCalendar_cache );
   }
}

function dpProEventCalendar_contentFilter($content) {
	global $dpProEventCalendar;
	
	// assuming you have created a page/post entitled 'debug'
	if ($GLOBALS['post']->post_type == 'pec-events') {
		
		$calendar_id = explode(",", get_post_meta($GLOBALS['post']->ID, 'pec_id_calendar', true)); 
		
		$calendar_id = $calendar_id[0];	
		
		$content = '[dpProEventCalendar id="'.$calendar_id.'" get="date"]'.
					'[dpProEventCalendar get="location"]'.
					'[dpProEventCalendar get="phone"]'.
					'[dpProEventCalendar get="link"]'.
					'[dpProEventCalendar get="facebook_url"]'.
					'<div class="dp_pec_clear"></div>'.
					'[dpProEventCalendar get="custom_fields"]'.
					'<div class="dp_pec_clear"></div>'.
					$content.
					'[dpProEventCalendar get="map"]';
					
		if($dpProEventCalendar['custom_css'] != "") {
			$content .= '<style type="text/css">'.$dpProEventCalendar['custom_css'].'</style>';
		}
	}
	// otherwise returns the database content
	return $content;
}

add_filter( 'the_content', 'dpProEventCalendar_contentFilter' );

function dpProEventCalendar_loadTemplate( $template ) {
	global $dpProEventCalendar;
	
	// assuming you have created a page/post entitled 'debug'
	if ($GLOBALS['post']->post_type == 'pec-events' && $dpProEventCalendar['event_single_enable']) {
		
		remove_filter( 'the_content', 'dpProEventCalendar_contentFilter' );
		remove_all_actions('wp_enqueue_scripts');
		return dirname( __FILE__ ) . '/templates/default/template.php';
		
	}
	
	return $template;
	
}

add_filter( 'template_include', 'dpProEventCalendar_loadTemplate', 100 );

if(!function_exists('cal_days_in_month')) {
	
	function cal_days_in_month($month, $year) { 
		return date('t', mktime(0, 0, 0, $month+1, 0, $year)); 
	}
	
}

function dpProEventCalendar_str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

function dpProEventCalendar_setup_ical_sync() {
	global $wpdb, $table_prefix;
	$table_name_calendar = $table_prefix.DP_PRO_EVENT_CALENDAR_TABLE_CALENDARS;

	$querystr = "
		SELECT id as calendar_id, sync_ical_url, sync_ical_frequency, sync_ical_enable
		FROM ".$table_name_calendar."
		";
	$calendars_obj = $wpdb->get_results($querystr, OBJECT);
	foreach($calendars_obj as $key) {
		
		if($key->sync_ical_enable && $key->sync_ical_url != "") {

			//Schedule

			if ( ! wp_next_scheduled( 'pecsyncical'.$key->calendar_id, array($key->calendar_id, $key->sync_ical_url) ) ) {
				$scheduled = wp_schedule_event( time(), $key->sync_ical_frequency, 'pecsyncical'.$key->calendar_id, array($key->calendar_id, $key->sync_ical_url));
				//die($scheduled.'<br>'.$key->calendar_id);
			}
			
			add_action( 'pecsyncical'.$key->calendar_id, 'dpProEventCalendar_ical_sync', 10 ,2 );
		
		} else {

			// Unschedule
			
			// Get the timestamp for the next event.
		
			wp_clear_scheduled_hook( 'pecsyncical'.$key->calendar_id, array($key->calendar_id, $key->sync_ical_url) );

		}
	}
}
add_action( 'init', 'dpProEventCalendar_setup_ical_sync', 10 );

/**
 * On the scheduled action hook, run a function.
 */
function dpProEventCalendar_ical_sync($calendar_id, $filename) {
	
	$filename_arr = explode(",", $filename);
	
	foreach($filename_arr as $url) {
		
		dpProEventCalendar_importICS($calendar_id, $url);
		
	}

}

function dpProEventCalendar_importICS($calendar_id, $filename, $tmp_filename = '', $category_ics = '') {
	global $dpProEventCalendar_cache;
	if($tmp_filename == "") {
		$tmp_filename = $filename;	
	}
	$extension = strrchr($tmp_filename, '.'); 
	$extensions = array('.ics');
	//if(in_array($extension, $extensions)) {
		include(dirname(__FILE__) . '/includes/ical_parser.php');
		$ical = new ICal($filename);
		$feed = $ical->cal;
		if(!empty($feed)) {
			
			//echo '<pre>';
			//print_r($feed['VCALENDAR']['X-WR-CALNAME']);
			//echo '</pre>';
			//die();
			
			set_time_limit(0);
			
			$all_uid = array();
			$count = 0;
			if(is_array($feed['VEVENT'])) {
				foreach($feed['VEVENT'] as $key) {
					$count++;
					
					$key['DESCRIPTION'] = ltrim($key['DESCRIPTION'], '<br>');
					
					foreach($key as $k => $v) {
						$key[substr($k, 0, strpos($k, ';'))] = $v;	
					}
					
					if($key['UID'] == "") {
						$key['UID'] = $key['DTSTART'].$key['SUMMARY'].'@pec-no-uid';
					}
					
					// XX 
					$args = array( 
						'posts_per_page' => 1, 
						'post_type'=> 'pec-events', 
						"meta_query" => array(
							'relation' => 'AND',
							array(
							   'key' => 'pec_id_calendar',
							   'value' => $calendar_id,
							),
							array(
							   'key' => 'pec_ics_uid',
							   'value' => $key['UID'],
							)
						)
					);
					/*
					,
							array(
							   'key' => 'pec_ics_uid_title',
							   'value' => $key['SUMMARY'],
							)
							*/
					
					$imported_posts = get_posts( $args );
					
					// Create post object
					$ics_event = array(
					  'post_title'    => $key['SUMMARY'],
					  'post_content'  => $key['DESCRIPTION'],
					  'post_status'   => 'publish',
					  'tax_input' 	  => array( 'pec_events_category' => $category_ics ),
					  'post_type'	  => 'pec-events'
					);
					
					if(!empty($imported_posts)) {
						$ics_event['ID'] = $imported_posts[0]->ID;
					}
					
					if($key['RRULE'] != "") {
						$rrule = explode(';', $key['RRULE']);
						
						if(is_array($rrule)) {
							$rrule_arr = array();
							foreach($rrule as $rule) {
								$rrule_arr[substr($rule, 0, strpos($rule, '='))] = substr($rule, strrpos($rule, '=') + 1);
							}
						}
					}
					
					if($key['EXDATE'] != "") {
						$exdate = wordwrap($key['EXDATE'], 15, ",", true);
						$exdate_string = array();
						foreach(explode(',', $exdate) as $exception) {
							$exdate_string[] = date("Y-m-d", strtotime($exception));
						}
						$exdate = implode(',', $exdate_string);
						
						update_post_meta($post_id, 'pec_exceptions', $exdate);	
						
					}
					
					// Insert the post into the database
					$post_id = wp_insert_post( $ics_event );
					/*
					if($key['UID'] == '7dmgqughp8dl2oarvtb96bq5j4@google.com') {
						echo $post_id;
						print_r($key);	
						die();
					}*/
					
					update_post_meta($post_id, 'pec_id_calendar', $calendar_id);
					
					
					$tzid = "UTC";
					$setTimeZone = get_option('timezone_string');
					if($setTimeZone == "") {
						$setTimeZone = timezone_name_from_abbr("", get_option('gmt_offset') * 3600, false);
					}
					
					if($feed['VTIMEZONE']['TZID'] != "") {
						$tzid = $feed['VTIMEZONE']['TZID'];
					}
					
					$start_date = new DateTime($key['DTSTART'], new DateTimeZone($tzid));
					$start_date->setTimeZone(new DateTimeZone($setTimeZone));
					
					if(strlen($key['DTEND']) == 8) {
						$end_date = date("Y-m-d", strtotime($key['DTEND']));
						$end_date_hh = date("H", strtotime($key['DTEND']));
						$end_date_mm = date("i", strtotime($key['DTEND']));
					} else {
						$end_date = new DateTime($key['DTEND'], new DateTimeZone($tzid));
						$end_date->setTimeZone(new DateTimeZone($setTimeZone));
						$end_date_hh = $end_date->format('H');
						$end_date_mm = $end_date->format('i');
						$end_date = $end_date->format('Y-m-d');
						
						
					
					}
					
					$all_day = false;
					$start_date_formatted = "";
					$set_until = false;
					
					if(strlen($key['DTSTART']) == 8) {
						update_post_meta($post_id, 'pec_all_day', 1);
						update_post_meta($post_id, 'pec_date', date("Y-m-d", strtotime($key['DTSTART'])).' 00:00:00');
						$start_date_formatted = date("Y-m-d", strtotime($key['DTSTART']));
						$all_day = true;
					} else {
						update_post_meta($post_id, 'pec_date', $start_date->format('Y-m-d H:i:s'));	
						$start_date_formatted = $start_date->format('Y-m-d');
					}
					
					if(is_array($rrule_arr)) {
						
						$recurring_frecuency = '';
						
						foreach($rrule_arr as $key2 => $value) {
							
							if($key2 == 'FREQ') {
								
								switch($value) {
									case 'DAILY':
										$recurring_frecuency = '1';
										break;
									case 'WEEKLY':
										$recurring_frecuency = '2';
										break;
									case 'MONTHLY':
										$recurring_frecuency = '3';
										break;
									case 'YEARLY':
										$recurring_frecuency = '4';
										break;
								}
								
								update_post_meta($post_id, 'pec_recurring_frecuency', $recurring_frecuency);
							}
							
							if($key2 == 'FREQ' && $value == 'DAILY') {
								update_post_meta($post_id, 'pec_daily_every', $rrule_arr['INTERVAL']);
	
								update_post_meta($post_id, 'pec_daily_working_days', '');
							}
							
							if($key2 == 'UNTIL' && $value != "") {
								
								if(strlen($value) == 8) {
									$end_date = date("Y-m-d", strtotime($value));
									$end_date_hh = date("H", strtotime($value));
									$end_date_mm = date("i", strtotime($value));
									
								} else {
									$end_date = new DateTime($value, new DateTimeZone($tzid));
									$end_date->setTimeZone(new DateTimeZone($setTimeZone));
									
									$end_date_hh = $end_date->format('H');
									$end_date_mm = $end_date->format('i');
									$end_date = $end_date->format('Y-m-d');
								}
								
								$set_until = true;
								
							}
							
							if($key2 == 'COUNT' && $value != "") {
								
								switch($recurring_frecuency) {
									case 1:
										$end_date = date("Y-m-d", strtotime("+".$value." days", strtotime($start_date_formatted)));
										break;
									case 2:
										$end_date = date("Y-m-d", strtotime("+".$value." weeks", strtotime($start_date_formatted)));
										break;
									case 3:
										$end_date = date("Y-m-d", strtotime("+".$value." months", strtotime($start_date_formatted)));
										break;
									case 4:
										$end_date = date("Y-m-d", strtotime("+".$value." years", strtotime($start_date_formatted)));
										break;	
								}
								
								$set_until = true;
							}
							
							if($key2 == 'FREQ' && $value == 'WEEKLY') {
								$day_arr = array();
								foreach(explode(',', $rrule_arr['BYDAY']) as $day) {
									switch($day) {
										case 'MO':
											$day_arr[] = 1;
											break;
										case 'TU':
											$day_arr[] = 2;
											break;
										case 'WE':
											$day_arr[] = 3;
											break;
										case 'TH':
											$day_arr[] = 4;
											break;
										case 'FR':
											$day_arr[] = 5;
											break;
										case 'SA':
											$day_arr[] = 6;
											break;
										case 'SU':
											$day_arr[] = 7;
											break;
									}
									
									update_post_meta($post_id, 'pec_weekly_day', $day_arr);
								}
	
								update_post_meta($post_id, 'pec_weekly_every', $rrule_arr['INTERVAL']);
	
							}
							
							if($key2 == 'FREQ' && $value == 'MONTHLY') {
								
								update_post_meta($post_id, 'pec_monthly_every', $rrule_arr['INTERVAL']);
								
								$setpos = "";
								switch($rrule_arr['BYSETPOS']) {
									case '1':
										$setpos = 'first';
										break;
									case '2':
										$setpos = 'second';
										break;
									case '3':
										$setpos = 'third';
										break;
	
									case '4':
										$setpos = 'fourth';
										break;
									case '-1':
										$setpos = 'last';
										break;
								}
								update_post_meta($post_id, 'pec_monthly_position', $setpos);
								
								$day_arr = '';
								foreach(explode(',', $rrule_arr['BYDAY']) as $day) {
									switch($day) {
										case 'MO':
											$day_arr = 'monday';
											break;
										case 'TU':
											$day_arr = 'tuesday';
											break;
										case 'WE':
											$day_arr = 'wednesday';
											break;
										case 'TH':
											$day_arr = 'thursday';
											break;
										case 'FR':
											$day_arr = 'friday';
											break;
										case 'SA':
											$day_arr = 'saturday';
											break;
										case 'SU':
											$day_arr = 'sunday';
											break;
									}
								}
								update_post_meta($post_id, 'pec_monthly_day', $day_arr);
							}
						}
					} else if($key['DTEND'] != "" && substr($key['DTEND'], 0, 8) != substr($key['DTSTART'], 0, 8)) {
	
						update_post_meta($post_id, 'pec_recurring_frecuency', '1');
						$recurring_frecuency = 1;
					}
					
					/*if($key['UID'] == '8849A059-D09F-4970-8EBF-815DFBABB2F2') {
						echo $key['SUMMARY'].'<br>'.$key['DTEND'].'<br>'.$end_date_hh;	
						die();
					}*/
					if($recurring_frecuency == "") {
						update_post_meta($post_id, 'pec_recurring_frecuency', '');
					}
					
					update_post_meta($post_id, 'pec_end_date', (($recurring_frecuency == '' || ($end_date == $start_date_formatted && !$set_until)) ? '' : $end_date));
					update_post_meta($post_id, 'pec_link', $key['URL']);
					update_post_meta($post_id, 'pec_share', '');
					update_post_meta($post_id, 'pec_map', '');
					update_post_meta($post_id, 'pec_end_time_hh', ($all_day ? '' : $end_date_hh));
					update_post_meta($post_id, 'pec_end_time_mm', ($all_day ? '' : $end_date_mm));
					update_post_meta($post_id, 'pec_hide_time', '');
					update_post_meta($post_id, 'pec_location', $key['LOCATION']);	
					update_post_meta($post_id, 'pec_ics_uid', $key['UID']);		
					update_post_meta($post_id, 'pec_ics_uid_title', $key['SUMMARY']);
					update_post_meta($post_id, 'pec_ics_filename', sha1($filename));	
					
					$all_uid[] = $key['UID'];					
				}
			}
			
			// Sync?
			
			if(filter_var($filename, FILTER_VALIDATE_URL) === FALSE) {
				
				
				// Remove Not found events
				
				$args = array( 
					'posts_per_page' => -1, 
					'post_type'=> 'pec-events', 
					"meta_query" => array(
						'relation' => 'AND',
						array(
						   'key' => 'pec_ics_filename',
						   'value' => sha1($filename)
						),
						array(
						   'key' => 'pec_ics_uid',
						   'value' => $all_uid,
						   'compare' => 'NOT IN'
						)
					)
				);
				
				$not_found_posts = get_posts( $args );
				
				foreach($not_found_posts as $key) {
					wp_delete_post( $key->ID );
				}
				
			}
			
			if(isset($dpProEventCalendar_cache['calendar_id_'.$calendar_id])) {
			   $dpProEventCalendar_cache['calendar_id_'.$calendar_id] = array();
			   update_option( 'dpProEventCalendar_cache', $dpProEventCalendar_cache );
		   }

		}
	//}	

}

function dpProEventCalendar_addFeaturedImageSupport()
{
    $supportedTypes = get_theme_support( 'post-thumbnails' );

    if( $supportedTypes === false )
        add_theme_support( 'post-thumbnails', array( 'pec-events' ) );               
    elseif( is_array( $supportedTypes ) )
    {
        $supportedTypes[0][] = 'pec-events';
        add_theme_support( 'post-thumbnails', $supportedTypes[0] );
    }
}

add_action( 'after_setup_theme', dpProEventCalendar_addFeaturedImageSupport, 11 );

if(!function_exists('mb_substr')) {
	function mb_substr($string, $offset, $length, $encoding = '') {
		$arr = preg_split("//u", $string);
		$slice = array_slice($arr, $offset + 1, $length);
	  	return implode("", $slice);	
	}
}

function dpProEventCalendar_datediffInWeeks($date1, $date2)
{
    if($date1 > $date2) return dpProEventCalendar_datediffInWeeks($date2, $date1);

	if(method_exists('DateTime','createFromFormat')) {
		$first = DateTime::createFromFormat('Y-m-d H:i:s', $date1);
		$second = DateTime::createFromFormat('Y-m-d H:i:s', $date2);
	} else {
		$first = dpProEventCalendar_create_from_format('Y-m-d H:i:s', $date1);
		$second = dpProEventCalendar_create_from_format('Y-m-d H:i:s', $date2);
	}
    //return floor($first->diff($second)->days/7);
	/*echo "First: ".floor($first->diff($second)->days/7)."<br>";
	echo "Second: ".floor(round(($second->format('U') - $first->format('U')) / (60*60*24) / 7) )."<br>";
	die();*/
	return floor(round(($second->format('U') - $first->format('U')) / (60*60*24) / 7) );
}

function dpProEventCalendar_create_from_format( $dformat, $dvalue )
{

	/*$schedule = $dvalue;
	$schedule_format = str_replace(array('Y','m','d', 'H', 'i','a'),array('%Y','%m','%d', '%I', '%M', '%p' ) ,$dformat);
	// %Y, %m and %d correspond to date()'s Y m and d.
	// %I corresponds to H, %M to i and %p to a
	$ugly = strptime($schedule, $schedule_format);*/
	$ymd = sprintf(
		// This is a format string that takes six total decimal
		// arguments, then left-pads them with zeros to either
		// 4 or 2 characters, as needed
		'%04d-%02d-%02d %02d:%02d:%02d',
		date('Y', strtotime($dvalue)),  // This will be "111", so we need to add 1900.
		date('m', strtotime($dvalue)),      // This will be the month minus one, so we add one.
		date('d', strtotime($dvalue)), 
		date('H', strtotime($dvalue)), 
		date('i', strtotime($dvalue)), 
		date('s', strtotime($dvalue))
	);
	$new_schedule = new DateTime($ymd);
	
	return $new_schedule;

}
?>