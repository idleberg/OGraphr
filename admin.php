<?php

/*  Copyright 200 David Gwyer (email : d.v.gwyer@presscoders.com)

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

register_uninstall_hook(__FILE__, 'ographr_delete_plugin_options');
add_action('admin_init', array(&$admin_core, 'ographr_init') );
add_action('admin_menu', array(&$admin_core, 'ographr_add_options_page') );
add_action('admin_footer', array(&$admin_core, 'ographr_javascript') );
add_action('add_meta_boxes', array(&$admin_core, 'ographr_meta_box') );  
add_action('save_post', array(&$admin_core, 'save_custom_meta') );  

$prefix = 'ographr_';

// Age restriction
$ages = array(
	"0" => array(
		'label' => "-", 'value' => "",
		),
	"1" => array(
		'label' => "all ages", 'value' => "_none"
		),
	);
$ages[] = array( "label" => "13+", "value" => "13");
$ages[] = array( "label" => "17+", "value" => "17");
$ages[] = array( "label" => "18+", "value" => "18");
$ages[] = array( "label" => "19+", "value" => "19");
$ages[] = array( "label" => "21+", "value" => "21");

$ographr_meta_fields = array(  
    array(  
        'label' => 'Status:', 
        'desc' => 'disable plug-in for this article',
        'id'    => $prefix.'disable_plugin',  
        'type'  => 'checkbox'  
    ),
    array(  
        'label'=> 'Primary Image:',  
        'desc'=> 'Images will only appear after saving the post',  
        'id'    => $prefix.'primary_image',  
        'type'  => 'select',  
        'options' => NULL
    ),
    array(  
        'label'=> 'Age Restriction:', 
        'id'    => $prefix.'restrict_age',  
        'type'  => 'select',  
        'options' => $ages
    ),
    array(  
        'label' => 'Content:', 
        'desc' => 'Contains alcohol',
        'id'    => $prefix.'restrict_content',  
        'type'  => 'checkbox'  
    ),
    array(  
        'label'=> 'Country Restriction:',  
        'desc'  => 'enable',  
        'id'    => $prefix.'restrict_country',  
        'type'  => 'checkbox'  
    ),
    array(  
        'label'=> NULL,  
        'id'    => $prefix.'country_mode',  
        'type'  => 'select',  
        'options' => array (  
            '0' => array (  
                'label' => 'allowed',  
                'value' => 'allowed'  
            ),  
            '1' => array (  
                'label' => 'disallowed',  
                'value' => 'disallowed'  
            ) 
        )  
    ),
    array(    
        'id'    => $prefix.'country_code',  
        'type'  => 'select',
    ),
);

class OGraphr_Admin_Core extends OGraphr_Core {

	// Delete options table entries ONLY when plugin deactivated AND deleted
	public function ographr_delete_plugin_options() {
		delete_option('ographr_options');
	}
	
	public function date_diff($date1, $date2) { 
		$current = $date1; 
		$datetime2 = date_create($date2); 
		$count = 0; 
		while(date_create($current) < $datetime2){ 
			$current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current))); 
			$count++; 
		} 
		return $count; 
	} 
	
	public function ographr_delete_postmeta() {		
		$published = wp_count_posts();
		$published = $published->publish;
		$args = array( 'numberposts' => $published, 'meta_key' => 'ographr_urls' );
		$ographr_urls = get_posts( $args );
		foreach($ographr_urls as $ographr_url) {
			$ographr_id = $ographr_url->ID;
			delete_post_meta($ographr_id, 'ographr_urls');
			delete_post_meta($ographr_id, 'ographr_indexed');
			
			// delete article settings
			delete_post_meta($ographr_id, 'ographr_restrict_age');
			delete_post_meta($ographr_id, 'ographr_country_code');
			delete_post_meta($ographr_id, 'ographr_country_mode');
			delete_post_meta($ographr_id, 'ographr_restrict_country');
			delete_post_meta($ographr_id, 'ographr_primary_image');
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

	// Define default option settings
	public function ographr_restore_defaults() {
		$tmp = get_option('ographr_options');
	    if((isset($tmp['chk_default_options_db'])) || (!is_array($tmp))) {
		
			if ($tmp['delete_postmeta'] == 1) {
				$this->ographr_delete_postmeta();
			}
				
			// Set default locale to WordPress language
			if (WPLANG) {
				$tmp_locale = WPLANG;
			} else {
				$tmp_locale = "_none";
			}
				
			//$this->ographr_set_defaults();
			delete_option('ographr_options');

			$options = $this->ographr_set_defaults();
			update_option('ographr_options', $options);
		}
	}

	// Init plugin options to white list our options
	public function ographr_init(){

		//global $options;
		$options = get_option('ographr_options');
		
		wp_register_style( 'OGraphr_Stylesheet', plugins_url('/assets/styles.min.css', __FILE__) );
		wp_register_script( 'OGraphr_JScript', plugins_url('/assets/scripts.min.js', __FILE__), array('jquery'), null, true );	
		
		register_setting( 'ographr_plugin_options', 'ographr_options', array($this, 'ographr_validate_options') );

	}

	// Add the Meta Box  
	public function ographr_meta_box() { 
		$options = get_option('ographr_options');	
		if (!isset($options['add_metabox'])) return;

		$screens = array('post', 'page');
		foreach($screens as $screen) {
			add_meta_box(  
	        'ographr_meta_box', // $id  
	        'OGraphr Settings', // $title   
	        array($this, 'show_ographr_meta_box'), // $callback  
	        'post', // $page  
	        'normal', // $context  
	        'default'); // $priority 
		}	   
		 
	}

	public function show_ographr_meta_box() {  
		global $ographr_meta_fields, $post; 

		$options = get_option('ographr_options');	
		if (!isset($options['add_metabox'])) return;

		// Use nonce for verification  
		echo '<input type="hidden" name="ographr_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';  
		     

		    // Begin the field table and loop  
		    echo '<table class="form-table">';  
		    foreach ($ographr_meta_fields as $field) {  
		        // get value of this field if it exists for this post  
		        $meta = get_post_meta($post->ID, $field['id'], true);  
		        // begin a table row with 
		        if ( ($field['id'] != "ographr_restrict_country") && ($field['id'] != "ographr_country_mode") && ($field['id'] != "ographr_country_code") ) {
		        	echo '<tr> 
		                <th><label for="'.$field['id'].'"><strong>'.$field['label'].'</strong></label></th> 
		                <td>';  
		                switch($field['type']) {  
		                    // case items will go here

							// checkbox  
							case 'checkbox':  
							    echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/> 
							        <label for="'.$field['id'].'">'.$field['desc'].'</label>';  
							break;  

							// select 
							case 'select':  
							    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">'; 
							    if ($field['id'] == "ographr_primary_image") {
							    	$images = get_post_meta($post->ID, 'ographr_urls', true);
									//$images = unserialize($images);

									if (strnatcmp(phpversion(),'5.2.0') >= 0) {
										$images = json_decode($images, true);
									} else { // fallback for PHP <5.2			
										$images = unserialize(base64_decode($images));
									}

									$tmp[] = array('label' => '(none selected)', 'value' => NULL);
									if(is_array($images)){
										foreach ($images as $image) {
											$tmp[] = array('label' => $image['img'], 'value' => $image['img']);
										}
									}

									$field['options'] = $tmp;
									unset($tmp);
									unset($images);
							    }
							    foreach ($field['options'] as $option) {  
							        echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';  
							    }  
							    echo '</select><br /><span class="description">'.$field['desc'].'</span>';  
							break; 

		                } //end switch  
		        echo '</td></tr>';  
		        }
		        
		    } // end foreach
	
		    // country restrictions
		    echo "<tr>
		    	<th><label for=\"ographr_restrict_country\"><strong>Country Restriction:</strong></label></th> 
	                <td>
	                	<input type=\"checkbox\" name=\"ographr_restrict_country\" id=\"ographr_restrict_country\"";
	                	if(get_post_meta($post->ID, 'ographr_restrict_country', true) ) {
	                		echo ' checked="checked"';
	                	}
	                	echo "/> 
			        	<label for=\"ographr_restrict_country\"></label>
			        	<select name=\"ographr_country_mode\" id=\"ographr_country_mode|\">";
			        		$mode = get_post_meta($post->ID, 'ographr_country_mode', true);
			        		echo"
			        		<option value=\"allowed\"";
			        		if ($mode == "allowed") {
			        			echo " selected=\"selected\"";
			        		}
			        		echo
			        		">allowed</option>
			        		<option value=\"disallowed\"";
			        		if ($mode == "disallowed") {
			        			echo " selected=\"selected\"";
			        		}
			        		echo 
			        		">disallowed</option>
			        	</select>
			        	&nbsp;in&nbsp;
			        	<select name=\"ographr_country_code\" id=\"ographr_country_code\">";
			        		$country_codes = $this->get_iso_codes();
			        		$code = get_post_meta($post->ID, 'ographr_country_code', true);
			        		foreach($country_codes as $k => $v) {
			        			echo "<option value='$k'";
			        			if ($code == $k) {
			        				echo " selected=\"selected\"";
				        		}
				        		
				        		echo
				        		">$v</option>";
			        		}
			        		unset($code); //save some RAM
			        	echo
			        	'</select>
			        </td>
		    </tr>';
		    if ($options['exec_mode'] == 1) {
				$timestamp = get_post_meta($post->ID, 'ographr_indexed', true);
				if ($timestamp != NULL) {
					echo "<tr><th></th> <td><span class=\"description\">This article was last indexed on " . gmdate("F d, Y", $timestamp) . " at " . gmdate("G:i:s", $timestamp) ."</span></td></tr>";
				}
			}	
		    echo '</table>'; // end table  
	}

	// Save the Data  
	public function save_custom_meta($post_id) { 
	    global $ographr_meta_fields;  
	      
	    // verify nonce  
	    if (!wp_verify_nonce($_POST['ographr_meta_box_nonce'], basename(__FILE__))) return $post_id;  
	    // check autosave  
	    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;  
	    // check permissions  
	    if ('page' == $_POST['post_type']) {  
	        if (!current_user_can('edit_page', $post_id)) {
	            return $post_id;  
	        } elseif (!current_user_can('edit_post', $post_id)) {  
	            return $post_id; 
	        }
	    } 
	      
	    // loop through fields and save the data  
	    foreach ($ographr_meta_fields as $field) {    		
      		
			$old = get_post_meta($post_id, $field['id'], true);
    		if (isset($_POST[$field['id']])) {
    			$new = $_POST[$field['id']];  
		        if ($new && $new != $old) {  
		            update_post_meta($post_id, $field['id'], $new);  
		        } elseif ('' == $new && $old) {  
		            delete_post_meta($post_id, $field['id'], $old);  
		        } 
    		}

    		if( ( ($field['id'] == "ographr_country_mode") || ($field['id'] == "ographr_country_code") ) && ($_POST['ographr_restrict_country'] != "on")) {
    			delete_post_meta($post_id, "ographr_restrict_country"); 
    			delete_post_meta($post_id, "ographr_country_mode"); 
    			delete_post_meta($post_id, "ographr_country_code"); 
    		}
    		if ($_POST['ographr_restrict_content'] != "on") {
				delete_post_meta($post_id, "ographr_restrict_content"); 
    		}
    		if ($_POST['ographr_disable_plugin'] != "on") {
				delete_post_meta($post_id, "ographr_disable_plugin"); 
    		}
	         	        
	    } // end foreach  
	}  
    

	// Add menu page
	public function ographr_add_options_page() {
		$page = add_submenu_page( 'options-general.php', 
		                                 __( 'OGraphr Settings', 'OGraphr' ), 
		                                 __( 'OGraphr', 'OGraphr' ),
		                                 'manage_options',
		                                 __FILE__, 
		                                 array($this, 'ographr_render_form') );
		
		add_action( 'admin_print_styles-' . $page, array($this, 'my_plugin_admin_styles') );
	}
	
	public function my_plugin_admin_styles() {	
			//global $options;
			$options = get_option('ographr_options');
	
			wp_enqueue_style( 'OGraphr_Stylesheet' );
			wp_enqueue_script( 'OGraphr_JScript' );
		
			if (isset($options['add_graph'])) {
				wp_enqueue_style( 'JQPlot_Stylesheet' );
				wp_enqueue_script( 'JQPlot_Core' );
				wp_enqueue_script( 'JQPlot_highlighter' );
				wp_enqueue_script( 'JQPlot_dateAxis' );
			}
	 
	}

	// Render the Plugin options form
	public function ographr_render_form() {
		$this->ographr_restore_defaults();
		?>
		<div class="wrap">
		
			<!-- Display Plugin Icon, Header, and Description -->
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>OGraphr Settings</h2>

			<form method="post" action="options.php">
				<?php settings_fields('ographr_plugin_options'); ?>
				<?php $options = get_option('ographr_options'); ?>
				<p>&nbsp;<label class="outside"><input name="ographr_options[advanced_opt]" type="checkbox" value="1" id="show_advanced" <?php if (isset($options['advanced_opt'])) { checked('1', $options['advanced_opt']); }  ?> /> Show advanced options </label></p>
				
				<!-- Beginning of the Plugin Options Form -->
				<table class="poststuff"><tbody><tr><td class="left">
						
						
						<!-- *********************** BEGIN: Main Content ******************* -->
						<form name="ographr-admin" method="post" action="<?php print get_admin_url() . "options-general.php?page=meta-ographr.php";?>">
						<?php wp_nonce_field('ographr_save_options','ographr_admin_options_form'); ?>
						<fieldset class="options">

						<div class="postbox">
							<span class="hndle heading">General</span>
							<div class="inside">
								<table> 
								<tbody>
								
								<!-- LINK TITLE -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Link Title:</label></th> 
								<td>
									<input type="text" size="60" name="ographr_options[website_title]" value="<?php if ($options['website_title']) { echo $options['website_title']; } else { echo '%postname%';} ?>" /><br/>
									<p class="description">
										<code>%postname%</code> &mdash; page or post title<br/>
										<code>%sitename%</code> &mdash; your blog's name (<em><?php if($mywp['blog_name'] = get_option('blogname')) { echo $mywp['blog_name']; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br/>
										<code>%siteurl%</code> &mdash; the URL of your blog (<em><?php $wp_url = get_option('home'); $wp_url = (preg_replace('/https?:\/\//', NULL, $wp_url)); echo $wp_url; ?></em>)
									</p>
								</td> 
								<td>&nbsp;</td>
								</tr>
							
								<!-- THUMBNAIL -->
								<tr> 
									<th class="pull-left" scope="row"><label>Thumbnail:</label></th> 
									<td colspan="2">
										<input type="text" size="60" name="ographr_options[website_thumbnail]" value="<?php echo $options['website_thumbnail']; ?>" /><br/>
										
										<p class="description">
												<code>%screenshot%</code> &mdash; your theme's default screenshot
												<?php
												$theme_path = get_bloginfo('template_url');
												$result = $this->remote_exists($theme_path . '/screenshot.png');
												if ($result) {
													print '(<a href="' . $theme_path . '/screenshot.png" target="_blank">preview</a>)';
												} else {
													print "(<a href=\"http://codex.wordpress.org/Theme_Development#Screenshot\" target=\"_blank\">none</a>)";
												}
													 ?>
										</p>
									</td>
								</tr>
								
								<tr class="centered advanced_opt"> 
									<th class="pull-left" scope="row"><label>&nbsp;</label></th> 
									<td colspan="2"><label><input name="ographr_options[not_always]" type="checkbox" value="1" <?php if (isset($options['not_always'])) { checked('1', $options['not_always']); } ?> /> Only add thumbnail when post contains no images </label></td> 
								</tr>
							
								<!-- META TAGS -->
								<tr> 
									<th class="pull-left" scope="row"><label>Meta-tags:</label></th> 
									<td colspan="2">

										<ul class="horizontal">
											<li>
												<ul>
													<li><label><input name="ographr_options[add_title]" type="checkbox" value="1" <?php if (isset($options['add_title'])) { checked('1', $options['add_title']); } ?> /> Add page title </label></li>
													<li><label><input name="ographr_options[add_excerpt]" type="checkbox" value="1" <?php if (isset($options['add_excerpt'])) { checked('1', $options['add_excerpt']); } ?> /> Add excerpt </label></li>
													<li><label><input name="ographr_options[add_permalink]" type="checkbox" value="1" class="atoggle" data-atarget="select.link_type" data-astate="1" <?php if (isset($options['add_permalink'])) { checked('1', $options['add_permalink']); } ?> /> Add link </label></li>
													<li><label><input name="ographr_options[add_author]" type="checkbox"  class="atoggle" value="1" data-atarget="input.allow_admin_tag" data-astate="1" <?php if (isset($options['add_author'])) { checked('1', $options['add_author']); } ?> /> Add author </label></li>
													<li><label><input name="ographr_options[add_section]" type="checkbox" value="1" <?php if (isset($options['add_section'])) { checked('1', $options['add_section']); } ?> /> Add category </label></li>
												</ul>
											</li>
											<li>
												<ul>
													<li><label><input name="ographr_options[add_tags]" type="checkbox" value="1" <?php if (isset($options['add_tags'])) { checked('1', $options['add_tags']); } ?> /> Add tags </label></li>
													<li><label><input name="ographr_options[add_pubtime]" type="checkbox" value="1" <?php if (isset($options['add_pubtime'])) { checked('1', $options['add_pubtime']); } ?> /> Add published time </label></li>
													<li><label class="advanced_opt"><input name="ographr_options[add_modtime]" type="checkbox" value="1" <?php if (isset($options['add_modtime'])) { checked('1', $options['add_modtime']); } ?> /> Add modified time </label></li>
													<li><label class="advanced_opt"><input name="ographr_options[add_embeds]" type="checkbox" value="1" <?php if (isset($options['add_embeds'])) { checked('1', $options['add_embeds']); } ?> /> Add embedded media </label></li>
												</ul>
											</li>
										</ul>

										

										&nbsp;

										&nbsp;
																		
										&nbsp;

										

										&nbsp;

										&nbsp;
										
										&nbsp;

										&nbsp;

									</td>
								</tr>
							
								<!-- TRIGGERS -->
								<tr class="advanced_opt"> 
									<th class="pull-left" scope="row"><label>Triggers:</label></th> 
									<td colspan="2">	
										<ul class="horizontal">
											<li>
												<ul>
													<li><label title="Click to toggle all items in this group"><input class="select-all" type="checkbox" value="0"><strong>&nbsp;Audio</strong></label></li>
													<li><label><input name="ographr_options[enable_etracks]" type="checkbox" value="1" <?php if ((isset($options['enable_etracks'])) && ($options['enable_etracks'])) { checked('1', $options['enable_etracks']); } ?> />&nbsp;8tracks</label></li>
													<li><label><input name="ographr_options[enable_bandcamp]" type="checkbox" value="1" <?php if ((isset($options['enable_bandcamp'])) && ($options['bandcamp_api'])) { checked('1', $options['enable_bandcamp']); } ?> />&nbsp;Bandcamp</label></li>
													<li><label><input name="ographr_options[enable_mixcloud]" type="checkbox" value="1" <?php if (isset($options['enable_mixcloud'])) { checked('1', $options['enable_mixcloud']); } ?> />&nbsp;Mixcloud</label></li>
													<li><label><input name="ographr_options[enable_official]" type="checkbox" value="1" <?php if (isset($options['enable_official'])) { checked('1', $options['enable_official']); } ?> />&nbsp;Official.fm</label></li>
													<li><label><input name="ographr_options[enable_soundcloud]" type="checkbox" value="1" <?php if (isset($options['enable_soundcloud'])) { checked('1', $options['enable_soundcloud']); } ?> />&nbsp;SoundCloud</label></li>
													<li><label><input name="ographr_options[enable_spotify]" type="checkbox" value="1" <?php if (isset($options['enable_spotify'])) { checked('1', $options['enable_spotify']); } ?> />&nbsp;Spotify</label></li>
												</ul>
											</li>
											<li>
												<ul >
													<li><label title="Click to toggle all items in this group"><input class="select-all" type="checkbox" value="0"><strong>&nbsp;Video</strong></label></li>
													<li><label><input name="ographr_options[enable_dailymotion]" type="checkbox" value="1" <?php if (isset($options['enable_dailymotion'])) { checked('1', $options['enable_dailymotion']); } ?> />&nbsp;Dailymotion</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_flickr]" type="checkbox" value="1" <?php if (isset($options['enable_flickr'])) { checked('1', $options['enable_flickr']); } ?> />&nbsp;Flickr</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_hulu]" type="checkbox" value="1" <?php if (isset($options['enable_hulu'])) { checked('1', $options['enable_hulu']); } ?> />&nbsp;Hulu</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_internetarchive]" type="checkbox" value="1" <?php if ((isset($options['enable_internetarchive'])) && ($options['enable_internetarchive'])) { checked('1', $options['enable_internetarchive']); } ?> />&nbsp;Internet Archive</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_myvideo]" type="checkbox" value="1" <?php if ((isset($options['enable_myvideo'])) && ($options['myvideo_dev_api']) && ($options['myvideo_web_api'])) { checked('1', $options['enable_myvideo']); } ?> />&nbsp;MyVideo</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_vimeo]" type="checkbox" value="1" <?php if (isset($options['enable_vimeo'])) { checked('1', $options['enable_vimeo']); } ?> />&nbsp;Vimeo</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_youtube]" type="checkbox" value="1" <?php if (isset($options['enable_youtube'])) { checked('1', $options['enable_youtube']); } ?> />&nbsp;YouTube</label></li>
												</ul>
											</li>
											<li>
												<ul>
													<li><label title="Click to toggle all items in this group"><input class="select-all" type="checkbox" value="0"><strong>&nbsp;Feed</strong></label></li>
													<li><label><input name="ographr_options[enable_bambuser]" type="checkbox" value="1" <?php if ((isset($options['enable_bambuser'])) && ($options['bambuser_api'])) { checked('1', $options['enable_bambuser']); } ?> />&nbsp;Bambuser</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_justintv]" type="checkbox" value="1" <?php if (isset($options['enable_justintv'])) { checked('1', $options['enable_justintv']); } ?> />&nbsp;Twitch.tv</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_livestream]" type="checkbox" value="1" <?php if (isset($options['enable_livestream'])) { checked('1', $options['enable_livestream']); } ?> />&nbsp;Livestream</label>&nbsp;</li>
													<li><label><input name="ographr_options[enable_ustream]" type="checkbox" value="1" <?php if (isset($options['enable_ustream'])) { checked('1', $options['enable_ustream']); } ?> />&nbsp;Ustream</label>&nbsp;</li>
												</ul>
											</li>
										</ul>

										<?php if((!isset($options['bandcamp_api'])) && (isset($options['enable_bandcamp']))) { echo '<br/><span style="color:red;font-size:x-small;">Bandcamp requires a valid <a href="#bandcamp_api_key" style="color:red;">API key</a></span>';} ?>
										<?php if((!isset($options['myvideo_dev_api'])) && (isset($options['enable_myvideo']))) { echo '<br/><span style="color:red;font-size:x-small;">MyVideo requires a valid <a href="#myvideo_developer_key" style="color:red;">Developer API key</a></span>';} ?>
										<?php if((!isset($options['myvideo_web_api'])) && (isset($options['enable_myvideo']))) { echo '<br/><span style="color:red;font-size:x-small;">MyVideo requires a valid <a href="#myvideo_website_key" style="color:red;">Website API key</a></span>';} ?>
									</td> 
								</tr>
								
								<!-- ADVERTISEMENT -->
								<tr class="advanced_opt"> 
									<th class="pull-left" scope="row"><label>Advertisement:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[add_comment]" type="checkbox" value="1" <?php if (isset($options['add_comment'])) { checked('1', $options['add_comment']); } ?> /> Display plug-in name in source</label><br/>
									</td>
								</tr>
							
								</tbody></table>
								<div class="submit">
										<input type="submit" class="button-primary" value="Save all changes" />
										<a href="#wpbody-content" class="button-secondary">Back to top</a>
									</div>
							</div>
						</div>
						
						<!-- F R O N T   P A G E -->
						<div class="postbox">
							<span class="hndle heading">Front Page</span>
							<div class="inside">
								<table> 
								<tbody>
							
								<tr class="advanced_opt"> 
									<th class="pull-left" scope="row"><label>Functionality:</label></th> 
									<td colspan="2">
									<label title="Enable plug-in on front page"><input name="ographr_options[enable_plugin_on_front]" type="checkbox" class="atoggle" value="1" data-atarget="input.enable_triggers" data-astate="1" <?php if (isset($options['enable_plugin_on_front'])) { checked('1', $options['enable_plugin_on_front']); } ?>/> Enable plug-in </label>&nbsp;
								
									<label title="Enable plug-in triggers on front page"><input name="ographr_options[enable_triggers_on_front]" type="checkbox" class="enable_triggers" value="1" <?php if (isset($options['enable_triggers_on_front'])) { checked('1', $options['enable_triggers_on_front']); }; if (!isset($options['enable_plugin_on_front'])) { print 'disabled="disabled"';} ?> /> Enable triggers </label>&nbsp;
									</td> 
								</tr>

								<!-- CUSTOM DESCRIPTION -->	
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Custom Description:</label></th> 
									<td colspan="2">
										<input type="text" size="60" name="ographr_options[website_description]" class="enable_triggers" value="<?php echo $options['website_description']; ?>" /><br/>
										<p class="description">
											<code>%tagline%</code> &mdash; your blog's tagline (<em><?php if(get_bloginfo('description')) { echo get_bloginfo('description'); } else { echo '<span style="color:red;">empty</span>';} ?></em>)
										</p>
									</td> 
								</tr>
							
								</tbody></table>

								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
							</div>
						</div>
					
						<!-- R E S T R I C T I O N S -->
						<div class="postbox advanced_opt">
							<span class="hndle heading">Restrictions</span>
							<div class="inside">
								<table> 
								<tbody>

									<!-- FILTERS -->
									<tr> 
										<th class="pull-left width-140" scope="row"><label>Filters:</label></th> 
										<td colspan="2">
											<label title="Filter Gravatar images"><input name="ographr_options[filter_gravatar]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_gravatar'])) { checked('1', $options['filter_gravatar']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?>/> Exclude avatars </label>&nbsp;
											
											<label title="Filter WordPress emoticons"><input name="ographr_options[filter_smilies]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_smilies'])) { checked('1', $options['filter_smilies']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude emoticons </label>&nbsp;
											
											<label title="Filter images belonging to themes"><input name="ographr_options[filter_themes]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_themes'])) { checked('1', $options['filter_themes']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude themes </label>&nbsp;
											
											<label title="Filter images belonging to plugins"><input name="ographr_options[filter_plugins]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_plugins'])) { checked('1', $options['filter_plugins']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude plug-ins </label>&nbsp;

											<label title="Filter images from the uploads folder"><input name="ographr_options[filter_uploads]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_uploads'])) { checked('1', $options['filter_uploads']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude uploads </label>&nbsp;
											
											<label title="Filter images from the WordPress includes directory"><input name="ographr_options[filter_includes]" type="checkbox" value="1" class="disable_filters" <?php if (isset($options['filter_includes'])) { checked('1', $options['filter_includes']); }; if(!$options['add_post_images']) print 'disabled="disabled"'; ?> /> Exclude includes </label>&nbsp;
										</td> 
									</tr>
								
									<!-- CUSTOM URLS -->
									<tr> 
										<th class="pull-left width-140" scope="row"><label>Custom URLs:</label></th> 
										<td colspan="2"><textarea name="ographr_options[filter_custom_urls]" cols="76%" rows="4" class="disable_filters"><?php echo $options['filter_custom_urls']; ?></textarea><br/>
											<p class="description">You can enter filenames and URLs (e.g. <em><?php echo 'http://' . $wp_url . '/wp-content'; ?></em>) to the filter-list above</p>
										</td> 
									</tr>

									<!-- AUTHOR -->	
									<tr> 
									<th class="pull-left width-140" scope="row"><label>Author Links:</label></th> 
									<td colspan="2">
										<label><input class="allow_admin_tag" name="ographr_options[allow_admin_tag]" type="checkbox" value="1" <?php if (isset($options['allow_admin_tag'])) { checked('1', $options['allow_admin_tag']); }  if (!isset($options['add_author'])) { print 'disabled="disabled"';} ?> /> Allow author tag for user <em>admin</em>  </label><br/>
									</td> 
									<td>&nbsp;</td>
									</tr>

									<!-- AGE -->	
									<tr> 
									<th class="pull-left width-140" scope="row"><label>Audience:</label></th> 
									<td colspan="2">
										<select name='ographr_options[restrict_age]'>
											<option value='_none' <?php selected('_none', $options['restrict_age']); ?> >all ages</option>
		 									<option value='13' <?php selected('13', $options['restrict_age']); ?> >13+</option>
		 									<option value='17' <?php selected('17', $options['restrict_age']); ?> >17+</option>
		 									<option value='18' <?php selected('18', $options['restrict_age']); ?> >18+</option>
		 									<option value='19' <?php selected('19', $options['restrict_age']); ?> >19+</option>
		 									<option value='21' <?php selected('21', $options['restrict_age']); ?> >21+</option>
										</select>
									</td> 
									<td>&nbsp;</td>
									</tr>

									<!-- COUNTRY -->	
									<tr> 
									<th class="pull-left width-140" scope="row"><label>Country:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[restrict_country]" type="checkbox" value="1" class="atoggle" data-atarget="select.restrict_country" data-astate="1" <?php if (isset($options['restrict_country'])) { checked('1', $options['restrict_country']); } ?> /></label>&nbsp;
										<select name='ographr_options[country_mode]' class="restrict_country" <?php if (!isset($options['restrict_country'])) print 'disabled="disabled"'; ?> >
											<option value='allowed' <?php if(isset($options['country_mode'])) selected('allowed', $options['country_mode']); ?> >allowed</option>
											<option value='disallowed' <?php if(isset($options['country_mode'])) selected('disallowed', $options['country_mode']); ?> >disallowed</option>
										</select>
										&nbsp;in&nbsp;
										<select name='ographr_options[country_code]' class="restrict_country" <?php if (!isset($options['restrict_country'])) print 'disabled="disabled"'; ?> >
		 									<?php $country_codes = $this->get_iso_codes(); foreach ($country_codes as $k => $v) {
												print "<option value='$k'" . selected($k, $options['country_code']) . ">$v</option>";
											}
											unset($country_codes); //save some RAM ?>
										</select>
										</td> 
									<td>&nbsp;</td>
									</tr>

									<!-- CONTENT -->	
									<tr> 
									<th class="pull-left width-140" scope="row"><label>Content:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[restrict_content]" type="checkbox" value="1" <?php if (isset($options['restrict_content'])) { checked('1', $options['restrict_content']); } ?> /> Contains alcohol </label>
									</td> 
									<td>&nbsp;</td>
									</tr>
								
									<!-- LIMIT ACCESS -->
									<tr> 
										<th class="pull-left width-140" scope="row"><label id="user_agents">User Agents:</label></th> 
										<td colspan="2">
											
											<!-- Checkbox -->
											<label><input name="ographr_options[facebook_ua]" type="checkbox" value="1" <?php if (isset($options['facebook_ua'])) { checked('1', $options['facebook_ua']); } ?> /> Facebook </label>&nbsp;
											<!-- Checkbox -->
											<label><input name="ographr_options[gplus_ua]" type="checkbox" value="1" <?php if (isset($options['gplus_ua'])) { checked('1', $options['gplus_ua']); } ?> /> Google+ </label>&nbsp;
											
											<!-- Checkbox -->
											<label><input name="ographr_options[linkedin_ua]" type="checkbox" value="1" <?php if (isset($options['linkedin_ua'])) { checked('1', $options['linkedin_ua']); } ?> /> LinkedIn </label>&nbsp;
											
											<!-- Checkbox -->
											<label><input name="ographr_options[twitter_ua]" type="checkbox" value="1" <?php if (isset($options['twitter_ua'])) { checked('1', $options['twitter_ua']); } ?> /> Twitter </label><br/>
											<p class="description">Once a user-agent has been selected, the plug-in will only be triggered when called by any of these sites.</p>
										</td>
									</tr>

									<!-- OPENGRAPH -->
									<tr> 
										<th class="pull-left width-140" scope="row"><label>Open Graph:</label></th> 
										<td colspan="2">
											<label><input name="ographr_options[limit_opengraph]" type="checkbox" value="1" <?php if (isset($options['limit_opengraph'])) { checked('1', $options['limit_opengraph']); } ?> /> Only add Open Graph tags on Facebook </label><br/>
											<p class="description">Note that other websites such as Google+ are able to interprete Open Graph tags as well.</p>
										</td> 
									</tr>

									<!-- JETPACK -->
									<tr> 
										<th class="pull-left width-140" scope="row"><label>Jetpack:</label></th> 
										<td colspan="2">
											<label title="Disable Jetpack's Open Graph tags to avoid duplicate tags"><input name="ographr_options[disable_jetpack]" type="checkbox" value="1" <?php if (isset($options['disable_jetpack'])) { checked('1', $options['disable_jetpack']); } if (!is_plugin_active('jetpack/jetpack.php')) { print 'disabled="disabled"'; } ?> /> Disable Jetpack's Open Graph Tags function </label>
										</td> 
									</tr>
							
								</tbody></table>

								<div class="submit">
										<input type="submit" class="button-primary" value="Save all changes" />
										<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
							</div>

						</div>
					
						<!-- A P I   K E Y S -->
						<div class="postbox">
							<span class="hndle heading">API Keys</span>
							<div class="inside">
							<p>
								Some services limit access to their API and require a valid developer key in order to make queries. These are marked <span style="font-style:italic;padding:1px;background-color:#ffd">yellow</span> in the list below. All other services will work out of the box, however, if you have reason to use your own developer keys you may enter them below.
							</p>
							<table> 
							<tbody>
							
							<!-- 8TRACKS -->	
							<tr class="centered  advanced_opt"> 
							<th class="pull-left width-140" scope="row"><label><a id="etracks_api_key"></a>8tracks:</label></th> 
							<td>
								<input type="text" size="60" class="centered" name="ographr_options[etracks_api]" value="<?php if (($options['etracks_api'] != ETRACKS_API_KEY) && ($options['etracks_api'])) { echo $options['etracks_api']; } ?>" />
								<a class="centered" href="http://8tracks.com/developers/new" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
							
							<!-- BAMBUSER -->	
							<tr class="centered  advanced_opt"> 
							<th class="pull-left width-140" scope="row"><label><a id="bambuser_api_key"></a>Bambuser:</label></th> 
							<td>
								<input type="text" size="60" class="centered" name="ographr_options[bambuser_api]" value="<?php if (($options['bambuser_api'] != BAMBUSER_API_KEY) && ($options['bambuser_api'])) { echo $options['bambuser_api']; } ?>" />
								<a class="centered" href="http://bambuser.com/api/keys" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>

							<!-- BANDCAMP -->	
							<tr class="centered "> 
							<th class="pull-left width-140" scope="row"><label><a id="bandcamp_api_key"></a>Bandcamp:</label></th> 
							<td>
								<input type="text" size="60" class="required centered" name="ographr_options[bandcamp_api]" value="<?php echo $options['bandcamp_api']; ?>" />
								<a class="centered" href="http://bandcamp.com/developer#key_request" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
						
							<!-- FLICKR -->	
							<tr class="centered  advanced_opt"> 
							<th class="pull-left width-140" scope="row"><label>Flickr:</label></th> 
							<td>
								<input type="text" size="60" class="centered" name="ographr_options[flickr_api]" value="<?php if (($options['flickr_api'] != FLICKR_API_KEY) && ($options['flickr_api'])) { echo $options['flickr_api']; } ?>" />
								<a class="centered" href="http://www.flickr.com/services/apps/create/apply/" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
							
							<!-- MYVIDEO DEVELOPER -->	
							<tr class="centered "> 
							<th class="pull-left width-140" scope="row"><label><a id="myvideo_developer_key"></a>MyVideo (Developer):</label></th> 
							<td>
								<input type="text" size="60" class="required centered" name="ographr_options[myvideo_dev_api]" value="<?php if ($options['myvideo_dev_api']) { echo $options['myvideo_dev_api']; } ?>" />
								<a class="centered" href="http://myvideo.de/API" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
							
							<!-- MYVIDEO WEBSITE -->	
							<tr class="centered "> 
							<th class="pull-left width-140" scope="row"><label><a id="myvideo_website_key"></a>MyVideo (Website):</label></th> 
							<td>
								<input type="text" size="60" class="required centered" name="ographr_options[myvideo_web_api]" value="<?php if ($options['myvideo_web_api']) { echo $options['myvideo_web_api']; } ?>" />
								<a class="centered" href="http://myvideo.de/API" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
						
							<!-- SOUNDCLOUD -->	
							<tr class="centered  advanced_opt"> 
							<th class="pull-left width-140" scope="row"><label>SoundCloud:</label></th> 
							<td>
								<input type="text" size="60" class="centered" name="ographr_options[soundcloud_api]" value="<?php if (($options['soundcloud_api'] != SOUNDCLOUD_API_KEY) && ($options['soundcloud_api'])) { echo $options['soundcloud_api']; } ?>" />
								<a class="centered" href="http://soundcloud.com/you/apps" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
						
							<!-- USTREAM -->	
							<tr class="centered  advanced_opt"> 
							<th class="pull-left width-140" scope="row"><label>Ustream:</label></th> 
							<td>
								<input type="text" size="60" class="centered" name="ographr_options[ustream_api]" value="<?php if (($options['ustream_api'] != USTREAM_API_KEY) && ($options['ustream_api'])) { echo $options['ustream_api']; } ?>" />
								<a class="centered" href="http://developer.ustream.tv/apikey/generate" title="Get an API key" target="_blank">?</a></td>
							</td>
							</tr>
						
							</tbody></table>

							<div class="submit">
								<input type="submit" class="button-primary" value="Save all changes" />
								<a href="#wpbody-content" class="button-secondary">Back to top</a>
							</div>			
						</div>

						</div>

						<!-- E X P E R T -->
						<div class="postbox advanced_opt">
							<span class="hndle heading">Expert Settings</span>
							<div class="inside">
								<table> 
								<tbody>
									
								<!-- IMAGE RETRIEVAL -->	
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Image Retrieval:</label></th> 
									<td colspan="2">
										<div id="enable_expiry">
											<label><input name="ographr_options[exec_mode]" type="radio" class="atoggle only_once" data-atarget=".no_expiry" data-astate="1" value="1" <?php if (isset($options['exec_mode'])) { checked('1', $options['exec_mode']); } ?>  />&nbsp;Only once when saving a post (default, better performance)&nbsp;</label><br/>

											<label><input name="ographr_options[exec_mode]" type="radio" class="atoggle" data-atarget=".no_expiry" data-astate="0" value="2" <?php if (isset($options['exec_mode'])) { checked('2', $options['exec_mode']); } ?>  />&nbsp;Everytime your site is visited (slow, more accurate)&nbsp;</label><br/>
											<p class="description">Retrieving images <em>on-post</em> decreases the loadtime of your page significantly, but on the downside the results might be outdated at some point. Should you choose to retrieve images <em>on-view</em>, it is recommended to <a href="#user_agents">restrict access</a> to decrease load times for human readers.</p>
										</div>
									</td> 
								</tr>
									
								<!-- DATA EXPIRY -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Data Expiry:</label></th> 
								<td colspan="2">
									<select name='ographr_options[data_expiry]' class="no_expiry" <?php if ($options['exec_mode'] == 2) print 'disabled="disabled"'; ?> >
										<?php if (($options['exec_mode']) && (!isset($options['data_expiry']))) { $options['data_expiry'] = "-1";} ?>
										<option value='-1' <?php selected('-1', $options['data_expiry']); ?> >never</option>
										<?php if($options['debug_level'] > 0) { ?>
											<option value='1' <?php selected('1', $options['data_expiry']); ?> >after 1 day</option>
											<option value='2' <?php selected('2', $options['data_expiry']); ?> >after 2 days</option>
											<option value='3' <?php selected('3', $options['data_expiry']); ?> >after 3 days</option>
										<?php } ?>
										<option value='7' <?php selected('7', $options['data_expiry']); ?> >after 1	week</option>
										<option value='14' <?php selected('14', $options['data_expiry']); ?> >after 2 weeks</option>
										<option value='21' <?php selected('21', $options['data_expiry']); ?> >after 3 weeks</option>
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

								<!-- HTML PREFIX -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Namespace:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[add_prefix]" type="checkbox" value="1" <?php if (isset($options['add_prefix'])) { checked('1', $options['add_prefix']); } ?> /> Add Open Graph prefix to source </label><br/>
										<p class="description">Facebook advises the inclusion of the Open Graph prefix, though tags will be interpreted without one. However, your WordPress theme needs to support <a href="http://codex.wordpress.org/Function_Reference/language_attributes" target="_blank">language attributes</a> to make this work! </p>
									</td>
								</tr>
									
								<!-- MORE TRIGGERS -->
								<a name="more_triggers"></a> 
								<tr> 
									<th class="pull-left width-140" scope="row"><label>More Triggers:</label></th> 
									<td colspan="2">

										<ul class="horizontal">
											<li>
												<ul>
													<li><label title="Add poster images from HTML5 video-players"><input name="ographr_options[enable_videoposter]" type="checkbox" value="1" <?php if (isset($options['enable_videoposter'])) { checked('1', $options['enable_videoposter']); } ?> /> Video posters </label></li>
													<li><label title="Add poster images from JW Player"><input name="ographr_options[enable_jwplayer]" type="checkbox" value="1" <?php if (isset($options['enable_jwplayer'])) { checked('1', $options['enable_jwplayer']); } ?> /> JW Player </label></li>
													<li><label title="Add poster images from NVB Player"><input name="ographr_options[enable_nvbplayer]" type="checkbox" value="1" <?php if (isset($options['enable_nvbplayer'])) { checked('1', $options['enable_nvbplayer']); } ?> /> NVB Player </label></li>
												</ul>
											</li>
											<li>
												<ul >
													<li><label title="Add image tags from your post"><input name="ographr_options[add_post_images]" type="checkbox" class="atoggle" data-atarget="input.disable_filters, textarea.disable_filters" data-astate="1" value="1" <?php if (isset($options['add_post_images'])) { checked('1', $options['add_post_images']); } ?> /> Post images </label></li>
													<li><label title="Add images uploaded through WordPress"><input name="ographr_options[add_attached_image]" type="checkbox" value="1" class="atoggle" data-atarget="input.post_thumbnail" data-astate="0" <?php if (isset($options['add_attached_image'])) { checked('1', $options['add_attached_image']); } ?> /> Attached images </label></li>
													<li class="centered"><label title="Add featured images from your post"><input name="ographr_options[add_post_thumbnail]" type="checkbox" value="1" class="post_thumbnail" <?php if (isset($options['add_post_thumbnail'])) { checked('1', $options['add_post_thumbnail']); }; if ($options['add_attached_image']) { print 'disabled="disabled"'; } ?> /> Featured image </label>
													<a class="centered" href="http://codex.wordpress.org/Post_Thumbnails" title="WordPress Codex: Post Thumbnails" target="_blank">?</a></label></label></li>
												</ul>
											</li>
										</ul>
									</td>
								</tr>

								<!-- CANONICAL URL -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Canonical URL:</label></th> 
									<td colspan="2">
								<label><input name="ographr_options[add_trailing_slash]" type="checkbox" value="1" <?php if (isset($options['add_trailing_slash'])) { checked('1', $options['add_trailing_slash']); } ?> /> Add trailing slash to URL if missing </label><br/>
								<p class="description">To avoid warnings in the Facebook <a href="http://developers.facebook.com/tools/debug" target="_blank">debug tool</a>, make sure to check the effect of this option!</p>
								
								<!-- LINK TYPE -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Link Type:</label></th> 
									<td colspan="2">
								
								<select name='ographr_options[link_type]' class="link_type">
									<option value='permalink' <?php selected('permalink', $options['link_type']); ?>>Permalink</option>
									<option value='shortlink' <?php selected('shortlink', $options['link_type']); ?>>Shortlink</option>
								</select>
								
								<!-- LANGUAGE -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Language:</label></th> 
									<td colspan="2">
								
								<select name='ographr_options[locale]'>
									<?php
										$languages = $this->get_language_codes();
										foreach ($languages as $k => $v) {
											echo "<option value='$k'" . selected($k, $options['locale']) . ">$v</option>";
										}
										unset($languages); // save some RAM
									?>
								</select>
								<br/>
								<p class="description">
								<?php
									if (!WPLANG) {
										print "WordPress is set to default language (<em>en</em>)"; 
									} else if (!defined('WPLANG')) {
										print "not defined, using default (<em>en</em>)"; 
									} else {
										print "WordPress is currently set to <code>" . WPLANG . "</code>";
									}
								?>
								</p>
								</td>
							</tr>
							
								<!-- GOOGLE SNIPPETS -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Alternative tags:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[add_twitter_meta]" type="checkbox" value="1" <?php if (isset($options['add_twitter_meta'])) { checked('1', $options['add_twitter_meta']); } ?> /> Twitter Cards </label>
										<a class="centered" href="https://dev.twitter.com/docs/cards" title="Twitter Documentation: Twitter Cards" target="_blank">?</a></label></label>&nbsp;
										
										<label><input name="ographr_options[add_google_meta]" type="checkbox" value="1" <?php if (isset($options['add_google_meta'])) { checked('1', $options['add_google_meta']); } ?> /> Google+ Meta </label>
										<a class="centered" href="https://developers.google.com/+/plugins/snippet/" title="Google+ Documentation: Snippets" target="_blank">?</a></label></label>&nbsp;
											
										<label><input name="ographr_options[add_link_rel]" type="checkbox" value="1" <?php if (isset($options['add_link_rel'])) { checked('1', $options['add_link_rel']); } ?> /> Canonical Link Elements </label>
										<a class="centered" href="http://developers.whatwg.org/links.html" title="WHATWG: Links" target="_blank">?</a></label></label>&nbsp;
											
									</td>
								</tr>
								
								<!-- INTERFACE -->
								<tr> 
									<th class="pull-left width-140" scope="row"><label>Interface:</label></th> 
									<td colspan="2">
										<label title="Add an OGraphr menu to your admin bar"><input name="ographr_options[add_adminbar]" type="checkbox" value="1" <?php if (isset($options['add_adminbar'])) { checked('1', $options['add_adminbar']); } ?> /> Add menu to admin bar</label>&nbsp;
										<label title="Add post-specific options to each post"><input name="ographr_options[add_metabox]" type="checkbox" value="1" <?php if (isset($options['add_metabox'])) { checked('1', $options['add_metabox']); } ?> /> Add settings for each article</label>&nbsp;
										<label title="Add a graph of indexed posts to the options page"><input name="ographr_options[add_graph]" id="add_graph" class="atoggle no_expiry" data-atarget=".disable_graph" data-astate="1" type="checkbox" value="1" <?php if (isset($options['add_graph'])) { checked('1', $options['add_graph']); }; if ($options['exec_mode'] == 2) print 'disabled="disabled"'; ?>/> Add visual graph</label>&nbsp;
									</td>
								</tr>
								
								<!-- STATISTICS -->
								<tr class="disable_graph"> 
									<th class="pull-left width-140" scope="row"><label>Visual Graph:</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[fill_curves]" class="disable_graph no_expiry" type="checkbox" value="1" <?php if (isset($options['fill_curves'])) { checked('1', $options['fill_curves']); }; if((!isset($options['add_graph'])) || ($options['exec_mode'] == 2)) print 'disabled="disabled"'; ?>/> Fill curves</label>&nbsp;
										
										<label ><input name="ographr_options[smooth_curves]" class="disable_graph no_expiry" type="checkbox" value="1" <?php if (isset($options['smooth_curves'])) { checked('1', $options['smooth_curves']); }; if((!isset($options['add_graph'])) || ($options['exec_mode'] == 2)) print 'disabled="disabled"'; ?> /> Smooth curves</label>&nbsp;
									</td>
								</tr>
								
								</tbody></table>

								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
							</div>

						</div>
					
						<!-- F A C E B O O K -->
						<div class="postbox advanced_opt">
							<span class="hndle heading">Facebook</span>
							<div class="inside">	
								<table> 
								<tbody>

								<!-- HUMAN READABLE-NAME -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Human-readable Name:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[fb_site_name]" value="<?php echo $options['fb_site_name']; ?>" /><br/>
									<p class="description">
										<code>%sitename%</code> &mdash; your blog's name (<em><?php if($wp_url) { echo $mywp['blog_name']; } else { echo '<span style="color:red;">empty</span>';} ?></em>)<br />
										<code>%siteurl%</code> &mdash; the URL of your blog (<em><?php echo $wp_url; ?></em>)
									<p>
								</td>
								</tr>
							
								<!-- OBJECT TYPE -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Object Type:</label></th> 
								<td colspan="2">
									<select name='ographr_options[fb_type]'>
										<?php
											$fb_types = array('activity', 'actor', 'album', 'article', 'athlete', 'author', 'band', 'bar', 'blog', 'book', 'cafe', 'cause', 'city', 'company', 'country', 'director', 'drink', 'food', 'game', 'government', 'hotel', 'landmark', 'movie', 'musician', 'non_profit', 'politician', 'product', 'public_figure', 'restaurant', 'school', 'song', 'sport', 'sports_league', 'sports_team', 'state_province', 'tv_show', 'university', 'website', );
											echo "<option value='_none'" . selected('_none', $options['fb_type']) . ">(none)</option>";
											foreach($fb_types as $fb_type) {
												echo "<option value='$fb_type'" . selected($fb_type, $options['fb_type']) . ">$fb_type</option>";
											}
											unset($fb_types);
										?>

									</select>
								</td>
								</tr>
							
								<!-- FACEBOOK ADMIN -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Admin ID:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[fb_admins]" value="<?php echo $options['fb_admins']; ?>" /><br/>
									<p class="description">If you administer a page for your blog on Facebook, you can enter your <a href="http://developers.facebook.com/docs/reference/api/user/" target="_blank">User ID</a> above</p>
								</td>
								</tr>
							
								<!-- FACEBOOK APP -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Application ID:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[fb_app_id]" value="<?php echo $options['fb_app_id']; ?>" /><br/>
									<p class="description">If your blog uses a Facebook app, you can enter your <a href="https://developers.facebook.com/apps" target="_blank">Application ID</a> above</p>
								</td>
								</tr>	
							
								</tbody></table>
								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
						</div>

						</div>


						<!-- TWITTER -->
						<div class="postbox advanced_opt">
							<span class="hndle heading">Twitter</span>
							<div class="inside">
								<p>
									Website owners must <a href="https://dev.twitter.com/cards" target="_blank">opt-in</a> to have cards displayed for your domain, and Twitter must approve the integration. Below you can specify both your <code>@username</code> and/or your user ID. Note that user IDs never change, while <code>@username</code> can be changed by its owner.
								</p>
								<table> 
								<tbody>

								<!-- WEBSITE USER -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Website User:</label></th> 
								<td colspan="2"><input type="text" size="60" name="ographr_options[twitter_site_user]" value="<?php echo $options['twitter_site_user']; ?>" /></td>
								</tr>
							
								<!-- WEBSITE ID -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Website ID:</label></th> 
								<td colspan="2"><input type="text" size="60" name="ographr_options[twitter_site_id]" value="<?php echo $options['twitter_site_id']; ?>" /></td>
								</tr>
							
							
								<!-- AUTHOR USER -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Author User:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[twitter_author_user]" value="<?php echo $options['twitter_author_user']; ?>" /><br/>
									<p class="description">
											<code>%user_twitter%</code> &mdash; use Twitter name saved in your <a href="<?php print get_admin_url() . "profile.php";?>">user profile</a> (requires plug-in, e.g. <a href="http://wordpress.org/extend/plugins/twitter-profile-field/" target="_blank">Twitter Profile Field</a>)<br/>
											<code>%user_aim%</code> &mdash; abuse <em>AIM</em> name saved in your profile<br/>
											<code>%user_jabber%</code> &mdash; abuse <em>Jabber</em> name saved in your profile<br/>
											<code>%user_yahoo%</code> &mdash; abuse <em>Yahoo! IM</em> name saved in your profile
									</p>
								</td>
								</tr>

								<!-- AUTHOR ID -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Author ID:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[twitter_author_id]" value="<?php echo $options['twitter_author_id']; ?>" /><br/>
									<p class="description">like above, you can use <code>%user_aim%</code>, <code>%user_jabber%</code> or <code>%user_yahoo%</code> &mdash; you can't use the same twice!</p>
								</td>
								</tr>

								</tbody></table>

								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>
							</div>

						</div>

						<!-- MOBILE APPS -->
						<div class="postbox advanced_opt">
							<span class="hndle heading">Mobile Apps</span>
							<div class="inside">
								<p>
									If you are offering a mobile app for iOS or Android, you can name (and link) them below. At this moment, only <a href="https://dev.twitter.com/docs/cards/app-installs-and-deep-linking" title="Twitter: App Installs and Deep-Linking" target="_blank">Twitter Cards</a> will make use of that information.
								</p>
								<table> 
								<tbody>

								<!-- IPHONE APP -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>iPhone App Name:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_iphone_name]" value="<?php echo $options['app_iphone_name']; ?>" /><br/>

								</td>
								</tr>

								<!-- IPHONE ID -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>iPhone App ID:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_iphone_id]" value="<?php echo $options['app_iphone_id']; ?>" /><br/>

								</td>
								</tr>

								<!-- IPHONE URL -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>App Store URL:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_iphone_url]" value="<?php echo $options['app_iphone_url']; ?>" /><br/>

								</td>
								</tr>

								<!-- UNIVERSAL APP -->
								<tr > 
									<th class="pull-left" scope="row"><label>&nbsp;</label></th> 
									<td colspan="2">
										<label><input name="ographr_options[app_universal]" class="atoggle" type="checkbox" value="1" data-atarget="input.app_ipad" data-astate="0" <?php if (isset($options['app_universal'])) { checked('1', $options['app_universal']); } ?> /> This is an Universal App working on both iPhone and iPad </label>
									</td>
								</tr>

								<!-- IPAD APP -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>iPad App Name:</label></th> 
								<td colspan="2">
									<input class="app_ipad" type="text" size="60" name="ographr_options[app_ipad_name]" value="<?php echo $options['app_ipad_name']; ?>" <?php if (isset($options['app_universal'])) { print 'disabled="disabled"';} ?>  /><br/>

								</td>
								</tr>

								<!-- IPAD ID -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>iPad App ID:</label></th> 
								<td colspan="2">
									<input class="app_ipad" type="text" size="60" name="ographr_options[app_ipad_id]" value="<?php echo $options['app_ipad_id']; ?>" <?php if (isset($options['app_universal'])) { print 'disabled="disabled"';} ?> /><br/>

								</td>
								</tr>

								<!-- IPAD URL -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>App Store URL:</label></th> 
								<td colspan="2">
									<input class="app_ipad" type="text" size="60" name="ographr_options[app_ipad_url]" value="<?php echo $options['app_ipad_url']; ?>" <?php if (isset($options['app_universal'])) { print 'disabled="disabled"';} ?> /><br/>

								</td>
								</tr>

								<tr>
									<th>&nbsp;</th>
								</tr>

								<!-- ANDROID APP -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Android App Name:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_android_name]" value="<?php echo $options['app_android_name']; ?>" /><br/>

								</td>
								</tr>

								<!-- ANDROID ID -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>Android App ID:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_android_id]" value="<?php echo $options['app_android_id']; ?>" /><br/>

								</td>
								</tr>

								<!-- ANDROID URL -->	
								<tr> 
								<th class="pull-left width-140" scope="row"><label>GooglePlay URL:</label></th> 
								<td colspan="2">
									<input type="text" size="60" name="ographr_options[app_android_url]" value="<?php echo $options['app_android_url']; ?>" /><br/>

								</td>
								</tr>

								</tbody></table>
								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
							</div>
						</div>

						<?php if( (WP_DEBUG == TRUE) || (isset($options['always_devmode'])) ){ ?>
						<!-- D E V E L O P E R -->
						<div class="postbox advanced_opt">
							<a name="developer_settings"></a> 
							<span class="hndle heading">Developer Settings</span>
							<div class="inside">
								<table> 
									<tbody>
								
									<!-- AGE -->	
										<tr> 
										<th class="pull-left width-140" scope="row"><label>Debug Level:</label></th> 
										<td colspan="2">
											<select name='ographr_options[debug_level]'>
			 									<?php for ($i = 0; $i <= 3; $i++) {
													print "<option value='$i'" . selected($i, $options['debug_level']) . ">$i</option>";
												} ?> 
											</select>
										</td> 
										<td>&nbsp;</td>
										</tr>

										<tr> 
										<th class="pull-left" scope="row"><label>Beta:</label></th> 
											<td colspan="2">
												<label><input name="ographr_options[enable_beta]" type="checkbox" value="1" <?php if (isset($options['enable_beta'])) { checked('1', $options['enable_beta']); }; ?> /> Enable beta features </label>&nbsp;
											</td> 
										</tr>

										<tr> 
										<th class="pull-left" scope="row"><label>User Agent:</label></th> 
											<td colspan="2">
												<label><input name="ographr_options[ua_testdrive]" type="checkbox" value="1" <?php if (isset($options['ua_testdrive'])) { checked('1', $options['ua_testdrive']); }; ?> /> Enable User Agent test </label>&nbsp;
											</td> 
										</tr>

										<tr> 
										<th class="pull-left" scope="row"><label>Display:</label></th> 
											<td colspan="2">
												<label><input name="ographr_options[always_devmode]" type="checkbox" value="1" <?php if (isset($options['always_devmode'])) { checked('1', $options['always_devmode']); }; ?> /> Always show developer settings </label>&nbsp;
											</td> 
										</tr>
								
									</tbody>
							</table>

								<div class="submit">
									<input type="submit" class="button-primary" value="Save all changes" />
									<a href="#wpbody-content" class="button-secondary">Back to top</a>
								</div>		
							</div>
						</div>
						<?php } ?>

						<label class="outside advanced_opt"><input name="ographr_options[chk_default_options_db]" type="checkbox" value="1" class="advanced_opt atoggle" data-atarget="input.del_postmeta" data-astate="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon saving</label>&nbsp;
						
						<label class="advanced_opt"><input name="ographr_options[delete_postmeta]" type="checkbox" value="1" class="advanced_opt del_postmeta" disabled="disabled" <?php if (isset($options['delete_postmeta'])) { checked('1', $options['delete_postmeta']); } ?> /> and delete all plug-in data </label>

						</fieldset>
						</form>
						<!-- *********************** END: Main Content ********************* -->
						<br/>
						<p class="description"><a style="" href="http://wordpress.org/extend/plugins/meta-ographr/" target="_blank">OGraphr <?php echo OGRAPHR_VERSION ?></a> &copy <?php $this_year = date('Y'); if (date('Y') > 2012) { print "2012-$this_year"; } else { print "2012"; } ?> by Jan T. Sott</p>
						</td> <!-- [left] -->

						<td class="right">
						<!-- *********************** BEGIN: Sidebar ************************ -->		

						<div class="postbox">
							<span class="hndle heading">Navigator</span>
							<div class="inside">
							<ul>
								<li><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/" title="Visit the plug-in page on WordPress.com" target="_blank">WordPress</a></li>
								<li class="advanced_opt"><a class="lwp" href="https://github.com/idleberg/OGraphr" title="Contribute to the GitHub repository" target="_blank">GitHub</a></li>
								<li><a class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/faq/" title="Frequently Asked Questions" target="_blank">FAQ</a></li>
								<li><a class="lwp" href="http://wordpress.org/tags/meta-ographr?forum_id=10" title="Seek help on the OGraphr support forum on WordPress.com" target="_blank">Need help?</a></li>
								<li class="advanced_opt"><a title="Read what changed over the last versions" class="lwp" href="http://wordpress.org/extend/plugins/meta-ographr/changelog/" target="_blank">Changelog</a></li>
								<li class="advanced_opt"><a class="lwp" href="http://plugins.svn.wordpress.org/meta-ographr/" title="Browse the Subversion repository for older releases of this plug-in" target="_blank">SVN</a></li>
								<li>&nbsp;</li>
								<li><a href="https://twitter.com/whyeye_org" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @whyeye_org</a>
								<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>
							</ul>			
							</div>

						</div>

						<div class="postbox">
							<span class="hndle heading">Donations</span>
							<div class="inside">
							<p>If you like this plug-in, please consider a small donation!</p>
							<script data-gittip-username="idleberg" src="//gttp.co/v1.js"></script>
							<p><small>You can also tip me using <a title="Tip me on Flattr" href="https://flattr.com/submit/auto?user_id=idleberg&url=http://github.com/idleberg/OGraphr">Flattr</a> or <a title="My Amazon Wishlist" href="http://www.amazon.de/registry/wishlist/PPAO8XTAGS4V/">Amazon</a>!</small></p>
		
							</div>

						</div>
						
						<?php if( (isset($options['add_graph'])) && (WP_DEBUG != TRUE) ){ ?>
							<div class="postbox">
								<span class="hndle heading">Statistics</span>
								<div class="inside">
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
									<div id="chartdiv" style="height:120px;width:100%; "></div>
								<?php } ?>
								<p style="font-size:8pt;">
									<?php print "<span style=\"color:#8560a8\">&#9632;</span><span style=\"color:#bd8cbf\">&#9632;</span> Posts indexed: $posts_harvested / $posts_published <span style=\"color:#999;\">&nbsp;$posts_percent%</span>"; ?><br/>
									<?php print "<span style=\"color:transparent\">&#9632;&#9632;</span> Pages indexed: $pages_harvested / $pages_published <span style=\"color:#999;\">&nbsp;$pages_percent%</span>"; ?>
								</p>
								
								</div>

							</div>
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
	public function ographr_validate_options($input) {
		 // strip html from textboxes
		$input['website_title'] = htmlentities($input['website_title']);
		$input['website_thumbnail'] = htmlentities($input['website_thumbnail']);
		$input['website_description'] = htmlentities($input['website_description']);
		$input['filter_custom_urls'] = htmlentities($input['filter_custom_urls']);
		$input['etracks_api'] = htmlentities($input['etracks_api']);
		$input['bambuser_api'] = htmlentities($input['bambuser_api']);
		$input['bandcamp_api'] = htmlentities($input['bandcamp_api']);
		$input['flickr_api'] = htmlentities($input['flickr_api']);
		$input['myvideo_dev_api'] = htmlentities($input['myvideo_dev_api']);
		$input['myvideo_web_api'] = htmlentities($input['myvideo_web_api']);
		$input['soundcloud_api'] = htmlentities($input['soundcloud_api']);
		$input['ustream_api'] = htmlentities($input['ustream_api']);
		$input['fb_site_name'] = htmlentities($input['fb_site_name']);
		if(isset($input['twitter_site_user']))
			$input['twitter_site_user'] = htmlentities($input['twitter_site_user']);
		if(isset($input['twitter_author_id']))
			$input['twitter_author_id'] = htmlentities($input['twitter_author_id']);
		if(isset($input['app_iphone_name']))
			$input['app_iphone_name'] = htmlentities($input['app_iphone_name']);
		if(isset($input['app_iphone_id']))
			$input['app_iphone_id'] = htmlentities($input['app_iphone_id']);
		if(isset($input['app_ipad_name']))
			$input['app_ipad_name'] = htmlentities($input['app_ipad_name']);
		if(isset($input['app_ipad_id']))
			$input['app_ipad_id'] = htmlentities($input['app_ipad_id']);
		if(isset($input['app_android_name']))
			$input['app_android_name'] = htmlentities($input['app_android_name']);
		if(isset($input['app_android_id']))
			$input['app_android_id'] = htmlentities($input['app_android_id']);
		
		// is Facebook Admin ID numeric?
		if(!is_numeric($input['fb_admins'])){
			$input['fb_admins'] = preg_replace("/[^0-9]+/", "", $input['fb_admins']);
		}
		
		// is Facebook Application ID numeric?
		if(!is_numeric($input['fb_app_id'])){
			$input['fb_app_id'] = preg_replace("/[^0-9]+/", "", $input['fb_app_id']);
		}
		
		// is Twitter Website User numeric?
		$input['twitter_site_user'] = preg_replace("/[^a-zA-Z0-9_]+/", "", $input['twitter_site_user']);
		
		// is Twitter Website ID numeric?
		if(!is_numeric($input['twitter_site_id'])){
			$input['twitter_site_id'] = preg_replace("/[^0-9]+/", "", $input['twitter_site_id']);
		}
		
		// is Twitter Author User numeric?
		if( ($input['twitter_author_user'] != "%user_twitter%")
			&& ($input['twitter_author_user'] != "%user_aim%") 
			&& ($input['twitter_author_user'] != "%user_yahoo%"))
			$input['twitter_author_user'] = preg_replace("/[^a-zA-Z0-9_]+/", "", $input['twitter_author_user']);
		
		// is Twitter Author ID numeric?
		if(!is_numeric($input['twitter_author_id'])){
			$input['twitter_author_id'] = preg_replace("/[^0-9]+/", "", $input['twitter_author_id']);
		}

		/*
		// is iPhone App ID valid?
		if(!is_numeric($input['app_iphone_id'])){
			$input['app_iphone_id'] = preg_replace("/[^a-ZA-Z0-9.]+/", "", $input['app_iphone_id']);
		}
			

		// is iPad App ID valid?
		if(!is_numeric($input['app_ipad_id'])){
			$input['app_ipad_id'] = preg_replace("/[^a-ZA-Z0-9.*]+/", "", $input['app_ipad_id']);
		}
		
		// is Android App ID valid?
		if(!is_numeric($input['app_android_id'])){
			$input['app_android_id'] = preg_replace("/[^a-ZA-Z0-9.]+/", "", $input['app_android_id']);
		}
		*/
						
		return $input;
	}

	//add JQuery to footer
	public function ographr_javascript() {
		
		//global $options;
		$options = get_option('ographr_options');
		
		if (isset($options['add_graph'])) {
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
			
			// suppress warnings
			$posts_total = NULL;
			$posts_indexed = NULL;

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
		?>
	
		<script type="text/javascript">
					
			function render_stats() {
				// does not work in WP_DEBUG mode (yet?)
				var line1=[<?php print $posts_total; ?>];
				var line2=[<?php print $posts_indexed; ?>];
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
						fill: <?php if ($options['fill_curves']) { print "true"; } else { print "false"; } ?>,
						fillAlpha: 0.9,
						markerOptions: {
							size:<?php if ($interval >= 35) { print 0; } else { print 5; } ?>,
						 	<?php if ($options['fill_curves']) { print 'color: "#ed1c24",'; } ?>
						},
						rendererOptions: {
							smooth: <?php if ($options['smooth_curves']) { print "true"; } else { print "false"; } ?>,
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
				          tickInterval:'<?php if ($interval > 8760) { print "10 years"; } else if ($interval > 720 ) { print "1 year"; } else if ($interval > 90 ) { print "1 month"; } else if ($interval > 21 ) { print "1 week"; } else { print "1 day"; } ?>',
				          min: <?php print '"' . date("F j, Y", strtotime(array_shift(array_keys($stats))) ) . '"'; ?>,
				          tickOptions:{
				            formatString:'%F'
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
	
		<?php }
	} //ographr_javascript

	public function get_language_codes() {
		$language_codes = array(
			"_none" => "(none)", "sq_AL" => "Albanian (Albania)", "ar_DZ" => "Arabic (Algeria)", "ar_BH" => "Arabic (Bahrain)", "ar_EG" => "Arabic (Egypt)", "ar_IQ" => "Arabic (Iraq)", "ar_JO" => "Arabic (Jordan)", "ar_KW" => "Arabic (Kuwait)", "ar_LB" => "Arabic (Lebanon)", "ar_LY" => "Arabic (Libya)", "ar_MA" => "Arabic (Morocco)", "ar_OM" => "Arabic (Oman)", "ar_QA" => "Arabic (Qatar)", "ar_SA" => "Arabic (Saudi Arabia)", "ar_SD" => "Arabic (Sudan)", "ar_SY" => "Arabic (Syria)", "ar_TN" => "Arabic (Tunisia)", "ar_AE" => "Arabic (United Arab Emirates)", "ar_YE" => "Arabic (Yemen)", "be_BY" => "Belarusian (Belarus)", "bg_BG" => "Bulgarian (Bulgaria)", "ca_ES" => "Catalan (Spain)", "zh_CN" => "Chinese (China)", "zh_HK" => "Chinese (Hong Kong)", "zh_SG" => "Chinese (Singapore)", "zh_TW" => "Chinese (Taiwan)", "hr_HR" => "Croatian (Croatia)", "cs_CZ" => "Czech (Czech Republic)", "da_DK" => "Danish (Denmark)", "nl_BE" => "Dutch (Belgium)", "nl_NL" => "Dutch (Netherlands)", "en_AU" => "English (Australia)", "en_CA" => "English (Canada)", "en_IN" => "English (India)", "en_IE" => "English (Ireland)", "en_MT" => "English (Malta)", "en_NZ" => "English (New Zealand)", "en_PH" => "English (Philippines)", "en_SG" => "English (Singapore)", "en_ZA" => "English (South Africa)", "en_GB" => "English (United Kingdom)", "en_US" => "English (United States)", "et_EE" => "Estonian (Estonia)", "fi_FI" => "Finnish (Finland)", "fr_BE" => "French (Belgium)", "fr_CA" => "French (Canada)", "fr_FR" => "French (France)", "fr_LU" => "French (Luxembourg)", "fr_CH" => "French (Switzerland)", "de_AT" => "German (Austria)", "de_DE" => "German (Germany)", "de_LU" => "German (Luxembourg)", "de_CH" => "German (Switzerland)", "el_CY" => "Greek (Cyprus)", "el_GR" => "Greek (Greece)", "he_IL" => "Hebrew (Israel)", "hi_IN" => "Hindi (India)", "hu_HU" => "Hungarian (Hungary)", "is_IS" => "Icelandic (Iceland)", "in_ID" => "Indonesian (Indonesia)", "ga_IE" => "Irish (Ireland)", "it_IT" => "Italian (Italy)", "it_CH" => "Italian (Switzerland)", "ja_JP" => "Japanese (Japan)", "ja_JP_JP" => "Japanese (Japan,JP)", "ko_KR" => "Korean (South Korea)", "lv_LV" => "Latvian (Latvia)", "lt_LT" => "Lithuanian (Lithuania)", "mk_MK" => "Macedonian (Macedonia)", "ms_MY" => "Malay (Malaysia)", "mt_MT" => "Maltese (Malta)", "no_NO" => "Norwegian (Norway)", "no_NO_NY" => "Norwegian (Norway,Nynorsk)", "pl_PL" => "Polish (Poland)", "pt_BR" => "Portuguese (Brazil)", "pt_PT" => "Portuguese (Portugal)", "ro_RO" => "Romanian (Romania)", "ru_RU" => "Russian (Russia)", "sr_BA" => "Serbian (Bosnia and Herzegovina)", "sr_ME" => "Serbian (Montenegro)", "sr_CS" => "Serbian (Serbia and Montenegro)", "sr_RS" => "Serbian (Serbia)", "sk_SK" => "Slovak (Slovakia)", "sl_SI" => "Slovenian (Slovenia)", "es_AR" => "Spanish (Argentina)", "es_BO" => "Spanish (Bolivia)", "es_CL" => "Spanish (Chile)", "es_CO" => "Spanish (Colombia)", "es_CR" => "Spanish (Costa Rica)", "es_DO" => "Spanish (Dominican Republic)", "es_EC" => "Spanish (Ecuador)", "es_SV" => "Spanish (El Salvador)", "es_GT" => "Spanish (Guatemala)", "es_HN" => "Spanish (Honduras)", "es_MX" => "Spanish (Mexico)", "es_NI" => "Spanish (Nicaragua)", "es_PA" => "Spanish (Panama)", "es_PY" => "Spanish (Paraguay)", "es_PE" => "Spanish (Peru)", "es_PR" => "Spanish (Puerto Rico)", "es_ES" => "Spanish (Spain)", "es_US" => "Spanish (United States)", "es_UY" => "Spanish (Uruguay)", "es_VE" => "Spanish (Venezuela)", "sv_SE" => "Swedish (Sweden)", "th_TH" => "Thai (Thailand)", "th_TH_TH" => "Thai (Thailand,TH)", "tr_TR" => "Turkish (Turkey)", "uk_UA" => "Ukrainian (Ukraine)", "vi_VN" => "Vietnamese (Vietnam)");
		return $language_codes;
	}


	public function get_iso_codes() {
		$country_codes = array(
		"AF" => 'Afghanistan', "AX" => "Åland Islands", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua & Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BQ" => "Bonaire, Sint Eustatius & Saba", "BA" => "Bosnia & Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", "BN" => "Brunei Darussalam", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos (Keeling) Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo", "CD" => "Congo (Democratic Republic)", "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Côte D'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CW" => "Curaçao", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands (Malvinas)", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GG" => "Guernsey", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island And McDonald Islands", "VA" => "Holy See (vatican City State)", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IM" => "Isle of Man", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JE" => "Jersey", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KI" => "Korea (North)", "KR" => "Korea (South)", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macao", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestine", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RE" => "Réunion", "RO" => "Romania", "RU" => "Russian Federation", "RW" => "Rwanda", "BL" => "Saint Barthélemy", "SH" => "Saint Helena, Ascension & Tristan Da Cunha", "KN" => "Saint Kitts & Nevis", "LC" => "Saint Lucia", "MF" => "Saint Martin (French Part)", "PM" => "Saint Pierre & Miquelon", "VC" => "Saint Vincent & The Grenadines", "WS" => "Samoa", "SM" => "San Marino", "ST" => "Sao Tome & Principe", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SX" => "Sint Maarten (Dutch Part)", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia & The South Sandwich Islands", "SS" => "South Sudan", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard & Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syrian Arab Republic", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "TRINIDAD & Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks & Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "GB" => "UNited Kingdom", "US" => "United States", "UM" => "United States Minor Outlying Islands", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Viet Nam", "VG" => "Virgin Islands (British)", "VI" => "Virgin Islands (U.S.)", "WF" => "Wallis & Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe", );
		return $country_codes;
	}

}; // end of class