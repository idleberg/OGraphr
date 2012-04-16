<?php
/*
Plugin Name: OGraphr
Plugin URI: http://ographr.whyeye.org
Description: This plugin scans posts for videos (YouTube, Vimeo, Dailymotion, Hulu, Blip.tv) and music players (SoundCloud, Mixcloud, Bandcamp, Official.fm) and adds their thumbnails as an OpenGraph meta-tag. While at it, the plugin also adds OpenGraph tags for the title, description (excerpt) and permalink. Thanks to Sutherland Boswell, Michael Wöhrer, and Matthias Gutjahr!
Version: 0.4.1
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
*/

// OGRAPHR OPTIONS
    define("OGRAPHR_VERSION", "0.4.1");

	// force output of all values in comment tags
	define("OGRAPHR_DEBUG", FALSE);

// SOUNDCLOUD
	// no need to change this unless you want to use your own SoundCloud API key (-> http://soundcloud.com/you/apps)
	define("SOUNDCLOUD_API_KEY", "15fd95172fa116c0837c4af8e45aa702");
	// default artwork size (mini=16x16, tiny=20x20, small=32x32, badge=47x47, t67x67, large=100x100, t300x300, crop=400x400, t500x500)
	define("SOUNDCLOUD_IMAGE_SIZE", "t300x300");
	
// VIMEO
	// default snapshot size (small=100, medium=200, large=640)
	define("VIMEO_IMAGE_SIZE", "medium");

// MIXCLOUD
	// default artwork size (small=25x25, thumbnail=50x50, medium_mobile=80x80, medium=150x150, large=300x300, extra_large=600x600)
	define("MIXCLOUD_IMAGE_SIZE", "large");

// OFFICIAL.FM
	// no need to change this unless you want to use your own Official.fm API key (-> http://official.fm/developers/manage#register)
	define("OFFICIAL_API_KEY", "yv4Aj7p3y5bYIhy3kd6X");

// BANDCAMP
	// default artwork size (small_art_url=100x100, large_art_url=350x350)
	define("BANDCAMP_IMAGE_SIZE", "large_art_url");

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/meta-ographr_admin.php';

class OGraphr_Core {

	// Featured Image (http://codex.wordpress.org/Post_Thumbnails)
	function get_featured_img() {
		global $post, $posts;
		if (has_post_thumbnail( $post->ID )) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			return $image[0];
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
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = unserialize(curl_exec($ch));
			$output = $output[0]['thumbnail_' . $image_size];
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('vimeo_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}

	// Get DailyMotion Thumbnail
	function get_dailymotion_thumbnail($id) {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$ch = curl_init();
			$videoinfo_url = "https://api.dailymotion.com/video/$id?fields=thumbnail_url";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->thumbnail_url;
				if (curl_error($ch) != null) {
					$output = ''; //new WP_Error('dailymotion_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
				}
			curl_close($ch); // Moved here to allow curl_error() operation above. Was previously below curl_exec() call.
			return $output;
		}
	}
	
	// Get Blip.tv Thumbnail
	function get_bliptv_thumbnail($id) {
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
	
	// Get Hulu Thumbnail
	function get_hulu_thumbnail($id) {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$videoinfo_url = "http://www.hulu.com/api/oembed.json?url=http://www.hulu.com/embed/$id";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->thumbnail_url;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('mixcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}

	// Get SoundCloud Thumbnail
	function get_soundcloud_thumbnail($type, $api_key, $id, $image_size = 't300xt300') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$ch = curl_init();
			$videoinfo_url = "http://api.soundcloud.com/$type/$id.json?client_id=$api_key";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->artwork_url;
			$output = str_replace('-large.', '-' . $image_size . '.', $output); // replace 100x100 default image
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('soundcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}

	// Get Mixcloud Thumbnail
	function get_mixcloud_thumbnail($id, $image_size = 'large') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$videoinfo_url = "http://api.mixcloud.com/$id";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->pictures->$image_size;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('mixcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}

	// Get Bandcamp Thumbnail
	function get_bandcamp_thumbnail($type, $api_key, $id, $image_size = 'large_art_url') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			//global $options;
			$ch = curl_init();
			if ($type == 'album') {
				$videoinfo_url = "http://api.bandcamp.com/api/album/2/info?key=$api_key&album_id=$id";
			} else if ($type == 'track') {
				$videoinfo_url = "http://api.bandcamp.com/api/track/1/info?key=$api_key&track_id=$id";
			}
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->$image_size;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('bandcamp_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}
	
	// Get Bandcamp Parent Thumbnail
	function get_bandcamp_parent_thumbnail($type, $api_key, $id, $image_size = 'large_art_url') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			//global $options;
			$ch = curl_init();
			$videoinfo_url = "http://api.bandcamp.com/api/track/1/info?key=$api_key&track_id=$id";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->album_id;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('bandcamp_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			
			// once more for the album
			$ch = curl_init();
			$videoinfo_url = "http://api.bandcamp.com/api/album/2/info?key=$api_key&album_id=$output";
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->$image_size;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('bandcamp_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			
			return $output;
		}
	}
	
	// Get Official Thumbnail
	function get_official_thumbnail($id, $key = 'yv4Aj7p3y5bYIhy3kd6X') {
		if (!function_exists('curl_init')) {
			return null;
		} else {
			$videoinfo_url = "http://official.fm/services/oembed.json?url=http://official.fm/tracks/$id&size=large&key=$key";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
			$output = curl_exec($ch);
			$output = json_decode($output);
			$output = $output->thumbnail_url;
			if (curl_error($ch) != null) {
				$output = ''; //new WP_Error('mixcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
			}
			curl_close($ch);
			return $output;
		}
	}

	//
	// The Main Event
	//
	function get_ographr_thumbnails($post_id=null) {
	
		// Get this plugins' settings
		$options = get_option('ographr_options');
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$facebook_ua = $options['facebook_ua'];
		$gplus_ua = $options['gplus_ua'];
		$linkedin_ua = $options['linkedin_ua'];		
		
		if(((preg_match('/facebookexternalhit/i',$user_agent)) && ($facebook_ua))
		|| ((preg_match('/Firefox\/6.0/i',$user_agent)) && ($gplus_ua))
		|| ((preg_match('/LinkedInBot/i',$user_agent)) && ($linkedin_ua))
		|| ((!$facebook_ua) && (!$gplus_ua) && (!$linkedin_ua))
		|| (OGRAPHR_DEBUG == TRUE)) {
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
		
			if (($web_thumb) && (!$options['not_always'])) {
				$og_thumbnails[] = $web_thumb;
			}
	
			// Get API keys
			$soundcloud_api = $options['soundcloud_api'];
			$bandcamp_api = $options['bandcamp_api'];
			$official_api = $options['official_api'];
		
			// debugging?
			if(OGRAPHR_DEBUG == TRUE) {
				print "\n\r<!-- OGRAPHR v" . OGRAPHR_VERSION ." DEBUGGER -->\n\r";
				
				if (($facebook_ua) || ($gplus_ua) || ($official_api)) {
					if ($user_agent) { print "<!-- User Agent: $user_agent -->\n\r"; }
					if ($facebook_ua) { print "<!-- Limited to Facebook User Agent -->\n\r"; }
					if ($gplus_ua) { print "<!-- Limited to Google+ User Agent -->\n\r"; }
					if ($linkedin_ua) { print "<!-- Limited to LinkedIn User Agent -->\n\r"; }
				}
				
				if ($options['filter_smilies']) { print "<!-- Emoticons are filtered -->\n\r"; }
				if ($options['filter_gravatar']) { print "<!-- Avatars are filtered -->\n\r"; }
				
				if ($options['filter_custom_urls']) {
					foreach(preg_split("/((\r?\n)|(\n?\r))/", $options['filter_custom_urls']) as $line){
						print "<!-- Custom URL /$line/ is filtered -->\n\r";
						}
				}
				
				
				if ($soundcloud_api) { print "<!-- SoundCloud API key: $soundcloud_api -->\n\r"; }
				if ($bandcamp_api) { print "<!-- Bandcamp API key: $bandcamp_api -->\n\r"; }
				if ($official_api) { print "<!-- Official.fm API key: $official_api -->\n\r"; }
			}
	
			if (($enable_on_front = $options['enable_on_front']) || is_single() || (is_page())) {
		
				// Get images in post
				preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
				foreach($matches[1] as $match) {
				  	if(OGRAPHR_DEBUG == TRUE) {
						print "<!-- Image tag: $match -->\n\r";
					}
					
					$no_smilies = FALSE;
					$no_gravatar = FALSE;
					$no_custom_url = TRUE;
					
					// filter Wordpress smilies
					preg_match('/\/wp-includes\/images\/smilies\/icon_.+/', $match, $filter);
					if ((!$options['filter_smilies']) || (!$filter[0])) {
						//$og_thumbnails[] = $match;
						$no_smilies = TRUE;
					}
					
					// filter Gravatar
					preg_match('/https?:\/\/w*.?gravatar.com\/avatar\/.*/', $match, $filter);
					if ((!$options['filter_gravatar']) || (!$filter[0])) {
						//$og_thumbnails[] = $match;
						$no_gravatar = TRUE;
					}
					
					// filter custom URLs
					foreach(preg_split("/((\r?\n)|(\n?\r))/", preg_quote($options['filter_custom_urls'], '/')) as $line) {
						//print "<!-- \$line=$line -->\n\r";
						preg_match("/$line/", $match, $filter);
						foreach($filter as $key => $value) {
							//print "<!-- \$value=$value -->\n\r";
							if ($value) {
								$no_custom_url = FALSE;						
							}
						}				
					}
					
					
					if (($no_gravatar) && ($no_smilies) && ($no_custom_url)) {
						$og_thumbnails[] = $match;
					}
					
				}
	
				// Get featured image
				if (($options['add_post_thumbnail']) && ( function_exists( 'has_post_thumbnail' )) ){ 
					$website_thumbnail = $this->get_featured_img();
					if ($website_thumbnail) {
						$og_thumbnails[] = $website_thumbnail;
					}
				}
			
	
				// YOUTUBE
					if($options['enable_youtube']) {
						// Checks for the old standard YouTube embed
						preg_match_all('#<object[^>]+>.+?https?://www.youtube.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches1);

						// Checks for YouTube iframe, the new standard since at least 2011
						preg_match_all('#https?://www.youtube.com/embed/([A-Za-z0-9\-_]+)#s', $markup, $matches2);

						// Dailymotion shortcode (Viper's Video Quicktags)
						preg_match_all('/\[youtube.*?]https?:\/\/w*.?youtube.com\/watch\?v=([A-Za-z0-9\-_]+).+?\[\/youtube]/', $markup, $matches3);

						$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
						$matches = array_unique($matches);

						// Now if we've found a YouTube ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$yt_thumbnail = 'http://img.youtube.com/vi/' . $match . '/0.jpg'; // no https connection
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- YouTube: $yt_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($yt_thumbnail)) {
							  $og_thumbnails[] = $yt_thumbnail;
							}
						}
					}

	
				// VIMEO
					if($options['enable_vimeo']) {
						// Vimeo Flash player ("old embed code")
						preg_match_all('#<object[^>]+>.+?https?://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches1);
				
						// Vimeo iFrame player ("new embed code")
						preg_match_all('#https?://player.vimeo.com/video/([0-9]+)#s', $markup, $matches2);
				
						// Vimeo shortcode (Viper's Video Quicktags)
						preg_match_all('/\[vimeo.*?]https?:\/\/w*.?vimeo.com\/([0-9]+)\[\/vimeo]/', $markup, $matches3);
				
						$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
						$matches = array_unique($matches);
		
						// Now if we've found a Vimeo ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$vm_thumbnail = $this->get_vimeo_thumbnail($match, VIMEO_IMAGE_SIZE);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- Vimeo: $vm_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($vm_thumbnail)) {
							  $og_thumbnails[] = $vm_thumbnail;
							}
						}
					}
				
	
				// DAILYMOTION
					if($options['enable_dailymotion']) {
						// Dailymotion Flash player
						preg_match_all('#<object[^>]+>.+?https?://www.dailymotion.com/swf/video/([A-Za-z0-9-_]+).+?</object>#s', $markup, $matches1);
				
						// Dailymotion iFrame player
						preg_match_all('#https?://www.dailymotion.com/embed/video/([A-Za-z0-9-_]+)#s', $markup, $matches2);
				
						// Dailymotion shortcode (Viper's Video Quicktags)
						preg_match_all('/\[dailymotion.*?]https?:\/\/w*.?dailymotion.com\/video\/([A-Za-z0-9-_]+)\[\/dailymotion]/', $markup, $matches3);
				
						$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
						$matches = array_unique($matches);

						// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$dailymotion_thumbnail = $this->get_dailymotion_thumbnail($match);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- Dailymotion: $dailymotion_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($dailymotion_thumbnail)) {
								$dailymotion_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $dailymotion_thumbnail); // remove suffix
								$og_thumbnails[] = $dailymotion_thumbnail;
							}
						}
					}
		
			
				// BLIP.TV
					if($options['enable_bliptv']) {

						// Blip.tv iFrame player
						preg_match_all( '/blip.tv\/play\/([A-Za-z0-9]+)/', $markup, $matches1 );
					
						// Blip.tv Flash player
						preg_match_all( '/a.blip.tv\/api.swf#([A-Za-z0-9%]+)/', $markup, $matches2 );
					
						$matches = array_merge($matches1[1], $matches2[1]);
						$matches = array_unique($matches);

						// Now if we've found a Blip.tv embed URL, let's set the thumbnail URL
						foreach($matches as $match) {
							$bliptv_thumbnail = $this->get_bliptv_thumbnail($match);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- Blip.tv: $bliptv_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($bliptv_thumbnail)) {
								$og_thumbnails[] = $bliptv_thumbnail;
							}
						}
					}
			
			
				// HULU	
				if($options['enable_hulu']) {

					// Blip.tv iFrame player
					preg_match_all( '/hulu.com\/embed\/([A-Za-z0-9\-_]+)/', $markup, $matches );				
					$matches = array_unique($matches[1]);

					// Now if we've found a Blip.tv embed URL, let's set the thumbnail URL
					foreach($matches as $match) {
						$hulu_thumbnail = $this->get_hulu_thumbnail($match);
						if(OGRAPHR_DEBUG == TRUE) {
							print "<!-- Hulu: $hulu_thumbnail (ID:$match) -->\n\r";
						}
						if (isset($hulu_thumbnail)) {
							$og_thumbnails[] = $hulu_thumbnail;
						}
					}
				}
					
					
				// SOUNDCLOUD
					if($options['enable_soundcloud']) {
						// Standard embed code for tracks (Flash and HTML5 player)
						preg_match_all('/api.soundcloud.com%2Ftracks%2F([0-9]+)/', $markup, $matches1);
				
						// Shortcode for tracks (Flash and HTML5 player)
						preg_match_all('/api.soundcloud.com\/tracks\/([0-9]+)/', $markup, $matches2);
				
						$matches = array_merge($matches1[1], $matches2[1]);
						$matches = array_unique($matches);
		
						// Now if we've found a SoundCloud ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$sc_thumbnail = $this->get_soundcloud_thumbnail('tracks', $soundcloud_api, $match, SOUNDCLOUD_IMAGE_SIZE);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- SoundCloud track: $sc_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($sc_thumbnail)) {
							  	$sc_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $sc_thumbnail); // remove suffix
								$og_thumbnails[] = $sc_thumbnail;
							}
						}
		
						// Standard embed code for playlists (Flash and HTML5 player)
						preg_match_all('/api.soundcloud.com%2Fplaylists%2F([0-9]+)/', $markup, $matches1);
				
						// Shortcode for playlists (Flash and HTML5 player)
						preg_match_all('/api.soundcloud.com\/playlists\/([0-9]+)/', $markup, $matches2);
				
						$matches = array_merge($matches1[1], $matches2[1]);
						$matches = array_unique($matches);
		
						// Now if we've found a SoundCloud ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$sc_thumbnail = $this->get_soundcloud_thumbnail('playlists', $soundcloud_api, $match, SOUNDCLOUD_IMAGE_SIZE);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- SoundCloud playlist: $sc_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($sc_thumbnail)) {
							  	$sc_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $sc_thumbnail); // remove suffix
								$og_thumbnails[] = $sc_thumbnail;
							}
						}
					}
				
	
				// MIXCLOUD	
					if($options['enable_mixcloud']) {
						// Standard embed code
						preg_match_all('/mixcloudLoader.swf\?feed=https?%3A%2F%2Fwww.mixcloud.com%2F([A-Za-z0-9\-_\%]+)&/', $markup, $matches);
						$matches = array_unique($matches[1]);
					
						// Standard embed (API v1, undocumented)
						// preg_match_all('/feed=http:\/\/www.mixcloud.com\/api\/1\/cloudcast\/([A-Za-z0-9\-_\%\/.]+)/', $markup, $mixcloud_ids);					
					
						// Now if we've found a Mixcloud ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$mixcloud_id = str_replace('%2F', '/', $match);
							$mixcloud_thumbnail = $this->get_mixcloud_thumbnail($mixcloud_id, MIXCLOUD_IMAGE_SIZE);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- MixCloud: $mixcloud_thumbnail -->\n\r";
							}
							if (isset($mixcloud_thumbnail)) {
								$og_thumbnails[] = $mixcloud_thumbnail;
							}
						}
					}
					
	
				// BANDCAMP
					if($options['enable_bandcamp']) {
						// Standard embed code for albums
						preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/album=([0-9]+)\//', $markup, $matches);					
						$matches = array_unique($matches[1]);
		
						// Now if we've found a Bandcamp ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$bandcamp_thumbnail = $this->get_bandcamp_thumbnail('album', $bandcamp_api, $match , BANDCAMP_IMAGE_SIZE);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- Bandcamp album: $bandcamp_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($bandcamp_thumbnail)) {
								$og_thumbnails[] = $bandcamp_thumbnail;
							}
						}

						// Standard embed code for single tracks
						preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/track=([0-9]+)\//', $markup, $matches);					
						$matches = array_unique($matches[1]);
					
						// Now if we've found a Bandcamp ID, let's set the thumbnail URL
						foreach($matches as $match) {
							$bandcamp_thumbnail = $this->get_bandcamp_parent_thumbnail('track', $bandcamp_api, $match);
							if(OGRAPHR_DEBUG == TRUE) {
								print "<!-- Bandcamp track: $bandcamp_thumbnail (ID:$match) -->\n\r";
							}
							if (isset($bandcamp_thumbnail)) {
								$og_thumbnails[] = $bandcamp_thumbnail;
							}
						}
					}
				
					// OFFICIAL.TV
						if($options['enable_official']) {

							// Official.tv iFrame
							preg_match_all( '/official.fm\/tracks\/([A-Za-z0-9]+)\?/', $markup, $matches );
							$matches = array_unique($matches[1]);

							// Now if we've found a Official.fm embed URL, let's set the thumbnail URL
							foreach($matches as $match) {
								$official_thumbnail = $this->get_official_thumbnail($match);
								if(OGRAPHR_DEBUG == TRUE) {
									print "<!-- Official.fm: $official_thumbnail (ID:$match) -->\n\r";
								}
								if (isset($official_thumbnail)) {
									$og_thumbnails[] = $official_thumbnail;
								}
							}
						}
		}
				
					// Let's print all this
					if(($options['add_comment']) && (OGRAPHR_DEBUG == FALSE)) {
						print "<!-- OGraphr v" . OGRAPHR_VERSION . " - http://ographr.whyeye.org -->\n\r";
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
				
					if (($options['website_description']) && (is_front_page())) {
						// Blog title
						$title = get_settings('blogname');
						if($title) {
							print "<meta property=\"og:title\" content=\"$title\" />\n\r"; 
						}
						// Add custom description
						$description = $options['website_description'];
						$wp_tagline = get_bloginfo('description');
						$description = str_replace("%tagline%", $wp_tagline, $description);
						if($description) {
							print "<meta property=\"og:description\" content=\"$description\" />\n\r";
						}
					} else { //single posts
						if ($options['add_title'] && ($title)) {
							// Post title
							print "<meta property=\"og:title\" content=\"$title\" />\n\r"; 
						}
				
						if($options['add_excerpt'] && ($description = wp_strip_all_tags((get_the_excerpt()), true))) {
							// Post excerpt
							print "<meta property=\"og:description\" content=\"$description\" />\n\r";
						}
					}
			
					// Add permalink
					if (($options['add_permalink']) && (is_front_page()) && ($link = get_option('home'))) {
						print "<meta property=\"og:url\" content=\"$link\" />\n\r";
					} else {
						if($options['add_permalink'] && ($link = get_permalink())) {
							print "<meta property=\"og:url\" content=\"$link\" />\n\r";
						}
					}
				
					// Add site name
					if ($site_name) {
						print "<meta property=\"og:site_name\" content=\"$site_name\" />\n\r";
					}
				
					// Add type
					if (($type = $options['fb_type']) && ($type != '_none')) {
						print "<meta property=\"og:type\" content=\"$type\" />\n\r";
					}
			
					// Add thumbnails
					if ($og_thumbnails) { // avoid error message when array is empty
						$og_thumbnails = array_unique($og_thumbnails); // unlikely, but hey!
						$total_img = count($og_thumbnails);
					}
								
					if (($total_img == 0) && ($web_thumb)) {
						print "<meta property=\"og:image\" content=\"$web_thumb\" />\n\r";
					} else if ($og_thumbnails) { // investige?
						foreach ($og_thumbnails as $og_thumbnail) {
							if ($og_thumbnail) {
								print "<meta property=\"og:image\" content=\"$og_thumbnail\" />\n\r";
							}
						}
					}
				
					// Add Facebook ID
					if ($fb_admins = $options['fb_admins']) {
						print "<meta property=\"fb:admins\" content=\"$fb_admins\" />\n\r";
					}

					// Add Facebook Application ID
					if ($fb_app_id = $options['fb_app_id']) {
						print "<meta property=\"fb:app_id\" content=\"$fb_app_id\" />\n\r";
					}
					
					//print "<meta property=\"og:description\" content=\"$user_agent\" />\n\r";

				}
			}
};

add_action('wp_head', 'OGraphr_Core_Init');
function OGraphr_Core_Init() {
	$core = new OGraphr_Core();
	$core->get_ographr_thumbnails();
}

// Display a Settings link on the main Plugins page
function ographr_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$ographr_links = '<a href="'.get_admin_url().'options-general.php?page=meta-ographr/meta-ographr_admin.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $ographr_links );
	}

	return $links;
}

?>