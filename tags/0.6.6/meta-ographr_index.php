<?php
/*
Plugin Name: OGraphr
Plugin URI: http://ographr.whyeye.org
Description: This plugin scans posts for videos (YouTube, Vimeo, Dailymotion, Hulu, Blip.tv) and music players (SoundCloud, Mixcloud, Bandcamp, Official.fm) and adds their thumbnails as an OpenGraph meta-tag. While at it, the plugin also adds OpenGraph tags for the title, description (excerpt) and permalink.
Version: 0.6.6
Author: Jan T. Sott
Author URI: http://whyeye.org
License: GPLv2 
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Thanks to Sutherland Boswell, Michael Wöhrer, and Matthias Gutjahr!
*/

// OGRAPHR OPTIONS
    define("OGRAPHR_VERSION", "0.6.6");
	// force output of all values in comment tags
	define("OGRAPHR_DEBUG", FALSE);
	// enables features that are still marked beta
	define("OGRAPHR_BETA", FALSE);
	// replace default description with user agent in use
	define("OGRAPHR_UATEST", TRUE);
	// specify timeout for all cURL instances (http://googlecode.blogspot.co.at/2012/01/lets-make-tcp-faster.html)
	define("OGRAPHR_TIMEOUT", 1000);

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
	define("MIXCLOUD_IMAGE_SIZE", "large");

// OFFICIAL.FM
	// no need to change this unless you want to use your own Official.fm API key (-> http://official.fm/developers/manage#register)
	define("OFFICIAL_API_KEY", "yv4Aj7p3y5bYIhy3kd6X");

// PLAY.FM
	// no need to change this unless you want to use your own Play.fm API key (-> http://www.play.fm/api/account)
	define("PLAYFM_API_KEY", "e5821e991f3b7bc982c3:109a0ca3bc");
	
// SOUNDCLOUD
	// no need to change this unless you want to use your own SoundCloud API key (-> http://soundcloud.com/you/apps)
	define("SOUNDCLOUD_API_KEY", "15fd95172fa116c0837c4af8e45aa702");
	// default artwork size (mini=16x16, tiny=20x20, small=32x32, badge=47x47, t67x67, large=100x100, t300x300, crop=400x400, t500x500)
	define("SOUNDCLOUD_IMAGE_SIZE", "t300x300");
	
// VIMEO
	// default snapshot size (small=100, medium=200, large=640)
	define("VIMEO_IMAGE_SIZE", "medium");
	
// USTREAM
	// no need to change this unless you want to use your own Ustream.fm API key (-> http://developer.ustream.tv/apikey/generate)
	define("USTREAM_API_KEY", "8E640EF9692DE21E1BC4373F890F853C");
	// default artwork size (small=120x90, medium=240x180)
	define("USTREAM_IMAGE_SIZE", "medium");
	
// JUSTIN.TV
	// default snapshot size (small=100, medium=200, large=640)
	define("JUSTINTV_IMAGE_SIZE", "image_url_large");
	
// USER-AGENTS
	// Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)
	define('DIGG_USERAGENT', '/Mozilla\/5\.0 \(compatible; MSIE 8\.0; Windows NT 5\.1; Trident\/4\.0; \.NET CLR 1\.1\.4322; \.NET CLR 2\.0\.50727\)/i');
	// facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)
	define('FACEBOOK_USERAGENT', '/facebookexternalhit/i');
	// Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20110814 Firefox/6.0
	define('GOOGLEPLUS_USERAGENT', '/Mozilla\/5\.0 \(Windows NT 6\.1; rv:6\.0\) Gecko\/20110814 Firefox/i');
	// LinkedInBot/1.0 (compatible; Mozilla/5.0; Jakarta Commons-HttpClient/3.1 +http://www.linkedin.com)
	define('LINKEDIN_USERAGENT', '/LinkedInBot/i');
	
$core = new OGraphr_Core();

add_action('init', array(&$core,'ographr_core_init'));
add_action('wp_head', array(&$core,'ographr_main_dish'));
add_action('save_post', array(&$core,'ographr_save_postmeta'));
add_action('delete_post', array(&$core,'ographr_delete_stats'));
add_action('admin_notices', array(&$core,'ographr_admin_notice'));
add_action('admin_bar_menu', array(&$core,'ographr_admin_bar'), 150);
add_filter('plugin_action_links', array(&$core, 'ographr_plugin_action_links'), 10, 2 );
add_filter('the_content', array(&$core, 'ographr_add_google_snips'));

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/meta-ographr_admin.php';
	
// Get this plugins' settings
$options = get_option('ographr_options');

// Get API keys
if (!$options['etracks_api']) { $options['etracks_api'] = ETRACKS_API_KEY; $etracks_api = $options['etracks_api']; }
if (!$options['bambuser_api']) { $options['bambuser_api'] = BAMBUSER_API_KEY; $bambuser_api = $options['bambuser_api']; }
if (!$options['flickr_api']) { $options['flickr_api'] = FLICKR_API_KEY; $flickr_api = $options['flickr_api']; }
if (!$options['official_api']) { $options['official_api'] = OFFICIAL_API_KEY; $official_api = $options['official_api']; }
if (OGRAPHR_BETA == TRUE )
	if (!$options['playfm_api']) { $options['playfm_api'] = PLAYFM_API_KEY; $playfm_api = $options['playfm_api']; }
if (!$options['soundcloud_api']) { $options['soundcloud_api'] = SOUNDCLOUD_API_KEY; $soundcloud_api = $options['soundcloud_api']; }
if (!$options['ustream_api']) { $options['ustream_api'] = USTREAM_API_KEY; $ustream_api = $options['ustream_api']; }

class OGraphr_Core {	
	
	function remote_exists($path){
		//return (@fopen($path,"r")==true);
		$response = wp_remote_head($path, array('timeout' => 1, 'compress' => TRUE, 'decompress' => TRUE));
		if (!is_wp_error($response)) {
			return ($response[response][code]==200);
		}
	}

	// Featured Image (http://codex.wordpress.org/Post_Thumbnails)
	function get_featured_img() {
		global $post, $posts;
		if (has_post_thumbnail( $post->ID )) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			return $image[0];
	  	}
	}

	// Get JSON Thumbnail
	function get_json_thumbnail($service, $json_url, $json_query) {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			//print "\t $service Query URL: $json_url\n";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $json_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, OGRAPHR_TIMEOUT);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			
			// special treatment
			if ($service == "Blip.tv") {
				$output = preg_match('/(?:blip_ws_results\(\[)(.*)(?:\]\);)/smi', $output, $match); // fix Blip.tv JSON file
				$output = $match[1];
			}
			
			$output = json_decode($output);
			
			// special treatment
			if ($service == "Justin.tv") {
				$output = $output[0];
			} else if ($service == "Flickr") {
				$ispublic = $output->photo->visibility->ispublic;
				if ($ispublic == 1) {
					$id = $output->photo->id;
					$server = $output->photo->server;
					$secret = $output->photo->secret;
					$farm = $output->photo->farm;
					$output = "http://farm" . $farm . ".staticflickr.com/" . $server . "/" . $id . "_" . $secret . "_" . FLICKR_IMAGE_SIZE . ".jpg";
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
			
			if (curl_error($ch) != null) {
				return;
			}
			curl_close($ch); // Moved here to allow curl_error() operation above. Was previously below curl_exec() call.
			//$exists = $this->remote_exists($output);
			//if($exists) {
				return $output;
			//}
		}
	}
		
	// Get Vimeo Thumbnail
	function get_vimeo_thumbnail($id, $image_size = 'large') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$ch = curl_init();
			$videoinfo_url = "http://vimeo.com/api/v2/video/$id.php";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, OGRAPHR_TIMEOUT);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = unserialize(curl_exec($ch));
			$output = $output[0]['thumbnail_' . $image_size];
			if (curl_error($ch) != null) {
				return;
			}
			curl_close($ch);
			return $output;
		}
	}
	
	/*
	// Get Play.fm Thumbnail
	function get_playfm_thumbnail($id, $api_key = PLAYFM_API_KEY) {
		$videoinfo_url = "http://blip.tv/players/episode/$id?skin=rss";
		$xml = simplexml_load_file( $videoinfo_url );
		if ( $xml == false ) {
			return new WP_Error( 'bliptv_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.' ) );
		} else {
			$result = $xml->xpath( "/rss/channel/item/media:thumbnail/@url" );
			$output = (string) $result[0]['url'];
			return $output;
		}
	}
	*/
	
	// Get Bandcamp Parent Thumbnail
	function get_bandcamp_parent_thumbnail($id, $api_key = BANDCAMP_API_KEY, $image_size = 'large_art_url') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$ch = curl_init();
			$videoinfo_url = "http://api.bandcamp.com/api/track/1/info?key=$api_key&track_id=$id";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, OGRAPHR_TIMEOUT);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->album_id;
			if (curl_error($ch) != null) {
				return;
			}
			curl_close($ch);
			
			// once more time for the album
			$ch = curl_init();
			$videoinfo_url = "http://api.bandcamp.com/api/album/2/info?key=$api_key&album_id=$output";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, OGRAPHR_TIMEOUT);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->$image_size;
			if (curl_error($ch) != null) {
				return;
			}
			curl_close($ch);
			
			return $output;
		}
	}

	//
	// The Main Dish
	//
	function ographr_main_dish($post_id=null) {
		
		if(OGRAPHR_DEBUG == TRUE) {
			$s_time = microtime();
		}
	
		global $options;
		
		if ((!$enable_plugin_on_front = $options['enable_plugin_on_front']) && (!is_single()) && (!is_page())) {
			return;
		}
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$digg_ua = $options['digg_ua'];
		$facebook_ua = $options['facebook_ua'];
		$gplus_ua = $options['gplus_ua'];
		$linkedin_ua = $options['linkedin_ua'];
				
		if ( ((preg_match(DIGG_USERAGENT, $user_agent)) && ($digg_ua))
		|| ((preg_match(FACEBOOK_USERAGENT, $user_agent)) && ($facebook_ua))
		|| ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) && ($gplus_ua))
		|| ((preg_match(LINKEDIN_USERAGENT,$user_agent)) && ($linkedin_ua))
		|| ((!$digg_ua) && (!$facebook_ua) && (!$gplus_ua) && (!$linkedin_ua))
		|| (OGRAPHR_DEBUG == TRUE) ) {
			// Get the post ID if none is provided
			if($post_id==null OR $post_id=='') $post_id = get_the_ID();

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
		
			if (($web_thumb) && (!$options['not_always']))
				$thumbnails[] = $web_thumb;		
			
			// Get date et al
			$today = date("U"); // Y-m-d H:i:s
			$last_indexed = get_post_meta($post_id, 'ographr_indexed', true);
			if (!$last_indexed) // set to release date of v0.5.3
				$last_indexed = 1336082400; // 2012-05-04 00:00:00 CET
			
			$interval = $today - $last_indexed;
			$expiry = $options['data_expiry'];
			if (($expiry) && ($expiry != "-1")) {
				$expiry = $expiry * 86400;
			} else {
				$expiry = $today + 86400; // tomorrow never dies
			}
			
			// debugging?
			if(OGRAPHR_DEBUG == TRUE) {
				print "\n<!--\tOGRAPHR v" . OGRAPHR_VERSION ." DEBUGGER\n";
				
				if ($options['exec_mode'] == 1) {
					print "\t Image Retrieval: On-Post\n";
					if ($options['data_expiry'] == -1) {
						print "\t Last indexed on " . date('Y-m-d', $last_indexed) . ", never expires\n";
					} else {
						print "\t Last indexed on " . date('Y-m-d', $last_indexed) . ", expires after " . round($expiry / 86400) ." days\n";
					}
				} else if ($options['exec_mode'] == 2) {
					print "\t Image Retrieval: On-View\n";
				}
				
				
				if (($digg_ua) || ($facebook_ua) || ($gplus_ua) || ($linkedin_ua)) {
					if ($user_agent) { print "\t User Agent: $user_agent\n"; }
					if ($digg_ua) { print "\t Limited to Digg User Agent\n"; }
					if ($facebook_ua) { print "\t Limited to Facebook User Agent\n"; }
					if ($gplus_ua) { print "\t Limited to Google+ User Agent\n"; }
					if ($linkedin_ua) { print "\t Limited to LinkedIn User Agent\n"; }
				}
				
				if ($options['filter_gravatar']) { print "\t Avatars are filtered\n"; }
				if ($options['filter_smilies']) { print "\t Emoticons are filtered \n"; }
				if ($options['filter_themes']) { print "\t Themes are filtered\n"; }
				if ($options['add_google_meta']) { print "\t Google+ Snippets are active\n"; }
				if ($options['add_image_prop']) { print "\t Schema.org image properties are being added\n"; }
				
				if ($options['filter_custom_urls']) {
					foreach(preg_split("/((\r?\n)|(\n?\r))/", $options['filter_custom_urls']) as $line){
						print "\t Custom URL /$line/ is filtered\n";
						}
				}
				
				print "\n"; // an empty line!
				
				if (current_user_can('manage_options')) {
					if ($etracks_api = $options['etracks_api']) { print "\t 8tracks API key: $etracks_api\n"; }
					if ($bambuser_api = $options['bambuser_api']) { print "\t Bambuser API key: $bambuser_api\n"; }
					if ($bandcamp_api = $options['bandcamp_api']) { print "\t Bandcamp API key: $bandcamp_api\n"; }
					if ($flickr_api = $options['flickr_api']) { print "\t Flickr API key: $flickr_api\n"; }
					if ($official_api = $options['official_api']) { print "\t Official.fm API key: $official_api\n"; }
					if (OGRAPHR_BETA == TRUE )
						if ($playfm_api = $options['playfm_api']) { print "\t Play.fm API key: $playfm_api\n"; }
					if ($soundcloud_api = $options['soundcloud_api']) { print "\t SoundCloud API key: $soundcloud_api\n"; }
					if ($ustream_api = $options['ustream_api']) { print "\t Ustream API key: $ustream_api\n"; }
					if ($viddler_api = $options['viddler_api']) { print "\t Viddler API key: $viddler_api\n"; }
				
					print "\n"; // an empty line!
				}
				
				if ($web_thumb) { print "\t Default Thumbnail: $web_thumb\n"; }
			}
			
			// GO!
			if (($enable_triggers_on_front = $options['enable_triggers_on_front']) || (is_single()) || (is_page())) {
				
				// Did we retrieve those images before and did they expire?
				if (($options['exec_mode'] == 1) && ($expiry >= $interval) )  {
					$meta_values = get_post_meta($post_id, 'ographr_urls', true);
					$meta_values = unserialize($meta_values);
					
					if (is_array($meta_values))
						foreach($meta_values as $meta_value)
							$meta_value =  htmlentities($meta_value);
				}

				if ((is_array($meta_values)) && (is_array($thumbnails)) && ($expiry >= $interval) ) {
					$thumbnails = array_merge($thumbnails, $meta_values);
					if(OGRAPHR_DEBUG == TRUE) {
						foreach($thumbnails as $thumbnail) {
							print "\t Post meta: $thumbnail\n";
						}
					}
				} else if ((is_array($meta_values)) && (!is_array($thumbnails)) && ($expiry >= $interval) ) {
					$thumbnails = $meta_values;
					if(OGRAPHR_DEBUG == TRUE) {
						foreach($thumbnails as $thumbnail) {
							print "\t Post meta: $thumbnail\n";
						}
					}
				} else {
					if ((OGRAPHR_DEBUG == TRUE) && ($options['exec_mode'] == 1) && ($expiry >= $interval)) {
						print "\t Empty post-meta, using On-View fallback\n\n";
					} else if ((OGRAPHR_DEBUG == TRUE) && ($options['exec_mode'] == 1) && ($expiry < $interval)) {
						print "\t Data expired, indexing\n\n";
					}
					
					// Get Widget Thumbnails (fallback)
					$widget_thumbnails = $this->get_widget_thumbnails($markup);
					if ((is_array($widget_thumbnails)) && (is_array($thumbnails))) {
						$thumbnails = array_merge($thumbnails, $widget_thumbnails);
					} else if ((is_array($widget_thumbnails)) && (!is_array($thumbnails))) {
						$thumbnails = $widget_thumbnails;
					}
					
					// double checking before writing to db
					$total_img = count($thumbnails);
					
					//write to db for future use
					if (($options['exec_mode'] == 1) && ($total_img >= 1)) {
						if (OGRAPHR_DEBUG == TRUE)
							print "\n\t New data indexed and written to database\n";
						
						if (is_array($thumbnails))
							foreach($thumbnails as $thumbnail)
								$thumbnail =  htmlentities($thumbnail);
						
						if(!(empty($thumbnails))) {
							$thumbnails_db = serialize($thumbnails);
							update_post_meta($post_id, 'ographr_urls', $thumbnails_db);
							$indexed = date("U"); // Y-m-d H:i:s
							update_post_meta($post_id, 'ographr_indexed', $indexed);
							// 0.6 double check
							$this->ographr_save_stats();
						}
					}
				}
				
				
				if(OGRAPHR_DEBUG == TRUE) {	
					print "\n"; // an empty line!
				}			
		}
				// close debugger tag
				if(OGRAPHR_DEBUG == TRUE) {	
					$e_time = microtime();
					$time = $e_time - $s_time;
					print "\t Processed in " . abs($time) . " seconds\n";
					print "-->\n";
				}
								
				// Let's print all this
				if(($options['add_comment']) && (OGRAPHR_DEBUG == FALSE)) {
					print "<!-- OGraphr v" . OGRAPHR_VERSION . " - http://ographr.whyeye.org -->\n";
				}
		
				// Add title & description
				$title = $options['website_title'];
				$site_name = $options['fb_site_name'];
				$wp_title = get_the_title();
				$wp_name = get_bloginfo('name');
				$wp_url = get_option('home');
				//$wp_author = get_the_author_meta('display_name'); // inside of loop!
				$wp_url = preg_replace('/https?:\/\//', NULL, $wp_url);
				$title = str_replace("%postname%", $wp_title, $title);
				$title = str_replace("%sitename%", $wp_name, $title);
				$title = str_replace("%siteurl%", $wp_url, $title);
				//$title = str_replace("%author%", $wp_author, $title); // inside of loop!
				if (!$title) {
					$title = $wp_title;
				}
				$site_name = str_replace("%sitename%", $wp_name, $site_name);
				$site_name = str_replace("%siteurl%", $wp_url, $site_name);
				
				//$title = $user_agent;
			
				if (($options['website_description']) && (is_front_page())) {
					// Blog title
					$title = get_settings('blogname');
					if($title) {
						if (($options['add_google_meta']) && ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) || (OGRAPHR_DEBUG)) )
							print "<meta name=\"title\" content=\"$title\" />\n";
						print "<meta property=\"og:title\" content=\"$title\" />\n";
					}
					// Add custom description
					$description = $options['website_description'];
					$wp_tagline = get_bloginfo('description');
					$description = str_replace("%tagline%", $wp_tagline, $description);
					if($description) {
						if (($options['add_google_meta']) && ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) || (OGRAPHR_DEBUG)) )
							print "<meta name=\"description\" content=\"$description\" />\n";
						print "<meta property=\"og:description\" content=\"$description\" />\n";
					}
				} else { //single posts
					if ($options['add_title'] && ($title)) {
						// Post title
						if (($options['add_google_meta']) && ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) || (OGRAPHR_DEBUG)) )
							print "<meta name=\"title\" content=\"$title\" />\n";
						print "<meta property=\"og:title\" content=\"$title\" />\n"; 
					}
					
					if($options['add_excerpt'] && ($description = wp_strip_all_tags((get_the_excerpt()), true))) {
						// Post excerpt
						if (OGRAPHR_UATEST == TRUE) {
							$description = $user_agent;
						}
						if ( ($options['add_google_meta']) && ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) || (OGRAPHR_DEBUG)) )
							print "<meta name=\"description\" content=\"$description\" />\n";
						print "<meta property=\"og:description\" content=\"$description\" />\n";
					}
				}
		
				// Add permalink
				if (($options['add_permalink']) && (is_front_page()) && ($link = get_option('home'))) {
					print "<meta property=\"og:url\" content=\"$link\" />\n";
				} else {
					if($options['add_permalink'] && ($link = get_permalink())) {
						print "<meta property=\"og:url\" content=\"$link\" />\n";
					}
				}
			
				// Add site name
				if ($site_name) {
					print "<meta property=\"og:site_name\" content=\"$site_name\" />\n";
				}
			
				// Add type
				if (($type = $options['fb_type']) && ($type != '_none')) {
					print "<meta property=\"og:type\" content=\"$type\" />\n";
				}
		
				// Add thumbnails
				if ($thumbnails) { // avoid error message when array is empty
					$thumbnails = array_unique($thumbnails); // unlikely, but hey!
					$total_img = count($thumbnails);
				}
					
				if (($total_img == 0) && ($web_thumb)) {
					print "<meta property=\"og:image\" content=\"$web_thumb\" />\n";
					$ext = pathinfo($web_thumb, PATHINFO_EXTENSION);
					if (($ext == "jpg") || ($ext == "jpe"))
						$ext = "jpeg";
					print "<meta property=\"og:image:type\" content=\"image/$ext\" />\n";
				} else if ($thumbnails) { // investigate?
					foreach ($thumbnails as $thumbnail) {
						if ($thumbnail) {
							//$thumbnail = preg_replace('/\?([A-Za-z0-9_-]+)\Z/', '', $thumbnail); // remove suffix
							print "<meta property=\"og:image\" content=\"$thumbnail\" />\n";
						}
					}
				}
				
				if ($total_img == 1) {
					$ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
					if (($ext == "jpg") || ($ext == "jpe"))
						$ext = "jpeg";
					print "<meta property=\"og:image:type\" content=\"image/$ext\" />\n";
				}
			
				// Add Facebook ID
				if ($fb_admins = $options['fb_admins']) {
					print "<meta property=\"fb:admins\" content=\"$fb_admins\" />\n";
				}

				// Add Facebook Application ID
				if ($fb_app_id = $options['fb_app_id']) {
					print "<meta property=\"fb:app_id\" content=\"$fb_app_id\" />\n";
				}

			}

		} // end of ographr_main_dish	
		
		
	function get_widget_thumbnails($markup) {		
		
		global $options;
		
		// Get images in post
		if ($options['add_post_images']) {
			preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
			foreach($matches[1] as $match) {
			  	if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
					print "\t Image tag: $match\n";
				}
			
				$no_smilies = FALSE;
				$no_themes = FALSE;
				$no_gravatar = FALSE;
				$no_custom_url = TRUE;
			
				// filter Wordpress smilies
				preg_match('/\/wp-includes\/images\/smilies\/icon_.+/i', $match, $filter);
				if ((!$options['filter_smilies']) || (!$filter[0])) {
					//$thumbnails[] = $match;
					$no_smilies = TRUE;
				}
			
				// filter Wordpress theme images
				preg_match('/\/wp-content\/themes\//i', $match, $filter);
				if ((!$options['filter_themes']) || (!$filter[0])) {
					//$thumbnails[] = $match;
					$no_themes = TRUE;
				}
			
				// filter Gravatar
				$pattern = '/https?:\/\/w*.?gravatar.com\/avatar\/.*/i';
				preg_match($pattern, $match, $filter);
				if ((!$options['filter_gravatar']) || (!$filter[0])) {
					//$thumbnails[] = $match;
					$no_gravatar = TRUE;
				}
			
				// filter custom URLs
				foreach(preg_split("/((\r?\n)|(\n?\r))/", preg_quote($options['filter_custom_urls'], '/')) as $line) {
					//print "<!-- \$line=$line -->\n";
					preg_match("/$line/", $match, $filter);
					foreach($filter as $key => $value) {
						if ($value) {
							$no_custom_url = FALSE;						
						}
					}				
				}
			
				if (($no_gravatar) && ($no_themes) && ($no_smilies) && ($no_custom_url)) {
					if (isset($match)) {
						if ($options['exec_mode'] == 1)  {
							$exists = $this->remote_exists($match);
							if($exists)
								$thumbnails[] = $match;
						} else {
							$thumbnails[] = $match;
						}
					}
				}
			
			}
		}
		
		// Get video poster
		if($options['enable_videoposter']) {
			preg_match_all('/<video.+?poster=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
			foreach($matches[1] as $match) {
				$match = preg_replace('/^\/\/+?/', 'http://', $match); // fix Viddler thumbnail URL
			  	if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
					print "\t HTML5 video poster: $match\n";
				}
			
				if (isset($match)) {
					if ($options['exec_mode'] == 1)  {
						$exists = $this->remote_exists($match);
						if(($exists) && (!$match))
							$thumbnails[] = $match;
					} else {
						$thumbnails[] = $match;
					}
				}
			}
		}

		// Get featured image
		if (($options['add_post_thumbnail']) && ( function_exists( 'has_post_thumbnail' )) ){ 
			$website_thumbnail = $this->get_featured_img();

			if (isset($website_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($website_thumbnail);
					if(($exists) && (!$website_thumbnail))
						$thumbnails[] = $website_thumbnail;
				} else {
					$thumbnails[] = $website_thumbnail;
				}
			}
		}
		
		
		// JWPlayer
		if($options['enable_jwplayer']) {
			preg_match_all('/jwplayer\(.*?(?:image:[\s]*?)["\']([a-zA-Z0-9_\-\.]+)["\'].*?\)/smi', $markup, $matches);
		
			foreach($matches[1] as $match) {
				if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
					print "\t JWPlayer image: $match\n";
				}
			
				if (isset($match)) {
					if ($options['exec_mode'] == 1)  {
						$exists = $this->remote_exists($match);
						if(($exists) && (!$match))
							$thumbnails[] = $match;
					} else {
						$thumbnails[] = $match;
					}
				}
			}
		}
		
		// 8TRACKS
		if($options['enable_eight_tracks']) {					
			$etracks_thumbnails = $this->find_etracks_widgets($markup);
			if (isset($etracks_thumbnails)) {			
				foreach ($etracks_thumbnails as $etracks_thumbnail) {
					if ($etracks_thumbnail)
						$thumbnails[] = $etracks_thumbnail;
				}
			}
		}
		
		// BAMBUSER
		if($options['enable_bambuser']) {					
			$bambuser_thumbnails = $this->find_bambuser_widgets($markup, $options['bambuser_api']);
			if (isset($bambuser_thumbnails)) {			
				foreach ($bambuser_thumbnails as $bambuser_thumbnail) {
					if ($bambuser_thumbnail)
						$thumbnails[] = $bambuser_thumbnail;
				}
			}
		}	
			
		// BANDCAMP
		if($options['enable_bandcamp']) {					
			$bandcamp_thumbnails = $this->find_bandcamp_widgets($markup, $options['bandcamp_api']);
			if (isset($bandcamp_thumbnails)) {			
				foreach ($bandcamp_thumbnails as $bandcamp_thumbnail) {
					if ($bandcamp_thumbnail)
						$thumbnails[] = $bandcamp_thumbnail;
				}
			}
		}
							
		// BLIP.TV
		if($options['enable_bliptv']) {					
			$bliptv_thumbnails = $this->find_bliptv_widgets($markup);
			if (isset($bliptv_thumbnails)) {			
				foreach ($bliptv_thumbnails as $bliptv_thumbnail) {
					if ($bliptv_thumbnail)
						$thumbnails[] = $bliptv_thumbnail;
				}
			}
		}
			
		// DAILYMOTION
		if($options['enable_dailymotion']) {					
			$dailymotion_thumbnails = $this->find_dailymotion_widgets($markup);
			if (isset($dailymotion_thumbnails)) {			
				foreach ($dailymotion_thumbnails as $dailymotion_thumbnail) {
					if ($dailymotion_thumbnail)
						$thumbnails[] = $dailymotion_thumbnail;
				}
			}
		}
		
		// FLICKR
		if($options['enable_flickr']) {					
			$flickr_thumbnails = $this->find_flickr_widgets($markup, $options['flickr_api']);
			if (isset($flickr_thumbnails)) {			
				foreach ($flickr_thumbnails as $flickr_thumbnail) {
						if ($flickr_thumbnail)
							$thumbnails[] = $flickr_thumbnail;
				}
			}
		}
		
		// HULU	
		if($options['enable_hulu']) {					
			$hulu_thumbnails = $this->find_hulu_widgets($markup);
			if (isset($hulu_thumbnails)) {			
				foreach ($hulu_thumbnails as $hulu_thumbnail) {
					if ($hulu_thumbnail)
						$thumbnails[] = $hulu_thumbnail;
				}
			}
		}
		
		// INTERNET ARCHIVE
		if($options['enable_internetarchive']) {					
			$internetarchive_thumbnails = $this->find_internetarchive_widgets($markup);
			if (isset($internetarchive_thumbnails)) {			
				foreach ($internetarchive_thumbnails as $internetarchive_thumbnail) {
					if ($internetarchive_thumbnail)
						$thumbnails[] = $internetarchive_thumbnail;
				}
			}
		}
		
		// JUSTIN.TV	
		if($options['enable_justintv']) {					
			$justintv_thumbnails = $this->find_justintv_widgets($markup);
			if (isset($justintv_thumbnails)) {			
				foreach ($justintv_thumbnails as $justintv_thumbnail) {
					if ($justintv_thumbnail)
						$thumbnails[] = $justintv_thumbnail;
				}
			}
		}
		
		// LIVESTREAM	
		if($options['enable_livestream']) {					
			$livestream_thumbnails = $this->find_livestream_widgets($markup);
			if (isset($livestream_thumbnails)) {			
				foreach ($livestream_thumbnails as $livestream_thumbnail) {
					if ($livestream_thumbnail)
						$thumbnails[] = $livestream_thumbnail;
				}
			}
		}
		
		// MIXCLOUD	
		if($options['enable_mixcloud']) {					
			$mixcloud_thumbnails = $this->find_mixcloud_widgets($markup);
			if (isset($mixcloud_thumbnails)) {			
				foreach ($mixcloud_thumbnails as $mixcloud_thumbnail) {
					if ($mixcloud_thumbnail)
						$thumbnails[] = $mixcloud_thumbnail;
				}
			}
		}	
			
		// OFFICIAL.TV
		if($options['enable_official']) {
			$official_thumbnails = $this->find_official_widgets($markup, $options['official_api']);
			if (isset($official_thumbnails)) {	
				foreach ($official_thumbnails as $official_thumbnail) {
					if ($official_thumbnail)
						$thumbnails[] = $official_thumbnail;
				}
			}
		}
	
		/*
		// PLAY.FM
		if($options['enable_playfm']) {
			$playfm_thumbnails = $this->find_playfm_widgets($markup, $options['playfm_api']);
			if (isset($playfm_thumbnails)) {	
				foreach ($playfm_thumbnails as $playfm_thumbnail) {
					if ($playfm_thumbnail)
						$thumbnails[] = $playfm_thumbnail;
				}
			}
		}
		*/
	
		// SOUNDCLOUD
		if($options['enable_soundcloud']) {
			$soundcloud_thumbnails = $this->find_soundcloud_widgets($markup, $options['soundcloud_api']);
			if (isset($soundcloud_thumbnails)) {	
				foreach ($soundcloud_thumbnails as $soundcloud_thumbnail) {
					if ($soundcloud_thumbnail)
						$thumbnails[] = $soundcloud_thumbnail;
				}
			}
		}					

		// USTREAM	
		if($options['enable_ustream']) {
			$ustream_thumbnails = $this->find_ustream_widgets($markup, $options['ustream_api']);
			if (isset($ustream_thumbnails)) {
				foreach ($ustream_thumbnails as $ustream_thumbnail) {
					if ($ustream_thumbnail)
						$thumbnails[] = $ustream_thumbnail;
				}
			}	
		}

		// VIDDLER
		if($options['enable_viddler']) {
			$viddler_thumbnails = $this->find_viddler_widgets($markup, $options['viddler_api']);
			if (isset($viddler_thumbnails)) {
				foreach ($viddler_thumbnails as $viddler_thumbnail) {
					if ($viddler_thumbnail)
						$thumbnails[] = $viddler_thumbnail;
				}
			}
		}					
	
		// VIMEO
		if($options['enable_vimeo']) {
			$vimeo_thumbnails = $this->find_vimeo_widgets($markup);
			if (isset($vimeo_thumbnails)) {
				foreach ($vimeo_thumbnails as $vimeo_thumbnail) {
					if ($vimeo_thumbnail)
						$thumbnails[] = $vimeo_thumbnail;
				}
			}
		}					

		// YOUTUBE
		if($options['enable_youtube']) {
			$youtube_thumbnails = $this->find_youtube_widgets($markup);
			if (isset($youtube_thumbnails)) {
				foreach ($youtube_thumbnails as $youtube_thumbnail) {
					if ($youtube_thumbnail)
						$thumbnails[] = $youtube_thumbnail;
				}
			}
		}
		
		return $thumbnails;
	}	// end get_widget_thumbnails
		
	
	function find_etracks_widgets($markup) {
		// 8tracks iFrame and embed players
		preg_match_all( '/8tracks.com\/mixes\/([0-9]+)\/player/i', $markup, $matches1 );
							
		// 8tracks shortcode (Jetpack)
		preg_match_all('/\[8tracks.*?url="https?:\/\/w*.?8tracks.com\/mixes\/([0-9]+)"/i', $markup, $matches2);
											
		$matches = array_merge($matches1[1], $matches2[1]);
		$matches = array_unique($matches);

		// Now if we've found a 8tracks embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "8tracks";
			$json_url = "http://8tracks.com/mixes/$match.jsonp?api_key=e310c354bf4633de8dca0e7fb0a3a23fcc1614fe";
			$json_query = "mix->cover_urls->" . ETRACKS_IMAGE_SIZE;
			$etracks_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($etracks_thumbnail)
					print "\t 8tracks: $etracks_thumbnail (ID:$match)\n";
				else
					print "\t 8tracks: Error from URL ($json_url)\n";
			}
			
			if (isset($etracks_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($etracks_thumbnail);
					if($exists) 
						$etracks_thumbnails[] = $etracks_thumbnail;
				} else {
					$etracks_thumbnails[] = $etracks_thumbnail;
				}
			}
		}
		return $etracks_thumbnails;
	} // end find_etracks_widgets
	
	
	function find_bambuser_widgets($markup, $api) {
		// Bambuser embed players
		preg_match_all( '/static.bambuser.com\/r\/player.swf\?vid=([0-9]+)/i', $markup, $matches1);
		
		// Bambuser iFrame players
		preg_match_all( '/embed.bambuser.com\/broadcast\/([0-9]+)/i', $markup, $matches2);
		
		$matches = array_merge($matches1[1], $matches2[1]);
		$matches = array_unique($matches);

		// Now if we've found a Bambuser embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Bambuser";
			$json_url = "http://api.bambuser.com/broadcast/$match.json?api_key=$api";
			$json_query = "result->preview";
			$bambuser_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($bambuser_thumbnail)
					print "\t Bambuser: $bambuser_thumbnail (ID:$match)\n";
				else
					print "\t Bambuser: Error from URL ($json_url)\n";
			}
			
			if (isset($bambuser_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($bambuser_thumbnail);
					if($exists) 
						$bambuser_thumbnails[] = $bambuser_thumbnail;
				} else {
					$bambuser_thumbnails[] = $bambuser_thumbnail;
				}
			}
		}
		return $bambuser_thumbnails;
	} // end find_bambuser_widgets
	
	function find_bandcamp_widgets($markup, $api) {
		// Standard embed code for albums
		preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/album=([0-9]+)\//i', $markup, $matches);					
		$matches = array_unique($matches[1]);

		// Now if we've found a Bandcamp ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Bandcamp";
			$json_url = "http://api.bandcamp.com/api/album/2/info?key=$api&album_id=$match";
			$json_query = BANDCAMP_IMAGE_SIZE;
			$bandcamp_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($bandcamp_thumbnail)
					print "\t Bandcamp album: $bandcamp_thumbnail (ID:$match)\n";
				else
					print "\t Bandcamp album: Error from URL ($json_url)\n";
			}
			
			if (isset($bandcamp_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($bandcamp_thumbnail);
					if($exists) 
						$bandcamp_thumbnails[] = $bandcamp_thumbnail;
				} else {
					$bandcamp_thumbnails[] = $bandcamp_thumbnail;
				}
			}
		}

		// Standard embed code for single tracks
		preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/track=([0-9]+)\//i', $markup, $matches);					
		$matches = array_unique($matches[1]);

		// Now if we've found a Bandcamp ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$bandcamp_thumbnail = $this->get_bandcamp_parent_thumbnail($match, $bandcamp_api);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($bandcamp_thumbnail)
					print "\t Bandcamp track: $bandcamp_thumbnail (ID:$match)\n";
				else
					print "\t Bandcamp track: Error from URL ($json_url)\n";
			}
			
			if (isset($bandcamp_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($bandcamp_thumbnail);
					if($exists) 
						$bandcamp_thumbnails[] = $bandcamp_thumbnail;
				} else {
					$bandcamp_thumbnails[] = $bandcamp_thumbnail;
				}
			}
		}
		return $bandcamp_thumbnails;
	} // end find_bandcamp_widgets
		
	function find_bliptv_widgets($markup) {
		// Blip.tv iFrame player
		preg_match_all( '/blip.tv\/play\/([A-Za-z0-9]+)/i', $markup, $matches1 );
	
		// Blip.tv Flash player
		preg_match_all( '/a.blip.tv\/api.swf#([A-Za-z0-9%]+)/i', $markup, $matches2 );
	
		$matches = array_merge($matches1[1], $matches2[1]);
		$matches = array_unique($matches);

		// Now if we've found a Blip.tv embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Blip.tv";
			$json_url = "http://blip.tv/players/episode/$match?skin=json";
			$json_query = "Post->thumbnailUrl";
			$bliptv_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($bliptv_thumbnail)
					print "\t Blip.tv: $bliptv_thumbnail (ID:$match)\n";
				else
					print "\t Blip.tv: Error from URL ($json_url)\n";
			}
			
			if (isset($bliptv_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($bliptv_thumbnail);
					if($exists) 
						$bliptv_thumbnails[] = $bliptv_thumbnail;
				} else {
					$bliptv_thumbnails[] = $bliptv_thumbnail;
				}
			}
		}
		return $bliptv_thumbnails;
	} // end find_bliptv_widgets
	
	function find_dailymotion_widgets($markup) {
		// Dailymotion Flash player
		preg_match_all('#<object[^>]+>.+?https?://w*.?dailymotion.com/swf/video/([A-Za-z0-9-_]+).+?</object>#s', $markup, $matches1);

		// Dailymotion iFrame player
		preg_match_all('#https?://w*.?dailymotion.com/embed/video/([A-Za-z0-9-_]+)#s', $markup, $matches2);

		// Dailymotion shortcode (Viper's Video Quicktags)
		preg_match_all('/\[dailymotion.*?]https?:\/\/w*.?dailymotion.com\/video\/([A-Za-z0-9-_]+)\[\/dailymotion]/i', $markup, $matches3);

		$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
		$matches = array_unique($matches);

		// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Dailymotion";
			$json_url = "https://api.dailymotion.com/video/$match?fields=thumbnail_url";
			$json_query = "thumbnail_url";
			$dailymotion_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($dailymotion_thumbnail)
					print "\t Dailymotion: $dailymotion_thumbnail\n";
				else
					print "\t Dailymotion: Error from URL ($json_url)\n";
			}
			
			if (isset($dailymotion_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($dailymotion_thumbnail);
					if($exists) 
						$dailymotion_thumbnails[] = $dailymotion_thumbnail;
				} else {
					$dailymotion_thumbnails[] = $dailymotion_thumbnail;
				}
			}
		}
		return $dailymotion_thumbnails;
	} //end find_dailymotion_widgets

	function find_flickr_widgets($markup, $api) {
		preg_match_all('/<object.*?data=\"http:\/\/www.flickr.com\/apps\/video\/stewart.swf\?.*?>(.*?photo_id=([0-9]+).*?)<\/object>/smi', $markup, $matches);
		$matches = $matches[2];
	
		// Now if we've found a Flickr embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Flickr";
			$json_url = "http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=$match&format=json&api_key=$api&nojsoncallback=1";
			$json_query = NULL;
			$flickr_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($flickr_thumbnail)
					print "\t Flickr: $flickr_thumbnail (ID:$match)\n";
				else
					print "\t Flickr: Error from URL ($json_url)\n";
			}
			
			if (isset($flickr_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($flickr_thumbnail);
					if($exists) 
						$flickr_thumbnails[] = $flickr_thumbnail;
				} else {
					$flickr_thumbnails[] = $flickr_thumbnail;
				}
			}
		}
		return $flickr_thumbnails;
	} // end find_flickr_widgets
	
	function find_hulu_widgets($markup) {
		// Hulu iFrame player
		preg_match_all( '/hulu.com\/embed\/([A-Za-z0-9\-_]+)/i', $markup, $matches );				
		$matches = array_unique($matches[1]);

		// Now if we've found a Hulu embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Hulu";
			$json_url = "http://www.hulu.com/api/oembed.json?url=http://www.hulu.com/embed/$match";
			$json_query = "thumbnail_url";
			$hulu_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($hulu_thumbnail)
					print "\t Hulu: $hulu_thumbnail (ID:$match)\n";
				else
					print "\t Hulu: Error from URL ($json_url)\n";
			}
			
			if (isset($hulu_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($hulu_thumbnail);
					if($exists) 
						$hulu_thumbnails[] = $hulu_thumbnail;
				} else {
					$hulu_thumbnails[] = $hulu_thumbnail;
				}
			}
		}
		return $hulu_thumbnails;
	} // end find_hulu_widgets
	
	function find_internetarchive_widgets($markup) {
		// Internet Archive iFrame players
		preg_match_all( '/archive.org\/embed\/([A-Za-z0-9]+)/i', $markup, $matches);
											
		$matches = array_unique($matches[1]);

		// Now if we've found a Internet Archive embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Internet Archive";
			$json_url = "http://archive.org/details/$match&output=json";
			$json_query = "misc->image";
			$internetarchive_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($internetarchive_thumbnail)
					print "\t Archive.org: $internetarchive_thumbnail (ID:$match)\n";
				else
					print "\t Archive.org: Error from URL ($json_url)\n";
			}
			
			if (isset($internetarchive_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($internetarchive_thumbnail);
					if($exists) 
						$internetarchive_thumbnails[] = $internetarchive_thumbnail;
				} else {
					$internetarchive_thumbnails[] = $internetarchive_thumbnail;
				}
			}
		}
		return $internetarchive_thumbnails;
	} // end find_internetarchive_widgets
	
	function find_justintv_widgets($markup) {
		// Justin.tv/Twitch.tv embed player
		preg_match_all( '/(?:justin|twitch).tv\/widgets\/live_embed_player.swf\?channel=([A-Za-z0-9-_]+)/i', $markup, $matches );
		
		$matches = array_unique($matches[1]);
		
		// Now if we've found a Justin.tv/Twitch.tv embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Justin.tv";
			$json_url = "http://api.justin.tv/api/stream/list.json?channel=$match";
			$json_query = "channel->" . JUSTINTV_IMAGE_SIZE;
			$justintv_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($justintv_thumbnail)
					print "\t Justin.tv/Twitch: $justintv_thumbnail (ID:$match)\n";
				else
					print "\t Justin.tv/Twitch: Error from URL ($json_url)\n";
			}
			
			if (isset($justintv_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($justintv_thumbnail);
					if($exists) 
						$justintv_thumbnails[] = $justintv_thumbnail;
				} else {
					$justintv_thumbnails[] = $justintv_thumbnail;
				}
			}
		}
		return $justintv_thumbnails;
	} //end find_justintv_widgets
	
	function find_livestream_widgets($markup) {
		// Standard embed code
		preg_match_all('/cdn.livestream.com\/embed\/([A-Za-z0-9\-_]+)/i', $markup, $matches);
		$matches = array_unique($matches[1]);

		// Now if we've found a Livestream ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$livestream_thumbnail = "http://thumbnail.api.livestream.com/thumbnail?name=$match";
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($livestream_thumbnail)
					print "\t Livestream: $livestream_thumbnail\n";
				else
					print "\t Livestream: Error from URL ($livestream_thumbnail)\n";
			}
			
			if (isset($livestream_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($livestream_thumbnail);
					if($exists) 
						$livestream_thumbnails[] = $livestream_thumbnail;
				} else {
					$livestream_thumbnails[] = $livestream_thumbnail;
				}
			}
		}
		return $livestream_thumbnails;
	} // end find_livestream_widgets
	
	function find_mixcloud_widgets($markup) {
		// Standard embed code
		preg_match_all('/mixcloudLoader.swf\?feed=https?%3A%2F%2Fwww.mixcloud.com%2F([A-Za-z0-9\-_\%]+)/i', $markup, $matches);
		$matches = array_unique($matches[1]);

		// Standard embed (API v1, undocumented)
		// preg_match_all('/feed=http:\/\/www.mixcloud.com\/api\/1\/cloudcast\/([A-Za-z0-9\-_\%\/.]+)/i', $markup, $mixcloud_ids);					

		// Now if we've found a Mixcloud ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$mixcloud_id = str_replace('%2F', '/', $match);
			$service = "Mixcloud";
			$json_url = "http://api.mixcloud.com/$match";
			$json_query = "pictures->" . MIXCLOUD_IMAGE_SIZE;
			$mixcloud_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($mixcloud_thumbnail)
					print "\t Mixcloud: $mixcloud_thumbnail\n";
				else
					print "\t Mixcloud: Error from URL ($json_url)\n";
			}
			
			if (isset($mixcloud_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($mixcloud_thumbnail);
					if($exists) 
						$mixcloud_thumbnails[] = $mixcloud_thumbnail;
				} else {
					$mixcloud_thumbnails[] = $mixcloud_thumbnail;
				}
			}
		}
		return $mixcloud_thumbnails;
	} // end find_mixcloud_widgets

	function find_official_widgets($markup, $api) {
		// Official.fm iFrame
		preg_match_all( '/official.fm\/tracks\/([A-Za-z0-9]+)\?/i', $markup, $matches );
		$matches = array_unique($matches[1]);

		// Now if we've found a Official.fm embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "Official.fm";
			$json_url = "http://official.fm/services/oembed.json?url=http://official.fm/tracks/$match&size=large&key=$api";
			$json_query = "thumbnail_url";
			$official_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($official_thumbnail)
					print "\t Official.fm: $official_thumbnail (ID:$match)\n";
				else
					print "\t Official.fm: Error from URL ($json_url)\n";
			}
			
			if (isset($official_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($official_thumbnail);
					if($exists) 
						$official_thumbnails[] = $official_thumbnail;
				} else {
					$official_thumbnails[] = $official_thumbnail;
				}
			}
		}
		return $official_thumbnails;
	} // end find_official_widgets
	
	/*	
	function find_playfm_widgets($markup, $api) {
		// Play.fm embed
		preg_match_all( '/playfmWidget.swf\?url=http%3A%2F%2Fwww.play.fm%2Frecordings%2Fflash%2F01%2Frecording%2F([0-9]+)/i', $markup, $matches );
		$matches = array_unique($matches[1]);

		// Now if we've found a Play.fm embed URL, let's set the thumbnail URL
		foreach($matches as $match) {
			$playfm_thumbnail = $this->get_playfm_thumbnail($match, $api);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($playfm_thumbnail)
					print "\t Play.fm: $playfm_thumbnail (ID:$match)\n";
				else
					print "\t Play.fm: Error from URL ($json_url)\n";
			}
			
			if (isset($playfm_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($playfm_thumbnail);
					if($exists) 
						$playfm_thumbnails[] = $playfm_thumbnail;
				} else {
					$playfm_thumbnails[] = $playfm_thumbnail;
				}
			}
		}
		return $playfm_thumbnails;
	} // end of find_playfm_widgets
	*/
	
	function find_soundcloud_widgets($markup, $api) {
		// Standard embed code for tracks (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com%2Ftracks%2F([0-9]+)/i', $markup, $matches1);

		// Shortcode for tracks (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com\/tracks\/([0-9]+)/i', $markup, $matches2);

		$matches = array_merge($matches1[1], $matches2[1]);
		$matches = array_unique($matches);

		// Now if we've found a SoundCloud ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "SoundCloud";
			$json_url = "http://api.soundcloud.com/tracks/$match.json?client_id=$api";
			$json_query = "artwork_url";
			$soundcloud_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			$soundcloud_thumbnail = str_replace('-large.', '-' . SOUNDCLOUD_IMAGE_SIZE . '.', $soundcloud_thumbnail); // replace 100x100 default image
			$soundcloud_thumbnail = preg_replace('/\?([A-Za-z0-9_-]+)\Z/', '', $soundcloud_thumbnail); // remove suffix

			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($soundcloud_thumbnail)
					print "\t SoundCloud track: $soundcloud_thumbnail (ID:$match)\n";
				else
					print "\t SoundCloud track: Error from URL ($json_url)\n";
				
			}
			
			if (isset($soundcloud_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($soundcloud_thumbnail);
					if($exists) 
						$soundcloud_thumbnails[] = $soundcloud_thumbnail;
				} else {
					$soundcloud_thumbnails[] = $soundcloud_thumbnail;
				}
			}
		}

		// Standard embed code for playlists (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com%2Fplaylists%2F([0-9]+)/i', $markup, $matches1);

		// Shortcode for playlists (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com\/playlists\/([0-9]+)/i', $markup, $matches2);

		$matches = array_merge($matches1[1], $matches2[1]);
		$matches = array_unique($matches);

		// Now if we've found a SoundCloud ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$service = "SoundCloud";
			$json_url = "http://api.soundcloud.com/playlists/$match.json?client_id=$api";
			$json_query = "artwork_url";
			$soundcloud_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			$soundcloud_thumbnail = str_replace('-large.', '-' . SOUNDCLOUD_IMAGE_SIZE . '.', $soundcloud_thumbnail); // replace 100x100 default image
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($soundcloud_thumbnail)
					print "\t SoundCloud playlist: $soundcloud_thumbnail (ID:$match)\n";
				else
					print "\t SoundCloud playlist: Error from URL ($json_url)\n";
			}
			
			if (isset($soundcloud_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($soundcloud_thumbnail);
					if($exists) 
						$soundcloud_thumbnails[] = $soundcloud_thumbnail;
				} else {
					$soundcloud_thumbnails[] = $soundcloud_thumbnail;
				}
			}
		}
		return $soundcloud_thumbnails;
	} // end find_soundcloud_widgets
	
	function find_ustream_widgets($markup, $api) {
		// Ustream iFrame player (recorded)
		preg_match_all( '/ustream.tv\/(?:embed\/|embed\/recorded\/)([0-9]+)/i', $markup, $matches );
		
		$matches = array_unique($matches[1]);

		// Now if we've found a Ustream embed URL, let's set the thumbnail URL
		foreach($matches as $match) {					
			$service = "Ustream";
			$json_url = "http://api.ustream.tv/json/channel/$match/getInfo?key=$api";
			$json_query = "results->imageUrl->" . USTREAM_IMAGE_SIZE;
			$ustream_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);						
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($ustream_thumbnail)
					print "\t Ustream: $ustream_thumbnail (ID:$match)\n";
				else
					print "\t Ustream: Error from URL ($json_url)\n";
			}
			
			if (isset($ustream_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($ustream_thumbnail);
					if($exists) 
						$ustream_thumbnails[] = $ustream_thumbnail;
				} else {
					$ustream_thumbnails[] = $ustream_thumbnail;
				}
			}
		}
		return $ustream_thumbnails;
	} // end find_ustream_widgets
	
	function find_viddler_widgets($markup, $api) {
		preg_match_all( '/viddler.com\/embed\/([A-Za-z0-9]+)/i', $markup, $matches );

		// Now if we've found a Viddler embed URL, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$service = "Viddler";
			$json_url = "http://api.viddler.com/api/v2/viddler.api.getDetails.json?video_id=$match&key=$api";
			$json_query = "video->thumbnail_url";
			$viddler_thumbnail = $this->get_json_thumbnail($service, $json_url, $json_query);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($viddler_thumbnail)
					print "\t Viddler: $viddler_thumbnail (ID:$match)\n";
				else
					print "\t Viddler: Error from URL ($json_url)\n";
			}
			
			if (isset($viddler_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($viddler_thumbnail);
					if($exists) 
						$viddler_thumbnails[] = $viddler_thumbnail;
				} else {
					$viddler_thumbnails[] = $viddler_thumbnail;
				}
			}
		}
		return $viddler_thumbnails;
	} // end find_viddler_widgets
	
	function find_vimeo_widgets($markup) {
		// Vimeo Flash player ("old embed code")
		preg_match_all('#<object[^>]+>.+?https?://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches1);

		// Vimeo iFrame player ("new embed code")
		preg_match_all('#https?://player.vimeo.com/video/([0-9]+)#s', $markup, $matches2);

		// Vimeo shortcode (Viper's Video Quicktags)
		preg_match_all('/\[vimeo.*?]https?:\/\/w*.?vimeo.com\/([0-9]+)\[\/vimeo]/i', $markup, $matches3);

		$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
		$matches = array_unique($matches);

		// Now if we've found a Vimeo ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$vimeo_thumbnail = $this->get_vimeo_thumbnail($match, VIMEO_IMAGE_SIZE);
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($vimeo_thumbnail)
					print "\t Vimeo: $vimeo_thumbnail (ID:$match)\n";
				else
					print "\t Vimeo: Error from URL ($json_url)\n";
			}
			
			if (isset($vimeo_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($vimeo_thumbnail);
					if($exists) 
						$vimeo_thumbnails[] = $vimeo_thumbnail;
				} else {
					$vimeo_thumbnails[] = $vimeo_thumbnail;
				}
			}
		}
		return $vimeo_thumbnails;
	} // end find_vimeo_widgets
	
	function find_youtube_widgets($markup) {
		// Checks for the old standard YouTube embed
		preg_match_all('#<object[^>]+>.+?https?://w*.?youtube.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches1);

		// Checks for YouTube iframe, the new standard since at least 2011
		preg_match_all('#https?://w*.?youtube.com/embed/([A-Za-z0-9\-_]+)#s', $markup, $matches2);

		// YouTube shortcode (Viper's Video Quicktags)
		preg_match_all('/\[youtube.*?]https?:\/\/w*.?youtube.com\/watch\?v=([A-Za-z0-9\-_]+).+?\[\/youtube]/i', $markup, $matches3);

		$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
		$matches = array_unique($matches);

		// Now if we've found a YouTube ID, let's set the thumbnail URL
		foreach($matches as $match) {
			$youtube_thumbnail = 'http://img.youtube.com/vi/' . $match . '/0.jpg'; // no https connection
			if((OGRAPHR_DEBUG == TRUE) && (is_single()) || (is_front_page())) {
				if ($youtube_thumbnail)
					print "\t YouTube: $youtube_thumbnail (ID:$match)\n";
				else
					print "\t YouTube: Error from URL ($json_url)\n";
			}
			
			if (isset($youtube_thumbnail)) {
				if ($options['exec_mode'] == 1)  {
					$exists = $this->remote_exists($youtube_thumbnail);
					if($exists) 
						$youtube_thumbnails[] = $youtube_thumbnail;
				} else {
					$youtube_thumbnails[] = $youtube_thumbnail;
				}
			}
		}
		return $youtube_thumbnails;
	} //end find_youtube_widgets
	
	
	// initialize
	function ographr_core_init() {
		global $core;
		global $options;
		
		//if (version_compare($options['last_update'], OGRAPHR_VERSION) == -1)
		//	$core->ographr_self_update();
	}
	
	// upgrades? currently not in use
	function ographr_self_update() {
		global $options;
		
		// house keeping
			$last_update = $options['last_updated'];
			$opt_updated = FALSE;
		
			// pre 0.5
			if (!$options['exec_mode']) {
				$options['exec_mode'] = "1";
				if ($options[‘flickr_api’] == FLICKR_API_KEY)
					$options[‘flickr_api’] = "";
				if ($options[‘official_api’] == OFFICIAL_API_KEY)
					$options[‘official_api’] = "";
				if ($options[‘soundcloud_api’] == SOUNDCLOUD_API_KEY)
					$options[‘soundcloud_api’] = "";
				if ($options[‘ustream_api’] == USTREAM_API_KEY)
					$options[‘ustream_api’] = "";
				$opt_updated = TRUE;
			}

			// pre 0.5.5
			if (!$last_update) {
				$options['enable_videoposter'] = "1";
				$options['enable_jwplayer'] = "1";
				$options['add_post_images'] = "1";
				$opt_updated = TRUE;
			}
			
			// 0.5.6
			if (version_compare($last_update, "0.5.6") == "<=") {
				$options['data_expiry'] = "-1";
				$opt_updated = TRUE;
			}
			
			// 0.5.9
			if (version_compare($last_update, "0.5.6") == "<=") {
				$options['enable_bambuser'] = "1";
				$opt_updated = TRUE;
			}
		
			// version that performed update
			if ($opt_updated == TRUE) {
				$options['last_update'] = OGRAPHR_VERSION;
				update_option('ographr_options', $options);
			}
	}
	
	// Display a Settings link on the OGraphr Plugins page
	function ographr_plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$ographr_links = '<a href="'.get_admin_url().'options-general.php?page=meta-ographr/meta-ographr_admin.php">' .__('Settings').'</a>';

			// make the 'Settings' link appear first
			array_unshift( $links, $ographr_links );
		}

		return $links;
	}


	function ographr_admin_notice(){
	    global $options;

		// Debug
		if ((OGRAPHR_DEBUG == TRUE) && (current_user_can('manage_options'))) {
			echo '<div class="error">
	       		<p>OGraphr is currently running in debug mode. You can disable it in the <a href="'.get_admin_url().'plugin-editor.php?file=meta-ographr%2Fmeta-ographr_index.php&plugin=meta-ographr%2Fmeta-ographr_index.php">plugin editor</a>!</p>
	    		</div>';
		}

		// Beta
		if ((OGRAPHR_BETA == TRUE) && (OGRAPHR_DEBUG == FALSE) && (current_user_can('manage_options'))) {
			echo '<div class="updated">
	       		<p>OGraphr is currently running with beta features enabled. You can disable this in the <a href="'.get_admin_url().'plugin-editor.php?file=meta-ographr%2Fmeta-ographr_index.php&plugin=meta-ographr%2Fmeta-ographr_index.php">plugin editor</a>!</p>
	    		</div>';
		}
	}


	// Save thumbnails as postdata
	function ographr_save_postmeta($post_id) {
		global $core;
		global $options;

		if($options['exec_mode'] == 2) {
			return;
		}

		$post_array = get_post($post_id); 
		$markup = $post_array->post_content;
		$markup = apply_filters('the_content',$markup);	

		$widget_thumbnails = $core->get_widget_thumbnails($markup);
		
		if (is_array($widget_thumbnails))
			foreach($widget_thumbnails as $widget_thumbnail)
				$widget_thumbnail =  htmlentities($widget_thumbnail);
				//$widget_thumbnail = preg_replace('/\?([A-Za-z0-9_-]+)\Z/', '', $widget_thumbnail); // remove suffix
				
		if(!(empty($widget_thumbnails))) {

			$widget_thumbnails = serialize($widget_thumbnails);
			update_post_meta($post_id, 'ographr_urls', $widget_thumbnails);
		
			$indexed = date("U"); //Y-m-d H:i:s
			update_post_meta($post_id, 'ographr_indexed', $indexed);
			// 0.6
			$this->ographr_save_stats();
		}

	}
	
	// 0.6
	function ographr_save_stats() {
		
		$stats = get_option('ographr_data');
		
		if(!$stats) {
			$yesterday = strtotime("yesterday");
			$yesterday = date("Y-m-d", $yesterday);		
			$stats[$yesterday] = array(
									'posts_total' => '0',
									'posts_indexed' => '0'
									);
		}
		
		// create function!
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
	
	// 0.6
	function ographr_delete_stats() {
		
		$stats = get_option('ographr_data');
		
		if($stats) {
			$posts_published = wp_count_posts();
			$posts_published = $posts_published->publish;
			$args = array( 'numberposts' => $posts_published, 'meta_key' => 'ographr_urls' );
			$myposts = get_posts( $args );
			$posts_indexed = count($myposts) - 1;

			$today = date("Y-m-d");

			$stats[$today] = array(
									'posts_total' => $posts_published,
									'posts_indexed' => $posts_indexed
									);

			update_option('ographr_data', $stats);
		}
	}
	
	
	function ographr_admin_bar() {
		global $options;	
		if (!$options['add_adminbar'])
			return;
			
		global $wp_admin_bar;

	    if (current_user_can('manage_options')) {
		
				//global $post;
				
				$published = wp_count_posts();
				$published = $published->publish;
				$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_urls' );
				$myposts = get_posts( $args );
				$harvested = count($myposts);
				
	            $menu_items = array(
	                array(
	                    'id' => 'ographr',
	                    'title' => "OGraphr [$harvested/$published]",
						'href' => admin_url('options-general.php?page=meta-ographr/meta-ographr_admin.php')
	                ),
					array(
	                    'id' => 'ographr-settings',
						'parent' => 'ographr',
	                    'title' => 'Settings',
						'href' => admin_url('options-general.php?page=meta-ographr/meta-ographr_admin.php')
	                ),
					array(
	                    'id' => 'ographr-home',
						'parent' => 'ographr',
	                    'title' => 'Website',
						'href' => 'http://wordpress.org/extend/plugins/meta-ographr/'
	                )
	            );

	        foreach ($menu_items as $menu_item) {
	            $wp_admin_bar->add_menu($menu_item);
	        }
	    }	
	}
	
	function ographr_add_google_snips($content) {
		
		global $options;
				
		if (($options['add_image_prop']) && ( is_single() ) && ((preg_match(GOOGLEPLUS_USERAGENT,$user_agent)) || (OGRAPHR_DEBUG)) ) {
			
			$doc = new DOMDocument();
			if ($content)
				$doc->loadHTML($content);
			
			// $body->setAttribute('itemtype', 'http://schema.org/Blog');
			
			// add Schema properties to all image tags,
			$imgs = $doc->getElementsByTagName('img');
			foreach ($imgs as $img) {
				$img->setAttribute('itemprop', 'image');
			}

			//$content = $doc->saveHTML();			
			$content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $doc->saveHTML()));
		}
		
		return $content;
	}

	
}; // end of class

?>