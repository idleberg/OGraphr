<?php
/*
	Plugin Name: OGraphr
	Plugin URI: https://github.com/idleberg/OGraphr
	Description: This plugin scans posts for embedded video and music players and adds their thumbnails URL as an OpenGraph meta-tag. While at it, the plugin also adds OpenGraph tags for the title, description (excerpt) and permalink. Facebook and other social networks can use these to style shared or "liked" articles.
	Version: 0.8.39
	Author: Jan T. Sott
	Author URI: https://github.com/idleberg
	License: GPLv2, MIT

	Thanks to Sutherland Boswell, Matthias Gutjahr, Michael Wöhrer and David DeSandro
*/

// OGRAPHR OPTIONS
    define("OGRAPHR_VERSION", "0.8.39");
	// replace default description with user agent in use
	define("OGRAPHR_UATEST", FALSE);
	// specify timeout for all HTTP requests (default is 1 second, http://googlecode.blogspot.co.at/2012/01/lets-make-tcp-faster.html)
	define("OGRAPHR_TIMEOUT", 1);
// ATTACHMENT IMAGE
	// default image size (thumbnail, medium, large, full)
	define("ATTACHMENT_IMAGE_SIZE", "medium");
	
// 8TRACKS
	// no need to change this unless you want to use your own 8tracks API key (-> http://8tracks.com/developers/new)
	define("ETRACKS_API_KEY", "e310c354bf4633de8dca0e7fb0a3a23fcc1614fe");
	// default artwork size (sq56=56x56, sq100=100x100, sq133=133x133, sq250=250x250, sq500=500x500, max133w=133 on longest side, max200=200 on longest side, max1024=1024 on longest side, original)
	define("ETRACKS_IMAGE_SIZE", "max200");
	
// BAMBUSER
	// no need to change this unless you want to use your own Bambuser API key (-> http://bambuser.com/api/keys)
	define("BAMBUSER_API_KEY", "0b2d6b4a0c990fe87c64af3fff13832e");
// BANDCAMP
	// default artwork size (small_art_url=100x100, large_art_url=350x350)
	define("BANDCAMP_IMAGE_SIZE", "large_art_url");
	
// FLICKR
	// no need to change this unless you want to use your own Flickr API key (-> http://www.flickr.com/services/apps/create/apply/)
	define("FLICKR_API_KEY", "2250a1cc92a662d9ea156b4e04ca7a88");
	// default artwork size (s=75x75, q=150x150, t=100 on longest side, m=240 on longest side, n=320 on longest side)
	define("FLICKR_IMAGE_SIZE", "n");
	
// MIXCLOUD
	// default artwork size (small=25x25, thumbnail=50x50, medium_mobile=80x80, medium=150x150, large=300x300, extra_large=600x600)
	define("MIXCLOUD_IMAGE_SIZE", "extra_large");
// OFFICIAL.FM
	// default artwork size (tiny=40x40, small=120x120, medium=300x300, large=600x600)
	define("OFFICIAL_IMAGE_SIZE", "large");
// SOUNDCLOUD
	// no need to change this unless you want to use your own SoundCloud API key (-> http://soundcloud.com/you/apps)
	define("SOUNDCLOUD_API_KEY", "15fd95172fa116c0837c4af8e45aa702");
	// default artwork size (mini=16x16, tiny=20x20, small=32x32, badge=47x47, t67x67, large=100x100, t300x300, crop=400x400, t500x500)
	define("SOUNDCLOUD_IMAGE_SIZE", "t500x500");
	
// SPOTIFY
	// default artwork size (60, 85, 120, 300, and 640)
	define("SPOTIFY_IMAGE_SIZE", "640");
// VIMEO
	// default snapshot size (thumbnail_small=100, thumbnail_medium=200, thumbnail_large=640)
	define("VIMEO_IMAGE_SIZE", "thumbnail_large");
	
// USTREAM
	// no need to change this unless you want to use your own Ustream.fm API key (-> http://developer.ustream.tv/apikey/generate)
	define("USTREAM_API_KEY", "8E640EF9692DE21E1BC4373F890F853C");
	// default artwork size (small=120x90, medium=240x180)
	define("USTREAM_IMAGE_SIZE", "medium");
	
// JUSTIN.TV
	// default snapshot size (small=100, medium=200, large=640)
	define("JUSTINTV_IMAGE_SIZE", "image_url_large");
// TWITTER CARD
	// default size for Twitter Card (summary=120x120, summary_large_image=438x?)
	define("TWITTER_CARD_TYPE", "summary");
// USER-AGENTS
	// facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)
	define('FACEBOOK_USERAGENT', '/facebookexternalhit/i');
	// Google (+https://developers.google.com/+/web/snippet/)
	define('GOOGLEPLUS_USERAGENT', '/Google \(\+https:\/\/developers\.google\.com\/\+\/web\/snippet\/\)/i');
	// LinkedInBot/1.0 (compatible; Mozilla/5.0; Jakarta Commons-HttpClient/3.1 +http://www.linkedin.com)
	define('LINKEDIN_USERAGENT', '/LinkedInBot/i');
	// Twitterbot
	define('TWITTER_USERAGENT', '/Twitterbot/i');

$core = new OGraphr_Core();

add_action('init', array(&$core,'ographr_core_init'));
add_action('wp_head', array(&$core,'ographr_main_dish'));
add_action('language_attributes', array(&$core,'ographr_namespace'));
add_action('save_post', array(&$core,'ographr_save_postmeta'));
add_action('delete_post', array(&$core,'ographr_delete_stats'));
add_action('admin_notices', array(&$core,'ographr_admin_notice'));
add_action('admin_bar_menu', array(&$core,'ographr_admin_bar'), 150);
add_filter('plugin_action_links', array(&$core, 'ographr_plugin_action_links'), 10, 2 );
register_activation_hook( __FILE__, array(&$core, 'ographr_activate') );

$options = get_option('ographr_options');
if (isset($options['disable_jetpack']))
	add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/admin.php';
}

class OGraphr_Core {
	
	// Define default option settings
	public function ographr_set_defaults() {

			// Set default locale to WordPress language
			if (WPLANG != NULL) {
				$tmp_locale = WPLANG;
			} else {
				$tmp_locale = "_none";
			}
			
			$options = array("last_update" => OGRAPHR_VERSION,
							"exec_mode" => "1",
							"data_expiry" => "-1",
							"advanced_opt" => NULL,
							"website_title" => "%postname%",
							"website_thumbnail" => NULL,
							"enable_plugin_on_front" => "1",
							"enable_triggers_on_front" => NULL,
							"website_description" => NULL,
							"not_always" => NULL,
							"add_adminbar" => NULL,
							"add_metabox" => NULL,
							"add_graph" => NULL,
							"add_prefix" => "1",
							"fill_curves" => NULL,
							"smooth_curves" => NULL,
							"add_comment" => "1",
							"add_title" => "1",
							"add_excerpt" => "1",
							"locale" => $tmp_locale,
							"add_permalink" => "1",
							"enable_etracks" => "1",
							"enable_bambuser" => "1",
							"enable_bandcamp" => NULL,
							"enable_dailymotion" => "1",
							"enable_flickr" => "1",
							"enable_hulu" => "1",
							"enable_internetarchive" => "1",
							"enable_justintv" => "1",
							"enable_livestream" => "1",
							"enable_mixcloud" => "1",
							"enable_myvideo" => NULL,
							"enable_official" => "1",
							"enable_soundcloud" => "1",
							"enable_spotify" => "1",
							"enable_ustream" => "1",
							"enable_vimeo" => "1",
							"enable_youtube" => "1",
							"add_post_images" => "1",
							"enable_videoposter" => "1",
							"enable_jwplayer" => "1",
							"enable_nvbplayer" => "1",
							"add_attached_image" => "1",
							"add_post_thumbnail" => NULL,
							"add_trailing_slash" => NULL,
							"link_type" => "permalink",
							"add_twitter_meta" => NULL,
							"add_google_meta" => NULL,
							"add_link_rel" => NULL,
							"filter_smilies" => "1",
							"filter_themes" => NULL,
							"filter_plugins" => NULL,
							"filter_uploads" => NULL,
							"filter_includes" => "1",
							"filter_gravatar" => "1",
							"allow_admin_tag" => NULL,
							"restrict_age" => "_none",
							"restrict_country" => NULL,
							"restrict_content" => NULL,
							"facebook_ua" => NULL,
							"gplus_ua" => NULL,
							"linkedin_ua" => NULL,
							"twitter_ua" => NULL,
							"limit_opengraph" => NULL,
							"disable_jetpack" => NULL,
							"fb_site_name" => "%sitename%",
							"fb_type" => "_none",
							"add_author" => NULL,
							"add_section" => NULL,
							"add_tags" => NULL,
							"add_pubtime" => NULL,
							"add_modtime" => NULL,
							"add_embeds" => NULL,
							"app_universal" => NULL,
							"app_iphone_name" => NULL,
							"app_iphone_id" => NULL,
							"app_iphone_url" => NULL,
							"app_ipad_name" => NULL,
							"app_ipad_id" => NULL,
							"app_ipad_url" => NULL,
							"app_android_name" => NULL,
							"app_android_id" => NULL,
							"app_android_url" => NULL,
							"debug_level" => "0",
							"enable_beta" => NULL,
							"ua_testdrive" => NULL,
							"always_devmode" => NULL,
			);
		
			return $options;
	}
	
	public function remote_exists($path){
		$response = wp_remote_head($path, array('timeout' => OGRAPHR_TIMEOUT, 'compress' => TRUE, 'decompress' => TRUE));
		if (!is_wp_error($response)) {
			return ($response['response']['code']==200);
		}
	}

	// Featured Image (http://codex.wordpress.org/Post_Thumbnails)
	public function get_featured_img() {
		global $post;
		
		if (has_post_thumbnail( $post->ID )) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			return $image[0];
	  	}
	}
	
	// Attachement Images
	public function get_attached_img() {
		global $post;
		
		if (!empty($post->ID)) {
		
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'post_status' => null,
				'post_parent' => $post->ID
			);

			$attachments = get_posts( $args );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					$images[] = wp_get_attachment_image_src( $attachment->ID, ATTACHMENT_IMAGE_SIZE );
				}
				return $images;
			}
		}
	}


	// Get JSON Thumbnail
	public function get_json_thumbnail($services, $json_url, $json_query) {
			$output = wp_remote_retrieve_body( wp_remote_get($json_url, array('timeout' => OGRAPHR_TIMEOUT)) );
		
		$output = json_decode($output);
		
		// special treatment
		if ($services['name'] == "Justin.tv/Twitch") {
			$output = $output[0];
		} else if ($services['name'] == "Flickr") {
			$ispublic = $output->photo->visibility->ispublic;
			if ($ispublic == 1) {
				$id = $output->photo->id;
				$server = $output->photo->server;
				$secret = $output->photo->secret;
				$farm = $output->photo->farm;
				$width = $output->photo->video->width;
				$height = $output->photo->video->height;
				$output = array(
					"img" => "http://farm" . $farm . ".staticflickr.com/" . $server . "/" . $id . "_" . $secret . "_" . FLICKR_IMAGE_SIZE . ".jpg",
					"w" => $width,
					"h" => $height,
				);
				//$exists = $this->remote_exists($output);
				//if($exists) {
				return $output;
				//}
			} else {
				return;
			}
		}

		$json_keys = explode('->', $json_query);
		foreach($json_keys as $json_key) {
			$output = $output->$json_key;
		}			

		return $output;
	} // end get_json_thumbnail

	//
	// The Main Dish
	//
	public function ographr_main_dish($post_id=null) {
		
		//global $options;
		$options = get_option('ographr_options');
		global $post;

		if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {
			$s_time = microtime(true);
		}

		// ographr_meta_disable
		if (isset($options['add_metabox'])) {
			$tmp = get_post_meta($post->ID, 'ographr_disable_plugin', true); 
			if ($tmp == "on") {
				return;
			}
		}
		
		// enable on front
		if ((!$enable_plugin_on_front = $options['enable_plugin_on_front']) && (!is_single()) && (!is_page())) {
			return;
		}
			
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (isset($options['facebook_ua'])) {
			$facebook_ua = $options['facebook_ua'];
		}
		if (isset($options['gplus_ua'])) {
			$gplus_ua = $options['gplus_ua'];
		}
		if (isset($options['linkedin_ua'])) {
			$linkedin_ua = $options['linkedin_ua'];
		}
		if (isset($options['twitter_ua'])) {
			$twitter_ua = $options['twitter_ua'];
		}
				
		if ( ((preg_match(FACEBOOK_USERAGENT, $user_agent)) && ($facebook_ua))
		|| ((preg_match(GOOGLEPLUS_USERAGENT, $user_agent)) && ($gplus_ua))
		|| ((preg_match(LINKEDIN_USERAGENT, $user_agent)) && ($linkedin_ua))
		|| ((preg_match(TWITTER_USERAGENT, $user_agent)) && ($twitter_ua))
		|| ((!isset($facebook_ua)) && (!isset($gplus_ua)) && (!isset($linkedin_ua)) && (!isset($twitter_ua)))
		|| ($options['debug_level'] > 0) ) {
			// Get the post ID if none is provided
			if($post_id==null OR $post_id=='') {
				$post_id = get_the_ID();
			}

			// Gets the post's content
			$post_array = get_post($post_id); 
			$markup = $post_array->post_content;
			$markup = apply_filters('the_content',$markup);

			// Get default website thumbnail
			$web_thumb = $options['website_thumbnail'];
			$screenshot = get_bloginfo('stylesheet_directory') . "/screenshot.png";
			if ($web_thumb == "%screenshot%") {
				$web_thumb = str_replace("%screenshot%", $screenshot, $web_thumb);
			}
		
			if (($web_thumb) && (!$options['not_always'])) {
				$thumbnails[]['img'] = $web_thumb;		
			}
			
			// Get date et al
			$today = date("U"); // Y-m-d H:i:s
			$last_indexed = get_post_meta($post_id, 'ographr_indexed', true);
			if (!$last_indexed) { // set to release date of v0.5.3
				$last_indexed = 1336082400; // 2012-05-04 00:00:00 CET
			}

			$interval = $today - $last_indexed;
			$expiry = NULL; // suppress warnings
			if (isset($options['data_expiry'])) {
				$expiry = $options['data_expiry'];
			}

			if (($expiry) && ($expiry != "-1")) {
				$expiry = $expiry * 86400;
			} else {
				$expiry = $today + 86400; // tomorrow never dies
			}
			
			// debugging?
			if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {
				print "<!--\tOGRAPHR DEBUGGER [".$options['debug_level']."]\n";

				if($options['debug_level'] >= 2) {
					print "\n\tSoftware\n";
					print "\t OGraphr " . OGRAPHR_VERSION;
					if (isset($options['enable_beta'])) { 
						print " (+beta)";
					}
					print "\n";
					print "\t WordPress " . get_bloginfo('version') . "\n";
					print "\t PHP " . phpversion() . "\n";
					if( ($options['debug_level'] >= 3) && (strnatcmp(phpversion(),'5.5.0') < 0) ) {
						print "\t MySQL " . mysql_get_server_info() . "\n";
						print "\t $user_agent\n";
					}
						
				}
				
				if($options['debug_level'] >= 2) {
					print "\n\tSettings\n";
					if ($options['exec_mode'] == 1) {
						print "\t Images retrieved on post\n";
					} else if ($options['exec_mode'] == 2) {
						print "\t Images retrieved on view\n";
					}
				
				
					if (isset($facebook_ua)) { print "\t Limited to Facebook User Agent\n"; }
					if (isset($gplus_ua)) { print "\t Limited to Google+ User Agent\n"; }
					if (isset($linkedin_ua)) { print "\t Limited to LinkedIn User Agent\n"; }
					if (isset($twitter_ua)) { print "\t Limited to Twitter User Agent\n"; }

					if (isset($options['disable_jetpack'])) { print "\t Jetpack Open Graph Tags are disabled\n"; }
					
					if (isset($options['filter_gravatar'])) { print "\t Avatars are filtered\n"; }
					if (isset($options['filter_smilies'])) { print "\t Emoticons are filtered \n"; }
					if (isset($options['filter_themes'])) { print "\t Themes are filtered\n"; }
					if (isset($options['filter_plugins'])) { print "\t Plug-ins are filtered\n"; }
					if (isset($options['filter_uploads'])) { print "\t Uploads are filtered\n"; }
					if (isset($options['add_twitter_meta'])) { print "\t Twitter Cards are enabled\n"; }
					if (isset($options['add_google_meta'])) { print "\t Google+ Meta Descriptions are enabled\n"; }
					if (isset($options['add_link_rel'])) { print "\t Link Elements are enabled\n"; }
					if (isset($options['add_embeds'])) { print "\t Media embeds are enabled\n"; }
					
					$tmp = get_post_meta($post->ID, 'ographr_restrict_age', true);
					if( (isset($tmp)) && ($tmp != NULL) ){
						print "\t Content suitable for audiences aged " . $tmp . "+ (local)\n";
					} else if (isset($options['restrict_age'])){
						if ($options['restrict_age'] == "_none") {
							print "\t Content suitable for all audiences (global)\n";
						} else {
							print "\t Content suitable for audiences aged " . $options['restrict_age'] . "+ (global)\n";
						}						
					}
					
					$tmp = get_post_meta($post->ID, 'ographr_restrict_country', true);
					if ($tmp == "on") {
						$tmp_cmode = get_post_meta($post->ID, 'ographr_country_mode', true);
						$tmp_ccode = get_post_meta($post->ID, 'ographr_country_code', true);
						if( (isset($tmp_cmode)) && (isset($tmp_ccode)) ) {
							print "\t Visitors from " . $tmp_ccode . " are " . $tmp_cmode . " (local)\n";
						} else if (isset($options['restrict_age'])) {
							print "\t Visitors from " . $options['country_code'] . " are " . $options['country_mode'] . " (global)\n";
						}
						unset($tmp_cmode);
						unset($tmp_ccode);
					}

					$tmp = get_post_meta($post->ID, 'ographr_restrict_content', true);
					if( (isset($tmp)) && ($tmp != NULL) ){
						print "\t Article contains alcohol (local)\n";
					} else if (isset($options['restrict_content'])) {
						print "\t Article contains alcohol (global)\n";
					}

					if ($options['filter_custom_urls']) {
						foreach(preg_split("/((\r?\n)|(\n?\r))/", $options['filter_custom_urls']) as $line){
							print "\t Custom filter: $line\n";
						}
					}
				}

				if ($options['debug_level'] == 3) {
					print "\n\tTriggers\n";
					if (isset($options['enable_etracks'])) { print "\t 8tracks enabled\n"; }
					if (isset($options['enable_bambuser'])) { print "\t Bambuser enabled\n"; }
					if (isset($options['enable_bandcamp'])) { print "\t Bandcamp enabled\n"; }
					if (isset($options['enable_dailymotion'])) { print "\t Dailymotion enabled\n"; }
					if (isset($options['enable_flickr'])) { print "\t Flickr enabled\n"; }
					if (isset($options['enable_hulu'])) { print "\t Hulu enabled\n"; }
					if (isset($options['enable_internetarchive'])) { print "\t Internet Archive enabled\n"; }
					if (isset($options['enable_justintv'])) { print "\t Justin.tv enabled\n"; }
					if (isset($options['enable_livestream'])) { print "\t Livestream enabled\n"; }
					if (isset($options['enable_mixcloud'])) { print "\t Mixcloud enabled\n"; }
					if (isset($options['enable_myvideo'])) { print "\t MyVideo enabled\n"; }
					if (isset($options['enable_official'])) { print "\t Official.fm enabled\n"; }
					if (isset($options['enable_soundcloud'])) { print "\t SoundCloud enabled\n"; }
					if (isset($options['enable_spotify'])) { print "\t Spotify enabled\n"; }
					if (isset($options['enable_ustream'])) { print "\t Ustream enabled\n"; }
					if (isset($options['enable_vimeo'])) { print "\t Vimeo enabled\n"; }
					if (isset($options['enable_youtube'])) { print "\t YouTube enabled\n"; }
				}
								
				if ( ($options['debug_level'] == 3) && (current_user_can('manage_options')) ) {
					print "\n\tAPI Keys\n";
					if ($etracks_api = $options['etracks_api']) { print "\t 8tracks: $etracks_api\n"; }
					if ($bambuser_api = $options['bambuser_api']) { print "\t Bambuser: $bambuser_api\n"; }
					if ($bandcamp_api = $options['bandcamp_api']) { print "\t Bandcamp: $bandcamp_api\n"; }
					if ($flickr_api = $options['flickr_api']) { print "\t Flickr: $flickr_api\n"; }
					if ($myvideo_dev_api = $options['myvideo_dev_api']) { print "\t MyVideo (Developer): $myvideo_dev_api\n"; }
					if ($myvideo_web_api = $options['myvideo_web_api']) { print "\t MyVideo (Website): $myvideo_web_api\n"; }
					if ($soundcloud_api = $options['soundcloud_api']) { print "\t SoundCloud : $soundcloud_api\n"; }
					if ($ustream_api = $options['ustream_api']) { print "\t Ustream: $ustream_api\n"; }
				}

				print "\n\tImages\n";
				if ($options['exec_mode'] == 1) {
					if ($options['data_expiry'] == -1) {
						print "\t Indexed: " . date('Y-m-d', $last_indexed) . "\n";
						print "\t Expiry: never\n\n";
					} else {
						print "\t Indexed: " . date('Y-m-d', $last_indexed) . "\n";
						print "\t Expiry: " . round($expiry / 86400) ." days\n\n";
					}
					
					if ($web_thumb) {
						print "\t Default Thumbnail: $web_thumb\n";
					}
				}
			}
			
			// Let's get started!
			if ( (isset($options['enable_triggers_on_front'])) || (is_single()) || (is_page()) ) {
				
				// Did we retrieve those images before and did they expire?
				if (($options['exec_mode'] == 1) && ($expiry >= $interval) )  {
					$meta_values = get_post_meta($post_id, 'ographr_urls', true);
					$meta_values = json_decode($meta_values, true);
				}

				if ( (isset($meta_values)) && (is_array($meta_values)) && (isset($thumbnails[0]['img'])) && ($expiry >= $interval) ) {
					$thumbnails = array_merge($thumbnails, $meta_values);
					if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {
						foreach($thumbnails as $thumbnail) {
							if (isset($thumbnail['img'])) {
								if (filter_var($thumbnail['img'], FILTER_VALIDATE_URL)) {
									if ($thumbnail['img'] != $web_thumb) {
										print "\t Post meta: " . $thumbnail['img'];
									}
									if( (isset($thumbnail['w'])) && (isset($thumbnail['h'])) ) {
										print " (" . $thumbnail['w'] . "×" . $thumbnail['h'] . ")";
									}
								print "\n";
								}								
							} else {
								print "\t no images\n";
							}
						}
						//print "\n";
					}
				} else if ( (isset($meta_values)) && (is_array($meta_values)) && (!isset($thumbnails)) && ($expiry >= $interval) ) {
					$thumbnails = $meta_values;
					if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {
						foreach($thumbnails as $thumbnail) {
							if(isset($thumbnail['img'])){
								if(filter_var($thumbnail['img'], FILTER_VALIDATE_URL)) {
									print "\t Post meta: " . $thumbnail['img'];
									if( (isset($thumbnail['w'])) && (isset($thumbnail['h'])) ) {
										print " (" . $thumbnail['w'] . "×" . $thumbnail['h'] . ")";
									}
								print "\n";
								}								
							} else {
								print "\t no images\n";
							}
								
						}
						//print "\n";
					}
				} else {
					if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && ($options['exec_mode'] == 1) && ($expiry >= $interval) ){
						print "\n\t Empty post-meta, retrieving images\n\n";
					} else if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && ($options['exec_mode'] == 1) && ($expiry < $interval) ) {
						print "\n\t Data expired, indexing\n\n";
					}
					
					// Get Widget Thumbnails (fallback)
					$widget_thumbnails = $this->get_widget_thumbnails($markup);
					if ((is_array($widget_thumbnails)) && (isset($thumbnails))) {
						$thumbnails = array_merge($thumbnails, $widget_thumbnails);
					} else if ((is_array($widget_thumbnails)) && (!isset($thumbnails))) { //is_array
						$thumbnails = $widget_thumbnails;
					}
					
					// double checking before writing to db
					$total_img = count($thumbnails);
					
					//write to db for future use
					if (($options['exec_mode'] == 1) && ($total_img >= 1)) {
						if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {
							print "\n\t New data indexed and written to database ($total_img)\n";
						}
						
						if(!empty($thumbnails)) {
							$thumbnails_db = json_encode($thumbnails);
							update_post_meta($post_id, 'ographr_urls', $thumbnails_db);

							$indexed = date("U"); // Y-m-d H:i:s
							update_post_meta($post_id, 'ographr_indexed', $indexed);
							// 0.6 double check
							$this->ographr_save_stats();
						}
					}
				}
		
		}
				// close debugger tag
				if( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) {	
					$e_time = microtime(true);
					$time = $e_time - $s_time;
					print "\n\tBenchmark\n";
					print "\t Processed in " . abs($time) . " seconds\n";
					print "-->\n";
				}
								
				// Let's print all this
				if(($options['add_comment']) && ($options['debug_level'] == 0) || ( ($options['debug_level'] > 0) && (!current_user_can('edit_plugins')) ) ){
					print "<!-- OGraphr v" . OGRAPHR_VERSION . " - https://github.com/idleberg/OGraphr -->\n";
				}
		
				// Add title & description
				$title = $options['website_title'];
				$site_name = $options['fb_site_name'];
				$mywp = array();
				$mywp['title'] = esc_html(get_the_title());
				$mywp['blog_name'] = get_bloginfo('name');
				$mywp['home_url'] = get_option('home');
				$mywp['home_url'] = preg_replace('/https?:\/\//', NULL, $mywp['home_url']);
				$title = str_replace("%postname%", $mywp['title'], $title);
				$title = str_replace("%sitename%", $mywp['blog_name'], $title);
				$title = str_replace("%siteurl%", $mywp['home_url'], $title);
				if (!$title) {
					$title = $mywp['title'];
				}
				$site_name = str_replace("%sitename%", $mywp['blog_name'], $site_name);
				$site_name = str_replace("%siteurl%", $mywp['home_url'], $site_name);

				if(isset($options['add_author'])) {			
					$add_author = $options['add_author'];
				}
				if(isset($options['add_section'])) {
					$add_section = $options['add_section'];
				}
				if(isset($options['add_tags'])) {
					$add_tags = $options['add_tags'];
			 	}
				if(isset($options['add_pubtime'])) {
					$add_pubtime = $options['add_pubtime'];
				}
				if(isset($options['add_modtime'])) {
					$add_modtime = $options['add_modtime'];
				}
				//$twitter_creator_name = $options['twitter_creator_name'];

				// suppress warnings
				$facebook_meta = array();
				$opengraph_meta = array();
				$google_meta = array();
				$twitter_meta = array();
				$link_rel = NULL;

				// only enter the loop if required!			
				if( ( (isset($add_author)) || (isset($add_section)) || (isset($add_tags)) || (isset($add_pubtime)) || (isset($add_modtime)) )  && ($options['fb_type'] == 'article') ){

					if ((have_posts()) && (is_single())) {
						while (have_posts()) : the_post();

							if (isset($add_author)) {
								$author_id=$post->post_author;
								$author_name = get_userdata($author_id);
								if( ($author_name->user_login != "admin") || (isset($options['allow_admin_tag'])) ){
									$mywp['author_url'] = get_author_posts_url($author_id);
									$facebook_meta['article:author'] = $mywp['author_url'];
								}								
							}							

							if (isset($add_section)) {
								$mywp['categories'] = get_the_category();
								if ($mywp['categories']) {
									foreach($mywp['categories'] as $category) {
										$facebook_meta['article:section'][] = $category->name;
								  }
								}
							}

							if (isset($add_tags)) {
								$mywp['tags'] = get_the_tags();
								if ($mywp['tags']) {
									foreach($mywp['tags'] as $tag) {
										$facebook_meta['article:tag'][] = $tag->name;
								  }
								}
							}

							if (isset($add_pubtime)) {
								$mywp['published'] = get_the_date('Y-m-d');
								$facebook_meta['article:published_time'] = $mywp['published'];
							}							

							if (isset($add_modtime)) {
								$mywp['modified'] = get_the_modified_date('Y-m-d');
								$facebook_meta['article:modified_time'] = $mywp['modified'];
							}

						endwhile;
					}
				}

				if (isset($options['add_twitter_meta'])) {
					
					// get values
					$twitter_site_user = $options['twitter_site_user'];
					$twitter_site_id = $options['twitter_site_id'];
					$twitter_author_user = $options['twitter_author_user'];
					$twitter_author_id = $options['twitter_author_id'];

					$app_iphone_name = $options['app_iphone_name'];
					$app_iphone_id = $options['app_iphone_id'];
					$app_iphone_url = $options['app_iphone_url'];
					
					// abuse contact fields for Twitter user
					if ($twitter_author_user == "%user_twitter%") {
						$mywp['twitter'] = get_the_author_meta('twitter');
						$twitter_author_user = str_replace("%user_twitter%", $mywp['twitter'], $twitter_author_user);
					} else if ($twitter_author_user == "%user_aim%") {
						$mywp['twitter'] = get_the_author_meta('aim');
						$twitter_author_user = str_replace("%user_aim%", $mywp['twitter'], $twitter_author_user);
					} else if ($twitter_author_user == "%user_jabber%") {
						$mywp['twitter'] = get_the_author_meta('jabber');
						$twitter_author_user = str_replace("%user_jabber%", $mywp['twitter'], $twitter_author_user);
					} else if ($twitter_author_user == "%user_yahoo%") {
						$mywp['twitter'] = get_the_author_meta('yim');
						$twitter_author_user = str_replace("%user_yahoo%", $mywp['twitter'], $twitter_author_user);
					}

					// abuse contact fields for Twitter ID
					if (($twitter_author_id == "%user_aim%") && ($twitter_author_user != "%user_aim%")) {
						$mywp['twitter'] = get_the_author_meta('aim');
						$twitter_author_id = str_replace("%user_aim%", $mywp['twitter'], $twitter_author_id);
					} else if (($twitter_author_id == "%user_yahoo%") && ($twitter_author_user != "%user_yahoo%")) {
						$mywp['twitter'] = get_the_author_meta('yim');
						$twitter_author_id = str_replace("%user_yahoo%", $mywp['twitter'], $twitter_author_id);
					}

					// type of twitter card
					$twitter_meta['twitter:card'] = TWITTER_CARD_TYPE;

					// domain of your blog
					$domain = get_site_url();
					if(isset($domain)) {
						$domain = parse_url($domain, PHP_URL_HOST);
						$twitter_meta['twitter:domain'] = "$domain";
					}

					// twitter site name
					if (strlen($twitter_site_user) > 1) {
						$twitter_meta['twitter:site'] = "@$twitter_site_user";
						if ((isset($twitter_site_id) ) && ($twitter_site_id != NULL)) {
							$twitter_meta['twitter:site:id'] = "$twitter_site_id";
						}
					}

					if (is_single()) {
						if (strlen($twitter_author_user) > 1) {
							$twitter_meta['twitter:creator'] = "@$twitter_author_user";
							if ((isset($twitter_author_id) ) && ($twitter_author_id != NULL)) {
								$twitter_meta['twitter:creator:id'] = "$twitter_author_id";
							}
						}
					}

					//iOS Apps
					if (($app_iphone_name != NULL) && ($app_iphone_id != NULL) && ($app_iphone_url != NULL)) {
						$twitter_meta['twitter:app:name:iphone'] = "$app_iphone_name";
						$twitter_meta['twitter:app:id:iphone'] = "$app_iphone_id";
						$twitter_meta['twitter:app:url:iphone'] = "$app_iphone_url";
						if (isset($options['app_universal'])) {
							$twitter_meta['twitter:app:name:ipad'] = "$app_iphone_name";
							$twitter_meta['twitter:app:id:ipad'] = "$app_iphone_id";
							$twitter_meta['twitter:app:url:ipad'] = "$app_iphone_url";
						} else {
							$app_ipad_name = $options['app_ipad_name'];
							$app_ipad_id = $options['app_ipad_id'];
							$app_ipad_url = $options['app_ipad_url'];

							if (($app_ipad_name != NULL) && ($app_ipad_id != NULL) && ($app_ipad_url != NULL)) {
								$twitter_meta['twitter:app:name:ipad'] = "$app_ipad_name";
								$twitter_meta['twitter:app:id:ipad'] = "$app_ipad_id";
								$twitter_meta['twitter:app:url:ipad'] = "$app_ipad_url";
							}
						}
					}

					$app_android_name = $options['app_android_name'];
					$app_android_id = $options['app_android_id'];
					$app_android_url = $options['app_android_url'];
					if (($app_android_name != NULL) && ($app_android_id != NULL) && ($app_android_url != NULL)) {
						$twitter_meta['twitter:app:name:android'] = "$app_android_name";
						$twitter_meta['twitter:app:id:android'] = "$app_android_id";
						$twitter_meta['twitter:app:url:android'] = "$app_android_url";
					}
				} //add_twitter_meta
	
				if ((isset($options['website_description'])) && (is_front_page())) {
					// Blog title
					$title = get_option('blogname');
					if($title) {
						if (isset($options['add_google_meta'])) {
							$google_meta['title'] = $title;
						}
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:title'] = $title;
						}
						$opengraph_meta['og:title'] = $title;
					}
					// Add custom description
					$description = $options['website_description'];
					$wp_tagline = get_bloginfo('description');
					$description = str_replace("%tagline%", $wp_tagline, $description);
					if($description) {
						if (isset($options['add_google_meta'])) {
							$google_meta['description'] = $description;
						}
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:description'] = $description;
						}
						$opengraph_meta['og:description'] = $description;
					}
				} else if( (!is_category()) && (!is_archive()) && (!is_search()) ) { //single posts
					if ($options['add_title'] && ($title)) {
						// Post title
						if (isset($options['add_google_meta'])) {
							$google_meta['title'] = $title;
						}
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:title'] = $title;
						}
						$opengraph_meta['og:title'] = $title;
					}
					
					if($options['add_excerpt'] && ($description = wp_strip_all_tags((get_the_excerpt()), true))) {
						// Post excerpt
						if (OGRAPHR_UATEST == TRUE) {
							$description = $user_agent;
						}
						if (isset($options['add_google_meta'])) {
							$google_meta['description'] = $description;
						}
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:description'] = $description;
						}
						$opengraph_meta['og:description'] = $description;
					}
				}
		
				// Add permalink
				if (($options['add_permalink']) && (is_front_page()) && ($link = get_option('home'))) {
					if( (isset($options['add_trailing_slash'])) && (substr($link, -1) !== '/') ) $link = $link . '/';
					$opengraph_meta['og:url'] = $link;
					if (isset($options['add_twitter_meta'])) {
						$twitter_meta['twitter:url'] = $link;
					}
				} else if( (!is_category()) && (!is_archive()) && (!is_search()) ) {
					if(isset($options['add_permalink'])) {
						if($options['link_type'] == "shortlink") {
							$link = wp_get_shortlink();
						} else {
							$link = get_permalink();
						}
						if( (isset($options['add_trailing_slash'])) && (substr($link, -1) !== '/') ) $link = $link . '/';{
							$opengraph_meta['og:url'] = $link;
						}
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:url'] = $link;
						}
					}
				}
			
				// Add site name
				if ($site_name) {
					$opengraph_meta['og:site_name'] = $site_name;
				}				
				
				// Add locale
				$locale = $options['locale'];
				if (isset($locale) && ($locale != "_none")) {
					$opengraph_meta['og:locale'] = $locale;
				}
			
				// Add type
				if (($type = $options['fb_type']) && ($type != '_none')) {
					$opengraph_meta['og:type'] = $type;
				}

				if (isset($options['add_metabox'])) {
					// Add age restriction
					$age = get_post_meta($post->ID, 'ographr_restrict_age', true); 
					if ($age == NULL) {
						$age = $options['restrict_age'];
					}
					if ( (isset($age)) && ($age != "_none") ) {
						$opengraph_meta['og:restrictions:age'] = $age . "+";
					}

					// Add country restriction
					$tmp = get_post_meta($post->ID, 'ographr_restrict_country', true);
					if ($tmp != "on") {
						if (isset($options['country_mode'])) $mode = $options['country_mode'];
						if (isset($options['country_code'])) $code = $options['country_code'];
					} else {
						$mode = get_post_meta($post->ID, 'ographr_country_mode', true);
						$code = get_post_meta($post->ID, 'ographr_country_code', true);
					}
					if  ( (isset($mode)) && ($mode != NULL) && (isset($code)) && ($code != NULL) ) {
						$opengraph_meta["og:restrictions:country:$mode"] = $code;
					}

					// Add content restriction
					$content = get_post_meta($post->ID, 'ographr_restrict_content', true); 
					if ($content == NULL)
						if (isset($options['restrict_content'])) $content = $options['restrict_content'];
					if(isset($content)){
						$opengraph_meta['og:restrictions:content'] = 'alcohol';
					}
				}	
				
		
				// Add thumbnails
				if (isset($thumbnails)) { // avoid error message when array is empty
					$total_img = count($thumbnails);
				}
					
				if ( ((!isset($total_img)) || ($total_img == 0)) && (!empty($web_thumb))) {
					$opengraph_meta['og:image'][] = $web_thumb;
					if (isset($options['add_twitter_meta'])) {
						$twitter_meta['twitter:image'] = $web_thumb;
					}
					$ext = pathinfo($web_thumb, PATHINFO_EXTENSION);
					if (($ext == "jpg") || ($ext == "jpe")) {
						$ext = "jpeg";
					}
					$opengraph_meta['og:image:type'] = "image/$ext";
				} else if (isset($thumbnails)) { // investigate?
					foreach ($thumbnails as $thumbnail) {
						if( ($thumbnail['img']) && (filter_var($thumbnail['img'], FILTER_VALIDATE_URL)) ){
							$opengraph_meta['og:image'][] = $thumbnail['img'];
							if (isset($options['add_metabox'])) {
								$tmp = get_post_meta($post->ID, 'ographr_primary_image', true);
								if ( ((isset($options['add_twitter_meta'])) && (!isset($tmp))) || ((isset($options['add_twitter_meta'])) && (($tmp == "_none") || ($tmp == NULL)) ) )  {
									$twitter_meta["twitter:image"] = $thumbnail['img'];
								} else {
									if ($tmp != "_none") {
										$twitter_meta['twitter:image'] = $tmp; // a bit repetitive though
									}
								}
							}	
						}
					}
				}

				// Add image-type if only one image has been found
				if ( (isset($total_img)) && ($total_img == 1) ) {
					$ext = preg_replace('/(?!.*\.(bmp|gif|jpe|jpeg|jpg|png|webp))(\?|&).*\Z/i', '', $thumbnails[0]['img']); // remove suffix (might need improvement)
					$ext = pathinfo($ext, PATHINFO_EXTENSION);
					if (($ext == "jpg") || ($ext == "jpe")) {
						$ext = "jpeg";
					}
					if (($ext == "bmp") || ($ext == "gif") || ($ext == "jpeg") || ($ext == "png") || ($ext == "webp")) {
						$opengraph_meta['og:image:type'] = "image/$ext";
					}
				}

				// Add video player
				if ( (isset($total_img)) && ($total_img == 1) && (isset($options['add_embeds'])) && (isset($thumbnails[0]['w']))  && (isset($thumbnails[0]['h'])) ) {
					
					switch ($thumbnails[0]['service']) {
					    case "8tracks":
					    	$player = 'https:\/\/8tracks.com\/mixes\/' . $thumbnails[0]['id'] . '\/player_v3\/autoplay'; // = HTML5 player
					    	break;
					    case "Bambuser":
					    	$player = 'https:\/\/static.bambuser.com\/r\/player.swf?vid=' . $thumbnails[0]['id'] . '&context=fb';
					    	$player_html5 = 'https://embed.bambuser.com/broadcast/' . $thumbnails[0]['id'];
					    	break;
					    case "Bandcamp album":
					    	$player = 'http:\/\/bandcamp.com\/EmbeddedPlayer.swf\/size=venti\/album=' . $thumbnails[0]['id'] . '\/';
					    	$player_html5 = 'https://bandcamp.com/EmbeddedPlayer/v=2/album=' . $thumbnails[0]['id'] . '/size=venti/';
					    	break;
					    case "Bandcamp track":
					    	$player = 'http:\/\/bandcamp.com\/EmbeddedPlayer.swf\/size=venti\/track=' . $thumbnails[0]['id'] . '\/';
					    	$player_html5 = 'https://bandcamp.com/EmbeddedPlayer/v=2/track=' . $thumbnails[0]['id'] . '/size=venti/';
					    	break;
					    case "Dailymotion":
					    	$player = 'http://www.dailymotion.com/swf/video/' . $thumbnails[0]['id'] . '?autoPlay=1';
					    	$player_html5 = 'https://www.dailymotion.com/embed/video/' . $thumbnails[0]['id'];
					    	break;
					    case "Mixcloud":
					    	$player = 'http://www.mixcloud.com/media/swf/player/mixcloudLoader.swf?feed=http%3A%2F%2Fwww.mixcloud.com%2Fapi%2F1%2Fcloudcast%2F' . str_replace('/', '%2F', $thumbnails[0]['id']) . '.json&amp;autoplay=1&amp;fb_feed=1&amp;embed_uuid=&amp;embed_type=facebook_share';
					    	$player_html5 = 'https://www.mixcloud.com/widget/iframe/?feed=http%3A%2F%2Fwww.mixcloud.com%2F' . str_replace('/', '%2F', $thumbnails[0]['id']) . '&amp;embed_type=widget_standard';
					    	break;
					    case "Official.fm":
					    	$player = 'https://official.fm/flash/ofm_player.swf?referer=facebook.com&autoplay=true&feed=/feed/tracks/' . $thumbnails[0]['id'] . '.json&skin_bg=000000&skin_fg=FFFFFF';
					    	$player_html5 = 'https://official.fm/player?width=435&height=200&artwork=1&artwork_left=1&tracklist=1&feed=%2Ffeed%2Ftracks%2F' . $thumbnails[0]['id'] . '.json&skin_bg=000000&skin_fg=FFFFFF';
					    	break;
					    case "SoundCloud track":
					    	$player = 'http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F' . $thumbnails[0]['id'] . '&amp;color=3b5998&amp;auto_play=true';
					    	$player_html5 = 'https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F' . $thumbnails[0]['id'];
					    	break;
					    case "SoundCloud playlist":
					    	$player = 'http://player.soundcloud.com/player.swf?url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F' . $thumbnails[0]['id'] . '&amp;color=3b5998&amp;auto_play=true&amp;show_artwork=false&amp;origin=facebook';
					    	$player_html5 = 'https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F4498423' . $thumbnails[0]['id'];
					    	break;
					     case "Ustream":
					    	$player = 'http:\/\/www.ustream.tv\/flash\/viewer.swf?cid=' . $thumbnails[0]['id'] . '&v3=1&bgcolor=000000&campaign=facebook';
					    	$player_html5 = 'https://www.ustream.tv/embed/' . $thumbnails[0]['id'];
					    	break;
					     case "Vimeo":
					    	$player = 'http://vimeo.com/moogaloop.swf?clip_id' . $thumbnails[0]['id'];
					    	$player_html5 = 'https://player.vimeo.com/video/' . $thumbnails[0]['id'];
					    	break;
					    case "YouTube":
					        $player = 'http://www.youtube.com/v/' . $thumbnails[0]['id'] . '?autohide=1&amp;version=3';
					        $player_html5 = 'https://www.youtube.com/embed/' . $thumbnails[0]['id'];
					        break;
					}
					if (isset($player)) {
						$opengraph_meta['og:video'] = $player;
						$opengraph_meta['og:video:width'] = $thumbnails[0]['w'];
						$opengraph_meta['og:video:height'] = $thumbnails[0]['h'];
						if (isset($options['add_twitter_meta'])) {
							$twitter_meta['twitter:card'] = "player"; // overwrite previous value
							if(isset($player_html5)) {
								$twitter_meta["twitter:player"] = $player_html5;
							} else {
								$twitter_meta["twitter:player"] = $player;
							}
							$twitter_meta["twitter:player:width"] = $thumbnails[0]['w'];
							$twitter_meta["twitter:player:height"] = $thumbnails[0]['h'];
						}
					}					
				}
							
				// Add Facebook ID
				if ($fb_admins = $options['fb_admins']) {
					$facebook_meta['fb:admins'] = $fb_admins;
				}

				// Add Facebook Application ID
				if ($fb_app_id = $options['fb_app_id']) {
					$facebook_meta['fb:app_id'] = $fb_app_id;
				}
				
				// Add Link elements
				if (isset($options['add_link_rel'])) {
					if (($total_img == 0) && (isset($web_thumb))) {
						$ext = pathinfo($web_thumb, PATHINFO_EXTENSION);
						if (($ext == "jpg") || ($ext == "jpe")) {
							$ext = "jpeg";
						}
						$link_rel = "<link rel=\"image_src\" type=\"image/$ext\" href=\"$web_thumb\" />\n";
					} else if ($thumbnails) { // investigate?
						foreach ($thumbnails as $thumbnail) {
							if ($thumbnail) {
								$ext = preg_replace('/(?!.*\.(bmp|gif|jpe|jpeg|jpg|png|webp))(\?|&).*\Z/i', '', $thumbnail['img']); // remove suffix (might need improvement)
								$ext = pathinfo($ext, PATHINFO_EXTENSION);
								if (($ext == "jpg") || ($ext == "jpe")) {
									$ext = "jpeg";
								}
								if (($ext == "bmp") || ($ext == "gif") || ($ext == "jpeg") || ($ext == "png") || ($ext == "webp")) {
									$link_rel = $link_rel . "<link rel=\"image_src\" type=\"image/$ext\" href=\"" . $thumbnail['img'] . "\" />\n";
								}
							}
						}
					}
				}

				unset($thumbnails); // saving tiny amounts of RAM

				// write user agent to description
				if (isset($options['ua_testdrive'])) {
					$opengraph_meta['og:description'] = $user_agent;
					if (isset($options['add_google_meta'])) {
						$google_meta['description'] = $user_agent;
					}
					if (isset($options['add_twitter_meta'])) {
						$twitter_meta['twitter:description'] = $user_agent;
					}
				}
				
				// Print Open Graph tags
				if (( (isset($options['limit_opengraph'])) && (preg_match(FACEBOOK_USERAGENT, $user_agent)) ) || ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) || (!isset($options['limit_opengraph'])) ) {
					if(is_array($opengraph_meta['og:image'])) {
						$opengraph_meta['og:image'] = array_unique($opengraph_meta['og:image']); // unlikely, but hey!
					}
					foreach($opengraph_meta as $key => $value) {
						if ($key == "og:image") {
							foreach($opengraph_meta['og:image'] as $val_image) {
								print "<meta property=\"$key\" content=\"$val_image\" />\n";
							}
						} else {
							print "<meta property=\"$key\" content=\"$value\" />\n";
						}
					}
					unset($opengraph_meta); // saving tiny amounts of RAM
				}
				
				// Print Facebook-specific tags
				if( (preg_match(FACEBOOK_USERAGENT, $user_agent)) || ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) ){
					// Print OG article tags
					foreach($facebook_meta as $key => $value) {
						if ($key == "article:section") {
							foreach($facebook_meta['article:section'] as $value) {
								print "<meta property=\"$key\" content=\"$value\" />\n";
							}
						} else if ($key == "article:tag") {
							foreach($facebook_meta['article:tag'] as $value) {
								print "<meta property=\"$key\" content=\"$value\" />\n";
							}
						} else {
							print "<meta property=\"$key\" content=\"$value\" />\n";
						}
					}
					unset($facebook_meta); // saving tiny amounts of RAM
				}
				
				// Print Twitter Cards
				if ((isset($options['add_twitter_meta'])) && ((preg_match(TWITTER_USERAGENT, $user_agent)) || ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) )) {
					foreach($twitter_meta as $key => $value) {
						print "<meta name=\"$key\" content=\"$value\" />\n";
					}
					unset($twitter_meta); // saving tiny amounts of RAM
				}

				// Print Google+ Meta
				if ((isset($options['add_google_meta'])) && ((preg_match(GOOGLEPLUS_USERAGENT, $user_agent)) || ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) )))
					foreach($google_meta as $key => $value) {
						print "<meta name=\"$key\" content=\"$value\" />\n";
					}
					unset($google_meta); // saving tiny amounts of RAM
				if (isset($options['add_link_rel'])) {
					print $link_rel;
				}

			}

		} // end of ographr_main_dish	
		
		
	public function get_widget_thumbnails($markup) {		
		
		//global $options;
		$options = get_option('ographr_options');

		$blog_url = get_option('home');
			
		// Get images in post
		if (isset($options['add_post_images'])) {
			preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
			foreach($matches[1] as $match) {
			  	if(( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && (is_single()) || (is_front_page())) {
					print "\t Image tag: $match\n";
				}
			
				$no_smilies = FALSE;
				$no_themes = FALSE;
				$no_plugins = FALSE;
				$no_uploads = FALSE;
				$no_includes = FALSE;
				$no_gravatar = FALSE;
				$no_custom_url = TRUE;
			
				// filter WordPress smilies
				preg_match('/\/wp-includes\/images\/smilies\/icon_.+/i', $match, $filter);
				if ((!isset($options['filter_smilies'])) || (!$filter[0])) {
					$no_smilies = TRUE;
				}
			
				// filter WordPress theme images
				preg_match('/\/wp-content\/themes\//i', $match, $filter);
				if ((!isset($options['filter_themes'])) || (!$filter[0])) {
					$no_themes = TRUE;
				}
			
				// filter WordPress plug-in images
				preg_match('/\/wp-content\/plugins\//i', $match, $filter);
				if ((!isset($options['filter_plugins'])) || (!$filter[0])) {
					$no_plugins = TRUE;
				}

				// filter WordPress upload directory
				$upload_dir = wp_upload_dir();
				$pattern = str_replace($blog_url, NULL, $upload_dir['baseurl']);
				$pattern = str_replace("/", "\/", $pattern);
				preg_match("/$pattern\//i", $match, $filter);
				if ((!isset($options['filter_uploads'])) || (!$filter[0])) {
					$no_uploads = TRUE;
				}

				// filter WordPress include directory
				preg_match('/\/wp-includes\//i', $match, $filter);
				if ((!isset($options['filter_includes'])) || (!$filter[0])) {
					$no_includes = TRUE;
				}
			
				// filter Gravatar
				$pattern = '/https?:\/\/w*.?gravatar.com\/avatar\/.*/i';
				preg_match($pattern, $match, $filter);
				if ((!isset($options['filter_gravatar'])) || (!$filter[0])) {
					$no_gravatar = TRUE;
				}
			
				// filter custom URLs
				foreach(preg_split("/((\r?\n)|(\n?\r))/", preg_quote($options['filter_custom_urls'], '/')) as $line) {
					preg_match("/$line/", $match, $filter);
					foreach($filter as $key => $value) {
						if ($value) {
							$no_custom_url = FALSE;						
						}
					}				
				}
			
				if (($no_gravatar) && ($no_themes) && ($no_plugins) && ($no_uploads) && ($no_includes) && ($no_smilies) && ($no_custom_url)) {
					if (isset($match)) {
						$match = $this->ographr_rel2abs($match, $blog_url);
						if ($options['exec_mode'] == 1)  {
							$exists = $this->remote_exists($match);
							if($exists)
								$thumbnails[]['img'] = $match;
						} else {
							$thumbnails[]['img'] = $match;
						}
					}
				}
			
			}
		}
		
		// Get video poster
		if (isset($options['enable_videoposter'])) {
			preg_match_all('/<video.+?poster=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
			foreach($matches[1] as $match) {
				$match = $this->ographr_rel2abs($match, $blog_url);
			  	if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && (is_single()) || (is_front_page()) ) {
					print "\t Video poster: $match\n";
				}
			
				if (isset($match)) {
					if ($options['exec_mode'] == 1)  {
						$exists = $this->remote_exists($match);
						if(($exists) && (!$match)) {
							$thumbnails[]['img'] = $match;
						}
					} else {
						$thumbnails[]['img'] = $match;
					}
				}
			}
		}

		// Get featured image
		if ( (isset($options['add_post_thumbnail'])) && ( function_exists( 'has_post_thumbnail' )) ){ 
			$website_thumbnail = $this->get_featured_img();
			
			if (isset($website_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($website_thumbnail);
					if(($exists) && (!$website_thumbnail))
						if ($options['debug_level'] > 0) {
							print "\t Post thumbnail: $website_thumbnail\n";
						}
						$thumbnails[] = $website_thumbnail;
				} else { // not sure why, think i read remote_exists is slow
					if ($options['debug_level'] > 0) {
						print "\t Post thumbnail: $website_thumbnail\n";
					}
					$thumbnails[]['img'] = $website_thumbnail;
				}
			}
		}

		// Get attachment images
		if (isset($options['add_attached_image'])) {
			$attached_thumbnails = $this->get_attached_img();

			if (isset($attached_thumbnails)) {
				foreach ($attached_thumbnails as $attached_thumbnail) {
					// investigate, produces error when exec_mode == 1
					if ( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && ($options['exec_mode'] == 2)) {
						print "\t Attached image: $attached_thumbnail[0]\n";
					}
					$thumbnails[]['img'] = $attached_thumbnail[0];
				}
			}
		}	
		
		// JWPlayer
		if (isset($options['enable_jwplayer'])) {
			preg_match_all('/jwplayer\(.*?(?:image:[\s]*?)[\'"]([^\'"]+)[\'"].*?\)/smi', $markup, $matches);

			foreach($matches[1] as $match) {
				$match = $this->ographr_rel2abs($match, $blog_url);
				if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && (is_single()) || (is_front_page())) {
					print "\t JW Player: $match\n";
				}
			
				if (isset($match)) {
					if ($options['exec_mode'] == 1)  {
						$exists = $this->remote_exists($match);
						if(($exists) && (!$match)) {
							$thumbnails[]['img'] = $match;
						}
					} else {
						$thumbnails[]['img'] = $match;
					}
				}
			}
		}
		
		// NVBPlayer
		if (isset($options['enable_nvbplayer'])) {
			preg_match_all('/(?:nvb.addVariable\([\'"]image_src[\'"],[\s])[\'"]([^\'"]+)[\'"].*?\)/smi', $markup, $matches);

			foreach($matches[1] as $match) {
				if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && (is_single()) || (is_front_page())) {
					print "\t NVB Player: $match\n";
				}
			
				if (isset($match)) {
					if ($options['exec_mode'] == 1)  {
						$exists = $this->remote_exists($match);
						if(($exists) && (!$match)) {
							$thumbnails[]['img'] = $match;
						}
					} else {
						$thumbnails[]['img'] = $match;
					}
				}
			}
		}

		// services
		$services = array(
					'etracks' => array(
									'name' => '8tracks',
									'patterns' => array(
										'/8tracks.com\/mixes\/([0-9]+)\/player/i',
										'/\[8tracks.*?url="https?:\/\/w*.?8tracks.com\/mixes\/([0-9]+)"/i'
									),
									'url' => 'http://8tracks.com/mixes/%MATCH%.jsonp?api_key=' . $options['etracks_api'],
									'queries' => array(
										'img' => 'mix->cover_urls->' . ETRACKS_IMAGE_SIZE,
										'wd' => 400,
										'hd' => 400
									),
								),
					'bambuser' => array(
									'name' => 'Bambuser',
									'patterns' => array(
										'/static.bambuser.com\/r\/player.swf\?vid=([0-9]+)/i',
										'/embed.bambuser.com\/broadcast\/([0-9]+)/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?bambuser.com\/v\/([0-9]+)/i',
									),
									'url' => 'http://api.bambuser.com/broadcast/%MATCH%.json?api_key=' . $options['bambuser_api'],									
									'queries' => array(
										'img' => 'result->preview',
										'w' => 'result->width',
										'h' => 'result->height',
									),
								),
					'bandcamp_album' => array(
									'name' => 'Bandcamp album',
									'patterns' => array(
										'/bandcamp.com\/EmbeddedPlayer\/(?:v=2\/)?album=([0-9]+)\//i',
									),
									'url' => 'http://api.bandcamp.com/api/album/2/info?album_id=%MATCH%&key=' . $options['bandcamp_api'],
									'queries' => array(
										'img' => BANDCAMP_IMAGE_SIZE,
										'wd' => 400,
										'hd' => 105,
									),
								),
					'bandcamp_track' => array(
									'name' => 'Bandcamp track',
									'patterns' => array(
										'/bandcamp.com\/EmbeddedPlayer\/(?:v=2\/)?track=([0-9]+)\//i',
									),
									'url' => 'http://api.bandcamp.com/api/album/2/info?album_id=%MATCH%&key=' . $options['bandcamp_api'],
									'queries' => array(
										'img' => BANDCAMP_IMAGE_SIZE,
										'wd' => 400,
										'hd' => 105,
									),
								),
					'dailymotion' => array(
									'name' => 'Dailymotion',
									'patterns' => array(
										'#<object[^>]+>.+?https?://w*.?dailymotion.com/swf/video/([A-Za-z0-9-_]+).+?</object>#s',
										'#//w*.?dailymotion.com/embed/video/([A-Za-z0-9-_]+)#s',
										'/\[dailymotion.*?]https?:\/\/w*.?dailymotion.com\/video\/([A-Za-z0-9-_]+)\[\/dailymotion]/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?dailymotion.com\/video\/([A-Za-z0-9-_]+)/i',
									),
									'url' => 'https://api.dailymotion.com/video/%MATCH%?fields=thumbnail_url',
									'queries' => array(
										'img' => 'thumbnail_url',
										'wd' => 480,
										'hd' => 360
									),
								),
					'flickr' => array(
									'name' => 'Flickr',
									'patterns' => array(
										'/<object.*?data=\"http:\/\/www.flickr.com\/apps\/video\/stewart.swf\?.*?>(.*?photo_id=([0-9]+).*?)<\/object>/smi'
									),
									'url' => 'http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=%MATCH%&format=json&nojsoncallback=1&api_key=' . $options['flickr_api'],
									'queries' => array(
										'img' => NULL
									),
								),
					'hulu' => array(
									'name' => 'Hulu',
									'patterns' => array(
										'/hulu.com\/embed\/([A-Za-z0-9\-_]+)/i'
									),
									'url' => 'http://www.hulu.com/api/oembed.json?url=http://www.hulu.com/embed/%MATCH%',
									'queries' => array(
										'img' => 'thumbnail_url',
										'w' => 'large_thumbnail_width',
										'h' => 'large_thumbnail_height',
									),
								),
					'internetarchive' => array(
									'name' => 'Internet Archive',
									'patterns' => array(
										'/archive.org\/embed\/([A-Za-z0-9]+)/i'
									),
									'url' => 'http://archive.org/details/%MATCH%&output=json',
									'query' => 'misc->image',
									'queries' => array(
										'img' => 'misc->image',
									),
								),
					'justintv' => array(
									'name' => 'Twitch',
									'patterns' => array(
										'/(?:twitch).tv\/widgets\/live_embed_player.swf\?channel=([A-Za-z0-9-_]+)/i'
									),
									'url' => 'http://api.justin.tv/api/stream/list.json?channel=%MATCH%',
									'queries' => array(
										'img' => 'channel->' . JUSTINTV_IMAGE_SIZE
									),
								),
					'livestream' => array(
									'name' => 'Livestream',
									'patterns' => array(
										'/cdn.livestream.com\/embed\/([A-Za-z0-9\-_]+)/i'
									),
									'url' => 'http://thumbnail.api.livestream.com/thumbnail?name==%MATCH%',
									'queries' => array(
										'img' => NULL
									),
								),
					'mixcloud' => array(
									'name' => 'Mixcloud',
									'patterns' => array(
										'/mixcloudLoader.swf\?feed=https?(?:%3A%2F%2F|:\/\/)www.mixcloud.com(?:%2F|\/)([A-Za-z0-9\-_\/\%]+)/i',
										'/iframe\/\?feed=https?(?:%3A%2F%2F|:\/\/)www.mixcloud.com(?:%2F|\/)([A-Za-z0-9\-_\/\%]+)/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?mixcloud.com\/([a-z0-9\-_\/\%]+)/i',
									),
									'url' => 'http://api.mixcloud.com/%MATCH%',
									'queries' => array(
										'img' => 'pictures->' . MIXCLOUD_IMAGE_SIZE,
										'wd' => 460,
										'hd' => 460,
									),
								),
					'myvideo' => array(
									'name' => 'MyVideo',
									'patterns' => array(
										'/myvideo.(?:at|be|ch|de|nl|ro)\/(?:movie|embed)\/([0-9]+)/i'
									),
									'url' => 'https://api.myvideo.de/prod/mobile/api2_rest.php?method=myvideo.videos.get_details&movie_id=%MATCH%&o_format=json&dev_id=' . $options['myvideo_dev_api'] . '&website_id=' . $options['myvideo_web_api'],
									'queries' => array(
										'img' => 'response->myvideo->movie->movie_thumbnail'
									),
								),
					'official' => array(
									'name' => 'Official.fm',
									'patterns' => array(
										'/official.fm(?:%2F%2F|\/\/)feed(?:%2F|\/)tracks(?:%2F|\/)([A-Za-z0-9]+)/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?official.fm\/tracks\/([A-Za-z0-9\-_]+)/i',
									),
									'url' => 'http://api.official.fm/tracks/%MATCH%?fields=cover&api_version=2',
									'queries' => array(
										'img' => 'track->cover->urls->' . OFFICIAL_IMAGE_SIZE,
										'wd' => 440,
										'hd' => 240
									),
								),
					'soundcloud_track' => array(
									'name' => 'SoundCloud track',
									'patterns' => array(
										'/api.soundcloud.com(?:%2F|\/)tracks(?:%2F|\/)([0-9]+)/i',
									),
									'url' => 'http://api.soundcloud.com/tracks/%MATCH%.json?client_id=' . $options['soundcloud_api'],
									'queries' => array(
										'img' => 'artwork_url',
										'wd' => 460,
										'hd' => 98,
									),
								),
					'soundcloud_playlist' => array(
									'name' => 'SoundCloud playlist',
									'patterns' => array(
										'/api.soundcloud.com(?:%2F|\/)playlists(?:%2F|\/)([0-9]+)/i'
									),
									'url' => 'http://api.soundcloud.com/playlists/%MATCH%.json?client_id=' . $options['soundcloud_api'],
									'queries' => array(
										'img' => 'artwork_url'
									),
								),
					'spotify' => array(
									'name' => 'Spotify',
									'patterns' => array(
										'/embed.spotify.com\/\?uri=spotify:((album|artist|track):([A-Za-z0-9]+))/i' // https://embed.spotify.com/?uri=spotify:track:5e6574xvOP2cKCeLQOVRs3
									),
									'url' => 'https://embed.spotify.com/oembed/?url=spotify:%MATCH%', // https://embed.spotify.com/oembed/?url=spotify:artist:7ae4vgLLhir2MCjyhgbGOQ
									'queries' => array(
										'img' => 'thumbnail_url'
									),
								),
					'ustream' => array(
									'name' => 'Ustream',
									'patterns' => array(
										'/ustream.tv\/(?:embed\/|embed\/recorded\/)([0-9]+)/i'
									),
									'url' => 'http://api.ustream.tv/json/channel/%MATCH%/getInfo?key=' . $options['ustream_api'],
									'queries' => array(
										'img' => 'results->imageUrl->' . USTREAM_IMAGE_SIZE
									),
								),
					'vimeo' => array(
									'name' => 'Vimeo',
									'patterns' => array(
										'#<object[^>]+>.+?https?://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s',
										'#//player.vimeo.com/video/([0-9]+)#s',
										'/\[vimeo.*?]https?:\/\/w*.?vimeo.com\/([0-9]+)\[\/vimeo]/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?vimeo.com\/([A-Za-z0-9\-_]+)/i',
									),
									'url' => 'http://vimeo.com/api/v2/video/%MATCH%.json',
 									'queries' => array(
										'img' => VIMEO_IMAGE_SIZE,
										'w' => 'width',
										'h' => 'height'
									),
								),
					'youtube' => array(
									'name' => 'YouTube',
									'patterns' => array(
										'#<object[^>]+>.+?https?://w*.?youtube.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s',
										'#//w*.?(?:youtube.com|youtube-nocookie.com)/embed/([A-Za-z0-9\-_]+)#s',
										'/\[youtube.*?]https?:\/\/w*.?youtube.com\/watch\?v=([A-Za-z0-9\-_]+).+?\[\/youtube]/i',
										'/^(?:href\=){0,1}https?:\/\/w*.?(?:youtube.com|youtu.be|youtube-nocookie.com)\/(?:watch\/\?v=|v\/)([A-Za-z0-9\-_]+)/i',
									),
									'url' => 'http://img.youtube.com/vi/%MATCH%',
									'queries' => array(
										'img' => NULL,
										'wd' => 480,
										'hd' => 360,
									),
								),

					);

		// media widgets
		foreach($services as $key => $value) {
			// SoundCloud special treatment
			if(($key == "soundcloud_track") || ($key == "soundcloud_playlist")) {
				$enabled = 'enable_soundcloud';
			// Bandcamp special treatment
			} else if(($key == "bandcamp_album") || ($key == "bandcamp_track")) {
				$enabled = 'enable_bandcamp';
			} else {
				$enabled = 'enable_' . $key;
			}
			
			if (isset($options[$enabled])) {
				$tmp_thumbnails = $this->find_json_widgets($markup, $services[$key], $options);
				if (isset($tmp_thumbnails)) {
					foreach ($tmp_thumbnails as $tmp_thumbnail) {
						if ($tmp_thumbnail)
							$thumbnails[] = $tmp_thumbnail;
					}
				}
			}

			/* SHAKEN GRID THEME
				$post_id = get_the_ID();
				$soy_vid_url = get_post_meta($post_id, 'soy_vid_url', true);
				$tmp_thumbnails = $this->find_json_widgets($soy_vid_url, $services[$key], $options);
				if (isset($tmp_thumbnails)) {
					foreach ($tmp_thumbnails as $tmp_thumbnail) {
						if ($tmp_thumbnail)
							$thumbnails[] = $tmp_thumbnail;
					}
				}
			}
			*/

		}

		return $thumbnails;
	}	// end get_widget_thumbnails

	public function find_json_widgets($markup, $services, $options) {

		//init array
		$matches = array();

		foreach($services['patterns'] as $pattern) {
			preg_match_all( $pattern, $markup, $tmp_matches );
			
			// Flickr special treatment
			if($services['name'] == "Flickr") {
				$matches = array_merge($matches, $tmp_matches[2]);
			} else {
				$matches = array_merge($matches, $tmp_matches[1]);
			}
			
			$matches = array_unique($matches);

		}
		
		// Now if we've found an embed code, let's get the thumbnail URL
		foreach($matches as $match) {
			
		 	// Mixcloud special treatment
			if($services['name'] == "Mixcloud") {
				$match = str_replace('%2F', '/', $match);
			}

			$json_url = str_replace('%MATCH%', $match, $services['url']);
			
			$json_thumbnail['service'] = $services['name'];
			$json_thumbnail['id'] = $match;

			// Livestream special treatment
			if($services['name'] == "Livestream") {
				$json_thumbnail['img'] = $json_url;
			// YouTube special treatment
			} else if($services['name'] == "YouTube") {
				$json_thumbnail['img'] = "http://img.youtube.com/vi/$match";
			} else {
				// Bandcamp special treatment
				if($services['name'] == "Bandcamp track") {
					$tmp_url = "http://api.bandcamp.com/api/track/1/info?track_id=$match&key=" . $options['bandcamp_api'];
					$tmp_query = "album_id";
					$match = $this->get_json_thumbnail($services, $tmp_url, $tmp_query);
					$json_url = str_replace('%MATCH%', $match, $services['url']);
				}
				// Flickr special treatment
				if($services['name'] == "Flickr") {
					$json_thumbnail = $this->get_json_thumbnail($services, $json_url, NULL);
				} else {
					$json_thumbnail['img'] = $this->get_json_thumbnail($services, $json_url, $services['queries']['img']);

					if(isset($services['queries']['wd'])) {
						$json_thumbnail['w'] = $services['queries']['wd'];
					} else if(isset($services['queries']['w'])) {
						$json_thumbnail['w'] = $this->get_json_thumbnail($services, $json_url, $services['queries']['w']);
					}

					if(isset($services['queries']['hd'])) {
						$json_thumbnail['h'] = $services['queries']['hd'];
					} else if(isset($services['queries']['h'])) {
						$json_thumbnail['h'] = $this->get_json_thumbnail($services, $json_url, $services['queries']['h']);
					}
				}
			}
			
			// Official.fm special treatment
			if($services['name'] == "Official.fm") {
				$tmp = substr($json_thumbnail['img'], 0, 2);
				if($tmp == "//") {
					$json_thumbnail['img'] = "http:" . $json_thumbnail['img'];
				}
			}

			// SoundCloud special treatment
			if( ($services['name'] == "SoundCloud track") || ($services['name'] == "SoundCloud playlist") ){
				$json_thumbnail['img'] = str_replace('-large.', '-' . SOUNDCLOUD_IMAGE_SIZE . '.', $json_thumbnail['img']); // replace 100x100 default image
			}

			// Spotify special treatment
			if($services['name'] == "Spotify") {
				$json_thumbnail['img'] = str_replace('cover', SPOTIFY_IMAGE_SIZE, $json_thumbnail['img']); 
			}
			
			// debugger output
			if( ( ($options['debug_level'] > 0) && (current_user_can('edit_plugins')) ) && (is_single()) || (is_front_page())) {
				if(isset($json_thumbnail['img'])){
					print "\n\t [" . $services['name'] . "]\n";
					print "\t ID: $match\n";
					// YouTube special treatment
					if($services['name'] == "YouTube") {
						print "\t Request: http://img.youtube.com/vi/" . $match . "\n";
						if (isset($options['add_embeds'])) {
							print "\t Image: ".$json_thumbnail['img']."/0.jpg\n";
						} else {
							print "\t Image #1: ".$json_thumbnail['img']."/0.jpg\n";
							print "\t Image #2: ".$json_thumbnail['img']."/1.jpg\n";
							print "\t Image #3: ".$json_thumbnail['img']."/2.jpg\n";
						}
					} else {
						print "\t Request: $json_url\n";
						print "\t Image: ".$json_thumbnail['img']."\n";
						if( (isset($json_thumbnail['w'])) && (isset($json_thumbnail['h'])) ) {
							print "\t Dimensions: " . $json_thumbnail['w'] . "×" .$json_thumbnail['h']. "\n";
						}
					}
					
				} else {
					print "\n\t ERROR: " . $services['name'] . " request failed ($json_url)\n";
				}
			}
			
			if (isset($json_thumbnail['img'])) {
				// YouTube special treatment
				if($services['name'] == "YouTube") {
					$json_thumbnails[] = array(
						'service' => $services['name'],
						'id' => $match,
						'img' => $json_thumbnail['img'] . '/0.jpg',
						'w' => $services['queries']['wd'],
						'h' => $services['queries']['hd'],
					);
					if (!isset($options['add_embeds'])) {
						$json_thumbnails[]['img'] = $json_thumbnail['img'] . '/1.jpg';
						$json_thumbnails[]['img'] = $json_thumbnail['img'] . '/2.jpg';	
					}
					
				} else if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($json_thumbnail['img']);
					if($exists) {
						$json_thumbnails[] = $json_thumbnail;
					}
				} else {
					$json_thumbnails[] = $json_thumbnail;
				}
			}
		}

		if (isset($json_thumbnails))
			return $json_thumbnails;
	} //end find_json_widgets	
	
	// initialize
	public function ographr_core_init() {
		//global $options;
		$options = get_option('ographr_options');
		
		if( empty($options) ) {
			$options = $this->ographr_set_defaults();
		}			

		// Get API keys
		if ( (!$options['etracks_api']) || (!$options['bambuser_api']) || (!$options['flickr_api']) || (!$options['myvideo_dev_api']) || (!$options['myvideo_web_api']) || (!$options['soundcloud_api']) || (!$options['ustream_api']) || ($options['last_update'] != OGRAPHR_VERSION) ) {
			if (!$options['etracks_api']) { $options['etracks_api'] = ETRACKS_API_KEY; }
			if (!$options['bambuser_api']) { $options['bambuser_api'] = BAMBUSER_API_KEY; }
			if (!$options['flickr_api']) { $options['flickr_api'] = FLICKR_API_KEY; }
			if (!$options['myvideo_dev_api']) { $myvideo_dev_api = $options['myvideo_dev_api']; }
			if (!$options['myvideo_web_api']) { $myvideo_web_api = $options['myvideo_web_api']; }
			if (!$options['soundcloud_api']) { $options['soundcloud_api'] = SOUNDCLOUD_API_KEY; $soundcloud_api = $options['soundcloud_api']; }
			if (!$options['ustream_api']) { $options['ustream_api'] = USTREAM_API_KEY; $ustream_api = $options['ustream_api']; }
			
			//upgrades
			if(isset($options['last_update'])) {
				if (version_compare($options['last_update'], "0.8", '<')) {
					if (isset($options['enable_eight_tracks'])) {
						$options['enable_etracks'] = $options['enable_eight_tracks'];
						unset($options['enable_eight_tracks']);
					}
				}
				
				if (version_compare($options['last_update'], "0.8.6", '<')) {
					$published = wp_count_posts();
					$published = $published->publish;
					$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_twitter_image' );
					$ographr_urls = get_posts( $args );
					foreach($ographr_urls as $ographr_url) {
						$ographr_id = $ographr_url->ID;
						$tmp = get_post_meta($ographr_id, 'ographr_twitter_image', true);
						update_post_meta($ographr_id, 'ographr_primary_image', $tmp);
						delete_post_meta($ographr_id, 'ographr_twitter_image');
					}
				}

				if (version_compare($options['last_update'], "0.8.9", '<')) {
					//delete old, incompatible index data
					$published = wp_count_posts();
					$published = $published->publish;
					$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_urls' );
					$ographr_urls = get_posts( $args );
					foreach($ographr_urls as $ographr_url) {
						$ographr_id = $ographr_url->ID;
						delete_post_meta($ographr_id, 'ographr_urls');
						delete_post_meta($ographr_id, 'ographr_indexed');
					}
				}

				if (version_compare($options['last_update'], "0.8.19", '<')) {
					$options['enable_spotify'] = 1;
				}
			}

			//save current version to db
			$options['last_update'] = OGRAPHR_VERSION;

			update_option('ographr_options', $options);
		}
	}

	// check for PHP version when activation
	public function ographr_activate( ) {
		$v = '5.2';
	    if (! version_compare( PHP_VERSION, $v, '<' ) ) {
	        return;
	    }

	    deactivate_plugins( basename( __FILE__ ) );
	    wp_die("The <strong>OGraphr</strong> plug-in could not be activated, for it requires PHP $v (or later).", "Plugin Activation Error");
	}

	// Add Open Graph prefix to HTML tag
	public function ographr_namespace($attr) {
		$options = get_option('ographr_options');
		
		if(isset($options['add_prefix'])) {
			$attr .= " prefix=\"og: http://ogp.me/ns#\""; 
		}

        return $attr;
	}
	
	// Display a Settings link on the OGraphr Plugins page
	public function ographr_plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$ographr_links = '<a href="'.get_admin_url().'options-general.php?page=meta-ographr/admin.php">' .__('Settings').'</a>';

			// make the 'Settings' link appear first
			array_unshift( $links, $ographr_links );
		}

		return $links;
	}

	public function ographr_rel2abs($rel, $base) {
	    /* return if already absolute URL */
	    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

	    /* queries and anchors */
	    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

	    /* parse base URL and convert to local variables:
	       $scheme, $host, $path */
	    extract(parse_url($base));

	    /* remove non-directory element from path */
	    $path = preg_replace('#/[^/]*$#', '', $path);

	    /* destroy path if relative url points to root */
	    if ($rel[0] == '/') $path = '';

	    /* dirty absolute URL */
	    $abs = "$host$path/$rel";

	    /* replace '//' or '/./' or '/foo/../' with '/' */
	    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
	    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

	    /* absolute URL is ready! */
	    return $scheme.'://'.$abs;
	}

	public function ographr_admin_notice(){
	    //global $options;
		$options = get_option('ographr_options');

		// Debug
		if (($options['debug_level'] > 0) && (current_user_can('manage_options'))) {
			echo '<div class="error">
	       		<p>OGraphr is currently running in debug mode.';
       		if(WP_DEBUG == TRUE) {
       			echo ' You can disable it in the <a href="'. get_admin_url() . 'options-general.php?page=meta-ographr/admin.php#developer_settings">developer settings</a>!</p></div>';
       		} else {
       			echo ' Activate the developer mode in the <a href="'.get_admin_url().'plugin-editor.php?file=meta-ographr%2Findex.php&plugin=meta-ographr%2Findex.php">plug-in editor</a> to control your debug level!</p></div>';
       		}
	       		
		} else if( (isset($options['enable_beta'])) && ($options['enable_beta'] != 0) && (current_user_can('manage_options')) ) {
			echo '<div class="updated">
	       		<p>OGraphr is currently running with beta features enabled.';
	    	if(WP_DEBUG == TRUE) {
       			echo ' You can disable it in the <a href="'. get_admin_url() . 'options-general.php?page=meta-ographr/admin.php#developer_settings">developer settings</a>!</p></div>';
       		} else {
       			echo ' Activate the developer mode in the <a href="'.get_admin_url().'plugin-editor.php?file=meta-ographr%2Findex.php&plugin=meta-ographr%2Findex.php">plug-in editor</a> to control beta features!</p></div>';
       		}
		}
	}


	// Save thumbnails as postdata
	public function ographr_save_postmeta($post_id) {
		global $core;

		$options = get_option('ographr_options');

		if($options['exec_mode'] == 2) {
			return;
		}

		$post_array = get_post($post_id); 
		$markup = $post_array->post_content;
		$markup = apply_filters('the_content',$markup);	

		$widget_thumbnails = $core->get_widget_thumbnails($markup);
		
		// if (is_array($widget_thumbnails))
		// 	foreach($widget_thumbnails as $widget_array)
		// 		foreach($widget_array as $widget_item)
		// 			$widget_item = htmlentities($widget_item);

		if(!(empty($widget_thumbnails))) {
			$widget_thumbnails = json_encode($widget_thumbnails);
			update_post_meta($post_id, 'ographr_urls', $widget_thumbnails);
			
			$indexed = date("U"); //Y-m-d H:i:s
			update_post_meta($post_id, 'ographr_indexed', $indexed);
			$this->ographr_save_stats();
		}
	}
	
	public function ographr_save_stats() {
		
		$stats = get_option('ographr_data');
		
		if(!$stats) {
			$yesterday = strtotime("yesterday");
			$yesterday = date("Y-m-d", $yesterday);		
			$stats[$yesterday] = array(
									'posts_total' => '0',
									'posts_indexed' => '0'
									);
		}
		
		// create function?
		$posts_published = wp_count_posts();
		$posts_published = $posts_published->publish;
		$args = array( 'numberposts' => $posts_published, 'meta_key' => 'ographr_urls' );
		$myposts = get_posts( $args );
		$posts_indexed = count($myposts);
			
		$today = date("Y-m-d");
	
		$stats[$today] = array(
								'posts_total' => $posts_published,
								'posts_indexed' => $posts_indexed
								);

		update_option('ographr_data', $stats);
	}
	
	public function ographr_delete_stats() {
		$stats = get_option('ographr_data');
		
		if($stats) {
			$posts_published = wp_count_posts();
			$posts_published = $posts_published->publish;
			$args = array( 'numberposts' => $posts_published, 'meta_key' => 'ographr_urls' );
			$myposts = get_posts( $args );
			$posts_indexed = count($myposts) - 1;  // line differs from ographr_save_stats!

			$today = date("Y-m-d");

			$stats[$today] = array(
									'posts_total' => $posts_published,
									'posts_indexed' => $posts_indexed
									);

			update_option('ographr_data', $stats);
		}
	}	
	
	public function ographr_admin_bar() {
		//global $options;
		$options = get_option('ographr_options');	
		if (!isset($options['add_adminbar'])) return;
			
		global $wp_admin_bar;

	    if (current_user_can('manage_options')) {				
	    		$published = wp_count_posts();
	    		$published = $published->publish;
	    		$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_urls' );
	    		$myposts = get_posts( $args );
	    		$harvested = count($myposts);
	    		
	    		$menu_items = array(
	    			array(
	    				'id' => 'ographr',
	    				'title' => "OGraphr [$harvested/$published]",
	    				'href' => admin_url('options-general.php?page=meta-ographr/admin.php')
	    			),
	    			array(
	    				'id' => 'ographr-settings',
	    				'parent' => 'ographr',
	    				'title' => 'Settings',
	    				'href' => admin_url('options-general.php?page=meta-ographr/admin.php')
	    			),
	    			array(
	    				'id' => 'ographr-faq',
	    				'parent' => 'ographr',
	    				'title' => 'FAQ',
	    				'href' => 'http://wordpress.org/extend/plugins/meta-ographr/faq/'
	    			),
	    			array(
	    				'id' => 'ographr-support',
	    				'parent' => 'ographr',
	    				'title' => 'Support',
	    				'href' => 'https://wordpress.org/tags/meta-ographr?forum_id=10'
	    			),
	    			array(
	    				'id' => 'ographr-git',
	    				'parent' => 'ographr',
	    				'title' => 'GitHub',
	    				'href' => 'http://github.com/idleberg/OGraphr'
	    			)
	    		);

	    	foreach ($menu_items as $menu_item) {
	    		$wp_admin_bar->add_menu($menu_item);
	    	}
	    }
	}

}; // end of class
?>