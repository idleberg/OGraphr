<?php
/*
Plugin Name: Meta-OGraphr
Plugin URI: http://whyeye.org
Description: This plugin scans posts for videos (YouTube, Vimeo, Dailymotion) and music players (SoundCloud, Mixcloud, Bandcamp) and adds their thumbnails as an OpenGraph meta-tag. While at it, the plugin also adds OpenGraph tags for the title, description (excerpt) and permalink. Thanks to Sutherland Boswell and Matthias Gutjahr!
Version: 0.1.1
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

// no need to change this unless you want to use your own SoundCloud API key (-> http://soundcloud.com/you/apps)
define("SOUNDCLOUD_API_KEY", "15fd95172fa116c0837c4af8e45aa702");

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/meta-ographr_admin.php';


//get first image of a post
function getFirstImage() {
  global $post, $posts;
  $image = '';
  
  $image = get_post_meta($post->ID, 'articleimg', true);

  if(empty($image)){ //Gets first image
	ob_start();
	ob_end_clean();
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$image = $matches [1] [0];
  }
  
  if(empty($image)){ //Defines a default image
	$image = get_option('MetaOGraphr_website_thumbnail');
	$image = ($image['text_string']);
  }
  return $image;
}

// Get Vimeo Thumbnail
function get_vimeo_thumbnail($id, $info = 'thumbnail_large') {
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
		$output = $output[0][$info];
		if (curl_error($ch) != null) {
			$output = ''; //new WP_Error('vimeo_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
		}
		curl_close($ch);
		return $output;
	}
};

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
};

// Get SoundCloud Thumbnail
function get_soundcloud_thumbnail($type, $id) {
	if (!function_exists('curl_init')) {
		return null;
	} else {
		$ch = curl_init();
		$key = get_option('MetaOGraphr_soundcloud_api');
		$key = ($key['text_string']);
		if ($key == null) {
			$key = SOUNDCLOUD_API_KEY;
		}
		$videoinfo_url = "http://api.soundcloud.com/$type/$id.json?client_id=$key";
		curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = curl_exec($ch);
		$output = json_decode($output);
		$output = $output->artwork_url;
		$output = str_replace('-large.', '-t300x300.', $output); // replace 100x100 default image
		if (curl_error($ch) != null) {
			$output = ''; //new WP_Error('soundcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
		}
		curl_close($ch);
		return $output;
	}
};

// Get Mixcloud Thumbnail
function get_mixcloud_thumbnail($id) {
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
		$output = $output->pictures->large;
		if (curl_error($ch) != null) {
			$output = ''; //new WP_Error('mixcloud_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
		}
		curl_close($ch);
		return $output;
	}
};

// Get Bandcamp Thumbnail
function get_bandcamp_thumbnail($type, $id) {
	if (!function_exists('curl_init')) {
		return null;
	} else {
		$ch = curl_init();
		$key = get_option('MetaOGraphr_bandcamp_api');
		$key = ($key['text_string']);
		if ($type == 'album') {
			$videoinfo_url = "http://api.bandcamp.com/api/album/2/info?key=$key&album_id=$id";
		} else if ($type == 'track') {
			$videoinfo_url = "http://api.bandcamp.com/api/track/1/info?key=$key&track_id=$id";
		}
		curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = curl_exec($ch);
		$output = json_decode($output);
		$output = $output->large_art_url;
		if (curl_error($ch) != null) {
			$output = ''; //new WP_Error('bandcamp_info_retrieval', __("Error retrieving video information from the URL <a href=\"" . $videoinfo_url . "\">" . $videoinfo_url . "</a>: <code>" . curl_error($ch) . "</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve."));
		}
		curl_close($ch);
		return $output;
	}
};

//
// The Main Event
//
function get_opengraph_thumbnails($post_id=null) {
	
	// Get the post ID if none is provided
	if($post_id==null OR $post_id=='') $post_id = get_the_ID();

	// Gets the post's content
	$post_array = get_post($post_id); 
	$markup = $post_array->post_content;
	$markup = apply_filters('the_content',$markup);
	$og_thumbnails[] = null;
	
	//
	$website_thumbnail = getFirstImage();
	if ($website_thumbnail) {
		$og_thumbnails[] = $website_thumbnail;
	}
	
	
	// YOUTUBE
		// Checks for the old standard YouTube embed
		preg_match_all('#<object[^>]+>.+?https?://www.youtube.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches);

		// Now if we've found a Vimeo ID, let's set the thumbnail URL
		foreach($matches[1] as $matche) {
			  $yt_thumbnail = 'http://img.youtube.com/vi/' . $matche. '/0.jpg';
			if (isset($yt_thumbnail)) {
			  $og_thumbnails[] = $yt_thumbnail;
			}
		}
	
		// Checks for YouTube iframe, the new standard since at least 2011
		preg_match_all('#https?://www.youtube.com/embed/([A-Za-z0-9\-_]+)#s', $markup, $matches);
		foreach($matches[1] as $match) {
			$yt_thumbnail = 'http://img.youtube.com/vi/' . $match . '/0.jpg';
			if (isset($yt_thumbnail)) {
			  $og_thumbnails[] = $yt_thumbnail;
			}
		}

	
	// VIMEO
		// Standard embed code
		preg_match_all('#<object[^>]+>.+?http://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches);
		
		// Now if we've found a Vimeo ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$vm_thumbnail = get_vimeo_thumbnail($match, $info = 'thumbnail_large');
			if (isset($vm_thumbnail)) {
			  $og_thumbnails[] = $vm_thumbnail;
			}
		}
		
		// Find Vimeo embedded with iframe code
		preg_match_all('#http://player.vimeo.com/video/([0-9]+)#s', $markup, $matches);
		
		// Now if we've found a Vimeo ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$vm_thumbnail = get_vimeo_thumbnail($match, $info = 'thumbnail_large');
			if (isset($vm_thumbnail)) {
			  $og_thumbnails[] = $vm_thumbnail;
			}
		}
		
	
	// DAILYMOTION
		// Dailymotion flash
		preg_match_all('#<object[^>]+>.+?http://www.dailymotion.com/swf/video/([A-Za-z0-9]+).+?</object>#s', $markup, $matches);

		// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$dailymotion_thumbnail = get_dailymotion_thumbnail($match);
			if (isset($dailymotion_thumbnail)) {
				$dailymotion_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $dailymotion_thumbnail); // remove suffix
				$og_thumbnails[] = $dailymotion_thumbnail;
			}
		}

		// Dailymotion iframe
		preg_match_all('#https?://www.dailymotion.com/embed/video/([A-Za-z0-9]+)#s', $markup, $matches);

		// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$dailymotion_thumbnail = get_dailymotion_thumbnail($match);
			if (isset($dailymotion_thumbnail)) {
				$dailymotion_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $dailymotion_thumbnail); // remove suffix
				$og_thumbnails[] = $dailymotion_thumbnail;
			}
		}
		
			
	// SOUNDCLOUD
		// Standard embed code for tracks (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com%2Ftracks%2F([0-9]+)/', $markup, $matches);
		
		// Now if we've found a SoundCloud ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$sc_thumbnail = get_soundcloud_thumbnail('tracks', $match);
			if (isset($sc_thumbnail)) {
			  	$sc_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $sc_thumbnail); // remove suffix
				$og_thumbnails[] = $sc_thumbnail;
			}
		}
		
		// Standard embed code for playlists (Flash and HTML5 player)
		preg_match_all('/api.soundcloud.com%2Fplaylists%2F([0-9]+)/', $markup, $matches);
		
		// Now if we've found a SoundCloud ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$sc_thumbnail = get_soundcloud_thumbnail('playlists', $match);
			if (isset($sc_thumbnail)) {
			  	$sc_thumbnail = preg_replace('/\?([A-Za-z0-9]+)/', '', $sc_thumbnail); // remove suffix
				$og_thumbnails[] = $sc_thumbnail;
			}
		}

	
	// MIXCLOUD	
		// Standard embed code
		preg_match_all('/mixcloudLoader.swf\?feed=http%3A%2F%2Fwww.mixcloud.com%2F([A-Za-z0-9\-_\%]+)&/', $markup, $match);
		
		// Now if we've found a Mixcloud ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$mixcloud_id = str_replace('%2F', '/', $match);
			$mixcloud_thumbnail = get_mixcloud_thumbnail($mixcloud_id);
			if (isset($mixcloud_thumbnail)) {
				$og_thumbnails[] = $mixcloud_thumbnail;
			}
		}
		
		// Standard embed (API v1, undocumented)
		// preg_match_all('/feed=http:\/\/www.mixcloud.com\/api\/1\/cloudcast\/([A-Za-z0-9\-_\%\/.]+)/', $markup, $mixcloud_ids);
					
	
	// BANDCAMP
		// Standard embed code for albums
		preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/album=([0-9]+)\//', $markup, $matches);
		
		// Now if we've found a Bandcamp ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$bandcamp_thumbnail = get_bandcamp_thumbnail('album', $match);
			if (isset($bandcamp_thumbnail)) {
				$og_thumbnails[] = $bandcamp_thumbnail;
			}
		}

		// Standard embed code for single tracks
		preg_match_all('/bandcamp.com\/EmbeddedPlayer\/v=2\/track=([0-9]+)\//', $markup, $matches);
		
		// Now if we've found a Bandcamp ID, let's set the thumbnail URL
		foreach($matches[1] as $match) {
			$bandcamp_thumbnail = get_bandcamp_thumbnail('track', $match);
			if (isset($bandcamp_thumbnail)) {
				$og_thumbnails[] = $bandcamp_thumbnail;
			}
		}
		
		// Let's print all this
		if($title = get_the_title()) {print "<meta property=\"og:title\" content=\"$title\" />\n\r"; }
		if($description = wp_strip_all_tags((get_the_excerpt()), true)) { print "<meta property=\"og:description\" content=\"$description\" />\n\r"; }
		if($link = get_permalink()) { print "<meta property=\"og:url\" content=\"$link\" />\n\r"; }
		$og_thumbnails = array_unique($og_thumbnails);
		foreach ($og_thumbnails as $og_thumbnail) {
			if ($og_thumbnail) {
				print "<meta property=\"og:image\" content=\"$og_thumbnail\" />\n\r";
			}
			
		}

};

add_action('wp_head', 'get_opengraph_thumbnails');

?>