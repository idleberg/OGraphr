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
add_action('admin_footer', 'ographr_javascript');
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
		$arr = array(	"advanced_opt" => "0",
						"website_title" => "%postname%",
						"website_thumbnail" => "",
						"bandcamp_api" => "",
						"soundcloud_api" => SOUNDCLOUD_API_KEY,
						"enable_on_front" => "0",
						"website_description" => "",
						"not_always" => "0",
						"add_comment" => "1",
						"add_title" => "1",
						"add_excerpt" => "1",
						"add_permalink" => "1",
						"enable_youtube" => "1",
						"enable_vimeo" => "1",
						"enable_dailymotion" => "1",
						"enable_bliptv" => "1",
						"enable_hulu" => "1",
						"enable_soundcloud" => "1",
						"enable_mixcloud" => "1",
						"enable_official" => "0",
						"enable_bandcamp" => "1",
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
		<p style="font-family:Georgia,serif;font-style:italic;color:grey;"><? echo OGRAPHR_VERSION ?> by Jan T. Sott</p>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('ographr_plugin_options'); ?>
			<?php $options = get_option('ographr_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
				
				<!-- //// GENERAL //// -->
				<!-- Checkbox Buttons -->
				<tr valign="top" style="border-bottom:#dddddd 1px solid;">
					<th scope="row">&nbsp;</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[advanced_opt]" type="checkbox" value="1" id="show_advanced" <?php if (isset($options['advanced_opt'])) { checked('1', $options['advanced_opt']); }  ?> /> Show advanced options </label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><h3>General</h3></th>
				</tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row">Link Title</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_title]" value="<?php if ($options['website_title']) { echo $options['website_title']; } else { echo '%postname%';} ?>" />
					</td>
				</tr>
				<tr><td><th scope="row"><div style="margin-top:-15px;"><code>%sitename%</code> &#8211; your blog's name (<em><? if($wp_name = get_option('blogname')) { echo $wp_name; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br/>
					<code>%siteurl%</code> &#8211; the URL of your blog (<em><? $wp_url = get_option('home'); $wp_url = (preg_replace('/https?:\/\//', NULL, $wp_url)); echo $wp_url; ?></em>)<br/>
						<code>%postname%</code> &#8211; page or post title</div></td></tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row">Thumbnail</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_thumbnail]" value="<?php echo $options['website_thumbnail']; ?>" /> (optional)<br/>
						<code>%screenshot%</code> &#8211; your theme's default screenshot
					</td>
				</tr>
				
				<!-- Textbox Control -->
				<tr id="advanced_opt">
					<th scope="row">&nbsp;</th>
					<td>
						<label><input name="ographr_options[not_always]" type="checkbox" value="1" <?php if (isset($options['not_always'])) { checked('1', $options['not_always']); } ?> /> Only add thumbnail when post contains no images </label>
					</td>
				</tr>

				
				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Meta-tags</th>
					<td>
						<label><input name="ographr_options[add_title]" type="checkbox" value="1" <?php if (isset($options['add_title'])) { checked('1', $options['add_title']); } ?> /> Add page title </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[add_excerpt]" type="checkbox" value="1" <?php if (isset($options['add_excerpt'])) { checked('1', $options['add_excerpt']); } ?> /> Add excerpt </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[add_permalink]" type="checkbox" value="1" <?php if (isset($options['add_permalink'])) { checked('1', $options['add_permalink']); } ?> /> Add permalink </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[add_post_thumbnail]" type="checkbox" value="1" <?php if (isset($options['add_post_thumbnail'])) { checked('1', $options['add_post_thumbnail']); } ?> /> Add post thumbnail (<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">?</a>)</label>
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top" id="advanced_opt">
					<th scope="row">Triggers</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_bliptv]" type="checkbox" value="1" <?php if (isset($options['enable_bliptv'])) { checked('1', $options['enable_bliptv']); } ?> /> Blip.tv </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_dailymotion]" type="checkbox" value="1" <?php if (isset($options['enable_dailymotion'])) { checked('1', $options['enable_dailymotion']); } ?> /> Dailymotion </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_hulu]" type="checkbox" value="1" <?php if (isset($options['enable_hulu'])) { checked('1', $options['enable_hulu']); } ?> /> Hulu </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_vimeo]" type="checkbox" value="1" <?php if (isset($options['enable_vimeo'])) { checked('1', $options['enable_vimeo']); } ?> /> Vimeo </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_youtube]" type="checkbox" value="1" <?php if (isset($options['enable_youtube'])) { checked('1', $options['enable_youtube']); } ?> /> YouTube </label><br/>
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_bandcamp]" type="checkbox" value="1" <?php if ((isset($options['enable_bandcamp'])) && ($options['bandcamp_api'])) { checked('1', $options['enable_bandcamp']); } ?> /> Bandcamp </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_mixcloud]" type="checkbox" value="1" <?php if (isset($options['enable_mixcloud'])) { checked('1', $options['enable_mixcloud']); } ?> /> Mixcloud </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_official]" type="checkbox" value="1" <?php if (isset($options['enable_official'])) { checked('1', $options['enable_official']); } ?> disabled="disabled" /> Official.fm </label>&nbsp;
						
						<!-- Checkbox -->
						<label><input name="ographr_options[enable_soundcloud]" type="checkbox" value="1" <?php if (isset($options['enable_soundcloud'])) { checked('1', $options['enable_soundcloud']); } ?> /> SoundCloud </label><br/>
						<span style="color:red;font-size:x-small;"><? if(!$options['bandcamp_api']) { echo 'Bandcamp requires a valid <a href="#bandcamp_api_key" style="color:red;">API key</a>';} ?></span>
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top" id="advanced_opt">
					<th scope="row">Filters</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[filter_smilies]" type="checkbox" value="1" <?php if (isset($options['filter_smilies'])) { checked('1', $options['filter_smilies']); } ?> /> Exclude emoticons </label>&nbsp;
						<label><input name="ographr_options[filter_gravatar]" type="checkbox" value="1" <?php if (isset($options['filter_gravatar'])) { checked('1', $options['filter_gravatar']); } ?> /> Exclude avatars </label>
					</td>
				</tr>
	
				<!-- Checkbox Buttons -->
				<tr valign="top" id="advanced_opt">
					<th scope="row">Limit Access</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[facebook_ua]" type="checkbox" value="1" <?php if (isset($options['facebook_ua'])) { checked('1', $options['facebook_ua']); } ?> /> Facebook </label>&nbsp;
						<!-- Checkbox -->
						<label><input name="ographr_options[gplus_ua]" type="checkbox" value="1" <?php if (isset($options['gplus_ua'])) { checked('1', $options['gplus_ua']); } ?> /> Google+ </label>&nbsp;
							<!-- Checkbox -->
							<label><input name="ographr_options[linkedin_ua]" type="checkbox" value="1" <?php if (isset($options['linkedin_ua'])) { checked('1', $options['linkedin_ua']); } ?> /> LinkedIn </label><br/>
							<small>Google+ does currently not use a unique <a href="http://code.google.com/p/google-plus-platform/issues/detail?id=178" target="_blank">user-agent</a>, hence the detection is unreliable</small>
					</td>
				</tr>
				
				<!-- Checkbox Buttons -->
				<tr valign="top" id="advanced_opt">
					<th scope="row">Advertisement</th>
					<td>
						<!-- Checkbox -->
						<label><input name="ographr_options[add_comment]" type="checkbox" value="1" <?php if (isset($options['add_comment'])) { checked('1', $options['add_comment']); } ?> /> Display plug-in name in source (<em>OGraphr v<? echo OGRAPHR_VERSION ?></em>)</label>
					</td>
				</tr>
				
				<!-- //// FRONT PAGE //// -->
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><h3>Front Page</h3></th>
				</tr>
			
				<tr>
					<th scope="row">Custom Description</th>
					<td>
						<input type="text" size="57" name="ographr_options[website_description]" value="<?php echo $options['website_description']; ?>" /> (optional)
					</td>
				</tr>
				<tr><td><th scope="row"><div style="margin-top:-15px;"><code>%tagline%</code> &#8211; your blog's tagline (<em><? if(get_bloginfo('description')) { echo get_bloginfo('description'); } else { echo '<span style="color:red;">empty</span>';} ?></em>)</th></div></td></tr>
				<tr valign="top" id="advanced_opt">
					<th scope="row">Meta-tags</th>
					<td>

						<label><input name="ographr_options[enable_on_front]" type="checkbox" value="1" <?php if (isset($options['enable_on_front'])) { checked('1', $options['enable_on_front']); } ?> /> Enable triggers on front page </label><br />
					</td>
				</tr>
				
				<!-- //// API Keys //// -->
				<tr><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;" id="advanced_opt">
					<th scope="row"><h3>API Keys</h3></th>
				</tr>
			
				<tr id="advanced_opt"><td><th scope="row"><div style="margin-top:-15px;">Bandcamp offers only limited access to their API and in any case you need to provide a valid <a href="http://bandcamp.com/developer#key_request" target="_blank">developer key</a> to make use of this feature. All other services can be used with the provided API keys.</th></div></td></tr>
				
				<!-- Textbox Control -->
				<tr id="advanced_opt">
					<th scope="row"><a name="bandcamp_api_key">&nbsp;</a>Bandcamp (<a href="http://bandcamp.com/developer#key_request" target="_blank">?</a>)</th>
					<td>
						<input type="text" size="57" name="ographr_options[bandcamp_api]" value="<?php echo $options['bandcamp_api']; ?>" /> (<strong>required</strong>)
					</td>
				</tr>
				
				<!-- Textbox Control -->
				<tr valign="top" id="advanced_opt">
					<th scope="row"><a name="official_api_key">&nbsp;</a>Official.fm (<a href="http://official.fm/developers/manage#register" target="_blank">?</a>)</th>
					<td>
						<input type="text" size="57" name="ographr_options[official_api]" value="<?php if ($options['official_api']) { echo $options['official_api']; } else { echo OFFICIAL_API_KEY; } ?>" /> (optional)
					</td>
				</tr>
				
				<!-- Textbox Control -->
				<tr valign="top" id="advanced_opt">
					<th scope="row"><a name="soundcloud_api_key">&nbsp;</a>SoundCloud (<a href="http://soundcloud.com/you/apps" target="_blank">?</a>)</th>
					<td>
						<input type="text" size="57" name="ographr_options[soundcloud_api]" value="<?php if ($options['soundcloud_api']) { echo $options['soundcloud_api']; } else { echo SOUNDCLOUD_API_KEY; } ?>" /> (optional)
					</td>
				</tr>

				<!-- //// FACEBOOK //// -->
				<tr id="advanced_opt"><td colspan="2"><div style="margin-top:10px;"><th scope="row"></th></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;" id="advanced_opt">
					<th scope="row"><h3>Facebook</h3></th>
				</tr>
				
				<!-- og:site_name -->
				<tr valign="top" id="advanced_opt">
					<th scope="row">Human-readable site name</th>
					<td>
						<input type="text" size="57" name="ographr_options[fb_site_name]" value="<?php echo $options['fb_site_name']; ?>" /> (optional)
					</td>
				</tr>
				<tr><td><th scope="row"><div style="margin-top:-15px;" id="advanced_opt"><code>%sitename%</code> &#8211; your blog's name (<em><? if($wp_url) { echo $wp_name; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br />
					<code>%siteurl%</code> &#8211; the URL of your blog (<em><? echo $wp_url; ?></em>)<br/></th></div></td></tr>
				
				<!-- Select Drop-Down Control -->
				<tr id="advanced_opt">
					<th scope="row">Object type (<a href="http://developers.facebook.com/docs/opengraphprotocol/#types" target="_blank">?</a>)</th>
					<td>
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
						(optional)<br/>
						<small>Pages of type <em>article</em> do not have publishing rights, and will not show up on user's profiles</small>
					</td>
					
					<!-- Textbox Control -->
					<tr valign="top" id="advanced_opt">
						<th scope="row">Facebook Admin ID</th>
						<td>
							<input type="text" size="57" name="ographr_options[fb_admins]" value="<?php echo $options['fb_admins']; ?>" />  (optional)<br/>
							<small>If you administer a page for your blog on Facebook, you can enter your <a href="http://developers.facebook.com/docs/reference/api/user/" targe="_blank">User ID</a></small>
						</td>
					</tr>

					<!-- Textbox Control -->
					<tr valign="top" id="advanced_opt">
						<th scope="row">Facebook Application ID</th>
						<td>
							<input type="text" size="57" name="ographr_options[fb_app_id]" value="<?php echo $options['fb_app_id']; ?>" /> (optional)<br/>
							<small>If your blog uses a Facebook app, you can enter your <a href="https://developers.facebook.com/apps" target="_blank">Application ID</a></small>
						</td>
					</tr>

				<tr  id="advanced_opt"><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;" >
					<th scope="row"><!--Database Options--></th>
					<td>
						<label id="advanced_opt"><input name="ographr_options[chk_default_options_db]" type="checkbox" value="1" id="advanced_opt" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon saving</label>
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
	$input['website_thumbnail'] =  htmlentities($input['website_thumbnail']);
	$input['website_description'] =  htmlentities($input['website_description']);
	$input['soundcloud_api'] =  htmlentities($input['soundcloud_api']);
	$input['bandcamp_api'] =  htmlentities($input['bandcamp_api']);
	$input['official_api'] =  htmlentities($input['official_api']);
	$input['fb_site_name'] =  htmlentities($input['fb_site_name']);
	$input['fb_admins'] =  htmlentities($input['fb_admins']);
	$input['fb_app_id'] =  htmlentities($input['fb_app_id']);
	return $input;
}

function ographr_javascript() {
	print '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>';
	print '<script type="text/javascript">';
	print '$(document).ready(function() {';
	print "		if (! $('#show_advanced').attr('checked') ) {";
	print "			$('div.nothing,#advanced_opt').hide();";
	print "		}";
	print "		$('#show_advanced').click(function(){";
	print "			$('div.nothing,#advanced_opt').fadeToggle('slow');";
	print '		});';
	print '});';
    print '</script>';
}