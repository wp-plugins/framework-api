<?php
/**
 * Plugin Name: Framework API
 * Plugin URI: http://memberbuddy.com/docs/framework-api
 * Description: Framework API
 * Version: 0.0.3
 * Author: Rob Holmes
 * Author URI: http://memberbuddy.com/people/rob
 */

/*  Copyright 2011 Rob Holmes ( email: rob@onemanonelaptop.com )

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
	
// Include the plugin framework api 
require_once( 'framework/framework.php' );


class FrameworkApi extends PluginFramework {

	// Force singelton
	static $instance = false;
	public static function getInstance() {
		return (self::$instance ? self::$instance : self::$instance = new self);
	} // function
		
	// Called at the start of the constructor
	public function plugin_construction() {	
		// Give the options page a title
		$this->options_page_title = "Plugin Framework Tests";
	
		// The settings menu link text
		$this->options_page_name = "Plugin Framework";
		
		// The slug of the plugin
		$this->slug = "framework-api";

		// Definte the plugin defaults
		$this->defaults = array ();

		// Actions & Filters
		
		
	} // function

	// Called at the end of the constructor
	public function after_construction() {	
	
	} // function
	
	
	// Called by admin init to register the options page metaboxes
	function plugin_options_meta_boxes() {
		$this->easy_options_metabox('admin-section-general','Options Page Field Tests');
		$this->easy_options_field( array ('title'=>'Text',	'id' => 'test-text', 'type'=>'text', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Text Area',	'id' => 'test-textarea', 'type'=>'textarea', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Checkbox',	'id' => 'test-checkbox', 'type'=>'checkbox', 'section'=>'admin-section-general', 'description' => '') );		
		$this->easy_options_field( array ('title'=>'Color',	'id' => 'test-color', 'type'=>'color', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Range',	'id' => 'test-range', 'type'=>'range', 'section'=>'admin-section-general', 'description' => '','unit'=>'&nbsp;Units') );	
		$this->easy_options_field( array ('title'=>'Attachment',	'id' => 'test-attachment', 'type'=>'attachment', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Margin',	'id' => 'test-margin', 'type'=>'margin', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Padding',	'id' => 'test-padding', 'type'=>'padding', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Border',	'id' => 'test-border', 'type'=>'border', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Font',	'id' => 'test-font', 'type'=>'font', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Box Shadow',	'id' => 'test-boxshadow', 'type'=>'boxshadow', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Align',	'id' => 'test-align', 'type'=>'align', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Bordertype',	'id' => 'test-bordertype', 'type'=>'bordertype', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Lineheight',	'id' => 'test-lineheight', 'type'=>'lineheight', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Fontsize',	'id' => 'test-fontsize', 'type'=>'fontsize', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Html Tags',	'id' => 'test-htmltags', 'type'=>'htmltags', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Background Repeat',	'id' => 'test-backgroundrepeat', 'type'=>'backgroundrepeat', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Background Positions',	'id' => 'test-backgroundpositions', 'type'=>'backgroundpositions', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Font Unit',	'id' => 'test-fontunit', 'type'=>'fontunit', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Font Family',	'id' => 'test-fontfamily', 'type'=>'fontfamily', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Background',	'id' => 'test-background', 'type'=>'background', 'section'=>'admin-section-general', 'description' => '') );
		$this->easy_options_field( array ('title'=>'Suggest Posts',	'id' => 'test-suggest-post', 'type'=>'suggest', 'section'=>'admin-section-general', 'description' => '','suggesturl'=>'post' ));
		$this->easy_options_field( array ('title'=>'Suggest Pages',	'id' => 'test-suggest-pages', 'type'=>'suggest', 'section'=>'admin-section-general', 'description' => '','suggesturl'=>'page') );
		$this->easy_options_field( array ('title'=>'Wysiwyg Area',	'id' => 'test-wysiwyg', 'type'=>'wysiwyg', 'section'=>'admin-section-general', 'description' => '') );
		
	} // function
	
	
	// Called by admin init to setup the post meta boxes
	function plugin_post_meta_boxes() {
	
		// Test each one of the field widgets in a post meta box
		$this->easy_post_field(array( "title" => "Text", "id" => "test-text", "type" => "text", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Text Area", "id" => "test-textarea", "type" => "textarea", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Checkbox", "id" => "test-checkbox", "type" => "checkbox", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Color", "id" => "test-color", "type" => "color", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Range", "id" => "test-range", "type" => "range", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Attachment", "id" => "test-attachment", "type" => "attachment", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Margin", "id" => "test-margin", "type" => "margin", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Padding", "id" => "test-padding", "type" => "padding", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Border", "id" => "test-border", "type" => "border", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Font", "id" => "test-font", "type" => "font", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Box Shadow", "id" => "test-boxshadow", "type" => "boxshadow", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Align", "id" => "test-align", "type" => "align", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Border Type", "id" => "test-bordertype", "type" => "bordertype", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Line Height", "id" => "test-lineheight", "type" => "lineheight", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Font Size", "id" => "test-checkbox", "type" => "checkbox", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "HTML Tags", "id" => "test-htmltags", "type" => "htmltags", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Background Repeat", "id" => "test-backgroundrepeat", "type" => "backgroundrepeat", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Background Positions", "id" => "test-backgroundpositions", "type" => "backgroundpositions", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Font Unit", "id" => "test-fontunit", "type" => "fontunit", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Font Family", "id" => "test-fontfamily", "type" => "fontfamily", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Background", "id" => "test-background", "type" => "background", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Suggest Posts", "id" => "test-suggest-posts", "type" => "suggest", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => "",'suggesturl'=>'post'  ));			
		$this->easy_post_field(array( "title" => "Suggest Pages", "id" => "test-suggest-pages", "type" => "suggest", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => "",'suggesturl'=>'page'  ));			
		$this->easy_post_field(array( "title" => "Wysiwyg Area", "id" => "test-wysiwyg", "type" => "wysiwyg", "post_type" => "post", "section" => "new-meta-boxes", "std" => "",  "description" => ""  ));			
		
		// Add the new meta box
		$this->easy_post_metabox( 'new-meta-boxes','Post Meta Field Test','post');
	
	
		// Add some fields to a second post meta box
		$this->easy_post_field(array( "title" => "Text", "id" => "test-text2", "type" => "text", "post_type" => "post", "section" => "another-new-meta-box", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Text Area", "id" => "test-textarea2", "type" => "textarea", "post_type" => "post", "section" => "another-new-meta-box", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Checkbox", "id" => "test-checkbox2", "type" => "checkbox", "post_type" => "post", "section" => "another-new-meta-box", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Color", "id" => "test-color2", "type" => "color", "post_type" => "post", "section" => "another-new-meta-box", "std" => "",  "description" => ""  ));			
		$this->easy_post_field(array( "title" => "Range", "id" => "test-range2", "type" => "range", "post_type" => "post", "section" => "another-new-meta-box", "std" => "",  "description" => ""  ));			
		
		// Add the new meta box
		$this->easy_post_metabox( 'another-new-meta-box','Post Meta Field Test In Another Meta Box','post');
		
	} // function
	
	
}
// Instantiate our class 
$frameworkApi = FrameworkApi::getInstance();

?>