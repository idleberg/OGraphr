<?php
//
//  SETTINGS CONFIGURATION CLASS
//
//  By Olly Benson / v 1.2 / 13 July 2011 / http://code.olib.co.uk
//  Modified / Bugfix by Karl Cohrs / 17 July 2011 / http://karlcohrs.com
//
//  HOW TO USE
//  * add a include() to this file in your plugin.
//  * amend the config class below to add your own settings requirements.
//  * to avoid potential conflicts recommended you do a global search/replace on this page to replace 'meta_ograph' with something unique
//  * Full details of how to use Settings see here: http://codex.wordpress.org/Settings_API
 
class meta_ographr_config {
 
// MAIN CONFIGURATION SETTINGS
 
var $group = "MetaOGraphr"; // defines setting groups (should be bespoke to your settings)
var $page_name = "meta_ographr"; // defines which pages settings will appear on. Either bespoke or media/discussion/reading etc
 
//  DISPLAY SETTINGS
//  (only used if bespoke page_name)
 
var $title = "OGraphr Settings";  // page title that is displayed
var $intro_text = "<span style=\"margin:32px 0;color:grey;font-family:Georgia;font-style:italic;\">Work in progress by Jan T. Sott</span><br/>"; // text below title
var $nav_title = "OGraphr"; // how page is listed on left-hand Settings panel
 
//  SECTIONS
//  Each section should be own array within $sections.
//  Should contatin title, description and fields, which should be array of all fields.
//  Fields array should contain:
//  * label: the displayed label of the field. Required.
//  * description: the field description, displayed under the field. Optional
//  * suffix: displays right of the field entry. Optional
//  * default_value: default value if field is empty. Optional
//  * dropdown: allows you to offer dropdown functionality on field. Value is array listed below. Optional
//  * function: will call function other than default text field to display options. Option
//  * callback: will run callback function to validate field. Optional
//  * All variables are sent to display function as array, therefore other variables can be added if needed for display purposes
 
var $sections = array(
'website' => array(
    'title' => "Website Specifics",
    'description' => "Here you specify the title formatting (use <span style=\"font-family:monospace;\">%site%</span> for the site name, <span style=\"font-family:monospace;\">%post%</span> for the post/page title) and the default thumbnail",
    'fields' => array (
      'page_title' => array (
          'label' => "Title",
          'description' => "(optional)",
          'length' => "128",
          'default_value' => "%post%"
          ),
      'website_thumbnail' => array (
          'label' => "Image URL",
          'description' => "(optional)",
          'length' => "128",
          'default_value' => ""
          ),
      )
    ),

    'bandcamp' => array(
        'title' => "Bandcamp",
        'description' => "Bandcamp provides only limited access to their API and in any case you need to provide a valid developer key. You can apply for one <a href=\"http://bandcamp.com/developer#key_request\" target\"_blank\">here</a>.",
        'fields' => array (
          'bandcamp_api' => array (
              'label' => "Bandcamp API key",
              'description' => "(<span style=\"font-weight:bold\">required</span>)",
              'length' => "64",
              'default_value' => ""
              ),
          )
        ),

	'soundcloud' => array(
        'title' => "SoundCloud",
        'description' => "If for some reason you prefer using your own SoundCloud API key, you can specify it below. You can get one <a href=\http://soundcloud.com/you/apps\" target=\"blank\">here</a>.",
        'fields' => array (
          'soundcloud_api' => array (
              'label' => "SoundCloud API key",
              'description' => "(optional)",
              'length' => "64",
              'default_value' => SOUNDCLOUD_API_KEY
              ),
          )
        ),
    );
 
 // DROPDOWN OPTIONS
 // For drop down choices.  Each set of choices should be unique array
 // Use key => value to indicate name => display name
 // For default_value in options field use key, not value
 // You can have multiple instances of the same dropdown options
 
var $dropdown_options = array (
    'dd_colour' => array (
        '#f00' => "Red",
        '#0f0' => "Green",
        '#00f' => "Blue",
        '#fff' => "White",
        '#000' => "Black",
        '#aaa' => "Gray",
        )
    );
 
//  end class
};
 
class meta_ographr {
 
function meta_ographr($settings_class) {
    global $meta_ographr;
    $meta_ographr = get_class_vars($settings_class);
 
    if (function_exists('add_action')) :
      add_action('admin_init', array( &$this, 'plugin_admin_init'));
      add_action('admin_menu', array( &$this, 'plugin_admin_add_page'));
      endif;
}
 
function plugin_admin_add_page() {
  global $meta_ographr;
  add_options_page($meta_ographr['title'], $meta_ographr['nav_title'], 'manage_options', $meta_ographr['page_name'], array( &$this,'plugin_options_page'));
  }
 
function plugin_options_page() {
  global $meta_ographr;
printf('</pre>
<div>
<h2>%s</h2>
%s
<form action="options.php" method="post">',$meta_ographr['title'],$meta_ographr['intro_text']);
 settings_fields($meta_ographr['group']);
 do_settings_sections($meta_ographr['page_name']);
 printf('<p><input type="submit" class="button-primary" name="Submit" value="%s" /></p></form></div>
<pre>
',__('Save Changes'));
  }
 
function plugin_admin_init(){
  global $meta_ographr;
  foreach ($meta_ographr["sections"] AS $section_key=>$section_value) :
    add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $meta_ographr['page_name'], $section_value);
    foreach ($section_value['fields'] AS $field_key=>$field_value) :
      $function = (!empty($field_value['dropdown'])) ? array( &$this, 'plugin_setting_dropdown' ) : array( &$this, 'plugin_setting_string' );
      $function = (!empty($field_value['function'])) ? $field_value['function'] : $function;
      $callback = (!empty($field_value['callback'])) ? $field_value['callback'] : NULL;
      add_settings_field($meta_ographr['group'].'_'.$field_key, $field_value['label'], $function, $meta_ographr['page_name'], $section_key,array_merge($field_value,array('name' => $meta_ographr['group'].'_'.$field_key)));
      register_setting($meta_ographr['group'], $meta_ographr['group'].'_'.$field_key,$callback);
      endforeach;
    endforeach;
  }
 
function plugin_section_text($value = NULL) {
  global $meta_ographr;
  printf("
%s
 
",$meta_ographr['sections'][$value['id']]['description']);
}
 
function plugin_setting_string($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  printf('<input id="%s" type="text" name="%1$s[text_string]" value="%2$s" size="40" /> %3$s%4$s',
    $value['name'],
    (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
    (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
    (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
  }
 
function plugin_setting_dropdown($value = NULL) {
  global $meta_ographr;
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
    $chooseFrom = "";
    $choices = $meta_ographr['dropdown_options'][$value['dropdown']];
  foreach($choices AS $key=>$option) :
    $chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
      $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
    endforeach;
    printf('
<select id="%s" name="%1$s[text_string]">%2$s</select>
%3$s',$value['name'],$chooseFrom,
  (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
  }

 
//end class
}
 
$meta_ographr_init = new meta_ographr('meta_ographr_config');

?>