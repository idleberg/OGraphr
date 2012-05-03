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

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//register_activation_hook(__FILE__, 'ographr_restore_defaults');
register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options');
add_action('admin_init', array($admin_core, 'ographr_init') );
add_action('admin_menu', array($admin_core, 'ographr_add_options_page') );
add_action('admin_head', array($admin_core, 'ographr_stylesheet') );
add_action('admin_footer', array($admin_core, 'ographr_javascript') );
add_filter( 'plugin_action_links', array($admin_core, 'ographr_plugin_action_links'), 10, 2 );

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
			delete_option('ographr_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
			$arr = array(	"exec_mode" => "1",
							"advanced_opt" => "0",
							"website_title" => "%postname%",
							"website_thumbnail" => "",
							"enable_plugin_on_front" => "1",
							"enable_triggers_on_front" => "0",
							"website_description" => "",
							"not_always" => "0",
							"add_comment" => "1",
							"add_title" => "1",
							"add_excerpt" => "1",
							"add_permalink" => "1",
							"enable_eight_tracks" => "1",
							"enable_bandcamp" => "1",
							"enable_bliptv" => "1",
							"enable_dailymotion" => "1",
							"enable_flickr" => "1",
							"enable_hulu" => "1",
							"enable_justintv" => "1",
							"enable_mixcloud" => "1",
							"enable_official" => "1",
							"enable_soundcloud" => "1",
							"enable_ustream" => "1",
							"enable_viddler" => "1",
							"enable_vimeo" => "1",
							"enable_youtube" => "1",
							"filter_smilies" => "1",
							"filter_gravatar" => "1",
							"facebook_ua" => "0",
							"gplus_ua" => "0",
							"linkedin_ua" => "0",
							"add_comment" => "1",
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
		add_options_page('OGraphr Settings', 'OGraphr', 'manage_options', __FILE__, array($this, 'ographr_render_form'));
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
				<?php $options = get_option('ographr_options'); ?>

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
						
							<!-- IMAGE RETRIEVAL -->	
							<tr valign="top" id="advanced_opt"> 
								<th align="left" scope="row"><label>Image Retrieval:</label></th> 
								<td colspan="2">
									<label><input name="ographr_options[exec_mode]" type="radio" value="1" <?php if (isset($options['exec_mode'])) { checked('1', $options['exec_mode']); } ?> />&nbsp;Only once when saving a post (default, better performance)&nbsp;</label><br/>
								
									<label><input name="ographr_options[exec_mode]" type="radio" value="2" <?php if (isset($options['exec_mode'])) { checked('2', $options['exec_mode']); } ?> />&nbsp;Everytime your site is visited (slow, more accurate)&nbsp;</label>
								</td> 
							</tr>
						
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row">&nbsp;</th> 
							<td colspan="2"><small>Retrieving images <em>on-post</em> decreases the loadtime of your page significantly, but on the downside the results might be outdated at some point. Should you choose to retrieve images <em>on-view</em>, it is recommended to <a href="#user_agents">restrict access</a> to decrease load times for human readers.</small></td> 
							<td>&nbsp;</td>
							</tr>
						
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
						
							<tr valign="center" id="advanced_opt"> 
								<th align="left" scope="row"><label>&nbsp;</label></th> 
								<td colspan="2"><label><input name="ographr_options[not_always]" type="checkbox" value="1" <?php if (isset($options['not_always'])) { checked('1', $options['not_always']); } ?> /> Only add thumbnail when post contains no images </label></td> 
							</tr>
						
							<!-- META TAGS -->
							<tr valign="center"> 
								<th align="left" scope="row"><label>Meta-tags:</label></th> 
								<td colspan="2"><label><input name="ographr_options[add_title]" type="checkbox" value="1" <?php if (isset($options['add_title'])) { checked('1', $options['add_title']); } ?> /> Add page title </label>&nbsp;

								<label><input name="ographr_options[add_excerpt]" type="checkbox" value="1" <?php if (isset($options['add_excerpt'])) { checked('1', $options['add_excerpt']); } ?> /> Add excerpt </label>&nbsp;

								<label><input name="ographr_options[add_permalink]" type="checkbox" value="1" <?php if (isset($options['add_permalink'])) { checked('1', $options['add_permalink']); } ?> /> Add permalink </label>&nbsp;

								<label><input name="ographr_options[add_post_thumbnail]" type="checkbox" value="1" <?php if (isset($options['add_post_thumbnail'])) { checked('1', $options['add_post_thumbnail']); } ?> /> Add post thumbnail (<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">?</a>)</label></td>
							</tr>
						
							<!-- TRIGGERS -->
							<tr valign="top" id="advanced_opt"> 
								<th align="left" scope="row"><label>Triggers:</label></th> 
								<td colspan="2">								
									<label><input name="ographr_options[enable_eight_tracks]" type="checkbox" value="1" <?php if ((isset($options['enable_eight_tracks'])) && ($options['enable_eight_tracks'])) { checked('1', $options['enable_eight_tracks']); } ?> />&nbsp;8tracks</label>&nbsp;
							
							
								<label><input name="ographr_options[enable_bandcamp]" type="checkbox" value="1" <?php if ((isset($options['enable_bandcamp'])) && ($options['bandcamp_api'])) { checked('1', $options['enable_bandcamp']); } ?> />&nbsp;Bandcamp</label>&nbsp;
							
								<label><input name="ographr_options[enable_bliptv]" type="checkbox" value="1" <?php if (isset($options['enable_bliptv'])) { checked('1', $options['enable_bliptv']); } ?> />&nbsp;Blip.tv</label>&nbsp;

								<label><input name="ographr_options[enable_dailymotion]" type="checkbox" value="1" <?php if (isset($options['enable_dailymotion'])) { checked('1', $options['enable_dailymotion']); } ?> />&nbsp;Dailymotion</label>&nbsp;
								<label><input name="ographr_options[enable_flickr]" type="checkbox" value="1" <?php if (isset($options['enable_flickr'])) { checked('1', $options['enable_flickr']); } ?> />&nbsp;Flickr</label>&nbsp;

								<label><input name="ographr_options[enable_hulu]" type="checkbox" value="1" <?php if (isset($options['enable_hulu'])) { checked('1', $options['enable_hulu']); } ?> />&nbsp;Hulu</label>&nbsp;
							
								<label><input name="ographr_options[enable_justintv]" type="checkbox" value="1" <?php if (isset($options['enable_justintv'])) { checked('1', $options['enable_justintv']); } ?> />&nbsp;Justin.tv</label>&nbsp;
							
								<label><input name="ographr_options[enable_mixcloud]" type="checkbox" value="1" <?php if (isset($options['enable_mixcloud'])) { checked('1', $options['enable_mixcloud']); } ?> />&nbsp;Mixcloud</label>&nbsp;
							
								<label><input name="ographr_options[enable_official]" type="checkbox" value="1" <?php if (isset($options['enable_official'])) { checked('1', $options['enable_official']); } ?> />&nbsp;Official.fm</label>&nbsp;
							
								<!-- PLAY.FM
								<label><input name="ographr_options[enable_playfm]" type="checkbox" value="1" <?php if ((isset($options['enable_playfm'])) && ($options['enable_playfm'])) { checked('1', $options['enable_playfm']); } ?> />&nbsp;Play.fm</label>&nbsp;
								-->
							
								<label><input name="ographr_options[enable_soundcloud]" type="checkbox" value="1" <?php if (isset($options['enable_soundcloud'])) { checked('1', $options['enable_soundcloud']); } ?> />&nbsp;SoundCloud</label>&nbsp;
							
								<label><input name="ographr_options[enable_ustream]" type="checkbox" value="1" <?php if (isset($options['enable_ustream'])) { checked('1', $options['enable_ustream']); } ?> />&nbsp;Ustream</label>&nbsp;
							
								<label><input name="ographr_options[enable_viddler]" type="checkbox" value="1" <?php if ((isset($options['enable_viddler'])) && ($options['viddler_api'])) { checked('1', $options['enable_viddler']); } ?> />&nbsp;Viddler</label>&nbsp;

								<label><input name="ographr_options[enable_vimeo]" type="checkbox" value="1" <?php if (isset($options['enable_vimeo'])) { checked('1', $options['enable_vimeo']); } ?> />&nbsp;Vimeo</label>&nbsp;

								<label><input name="ographr_options[enable_youtube]" type="checkbox" value="1" <?php if (isset($options['enable_youtube'])) { checked('1', $options['enable_youtube']); } ?> />&nbsp;YouTube</label>
							
								<? if((!$options['bandcamp_api']) && ($options['enable_bandcamp'])) { echo '<br/><span style="color:red;font-size:x-small;">Bandcamp requires a valid <a href="#bandcamp_api_key" style="color:red;">API key</a></span>';} ?>
								<? if((!$options['viddler_api']) && ($options['enable_viddler'])) { echo '<br/><span style="color:red;font-size:x-small;">Viddler requires a valid <a href="#viddler_api_key" style="color:red;">API key</a></span>';} ?></td> 
							</tr>
						
							<!-- ADVERTISEMENT -->
							<tr valign="center" id="advanced_opt"> 
								<th align="left" scope="row"><label>Advertisement:</label></th> 
								<td colspan="2"><label><input name="ographr_options[add_comment]" type="checkbox" value="1" <?php if (isset($options['add_comment'])) { checked('1', $options['add_comment']); } ?> /> Display plug-in name in source (<em>OGraphr v<? echo OGRAPHR_VERSION ?></em>)</label></td>
							</tr>
						
							</tbody></table></dd>
						</dl>
					
						<!-- F R O N T   P A G E -->
						<dl>
							<dt><h3>Front Page</h3></dt>
							<dd>
								<table width="100%" cellspacing="2" cellpadding="5"> 
								<tbody>
							
								<tr valign="center" id="advanced_opt"> 
									<th align="left" scope="row"><label>Functionality:</label></th> 
									<td colspan="2">
									<label><input name="ographr_options[enable_plugin_on_front]" type="checkbox" id="enable_plugin" value="1" <?php if (isset($options['enable_plugin_on_front'])) { checked('1', $options['enable_plugin_on_front']); } ?> /> Enable plugin </label>&nbsp;
								
									<label><input name="ographr_options[enable_triggers_on_front]" type="checkbox" id="enable_triggers" value="1" <?php if (isset($options['enable_triggers_on_front'])) { checked('1', $options['enable_triggers_on_front']); }; if (!$options['enable_plugin_on_front']) { print 'disabled="disabled"';} ?> /> Enable triggers </label>&nbsp;
									</td> 
								</tr>

								<!-- CUSTOM DESCRIPTION -->	
								<tr valign="center"> 
								<th align="left" width="140px" scope="row"><label>Custom Description:</label></th> 
								<td width="30px"><input type="text" size="75" name="ographr_options[website_description]" value="<?php echo $options['website_description']; ?>" /></td> 
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
						<dl id="advanced_opt">
							<dt><h3>Restrictions</h3></dt>
							<dd>
	
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>

								<!-- FILTERS -->
								<tr valign="center"> 
									<th align="left" width="140px" scope="row"><label>Filters:</label></th> 
									<td colspan="2"><label><input name="ographr_options[filter_smilies]" type="checkbox" value="1" <?php if (isset($options['filter_smilies'])) { checked('1', $options['filter_smilies']); } ?> /> Exclude emoticons </label>&nbsp;
									<label><input name="ographr_options[filter_gravatar]" type="checkbox" value="1" <?php if (isset($options['filter_gravatar'])) { checked('1', $options['filter_gravatar']); } ?> /> Exclude avatars </label></td> 
								</tr>
							
								<!-- CUSTOM URLS -->
								<tr valign="top"> 
									<th align="left" width="140px" scope="row"><label>Custom URLs:</label></th> 
									<td colspan="2"><textarea name="ographr_options[filter_custom_urls]" cols="76%" rows="4" ><?php echo $options['filter_custom_urls']; ?></textarea><br/>
										<small><strong>BETA:</strong> You can enter filenames and URLs (e.g. <em><? echo 'http://' . $wp_url . '/wp-content'; ?></em>) to the filter-list above</small></td> 
								</tr>
							
								<!-- LIMIT ACCESS -->
								<tr valign="center"> 
									<th align="left" width="140px" scope="row"><a name="user_agents" id="user_agents"></a><label>User Agents:</label></th> 
									<td colspan="2"><label><input name="ographr_options[facebook_ua]" type="checkbox" value="1" <?php if (isset($options['facebook_ua'])) { checked('1', $options['facebook_ua']); } ?> /> Facebook </label>&nbsp;
										<!-- Checkbox -->
										<label><input name="ographr_options[gplus_ua]" type="checkbox" value="1" <?php if (isset($options['gplus_ua'])) { checked('1', $options['gplus_ua']); } ?> /> Google+ </label>&nbsp;
											<!-- Checkbox -->
											<label><input name="ographr_options[linkedin_ua]" type="checkbox" value="1" <?php if (isset($options['linkedin_ua'])) { checked('1', $options['linkedin_ua']); } ?> /> LinkedIn </label></td>
								</tr>
							
								<tr valign="top"> 
									<th align="left" width="140px" scope="row"><label>&nbsp;</label></th> 
									<td colspan="2"><small>Once a user-agent has been selected, the plugin will only be triggered when called by any of these sites. Google+ currently does not use a unique <a href="http://code.google.com/p/google-plus-platform/issues/detail?id=178" target="_blank" >user-agent</a>!</small></td>
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
							<p id="advanced_opt">All other services will work without providing an API key. However, if you prefer using your own ones, you can enter them below.</p>
							<table width="100%" cellspacing="2" cellpadding="5"> 
							<tbody>
							
							<!-- 8TRACKS -->	
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label><a name="etracks_api_key" id="etracks_api_key"></a>8tracks:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[etracks_api]" value="<?php if ($options['etracks_api']) { echo $options['etracks_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>

							<!-- BANDCAMP -->	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label><a name="bandcamp_api_key" id="bandcamp_api_key"></a>Bandcamp:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[bandcamp_api]" value="<?php echo $options['bandcamp_api']; ?>" /></td> 
							<td><small>(<strong>required</strong>)</small></td>
							</tr>
						
							<!-- FLICKR -->	
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Flickr:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[flickr_api]" value="<?php if ($options['flickr_api']) { echo $options['flickr_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- OFFICIAL.FM -->	
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Official.fm:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[official_api]" value="<?php if ($options['official_api']) { echo $options['official_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- PLAY.FM 	
							<tr valign="center"> 
							<th align="left" width="140px" scope="row"><label>Play.fm:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[playfm_api]" value="<?php if ($options['playfm_api']) { echo $options['playfm_api']; } else { echo PLAYFM_API_KEY; } ?>" /></td>
							-->
						
							<!-- SOUNDCLOUD -->	
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>SoundCloud:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[soundcloud_api]" value="<?php if ($options['soundcloud_api']) { echo $options['soundcloud_api']; } ?>" /></td> 
							<td><small>(optional)</small></td>
							</tr>
						
							<!-- USTREAM -->	
							<tr valign="center" id="advanced_opt"> 
							<th align="left" width="140px" scope="row"><label>Ustream:</label></th> 
							<td width="30px"><input type="text" size="75" name="ographr_options[ustream_api]" value="<?php if ($options['ustream_api']) { echo $options['ustream_api']; } ?>" /></td> 
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
						<dl id="advanced_opt">
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
						
							<!-- OFFICIAL.FM -->	
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

						<label id="advanced_opt"><input name="ographr_options[chk_default_options_db]" type="checkbox" value="1" id="advanced_opt" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon saving</label>
						<div class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
						</div>

						</fieldset>
						</form>
						<!-- *********************** END: Main Content ********************* -->
						<p class="yifooter"><a style="" href="http://wordpress.org/extend/plugins/meta-ographr/" target="_blank">OGraphr <? echo OGRAPHR_VERSION ?></a> &copy 2012 by Jan T. Sott</p>
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
								<li id="advanced_opt"><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/changelog/" target="_blank">Changes</a></li>
								<li id="advanced_opt"><a class="lwp" href="http://plugins.svn.wordpress.org/meta-ographr/" target="_blank">SVN</a></li>
							
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
		?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
				if (! $('#show_advanced').attr('checked') ) {
					$('div.nothing,#advanced_opt').hide();
				}
				$('#show_advanced').click(function(){
					$('div.nothing,#advanced_opt').fadeToggle('slow');
				});
			
				$("#enable_plugin").click(enable_cb);
		});
	
		function enable_cb() {
		  if (this.checked) {
		    $("input#enable_triggers").removeAttr("disabled");
		  } else {
		    $("input#enable_triggers").attr("disabled", true);
		  }
		}
	    </script>
		<?php
	}

	//add CSS to header
	function ographr_stylesheet() {
		?>
		<style type="text/css">
		table#outer {
			width: 100%;
			border: 0 none;
			padding:0;
			margin:0; 
		}
		table#outer fieldset {
			border: 0 none;
			padding:0;
			margin:0;
		}
		table#outer td.left, table#outer td.right {
			vertical-align:top;
		}
		table#outer td.left {
			padding: 0 8px 0 0;
		}
		table#outer td.right {
			padding: 0 0 0 8px;
			width: 210px;
		}
		td.right ul, td.right ul li {
			list-style: none;
			padding:0;
			margin:0;
			}
		td.right a {
			text-decoration:none;
			background-position:0px 60%;
			background-repeat:no-repeat;
			padding: 4px 0px 4px 22px;
			border: 0 none;
			display:block;}
		td.right a.lhome {
			background-image:url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAgMAAABinRfyAAAADFBMVEUAAADeACH///8AAABwQ/WkAAAABHRSTlP///8AQCqp9AAAAAFiS0dEAxEMTPIAAAAJcEhZcwAAAEgAAABIAEbJaz4AAAAJdnBBZwAAABAAAAAQAFzGrcMAAABHSURBVAjXY2BgYOBnEA0NDWYQCA0NYTgKIo6lpaUAiWVLGI4tS50CFVuWBuSGLQOylk5bAhH7GBrqwvCHgUGE4f///2DiAAAcwB84mfGumgAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxMi0wNC0xM1QxNDoyOToyMiswMjowMJVTIygAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTItMDQtMTNUMTQ6Mjk6MjIrMDI6MDDkDpuUAAAAAElFTkSuQmCC);
		}
		td.right a.lpaypal {
			background-image:url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQEhELxx+pjgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAAAnUExURZwMDOfv787W3tbe55y1xgAxY/f39////73O1oSctXOUrZSlva29zmehiRYAAAABdFJOUwBA5thmAAAAdElEQVR42m1O0RLAIAgyG1Gr///eYbXrbjceFAkxM4GzwAyse5qgqEcB5gyhB+kESwi8cYfgnu2DMEcfFDDNwCakR06T4uq5cK0n9xOQPXByE3JEpYG2hKYgHdnxZgUeglxjCV1vihx4N1BluM6JC+8v//EAp9gC4zRZsZgAAAAASUVORK5CYII=)
		}
		td.right a.lamazon {
			background-image:url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQESUI53q1mgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAABgUExURerBhcOLOqB1OX1gOE5DNjc1NYKBgfGnPNqZO4hnOEM8NWZSN86SO1pKNnFZN7eDOuWgPJRuOVBOTpuamo+NjURCQubm5v///9rZ2WloaKinp11bW3Z0dPPy8srKyrSzs09bnaIAAACiSURBVHjaTY3ZFoMgDAUDchuruFIN1qX//5eNYJc85EyG5EIBBNACEibsimi5UaUURJtI5wm+KwgSJflVkOFscBUTM1vgrmacThfomGVLO9MhIYFsF8wyx6Jnl88HUxEay+wYmlM6oNKcNYrIC58iHMcIyQlZRNmf/2LRQUX8bYwh3PCYWmOGrueargdXGO5d6UGm5FSmBqzXEzK2cN9PcXsD9XsKTHawijcAAAAASUVORK5CYII=)
		}
		td.right a.lwp {
			background-image:url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACWklEQVR42n1TyWoaUBR11UU+oJ/QQqHL0C/qwkU2bl2EYEiixeCQSRIFN4EEEodYDYRYUUwgQwuNrVoTcNiIaOtEnT2955LQkrR9cOG9e8857w7vGQyPltVqfbm8vGxfWVm5Ehvf2xV9jBn+twRostls3wOBQOLu7u5juVz+QeOePsaIeUKcn59/JoHYxsZGvlarFdLpNHw+H8QHi8WC7e1tXFxcoNFofCOGWHL+vNkhoE/NZrNN8NLS0l8tGo1C1k9iWdID+TVT6/V62ePjY0QiEayuriIcDuuetre3B6kfw+GQAhgMBmVyRPSV3i7g04dgoVCAy+VSgel0qr79/X3Y7XaUyiU9t5pNxk81CxHIVSqVK97OJU2D0+mEw+HA7e2t+o6OjvR8eBjQ883nG5Aj3C8UGIxGo8bW1habBNljZ2cHa2triMVimoVMAe+koT6vTwVkIpCSOZGmCvDg9XpxdnamhFQqBbfbrZ3neTweY319XUup1+vweDzo9/sU6FCgwHRCoRB2d3cxmUxQrVb1xmw2i0QioSLBYFCFT05OIKP8XYI0ws2GZDIZ+P1+lEolJRwcHGjti4uLEAeKxSJJEDzi8bg2kQN4GGO90+nkObJkMqkCrVZLp2EymSAxTCdT5HM5sFftdjtHDrn6FpiFPI7rbrfboYjMmSAICAsLC7i8vNQszs/PIY+tQ6ze/vgpb25ufuVTlgbpCDkNGrPiYkwwmSdP+fFnkoZ+kJqvhdOgcU/fvz7TjNhzsRdib2ZnZ98ajcb3ZrO5Mjc3Bxr39DFGzD2WnJlfboSSy4YB5JcAAAAASUVORK5CYII=)
		}
		td.right a.ltwitter {
			background-image:url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAB20lEQVR4nKWTPWhTURzFf+/mJXl5uby+JFaTfiwawWArQYmDU0GHTtKgc9VBEfxqVeokKuokVARDYxEpLh0KunQQFBR0EIu0RdpBKqh0EIy2tc9Y4/twKA3pCxRKzvi/95x77jn8Fc/zaASiIfZmBMSFoS7RVzy+oYA4X8jWXegrmmJgZBJpvszv29W5oYAmjVExMDIp+oerL0WNpivEklniKca/WqfF5Uc9tRylNsSjY+8+PPm00MFKGcq/XuG5J0KxrTMVo1lHDcKKhbr03bIXS+3u3VOLdQ7ymdZxogYkkhBPdonYtulKxNAJhiGggiaxZUxqTfGCuPTwaZ0DIJ0bnZ54v4yJY4PjgBpcJa/h31/4swzWEu61HsXfwtzZzuYXq8QQhLT1ZICASu+e9vmZM4feVr8gzt03186PdbTc7t0u5/FcUBR/6Ejh2ldzLfcyW2QeQAXQInpB9D8Yw/Om0KJmZmf6J064DdVn0HUYPJCa2mHqRcCqZvB5wbqRG3p++Ee5kiUUBhkD3VhnXwY8+3o2MXdxb+tJ4I2/RgncHJz40j08W2r7+NuTqEFQBFLFPpiMlG7tT83uTkTvAM9qTflb6AaOAOmamQW8Bh4D3/yZ+AU2jYa38T+I6JdNPFroagAAAABJRU5ErkJggg==)
		}
		td.right ul li {
			padding:0;
			margin:0;
		}
		table#outer td dl {
			padding:0;
			margin: 10px 0 20px 0;
			background-color: white;
			border: 1px solid #dfdfdf;
		}
		table#outer td dl {
			-moz-border-radius: 5px;
			-khtml-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px;
		}
		table h3, table h4 {
			-moz-border-radius-topleft: 5px;
			-moz-border-radius-topright: 5px;
			-khtml-border-top-left-radius: 5px;
			-khtml-border-top-right-radius: 5px;
			-webkit-border-top-left-radius: 5px;
			-webkit-border-top-right-radius: 5px;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
		}
		table td dl dd{
			-moz-border-radius-bottomleft: 5px;
			-moz-border-radius-bottomright: 5px;
			-khtml-border-bottom-left-radius: 5px;
			-khtml-border-bottom-right-radius: 5px;
			-webkit-border-bottom-left-radius: 5px;
			-webkit-border-bottom-right-radius: 5px;
			border-bottom-left-radius: 5px;
			border-bottom-right-radius: 5px;
		}
		table#outer dl h3, table#outer td.right dl h4 {
			font-size: 10pt;
			font-weight: bold;
			margin:0;
			padding: 4px 10px 4px 10px;
			background-color: ##F1F1F1;
			background-image:-ms-linear-gradient(top,#f9f9f9,#ececec);
			background-image:-moz-linear-gradient(top,#f9f9f9,#ececec);
			background-image:-o-linear-gradient(top,#f9f9f9,#ececec);
			background-image:-webkit-gradient(linear,left top,left bottom,from(#f9f9f9),to(#ececec));
			background-image:-webkit-linear-gradient(top,#f9f9f9,#ececec);
			background-image:linear-gradient(top,#f9f9f9,#ececec);
			text-shadow: white 0 1px 0;
		}
		table#outer td.left dl h4 {
			font-size: 10pt;
			font-weight: bold;
			margin:0;
			padding: 4px 0 4px 0;
		}
		dd {
			background-color: #f8f8f8;
		}
		table#outer td.left dd {
			margin:0;
			padding: 10px 20px 10px 20px;
		}
		table#outer td.right dd {
			margin:0;
			padding: 5px 10px 5px 10px;
		}
		table#outer .info {
			color: #555;
			font-size: .85em;
		}
		table#outer p {
			padding:5px 0 5px 0;
			margin:0;
		}
		input.yi_warning:hover {
			background: #ce0000;
			color: #fff;
		}
		table#outer .yifooter {
			text-align: center;
			font-size: .85em;
		}
		table#outer .yifooter a, table#outer .yifooter a:link {
			text-decoration:none;
		}
		table#outer td small {
			color: #555; font-size: .85em;
		}
		table#outer hr {
			border: none 0;
			border-top: 1px solid #BBBBBB;
			height: 1px;
		}
		table#outer ul {
			list-style:none;
		}
		table#outer ul.mybullet {
			list-style-type:disc;
			padding-left: 20px;
		}
		.yiinfo {
			font-size:85%;
			line-height: 115%;
			}
		</style>
		<?php
	}
}; // end of class