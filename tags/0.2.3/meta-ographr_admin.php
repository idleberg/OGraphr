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


// ------------------------------------------------------------------------
// PLUGIN PREFIX:                                                          
// ------------------------------------------------------------------------
// A PREFIX IS USED TO AVOID CONFLICTS WITH EXISTING PLUGIN FUNCTION NAMES.
// WHEN CREATING A NEW PLUGIN, CHANGE THE PREFIX AND USE YOUR TEXT EDITORS 
// SEARCH/REPLACE FUNCTION TO RENAME THEM ALL QUICKLY.
// ------------------------------------------------------------------------

// 'ographr_' prefix is derived from [p]plugin [o]ptions [s]tarter [k]it

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//register_activation_hook(__FILE__, 'ographr_restore_defaults');
register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options');
add_action('admin_init', 'ographr_init' );
add_action('admin_menu', 'ographr_add_options_page');
add_filter( 'plugin_action_links', 'ographr_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function ographr_delete_plugin_options() {
	delete_option('ographr_options');
	
	$pointone = get_option('MetaOGraphr_soundcloud_api');
	if (!isset($pointone)) {
		ographr_delete_old_plugin_options();
	}
}

// Delete options from 0.1 of this plugin
function ographr_delete_old_plugin_options() {
	delete_option('MetaOGraphr_page_title');
	delete_option('MetaOGraphr_website_thumbnail');
	delete_option('MetaOGraphr_soundcloud_api');
	delete_option('MetaOGraphr_bandcamp_api');
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
		$arr = array(	"website_title" => "%postname%",
						"website_thumbnail" => "",
						"bandcamp_api" => "",
						"soundcloud_api" => SOUNDCLOUD_API_KEY,
						"enable_on_front" => "0",
						"website_description" => "",
						"add_comment" => "1",
						"add_title" => "1",
						"add_excerpt" => "1",
						"add_permalink" => "1",
						"enable_youtube" => "1",
						"enable_vimeo" => "1",
						"enable_dailymotion" => "1",
						"enable_soundcloud" => "1",
						"enable_mixcloud" => "1",
						"enable_bandcamp" => "1",
						"add_comment" => "1"
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
	register_setting( 'ographr_plugin_options', 'ographr_options', 'ographr_validate_options' );
	
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'ographr_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function ographr_add_options_page() {
	add_options_page('OGraphr Settings', 'OGraphr', 'manage_options', __FILE__, 'ographr_render_form');
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
	ographr_restore_defaults();
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>OGraphr Settings</h2>
		<p style="font-family:Georgia,serif;font-style:italic;color:grey;">work in progress by Jan T. Sott</p>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('ographr_plugin_options'); ?>
			<?php $options = get_option('ographr_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
				
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><h3>General</h3></th>
				</tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row">Link Title</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_title]" value="<?php if ($options['website_title']) { echo $options['website_title']; } else { echo '%postname%';} ?>" />
					</td>
				</tr>
				<tr><td><th scope="row"><div style="margin-top:-15px;"><span style="font-family:monospace;">%sitename%</span> &#8211; your blog's name (<em><? if(get_option('blogname')) { echo get_option('blogname'); } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br/>
					<span style="font-family:monospace;">%postname%</span> &#8211; page or post title</th></div></td></tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row">Thumbnail</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_thumbnail]" value="<?php echo $options['website_thumbnail']; ?>" /> (optional)
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Meta-tags</th>
					<td>
						<label><input name="ographr_options[add_title]" type="checkbox" value="1" <?php if (isset($options['add_title'])) { checked('1', $options['add_title']); } ?> /> Add page title </label><br />
						
						<!-- Checkbox -->
						<label><input name="ographr_options[add_excerpt]" type="checkbox" value="1" <?php if (isset($options['add_excerpt'])) { checked('1', $options['add_excerpt']); } ?> /> Add excerpt </label><br />
						
						<!-- Checkbox -->
						<label><input name="ographr_options[add_permalink]" type="checkbox" value="1" <?php if (isset($options['add_permalink'])) { checked('1', $options['add_permalink']); } ?> /> Add permalink </label><br />
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Triggers</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_youtube]" type="checkbox" value="1" <?php if (isset($options['enable_youtube'])) { checked('1', $options['enable_youtube']); } ?> /> YouTube </label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_vimeo]" type="checkbox" value="1" <?php if (isset($options['enable_vimeo'])) { checked('1', $options['enable_vimeo']); } ?> /> Vimeo </label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_dailymotion]" type="checkbox" value="1" <?php if (isset($options['enable_dailymotion'])) { checked('1', $options['enable_dailymotion']); } ?> /> Dailymotion </label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_soundcloud]" type="checkbox" value="1" <?php if (isset($options['enable_soundcloud'])) { checked('1', $options['enable_soundcloud']); } ?> /> SoundCloud <? if(!$options['soundcloud_api']) { echo '(requires <a href="#soundcloud_api_key">API key</a>)';} ?></label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_mixcloud]" type="checkbox" value="1" <?php if (isset($options['enable_mixcloud'])) { checked('1', $options['enable_mixcloud']); } ?> /> Mixcloud </label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_bandcamp]" type="checkbox" value="1" <?php if (isset($options['enable_bandcamp'])) { checked('1', $options['enable_bandcamp']); } ?> /> Bandcamp <? if(!$options['bandcamp_api']) { echo '(requires <a href="#bandcamp_api_key">API key</a>)';} ?></label><br/>
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Advertisement</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[add_comment]" type="checkbox" value="1" <?php if (isset($options['add_comment'])) { checked('1', $options['add_comment']); } ?> /> Display plug-in name in source (<em>OGraphr v<? echo OGRAPHR_VERSION ?></em>)</label>
					</td>
				</tr>
				
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><h3>Front page</h3></th>
				</tr>
				
				<tr>
					<th scope="row">Custom Description</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_description]" value="<?php echo $options['website_description']; ?>" /> (optional)
					</td>
				</tr>
				<tr><td><th scope="row"><div style="margin-top:-15px;"><span style="font-family:monospace;">%tagline%</span> &#8211; your blog's tagline (<em><? if(get_bloginfo('description')) { echo get_bloginfo('description'); } else { echo '<span style="color:red;">empty</span>';} ?></em>)</th></div></td></tr>
				<tr valign="top">
					<th scope="row">Meta-tags</th>
					<td>

						<label><input name="ographr_options[enable_on_front]" type="checkbox" value="1" <?php if (isset($options['enable_on_front'])) { checked('1', $options['enable_on_front']); } ?> /> Enable on front page </label><br />
					</td>
				</tr>
				
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><h3>Bandcamp</h3></th>
				</tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row"><a name="bandcamp_api_key">&nbsp;</a>Bandcamp API Key</th>
					<td>
						<input type="text" size="57" name="ographr_options[bandcamp_api]" value="<?php echo $options['bandcamp_api']; ?>" /> (<strong>required</strong>)
					</td>
				</tr>
				
				<tr><td><th scope="row"><div style="margin-top:-15px;">Bandcamp provides only limited access to their API and in any case you need to provide a valid developer key. You can apply for one <a href="http://bandcamp.com/developer#key_request" target"_blank">here</a>.</th></div></td></tr>
				
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><h3>SoundCloud</h3></th>
				</tr>
				
				<!-- Textbox Control -->
				<tr valign="top">
					<th scope="row"><a name="soundcloud_api_key">&nbsp;</a>SoundCloud API Key</th>
					<td>
						<input type="text" size="57" name="ographr_options[soundcloud_api]" value="<?php if ($options['soundcloud_api']) { echo $options['soundcloud_api']; } else { echo SOUNDCLOUD_API_KEY; } ?>" /> (optional)
					</td>
				</tr>
				
				<tr><td><th scope="row"><div style="margin-top:-10px;">If for some reason you prefer using your own SoundCloud API key, you can specify it above. You can get one <a href="http://soundcloud.com/you/apps" target="_blank">here</a>.</th></div></td></tr>

				<tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><!--Database Options--></th>
					<td>
						<label><input name="ographr_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon saving</label>
						<br />
					</td>
				</tr>

			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>

		<p style="margin-top:15px;">
			<p style="font-family:Georgia,serif;font-style:italic;color:grey;">If you have found this plug-in any useful, why not <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FVPU9H7CMUU6U" target="_blank" style="color:grey;">buy me a coffee</a>? Thanks!</p>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function ographr_validate_options($input) {
	 // strip html from textboxes
	$input['website_title'] =  htmlentities($input['website_title']);
	$input['website_description'] =  htmlentities($input['website_description']);
	$input['soundcloud_api'] =  htmlentities($input['soundcloud_api']);
	$input['bandcamp_api'] =  htmlentities($input['bandcamp_api']);
	return $input;
}

// Display a Settings link on the main Plugins page
function ographr_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$ographr_links = '<a href="'.get_admin_url().'options-general.php?page=plugin-options-starter-kit/plugin-options-starter-kit.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $ographr_links );
	}

	return $links;
}