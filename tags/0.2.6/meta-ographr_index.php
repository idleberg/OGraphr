<?php
/*
Plugin Name: OGraphr
Plugin URI: http://whyeye.org
Description: This plugin scans posts for videos (YouTube, Vimeo, Dailymotion) and music players (SoundCloud, Mixcloud, Bandcamp) and adds their thumbnails as an OpenGraph meta-tag. While at it, the plugin also adds OpenGraph tags for the title, description (excerpt) and permalink. Thanks to Sutherland Boswell and Matthias Gutjahr!
Version: 0.2.6
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
    define("OGRAPHR_VERSION", "0.2.6");

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

//MIXCLOUD
	// default artwork size (small=25x25, thumbnail=50x50, medium_mobile=80x80, medium=150x150, large=300x300, extra_large=600x600)
	define("MIXCLOUD_IMAGE_SIZE", "large");
	
//BANDCAMP
	// default artwork size (small_art_url=100x100, large_art_url=350x350)
	define("BANDCAMP_IMAGE_SIZE", "large_art_url");

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/meta-ographr_admin.php';

class OGraphr_Core {

	//get first image of a post
	function get_article_img() {
	  global $post, $posts;
	  $image = '';
  
	  $image = get_post_meta($post->ID, 'articleimg', true);
  
	  return $image;
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

	//
	// The Main Event
	//
	function get_ographr_thumbnails($post_id=null) {
	
		// Get this plugins' settings
		$options = get_option('ographr_options');
	
		
		//if (is_single() || is_page()) {
	
		// Get the post ID if none is provided
		if($post_id==null OR $post_id=='') $post_id = get_the_ID();

		// Gets the post's content
		$post_array = get_post($post_id); 
		$markup = $post_array->post_content;
		$markup = apply_filters('the_content',$markup);
		$og_thumbnails[] = null;

		// Get default website thumbnail
		$web_thumb = $options['website_thumbnail'];
		if ($web_thumb) {
			$og_thumbnails[] = $web_thumb;
		}
	
		// Get API keys
		$soundcloud_api = $options['soundcloud_api'];
		$bandcamp_api = $options['bandcamp_api'];

		// debugging?
		if(OGRAPHR_DEBUG == TRUE) {
			print "\n\r<!-- OGRAPHR v" . OGRAPHR_VERSION ." DEBUGGER -->\n\r";
			print "<!-- SoundCloud API key: $soundcloud_api -->\n\r";
			print "<!-- Bandcamp API key: $bandcamp_api -->\n\r";
		}
	
		if (($enable_on_front = $options['enable_on_front']) || is_single() || (is_page())) {
		
			// Get images in post
			preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $markup, $matches);
			foreach($matches[1] as $match) {
			  	if(OGRAPHR_DEBUG == TRUE) {
					print "<!-- Image tag: $match -->\n\r";
				}
				// filter Wordpress smilies
				preg_match('/\/images\/smilies\/icon_.+/', $match, $filter);
				if (!$filter[0]) {
					$og_thumbnails[] = $match;
				}
			}
	
			// Get images attached to post (duplicates will be filtered later)
			$website_thumbnail = $this->get_article_img();
			if ($website_thumbnail) {
				$og_thumbnails[] = $website_thumbnail;
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

					// Now if we've found a YouTube ID, let's set the thumbnail URL
					foreach($matches as $match) {
						$yt_thumbnail = 'http://img.youtube.com/vi/' . $match . '/0.jpg';
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
					preg_match_all('#<object[^>]+>.+?http://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches1);
				
					// Vimeo iFrame player ("new embed code")
					preg_match_all('#http://player.vimeo.com/video/([0-9]+)#s', $markup, $matches2);
				
					// Vimeo shortcode (Viper's Video Quicktags)
					preg_match_all('/\[vimeo.*?]https?:\/\/w*.?vimeo.com\/([0-9]+)\[\/vimeo]/', $markup, $matches3);
				
					$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);
		
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
					preg_match_all('#<object[^>]+>.+?http://www.dailymotion.com/swf/video/([A-Za-z0-9-_]+).+?</object>#s', $markup, $matches1);
				
					// Dailymotion iFrame player
					preg_match_all('#https?://www.dailymotion.com/embed/video/([A-Za-z0-9-_]+)#s', $markup, $matches2);
				
					// Dailymotion shortcode (Viper's Video Quicktags)
					preg_match_all('/\[dailymotion.*?]https?:\/\/w*.?dailymotion.com\/video\/([A-Za-z0-9-_]+)\[\/dailymotion]/', $markup, $matches3);
				
					$matches = array_merge($matches1[1], $matches2[1], $matches3[1]);

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
		
			
			// SOUNDCLOUD
				if($options['enable_soundcloud']) {
					// Standard embed code for tracks (Flash and HTML5 player)
					preg_match_all('/api.soundcloud.com%2Ftracks%2F([0-9]+)/', $markup, $matches1);
				
					// Shortcode for tracks (Flash and HTML5 player)
					preg_match_all('/api.soundcloud.com\/tracks\/([0-9]+)/', $markup, $matches2);
				
					$matches = array_merge($matches1[1], $matches2[1]);
		
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
					preg_match_all('/mixcloudLoader.swf\?feed=http%3A%2F%2Fwww.mixcloud.com%2F([A-Za-z0-9\-_\%]+)&/', $markup, $matches);
					
					// Standard embed (API v1, undocumented)
					// preg_match_all('/feed=http:\/\/www.mixcloud.com\/api\/1\/cloudcast\/([A-Za-z0-9\-_\%\/.]+)/', $markup, $mixcloud_ids);
					
					// Now if we've found a Mixcloud ID, let's set the thumbnail URL
					foreach($matches[1] as $match) {
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
		
					// Now if we've found a Bandcamp ID, let's set the thumbnail URL
					foreach($matches[1] as $match) {
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
		
					// Now if we've found a Bandcamp ID, let's set the thumbnail URL
					foreach($matches[1] as $match) {
						$bandcamp_thumbnail = $this->get_bandcamp_thumbnail('track', $bandcamp_api, $match);
						if(OGRAPHR_DEBUG == TRUE) {
							print "<!-- Bandcamp track: $bandcamp_thumbnail (ID:$match) -->\n\r";
						}
						if (isset($bandcamp_thumbnail)) {
							$og_thumbnails[] = $bandcamp_thumbnail;
						}
					}
				}
	}
				
				// Let's print all this
				if(($options['add_comment']) && (OGRAPHR_DEBUG == FALSE)) {
					print "<!-- OGraphr v" . OGRAPHR_VERSION . " - http://p.ly/ographr -->\n\r";
				}
			
				// Add title & description
				$title = $options['website_title'];
				$site_name = $options['fb_site_name'];
				$wp_title = get_the_title();
				$wp_name = get_bloginfo('name');
				$wp_url = get_option('home');
				$wp_url = preg_replace('/https?:\/\//', NULL, $wp_url);
				$title = str_replace("%postname%", $wp_title, $title);
				$title = str_replace("%sitename%", $wp_name, $title);
				$title = str_replace("%siteurl%", $wp_url, $title);
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
				$og_thumbnails = array_unique($og_thumbnails);
				foreach ($og_thumbnails as $og_thumbnail) {
					if ($og_thumbnail) {
						print "<meta property=\"og:image\" content=\"$og_thumbnail\" />\n\r";
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
			}
};

add_action('wp_head', 'OGraphr_Core_Init');

function OGraphr_Core_Init() {
	$core = new OGraphr_Core();
	$core->get_ographr_thumbnails();
}


?>