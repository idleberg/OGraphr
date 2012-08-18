<?php

/*  Copyright 2009 David Gwyer (email : d.v.gwyer@presscoders.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$admin_core = new OGraphr_Admin_Core();
$options = get_option('ographr_options');

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//register_activation_hook(__FILE__, 'ographr_restore_defaults');
register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options');
add_action('admin_init', array(&$admin_core, 'ographr_init') );
add_action('admin_menu', array(&$admin_core, 'ographr_add_options_page') );
add_action('admin_footer', array(&$admin_core, 'ographr_javascript') );

class OGraphr_Admin_Core {
	// --------------------------------------------------------------------------------------
	// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options')
	// --------------------------------------------------------------------------------------
	// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
	// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
	// --------------------------------------------------------------------------------------

	// Delete options table entries ONLY when plugin deactivated AND deleted
	function ographr_delete_plugin_options() {
		delete_option('ographr_options');
	}
	
	function date_diff($date1, $date2) { 
		$current = $date1; 
		$datetime2 = date_create($date2); 
		$count = 0; 
		while(date_create($current) < $datetime2){ 
			$current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current))); 
			$count++; 
		} 
		return $count; 
	} 
	
	function ographr_delete_postmeta() {
		
		$published = wp_count_posts();
		$published = $published->publish;
		$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_urls' );
		$ographr_urls = get_posts( $args );
		foreach($ographr_urls as $ographr_url) {
			$ographr_id = $ographr_url->ID;
			delete_post_meta($ographr_id, 'ographr_urls');
			delete_post_meta($ographr_id, 'ographr_indexed');
		}
		$today = date("Y-m-d");
		$yesterday = strtotime("yesterday");
		$yesterday = date("Y-m-d", $yesterday);	
		$stats[$yesterday] = array(
								'posts_total' => $published,
								'posts_indexed' => '0'
								);
		update_option('ographr_data', $stats);
	}

	// ------------------------------------------------------------------------------
	// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'ographr_restore_defaults')
	// ------------------------------------------------------------------------------
	// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
	// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
	// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
	//
	// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
	// ------------------------------------------------------------------------------

	// Define default option settings
	function ographr_restore_defaults() {
		$tmp = get_option('ographr_options');
	    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		
			if ($tmp['delete_postmeta'] == 1)
				$this->ographr_delete_postmeta();
			
			delete_option('ographr_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
			$arr = array(	"exec_mode" => "1",
							"data_expiry" => "-1",
							"advanced_opt" => "0",
							"website_title" => "%postname%",
							"website_thumbnail" => "",
							"enable_plugin_on_front" => "1",
							"enable_triggers_on_front" => "0",
							"website_description" => "",
							"not_always" => "0",
							"add_adminbar" => "0",
							"add_graph" => "0",
							"fill_curves" => "0",
							"smooth_curves" => "1",
							"add_comment" => "1",
							"add_title" => "1",
							"add_excerpt" => "1",
							"add_permalink" => "1",
							"enable_eight_tracks" => "1",
							"enable_bambuser" => "1",
							"enable_bandcamp" => "0",
							"enable_bliptv" => "1",
							"enable_dailymotion" => "1",
							"enable_flickr" => "1",
							"enable_hulu" => "1",
							"enable_internetarchive" => "1",
							"enable_justintv" => "1",
							"enable_livestream" => "1",
							"enable_mixcloud" => "1",
							"enable_official" => "1",
							"enable_soundcloud" => "1",
							"enable_ustream" => "1",
							"enable_viddler" => "0",
							"enable_vimeo" => "1",
							"enable_youtube" => "1",
							"add_post_images" => "1",
							"enable_videoposter" => "1",
							"enable_jwplayer" => "1",
							"add_attached_image" => "1",
							"add_post_thumbnail" => "0",
							"add_google_meta" => "0",
							"add_image_prop" => "0",
							"filter_smilies" => "1",
							"filter_themes" => "0",
							"filter_gravatar" => "1",
							"facebook_ua" => "0",
							"gplus_ua" => "0",
							"linkedin_ua" => "0",
							"digg_ua" => "0",
							"fb_site_name" => "%sitename%",
							"fb_type" => "_none"
			);
		
			update_option('ographr_options', $arr);
		}
	}

	// ------------------------------------------------------------------------------
	// CALLBACK FUNCTION FOR: add_action('admin_init', 'ographr_init' )
	// ------------------------------------------------------------------------------
	// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
	// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
	// API UNTIL YOU DO.
	// ------------------------------------------------------------------------------

	// Init plugin options to white list our options
	function ographr_init(){
		
		global $options;
		
		// 0.6
		wp_register_style( 'OGraphr_Stylesheet', plugins_url('/inc/style.css', __FILE__) );
		wp_register_script( 'OGraphr_JScript', plugins_url('/inc/scripts.js', __FILE__) );
		
		
		if ($options['add_graph']) {
			wp_register_style( 'JQPlot_Stylesheet', plugins_url('/inc/jquery.jqplot.min.css', __FILE__) );
			wp_register_script( 'JQPlot_Core', plugins_url('/inc/jquery.jqplot.min.js', __FILE__) );
			wp_register_script( 'JQPlot_highlighter', plugins_url('/inc/jqplot.highlighter.min.js', __FILE__) );
			wp_register_script( 'JQPlot_dateAxis', plugins_url('/inc/jqplot.dateAxisRenderer.min.js', __FILE__) );
		}		
		
		register_setting( 'ographr_plugin_options', 'ographr_options', array($this, 'ographr_validate_options') );

	}

	// ------------------------------------------------------------------------------
	// CALLBACK FUNCTION FOR: add_action('admin_menu', 'ographr_add_options_page');
	// ------------------------------------------------------------------------------
	// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
	// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
	// ------------------------------------------------------------------------------

	// Add menu page
	function ographr_add_options_page() {
		//add_options_page('OGraphr Settings', 'OGraphr', 'manage_options', __FILE__, array($this, 'ographr_render_form'));
		
		// 0.6
		$page = add_submenu_page( 'options-general.php', 
		                                 __( 'OGraphr Settings', 'OGraphr' ), 
		                                 __( 'OGraphr', 'OGraphr' ),
		                                 'manage_options',
		                                 __FILE__, 
		                                 array($this, 'ographr_render_form') );
		
		add_action( 'admin_print_styles-' . $page, array($this, 'my_plugin_admin_styles') );
	}
	
	function my_plugin_admin_styles() {
	       /*
	        * It will be called only on your plugin admin page, enqueue our stylesheet here
	        */
	
	global $options;
	
			wp_enqueue_style( 'OGraphr_Stylesheet' );
			wp_enqueue_script( 'OGraphr_JScript' );
		
			if ($options['add_graph']) {
				wp_enqueue_style( 'JQPlot_Stylesheet' );
				wp_enqueue_script( 'JQPlot_Core' );
				wp_enqueue_script( 'JQPlot_highlighter' );
				wp_enqueue_script( 'JQPlot_dateAxis' );
			}
	 
	}


	// ------------------------------------------------------------------------------
	// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
	// ------------------------------------------------------------------------------
	// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
	// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
	// SETTINGS ADMIN MENU.
	// ------------------------------------------------------------------------------

	// Render the Plugin options form
	function ographr_render_form() {
		$this->ographr_restore_defaults();
		?>
		<div class="wrap">
		
			<!-- Display Plugin Icon, Header, and Description -->
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>OGraphr Settings</h2>

			<form method="post" action="options.php">
				<?php settings_fields('ographr_plugin_options'); ?>
				<?php global $options; // = get_option('ographr_options'); ?>

				<br/><label><input name="ographr_options[advanced_opt]" type="checkbox" value="1" id="show_advanced" <?php if (isset($options['advanced_opt'])) { checked('1', $options['advanced_opt']); }  ?> /> Show advanced options </label>

				<!-- Beginning of the Plugin Options Form -->
				<table id="outer"><tbody><tr><td class="left">
						<!-- *********************** BEGIN: Main Content ******************* -->
						<form name="ographr-admin" method="post" action="http://wp.whyeye.org/wp-admin/options-general.php?page=meta-ographr.php">
						<?php wp_nonce_field('ographr_save_options','ographr_admin_options_form'); ?>
						<fieldset class="options">

						<dl>
							<dt><h3>General</h3></dt>
							<dd>
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>
							
							<!-- LINK TITLE -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Link Title:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[website_title]" value="<?php if ($options['website_title']) { echo $options['website_title']; } else { echo '%postname%';} ?>" /></td> 
							<td>&nbsp;</td>
							</tr>
						
							<tr valign="center"> 
								<th align="left" scope="row"><label>&nbsp;</label></th> 
								<td colspan="2"><small><code>%postname%</code> &#8211; page or post title<br/><code>%sitename%</code> &#8211; your blog's name (<em><? if($wp_name = get_option('blogname')) { echo $wp_name; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br/>
									<code>%siteurl%</code> &#8211; the URL of your blog (<em><? $wp_url = get_option('home'); $wp_url = (preg_replace('/https?:\/\//', NULL, $wp_url)); echo $wp_url; ?></em>)</small></td> 
							</tr>
						
							<!-- THUMBNAIL -->
							<tr valign="center"> 
								<th align="left" scope="row"><label>Thumbnail:</label></th> 
								<td><input type="text" size="75" name="ographr_options[website_thumbnail]" value="<?php echo $options['website_thumbnail']; ?>" /></td> 
								<td><small>(optional)</small></td>
							</tr>
						
							<tr valign="center"> 
								<th align="left" scope="row"><label>&nbsp;</label></th> 
								<td colspan="2"><small><code>%screenshot%</code> &#8211; your theme's default screenshot
								<?php
								$theme_path = get_bloginfo('template_url');
								$result = OGraphr_Core::remote_exists($theme_path . '/screenshot.png');
								if ($result) {
									print '(<a href="' . $theme_path . '/screenshot.png" target="_blank">preview</a>)';
								} else {
									print "(<span style=\"color:red;\">none</span>)";
								}
									 ?>
								</small></td> 
							</tr>
						
							<tr valign="center" class="advanced_opt"> 
								<th align="left" scope="row"><label>&nbsp;</label></th> 
								<td colspan="2"><label><input name="ographr_options[not_always]" type="checkbox" value="1" <?php if (isset($options['not_always'])) { checked('1', $options['not_always']); } ?> /> Only add thumbnail when post contains no images </label></td> 
							</tr>
						
							<!-- META TAGS -->
							<tr valign="center"> 
								<th align="left" scope="row"><label>Meta-tags:</label></th> 
								<td colspan="2"><label><input name="ographr_options[add_title]" type="checkbox" value="1" <?php if (isset($options['add_title'])) { checked('1', $options['add_title']); } ?> /> Add page title </label>&nbsp;

								<label><input name="ographr_options[add_excerpt]" type="checkbox" value="1" <?php if (isset($options['add_excerpt'])) { checked('1', $options['add_excerpt']); } ?> /> Add excerpt </label>&nbsp;

								<label><input name="ographr_options[add_permalink]" type="checkbox" value="1" <?php if (isset($options['add_permalink'])) { checked('1', $options['add_permalink']); } ?> /> Add permalink </label>&nbsp;

								</td>
							</tr>
						
							<!-- TRIGGERS -->
							<tr valign="top" class="advanced_opt"> 
								<th align="left" scope="row"><label>Triggers:</label></th> 
								<td colspan="2">								
									<label><input name="ographr_options[enable_eight_tracks]" type="checkbox" value="1" <?php if ((isset($options['enable_eight_tracks'])) && ($options['enable_eight_tracks'])) { checked('1', $options['enable_eight_tracks']); } ?> />&nbsp;8tracks</label>&nbsp;
							
									<label><input name="ographr_options[enable_bambuser]" type="checkbox" value="1" <?php if ((isset($options['enable_bambuser'])) && ($options['enable_bambuser'])) { checked('1', $options['enable_bambuser']); } ?> />&nbsp;Bambuser</label>&nbsp;
							
									<label><input name="ographr_options[enable_bandcamp]" type="checkbox" value="1" <?php if ((isset($options['enable_bandcamp'])) && ($options['bandcamp_api'])) { checked('1', $options['enable_bandcamp']); } ?> />&nbsp;Bandcamp</label>&nbsp;
							
									<label><input name="ographr_options[enable_bliptv]" type="checkbox" value="1" <?php if (isset($options['enable_bliptv'])) { checked('1', $options['enable_bliptv']); } ?> />&nbsp;Blip.tv</label>&nbsp;

									<label><input name="ographr_options[enable_dailymotion]" type="checkbox" value="1" <?php if (isset($options['enable_dailymotion'])) { checked('1', $options['enable_dailymotion']); } ?> />&nbsp;Dailymotion</label>&nbsp;
									<label><input name="ographr_options[enable_flickr]" type="checkbox" value="1" <?php if (isset($options['enable_flickr'])) { checked('1', $options['enable_flickr']); } ?> />&nbsp;Flickr</label>&nbsp;

									<label><input name="ographr_options[enable_hulu]" type="checkbox" value="1" <?php if (isset($options['enable_hulu'])) { checked('1', $options['enable_hulu']); } ?> />&nbsp;Hulu</label>&nbsp;
									
									<label><input name="ographr_options[enable_internetarchive]" type="checkbox" value="1" <?php if ((isset($options['enable_internetarchive'])) && ($options['enable_internetarchive'])) { checked('1', $options['enable_internetarchive']); } ?> />&nbsp;Internet Archive</label>&nbsp;
							
									<label><input name="ographr_options[enable_justintv]" type="checkbox" value="1" <?php if (isset($options['enable_justintv'])) { checked('1', $options['enable_justintv']); } ?> />&nbsp;Justin.tv</label>&nbsp;
									
									<label><input name="ographr_options[enable_livestream]" type="checkbox" value="1" <?php if (isset($options['enable_livestream'])) { checked('1', $options['enable_livestream']); } ?> />&nbsp;Livestream</label>&nbsp;
							
									<label><input name="ographr_options[enable_mixcloud]" type="checkbox" value="1" <?php if (isset($options['enable_mixcloud'])) { checked('1', $options['enable_mixcloud']); } ?> />&nbsp;Mixcloud</label>&nbsp;
							
									<label><input name="ographr_options[enable_official]" type="checkbox" value="1" <?php if (isset($options['enable_official'])) { checked('1', $options['enable_official']); } ?> />&nbsp;Official.fm</label>&nbsp;
							
									<?php if (OGRAPHR_BETA == TRUE) { ?>
										<label><input name="ographr_options[enable_playfm]" type="checkbox" value="1" <?php if ((isset($options['enable_playfm'])) && ($options['enable_playfm'])) { checked('1', $options['enable_playfm']); } ?> disabled="disabled" />&nbsp;Play.fm</label>&nbsp;
									<? } ?>
							
									<label><input name="ographr_options[enable_soundcloud]" type="checkbox" value="1" <?php if (isset($options['enable_soundcloud'])) { checked('1', $options['enable_soundcloud']); } ?> />&nbsp;SoundCloud</label>&nbsp;
							
									<label><input name="ographr_options[enable_ustream]" type="checkbox" value="1" <?php if (isset($options['enable_ustream'])) { checked('1', $options['enable_ustream']); } ?> />&nbsp;Ustream</label>&nbsp;
							
									<label><input name="ographr_options[enable_viddler]" type="checkbox" value="1" <?php if ((isset($options['enable_viddler'])) && ($options['viddler_api'])) { checked('1', $options['enable_viddler']); } ?> />&nbsp;Viddler</label>&nbsp;

									<label><input name="ographr_options[enable_vimeo]" type="checkbox" value="1" <?php if (isset($options['enable_vimeo'])) { checked('1', $options['enable_vimeo']); } ?> />&nbsp;Vimeo</label>&nbsp;

									<label><input name="ographr_options[enable_youtube]" type="checkbox" value="1" <?php if (isset($options['enable_youtube'])) { checked('1', $options['enable_youtube']); } ?> />&nbsp;YouTube</label>
							
								<? if((!$options['bandcamp_api']) && ($options['enable_bandcamp'])) { echo '<br/><span style="color:red;font-size:x-small;">Bandcamp requires a valid <a href="#bandcamp_api_key" style="color:red;">API key</a></span>';} ?>
								<? if((!$options['viddler_api']) && ($options['enable_viddler'])) { echo '<br/><span style="color:red;font-size:x-small;">Viddler requires a valid <a href="#viddler_api_key" style="color:red;">API key</a></span>';} ?></td> 
							</tr>
							
							<!-- ADVERTISEMENT -->
							<tr valign="top" class="advanced_opt"> 
								<th align="left" scope="row"><label>Advertisement:</label></th> 
								<td colspan="2">
									<label><input name="ographr_options[add_comment]" type="checkbox" value="1" <?php if (isset($options['add_comment'])) { checked('1', $options['add_comment']); } ?> /> Display plug-in name in source (<em>OGraphr v<? echo OGRAPHR_VERSION ?></em>)</label><br/>
								</td>
							</tr>
						
							</tbody></table></dd>
						</dl>
						
						<!-- F R O N T   P A G E -->
						<dl>
							<dt><h3>Front Page</h3></dt>
							<dd>
								<table width="100%" cellspacing="2" cellpadding="5"> 
								<tbody>
							
								<tr valign="center" class="advanced_opt"> 
									<th align="left" scope="row"><label>Functionality:</label></th> 
									<td colspan="2">
									<label><input name="ographr_options[enable_plugin_on_front]" type="checkbox" id="enable_plugin" value="1" <?php if (isset($options['enable_plugin_on_front'])) { checked('1', $options['enable_plugin_on_front']); } ?> /> Enable plug-in </label>&nbsp;
								
									<label><input name="ographr_options[enable_triggers_on_front]" type="checkbox" class="enable_triggers" value="1" <?php if (isset($options['enable_triggers_on_front'])) { checked('1', $options['enable_triggers_on_front']); }; if (!$options['enable_plugin_on_front']) { print 'disabled="disabled"';} ?> /> Enable triggers </label>&nbsp;
									</td> 
								</tr>

								<!-- CUSTOM DESCRIPTION -->	
								<tr valign="center"> 
								<th align="left" width="140px" scope="row"><label>Custom Description:</label></th> 
								<td width="30px"><input type="text" size="75" name="ographr_options[website_description]" class="enable_triggers" value="<?php echo $options['website_description']; ?>" /></td> 
								<td><small>(optional)</small></td>
								</tr>
							
								<tr valign="center"> 
									<th align="left" scope="row"><label>&nbsp;</label></th> 
									<td colspan="2"><small><code>%tagline%</code> &#8211; your blog's tagline (<em><? if(get_bloginfo('description')) { echo get_bloginfo('description'); } else { echo '<span style="color:red;">empty</span>';} ?></em>)</small></td> 
								</tr>
							
								</tbody></table></dd>			
						</dd>

						</dl>
					
						<!-- R E S T R I C T I O N S -->
						<dl class="advanced_opt">
							<dt><h3>Restrictions</h3></dt>
							<dd>
	
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>

								<!-- FILTERS -->
								<tr valign="center"> 
									<th align="left" width="140px" scope="row"><label>Filters:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[filter_gravatar]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_gravatar'])) { checked('1', $options['filter_gravatar']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?>/> Exclude avatars </label>&nbsp;
										
										<label><input name="ographr_options[filter_smilies]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_smilies'])) { checked('1', $options['filter_smilies']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude emoticons </label>&nbsp;
										
										<label><input name="ographr_options[filter_themes]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_themes'])) { checked('1', $options['filter_themes']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude themes </label>&nbsp;
									</td> 
										
								</tr>
							
								<!-- CUSTOM URLS -->
								<tr valign="top"> 
									<th align="left" width="140px" scope="row"><label>Custom URLs:</label></th> 
									<td colspan="2"><textarea name="ographr_options[filter_custom_urls]" cols="76%" rows="4" class="disable_filters"><?php echo $options['filter_custom_urls']; ?></textarea><br/>
										<small>You can enter filenames and URLs (e.g. <em><? echo 'http://' . $wp_url . '/wp-content'; ?></em>) to the filter-list above</small></td> 
								</tr>
							
								<!-- LIMIT ACCESS -->
								<tr valign="center"> 
									<th align="left" width="140px" scope="row"><a name="user_agents" id="user_agents"></a><label>User Agents:</label></th> 
									<td colspan="2">
										
										<!-- Checkbox -->
										<label><input name="ographr_options[digg_ua]" type="checkbox" value="1" <?php if (isset($options['digg_ua'])) { checked('1', $options['digg_ua']); } ?> /> Digg </label>&nbsp;
										
										<!-- Checkbox -->
										<label><input name="ographr_options[facebook_ua]" type="checkbox" value="1" <?php if (isset($options['facebook_ua'])) { checked('1', $options['facebook_ua']); } ?> /> Facebook </label>&nbsp;
										<!-- Checkbox -->
										<label><input name="ographr_options[gplus_ua]" type="checkbox" value="1" <?php if (isset($options['gplus_ua'])) { checked('1', $options['gplus_ua']); } ?> /> Google+ </label>&nbsp;
										
										<!-- Checkbox -->
										<label><input name="ographr_options[linkedin_ua]" type="checkbox" value="1" <?php if (isset($options['linkedin_ua'])) { checked('1', $options['linkedin_ua']); } ?> /> LinkedIn </label></td>
								</tr>
							
								<tr valign="top"> 
									<th align="left" width="140px" scope="row"><label>&nbsp;</label></th> 
									<td colspan="2"><small>Once a user-agent has been selected, the plugin will only be triggered when called by any of these sites. Neither Digg nor <a href="http://code.google.com/p/google-plus-platform/issues/detail?id=178" target="_blank" >Google+</a> currently use a unique user-agent, hence the detection is inaccurate</a>!</small></td>
								</tr>
						
							</tbody></table>			
						</dd>

						</dl>
					
						<!-- A P I   K E Y S -->
						<dl>
							<dt><h3>API Keys</h3></dt>
							<dd>
							<p>
								Bandcamp offers only limited access to their API and in any case you have to provide a valid <a href="http://bandcamp.com/developer#key_request" target="_blank">developer key</a> to make use of this feature. To support <em>legacy</em> Viddler widgets you will have to provide a valid <a href="http://developers.viddler.com/">API key</a>, whereas new embed codes use HTML5-compliant poster images and will work without one.
							</p>
							<p class="advanced_opt">All other services will work without providing an API key. However, if you prefer using your own ones, you can enter them below.</p>
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>
							
							<!-- 8TRACKS -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label><a name="etracks_api_key" id="etracks_api_key"></a>8tracks:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[etracks_api]" value="<?php if (($options['etracks_api'] != ETRACKS_API_KEY) && ($options['etracks_api'])) { echo $options['etracks_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
							
							<!-- BAMBUSER -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label><a name="bambuser_api_key" id="bambuser_api_key"></a>Bambuser:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[bambuser_api]" value="<?php if (($options['bambuser_api'] != BAMBUSER_API_KEY) && ($options['bambuser_api'])) { echo $options['bambuser_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>

							<!-- BANDCAMP -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label><a name="bandcamp_api_key" id="bandcamp_api_key"></a>Bandcamp:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[bandcamp_api]" value="<?php echo $options['bandcamp_api']; ?>" /></td> 
							<td><small>(<strong>required</strong>)</small></td>
							</tr>
						
							<!-- FLICKR -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Flickr:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[flickr_api]" value="<?php if (($options['flickr_api'] != FLICKR_API_KEY) && ($options['flickr_api'])) { echo $options['flickr_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- OFFICIAL.FM -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Official.fm:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[official_api]" value="<?php if (($options['official_api'] != OFFICIAL_API_KEY) && ($options['official_api'])) { echo $options['official_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<?php if (OGRAPHR_BETA == TRUE) { ?>	
								<tr valign="center" class="advanced_opt"> 
								<th align="left" width="140px" scope="row"><label>Play.fm:</label></th> 
								<td width="30px"><input type="text" size="75" name="ographr_options[playfm_api]" value="<?php if (($options['playfm_api'] != PLAYFM_API_KEY) && ($options['playfm_api'])) { echo $options['playfm_api']; } ?>" disabled="disabled" /></td>
								<td><small>(optional)</small></td>
							<? } ?>
						
							<!-- SOUNDCLOUD -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>SoundCloud:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[soundcloud_api]" value="<?php if (($options['soundcloud_api'] != SOUNDCLOUD_API_KEY) && ($options['soundcloud_api'])) { echo $options['soundcloud_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- USTREAM -->	
							<tr valign="center" class="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Ustream:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[ustream_api]" value="<?php if (($options['ustream_api'] != USTREAM_API_KEY) && ($options['ustream_api'])) { echo $options['ustream_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- VIDDLER  -->
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label><a name="viddler_api_key" id="viddler_api_key"></a>Viddler:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[viddler_api]" value="<?php echo $options['viddler_api']; ?>" /></td> 
							<td><small>(<strong>required</strong>)</small></td>
							</tr>	
						
							</tbody></table>			
						</dd>

						</dl>
					
						<!-- F A C E B O O K -->
						<dl class="advanced_opt">
							<dt><h3>Facebook</h3></dt>
							<dd>	
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>

							<!-- HUMAN READABLE-NAME -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Human-readable Name:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[fb_site_name]" value="<?php echo $options['fb_site_name']; ?>" /></td> 
							<td><small>(optional)</small</td>
							</tr>
						
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>&nbsp;</label></th> 
							<td colspan="2"><small><code>%sitename%</code> &#8211; your blog's name (<em><? if($wp_url) { echo $wp_name; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br />
								<code>%siteurl%</code> &#8211; the URL of your blog (<em><? echo $wp_url; ?></em>)</small></td> 
							</tr>
						
							<!-- OBJECT TYPE -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Object Type:</label></th> 
							<td width="30px">
								<select name='ographr_options[fb_type]'>
									<option value='_none' <?php selected('_none', $options['fb_type']); ?>>(none)</option>
									<option value='activity' <?php selected('activity', $options['fb_type']); ?>>activity</option>
									<option value='actor' <?php selected('actor', $options['fb_type']); ?>>actor</option>
									<option value='album' <?php selected('album', $options['fb_type']); ?>>album</option>
									<option value='article' <?php selected('article', $options['fb_type']); ?>>article</option>
									<option value='athlete' <?php selected('athlete', $options['fb_type']); ?>>athlete</option>
									<option value='author' <?php selected('author', $options['fb_type']); ?>>author</option>
									<option value='band' <?php selected('band', $options['fb_type']); ?>>band</option>
									<option value='bar' <?php selected('bar', $options['fb_type']); ?>>bar</option>
									<option value='blog' <?php selected('blog', $options['fb_type']); ?>>blog</option>
									<option value='book' <?php selected('book', $options['fb_type']); ?>>book</option>
									<option value='cafe' <?php selected('cafe', $options['fb_type']); ?>>cafe</option>
									<option value='cause' <?php selected('cause', $options['fb_type']); ?>>cause</option>
									<option value='city' <?php selected('city', $options['fb_type']); ?>>city</option>
									<option value='company' <?php selected('company', $options['fb_type']); ?>>company</option>
									<option value='country' <?php selected('country', $options['fb_type']); ?>>country</option>
									<option value='director' <?php selected('director', $options['fb_type']); ?>>director</option>
									<option value='drink' <?php selected('drink', $options['fb_type']); ?>>drink</option>
									<option value='food' <?php selected('food', $options['fb_type']); ?>>food</option>
									<option value='game' <?php selected('game', $options['fb_type']); ?>>game</option>
									<option value='government' <?php selected('government', $options['fb_type']); ?>>government</option>
									<option value='hotel' <?php selected('hotel', $options['fb_type']); ?>>hotel</option>
									<option value='landmark' <?php selected('landmark', $options['fb_type']); ?>>landmark</option>
									<option value='movie' <?php selected('movie', $options['fb_type']); ?>>movie</option>
									<option value='musician' <?php selected('musician', $options['fb_type']); ?>>musician</option>
									<option value='non_profit' <?php selected('non_profit', $options['fb_type']); ?>>non_profit</option>
									<option value='politician' <?php selected('politician', $options['fb_type']); ?>>politician</option>
									<option value='product' <?php selected('product', $options['fb_type']); ?>>product</option>
									<option value='public_figure' <?php selected('public_figure', $options['fb_type']); ?>>public_figure</option>
									<option value='restaurant' <?php selected('restaurant', $options['fb_type']); ?>>restaurant</option>
									<option value='school' <?php selected('school', $options['fb_type']); ?>>school</option>
									<option value='song' <?php selected('song', $options['fb_type']); ?>>song</option>
									<option value='sport' <?php selected('sport', $options['fb_type']); ?>>sport</option>
									<option value='sports_league' <?php selected('sports_league', $options['fb_type']); ?>>sports_league</option>
									<option value='sports_team' <?php selected('sports_team', $options['fb_type']); ?>>sports_team</option>
									<option value='state_province' <?php selected('state_province', $options['fb_type']); ?>>state_province</option>
									<option value='tv_show' <?php selected('tv_show', $options['fb_type']); ?>>tv_show</option>
									<option value='university' <?php selected('university', $options['fb_type']); ?>>university</option>
									<option value='website' <?php selected('website', $options['fb_type']); ?>>website</option>

								</select>
								</td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- FACEBOOK ADMIN -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Admin ID:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[fb_admins]" value="<?php echo $options['fb_admins']; ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>&nbsp;</label></th> 
							<td colspan="2"><small>If you administer a page for your blog on Facebook, you can enter your <a href="http://developers.facebook.com/docs/reference/api/user/" target="_blank">User ID</a> above</small></td> 
							</tr>
						
							<!-- FACEBOOK APP -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Application ID:</label></th> 
							<td><input type="text" size="75" name="ographr_options[fb_app_id]" value="<?php echo $options['fb_app_id']; ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>&nbsp;</label></th> 
							<td colspan="2"><small>If your blog uses a Facebook app, you can enter your <a href="https://developers.facebook.com/apps" target="_blank">Application ID</a> above</small></td> 

							</tr>						
						
							</tbody></table>			
						</dd>

						</dl>
						
						<!-- E X P E R T -->
						<dl class="advanced_opt">
							<dt><h3>Expert Settings</h3></dt>
							<dd>
								<table width="100%" cellspacing="2" cellpadding="5"> 
								<tbody>
									
								<!-- IMAGE RETRIEVAL -->	
								<tr valign="top"> 
									<th align="left" scope="row"><label>Image Retrieval:</label></th> 
									<td colspan="2">
										<div id="enable_expiry">
											<label><input name="ographr_options[exec_mode]" type="radio" value="1" <?php if (isset($options['exec_mode'])) { checked('1', $options['exec_mode']); } ?>  />&nbsp;Only once when saving a post (default, better performance)&nbsp;</label><br/>

											<label><input name="ographr_options[exec_mode]" type="radio" value="2" <?php if (isset($options['exec_mode'])) { checked('2', $options['exec_mode']); } ?> id="enable_expiry" />&nbsp;Everytime your site is visited (slow, more accurate)&nbsp;</label>
										</div>
									</td> 
								</tr>

								<tr valign="center"> 
								<th align="left" width="140px" scope="row">&nbsp;</th> 
								<td colspan="2"><small>Retrieving images <em>on-post</em> decreases the loadtime of your page significantly, but on the downside the results might be outdated at some point. Should you choose to retrieve images <em>on-view</em>, it is recommended to <a href="#user_agents">restrict access</a> to decrease load times for human readers.</small></td> 
								<td>&nbsp;</td>
								</tr>
									
								<!-- DATA EXPIRY -->	
								<tr valign="center"> 
								<th align="left" width="140px" scope="row"><label>Data Expiry:</label></th> 
								<td colspan="2">
									<select name='ographr_options[data_expiry]' class="no_expiry" <?php if ($options['exec_mode'] == 2) print 'disabled="disabled"'; ?> >
										<option value='-1' <?php selected('-1', $options['data_expiry']); ?> >never</option>
										<?php if(OGRAPHR_DEBUG) { ?>
											<option value='1' <?php selected('1', $options['data_expiry']); ?> >after 1 day</option>
										<?php } ?>
										<option value='30' <?php selected('30', $options['data_expiry']); ?> >after 30 days</option>
										<option value='60' <?php selected('60', $options['data_expiry']); ?> >after 60 days</option>
										<option value='90' <?php selected('90', $options['data_expiry']); ?> >after 90 days</option>
										<option value='180' <?php selected('180', $options['data_expiry']);?> >after 6 months</option>
										<option value='270' <?php selected('270', $options['data_expiry']); ?> >after 9 months</option>
										<option value='364' <?php selected('364', $options['data_expiry']); ?> >after 12 months</option>
									</select>
									</td> 
								<td>&nbsp;</td>
								</tr>
									
								<!-- MORE TRIGGERS -->
								<tr valign="center"> 
									<th align="left" scope="row"><label>More Triggers:</label></th> 
									<td colspan="2">

										<label><input name="ographr_options[enable_videoposter]" type="checkbox" value="1" <?php if (isset($options['enable_videoposter'])) { checked('1', $options['enable_videoposter']); } ?> /> Video posters </label>&nbsp;

										<label><input name="ographr_options[enable_jwplayer]" type="checkbox" value="1" <?php if (isset($options['enable_jwplayer'])) { checked('1', $options['enable_jwplayer']); } ?> /> JW Player </label>&nbsp;

										<label><input name="ographr_options[add_post_images]" type="checkbox" id="enable_images" value="1" <?php if (isset($options['add_post_images'])) { checked('1', $options['add_post_images']); } ?> /> Post images </label>&nbsp;

										<label><input name="ographr_options[add_attached_image]" type="checkbox" value="1" id="attached_image" <?php if (isset($options['add_attached_image'])) { checked('1', $options['add_attached_image']); } ?> /> Attached images </label>&nbsp;
										
										<label><input name="ographr_options[add_post_thumbnail]" type="checkbox" value="1" class="post_thumbnail" <?php if (isset($options['add_post_thumbnail'])) { checked('1', $options['add_post_thumbnail']); }; if ($options['add_attached_image']) { print 'disabled="disabled"'; } ?> /> Post thumbnail (<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">?</a>)</label>&nbsp;
									</td>
								</tr>
							
								<!-- GOOGLE SNIPPETS -->
								<tr valign="center"> 
									<th align="left" scope="row"><label>Google+ Snippets:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[add_google_meta]" type="checkbox" value="1" <?php if (isset($options['add_google_meta'])) { checked('1', $options['add_google_meta']); } ?> /> Meta-tags (<a href="https://developers.google.com/+/plugins/snippet/" target="_blank">?</a>)</label>&nbsp;

										<label><input name="ographr_options[add_image_prop]" type="checkbox" value="1" <?php if (isset($options['add_image_prop'])) { checked('1', $options['add_image_prop']); } ?> /> Image properties (<a href="http://schema.org/docs/gs.html" target="_blank">?</a>)</label>&nbsp;
									</td>
								</tr>
								
								<!-- INTERFACE -->
								<tr valign="center"> 
									<th align="left" scope="row"><label>Interface:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[add_adminbar]" type="checkbox" value="1" <?php if (isset($options['add_adminbar'])) { checked('1', $options['add_adminbar']); } ?> /> Add menu to admin bar</label>&nbsp;
										
										<label><input name="ographr_options[add_graph]" id="enable_graph" class="no_expiry" type="checkbox" value="1" <?php if (isset($options['add_graph'])) { checked('1', $options['add_graph']); }; if ($options['exec_mode'] == 2) print 'disabled="disabled"'; ?>/> Add visual graph</label>&nbsp;
									</td>
								</tr>
								
								<!-- STATISTICS -->
								<tr valign="center"> 
									<th align="left" scope="row"><label>Visual Graph:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[fill_curves]" class="disable_graph no_expiry" type="checkbox" value="1" <?php if (isset($options['fill_curves'])) { checked('1', $options['fill_curves']); }; if((!$options['add_graph']) || ($options['exec_mode'] == 2)) print 'disabled="disabled"'; ?>/> Fill curves</label>&nbsp;
										
										<label><input name="ographr_options[smooth_curves]" class="disable_graph no_expiry" type="checkbox" value="1" <?php if (isset($options['smooth_curves'])) { checked('1', $options['smooth_curves']); }; if((!$options['add_graph']) || ($options['exec_mode'] == 2)) print 'disabled="disabled"'; ?> /> Smooth curves</label>&nbsp;
									</td>
								</tr>
								
								</tbody></table></dd>			
						</dd>

						</dl>

						<label class="advanced_opt"><input name="ographr_options[chk_default_options_db]" type="checkbox" value="1" class="advanced_opt" <?php if(!OGRAPHR_DEBUG) print 'id="enable_delete"'; ?> <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon saving</label>&nbsp;
						
						<label class="advanced_opt"><input name="ographr_options[delete_postmeta]" type="checkbox" value="1" class="advanced_opt<?php if(!OGRAPHR_DEBUG) print ' enable_delete'; ?>" <?php if (isset($options['delete_postmeta'])) { checked('1', $options['delete_postmeta']); } ?> <?php if(!OGRAPHR_DEBUG) print 'disabled="disabled"'; ?> /> and delete all indexed data </label>
						
						<div class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
						</div>

						</fieldset>
						</form>
						<!-- *********************** END: Main Content ********************* -->
						<p class="yifooter"><a style="" href="http://wordpress.org/extend/plugins/meta-ographr/" target="_blank">OGraphr <? echo OGRAPHR_VERSION ?></a> &copy <? $this_year = date(Y); if (date(Y) > 2012) { print "2012-$this_year"; } else { print "2012"; } ?> by Jan T. Sott</p>
						</td> <!-- [left] -->

						<td class="right">
						<!-- *********************** BEGIN: Sidebar ************************ -->		

						<dl>
							<dt><h4>Navigator</h4></dt>
							<dd>
							<ul>
								<li><strong><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/" target="_blank">Website</a></strong></li>
								<li><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/faq/" title="Frequently Asked Questions" target="_blank">FAQ</a></li>
								<li><a class="lwp" href="http://wordpress.org/tags/meta-ographr?forum_id=10" target="_blank">Need help?</a></li>
								<li class="advanced_opt"><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/changelog/" target="_blank">Changes</a></li>
								<li class="advanced_opt"><a class="lwp" href="http://plugins.svn.wordpress.org/meta-ographr/" target="_blank">SVN</a></li>
							
								<li><a class="lhome" href="http://whyeye.org" target="_blank">whyEye.org</a></li>
								<li>&nbsp;</li>
								<li><a href="https://twitter.com/whyeye_org" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @whyeye_org</a>
								<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>
							</ul>			
							</dd>

						</dl>

						<dl>
							<dt><h4>Humble Mumble</h4></dt>
							<dd>
							<p style="font-size:8pt;">If you want to support this plugin, why not buy me a coffee?</p>
							<ul>
								<li><strong><a class="lpaypal" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FVPU9H7CMUU6U" target="_blank">Buy coffee!</a></strong></li>
								<li><a class="lamazon" href="http://www.amazon.de/registry/wishlist/PPAO8XTAGS4V/ref=cm_sw_r_tw_ws_F5.Hpb18F73RS" target="_blank">Wishful thinking</a></li>
							</ul>			
							</dd>

						</dl>
						
						<?php if ($options['add_graph']) { ?>
							<dl>
								<dt><h4>Statistics</h4></dt>
								<dd>
									<?php
										global $post;
										$posts_published = wp_count_posts();
										$posts_published = $posts_published->publish;
										$args = array( 'numberposts' => $posts_published, 'meta_key' => 'ographr_urls' );
										$myposts = get_posts( $args );
										$posts_harvested = count($myposts);
										
										if ($posts_published >= 1 ) {
											$posts_percent = $posts_harvested * 100 / $posts_published;
											$posts_percent = round($posts_percent, 1);
										} else {
											$posts_percent = 0;
										}
										
										$pages_published = wp_count_posts('page');
										$pages_published = $pages_published->publish;
										$args = array( 'number' => $pages_published, 'meta_key' => 'ographr_urls' );
										$mypages = get_pages( $args );
										$pages_harvested = count($mypages);
										
										if ($pages_published >= 1 ) {
											$pages_percent = $pages_harvested * 100 / $pages_published;
											$pages_percent = round($pages_percent, 1);
										} else {
											$pages_percent = 0;
										}
										
									?>
								<?php if ( ($options['exec_mode'] == 1) && ($options['add_graph']) ) { ?>								
									<div id="chartdiv" style="height:110px;width:100%; "></div>
								<?php } ?>
								<p style="font-size:8pt;">
									<? print "Posts indexed: $posts_harvested / $posts_published <span style=\"color:#999;\">&nbsp;$posts_percent%</span>"; ?><br/>
									<? print "Pages indexed: $pages_harvested / $pages_published <span style=\"color:#999;\">&nbsp;$pages_percent%</span>"; ?>
								</p>
								
								</dd>

							</dl>
						<?php } ?>
						<!-- *********************** END: Sidebar ************************ -->
						</td> <!-- [right] -->


						</tr></tbody></table>
				<!-- Table Structure Containing Form Controls -->
				<!-- Each Plugin Option Defined on a New Table Row -->

			</form>

		</div>
		<?php	
	}

	// Sanitize and validate input. Accepts an array, return a sanitized array.
	function ographr_validate_options($input) {
		 // strip html from textboxes
		$input['website_title'] =  htmlentities($input['website_title']);
		$input['website_thumbnail'] =  htmlentities($input['website_thumbnail']);
		$input['website_description'] =  htmlentities($input['website_description']);
		$input['filter_custom_urls'] =  htmlentities($input['filter_custom_urls']);
		$input['etracks_api'] =  htmlentities($input['etracks_api']);
		$input['bambuser_api'] =  htmlentities($input['bambuser_api']);
		$input['bandcamp_api'] =  htmlentities($input['bandcamp_api']);
		$input['flickr_api'] =  htmlentities($input['flickr_api']);
		$input['official_api'] =  htmlentities($input['official_api']);
		$input['soundcloud_api'] =  htmlentities($input['soundcloud_api']);
		$input['ustream_api'] =  htmlentities($input['ustream_api']);
		$input['viddler_api'] =  htmlentities($input['viddler_api']);
		$input['fb_site_name'] =  htmlentities($input['fb_site_name']);
		$input['fb_admins'] =  htmlentities($input['fb_admins']);
		$input['fb_app_id'] =  htmlentities($input['fb_app_id']);
		
		return $input;
	}

	//add JQuery to footer
	function ographr_javascript() {
		
		global $options;
		
		if ($options['add_graph']) {
			$stats = get_option('ographr_data');
			if(empty($stats)) {
				$published = wp_count_posts();
				$published = $published->publish;
				
				$yesterday = strtotime("yesterday");
				$yesterday = date("Y-m-d", $yesterday);		
				$stats[$yesterday] = array(
										'posts_total' => $published,
										'posts_indexed' => '0'
										);
			}
				
			foreach($stats as $key => $value) {
				$posts_total = "$posts_total, ['$key', $value[posts_total]]";
			}
			$posts_total = substr($posts_total, 2);
	
			foreach($stats as $key => $value) {
				$posts_indexed = "$posts_indexed, ['$key', $value[posts_indexed]]";
			}
			$posts_indexed = substr($posts_indexed, 2);
			
			// scale grid
			$first_day = array_shift(array_keys($stats));
			$today = strtotime("today");
			$last_day = date("Y-m-d", $today);
			$interval = $this->date_diff($first_day, $last_day);
			var_dump($interval);
		?>
	
		<script type="text/javascript">
					
			function render_stats() {

				var line1=[<? print $posts_total; ?>];
				var line2=[<? print $posts_indexed; ?>];
				  var plot1 = jQuery.jqplot('chartdiv', [line1, line2], {
					series:[{color:'#bd8cbf'},{color:'#8560a8'}],
					axesDefaults: {
						pad: 0,
						tickOptions: {
							showLabel: false,
						},
					},							
					seriesDefaults: {
						lineWidth: '1.5',
						showMarker: true,
						fill: <? if ($options['fill_curves']) { print "true"; } else { print "false"; } ?>,
						fillAlpha: 0.9,
						markerOptions: {
							size:5,
						 	<?php if ($options['fill_curves']) { print 'color: "#ed1c24",'; } ?>
						},
						rendererOptions: {
							smooth: <? if ($options['smooth_curves']) { print "true"; } else { print "false"; } ?>,
							}
						},
					grid: {
			            drawBorder: false,
			            shadow: false,
						background: '#fcfcfc',
						borderWidth: '1'
					},
					axes:{
				        xaxis:{
				          renderer:jQuery.jqplot.DateAxisRenderer,
				          tickInterval:'<?php if ($interval > 720 ) { print "1 year"; } else if ($interval > 90 ) { print "1 month"; } else if ($interval > 21 ) { print "1 week"; } else { print "1 day"; } ?>',
				          min: <? print '"' . date("F j, Y", strtotime(array_shift(array_keys($stats))) ) . '"'; ?>,
				          tickOptions:{
				            formatString:'%b&nbsp;%#d'
				          }		
				        }
				      },
				      highlighter: {
				        show: true,
				        sizeAdjust: 7.5
				      },
				      cursor: {
				        show: false
				      }
				  });
			}
	    </script>
	
		<?php } // OGRAPHR_BETA == TRUE
	}

}; // end of class