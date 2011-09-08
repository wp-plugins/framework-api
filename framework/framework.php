<?php
/**
	Version: 0.0.2
 
	Copyright 2011 Rob Holmes ( email: rob@onemanonelaptop.com )

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

	TODO:

	make meta boxes load up closed if required on first view

	add character counting to textbox / textarea

	
*/
if (!class_exists('PluginFramework')) {
	abstract class PluginFramework {
		
		// The Title that is displayed on the options page
		protected $options_page_title = "";
		
		// The menu anchor link text for the options page
		protected $options_page_name = "";
		
		// The slug of the plugin
		protected $slug = "";
		
		// The name used to store the saved settings in the options table
		protected $options_name = '';
		
		// The option group name
		protected $options_group = '';
		
		// The plugin defaults
		protected $defaults = array ();
		
		// The name of the file e.g. my-plugin/my-plugin.php
		protected $filename = '';

		// setup some defaults
		protected $yesno = array ('1'=>'Yes','0'=>'No');
		protected $image_extensions = array('png','gif','jpg','ico');
		protected $html_tags = array('h1','h2','h3','h4','h5','h6','div','span','p');
		protected $background_positions = array('center center'=>'Center Center','center top'=>'Center Top','center bottom'=>'Center Bottom','left center'=>'Left Center','left top'=>'Left Top',);
		protected $background_repeat = array('no-repeat'=>'No Repeat','repeat-x'=>'Repeat X','repeat-y'=>'Repeat Y','repeat'=>'Repeat All');
		 
		
		// Set true if the options page should appera under appearance not plugins
		protected $theme = false;
		
		protected $feed_url = 'http://memberbuddy.com/feed';
		
		protected $has_post_meta = false; // load the admin scripts on the post new pages?
		
		protected $debug_mode = false;
		
		
		// Force singelton - Technique by Aaron D. Campbell
		static $instance = false;
		public static function getInstance() {
			return (self::$instance ? self::$instance : self::$instance = new self);
		}
		
		function __construct() {
			
			require_once ABSPATH.'/wp-admin/includes/plugin.php';
			// load the settings into the class
			
			
			// Load the plugin specific data
			$this->plugin_construction();
			
			// Fix some defaults if i was too lazy to specify them
			if (empty($this->options_name)) {$this->options_name = $this->slug . "-options"; }
			if (empty($this->options_group)) {$this->options_group = $this->slug; }
		
			$this->plugin_load_settings();
		


		// The name of the file e.g. my-plugin/my-plugin.php cant use $this->filename = plugin_basename(__FILE__); as may not be in same directory
			$this->filename = $this->slug . "/" . $this->slug . ".php";
		
			// full file and path to the plugin file
			$this->plugin_file =  WP_PLUGIN_DIR .'/'.$this->filename ;

			// store the path to the plugin
			$this->plugin_path = WP_PLUGIN_DIR.'/'.str_replace(basename( $this->filename),"",plugin_basename($this->filename));
			
			// store the url to the plugin
			$this->plugin_url = plugin_dir_url( $this->plugin_file );
		
			// store the plugin data from the plugins file
			$this->plugin_info = get_plugin_data($this->plugin_file);
		
			$this->framework_file = __FILE__;
			$this->framework_path = str_replace(basename( $this->framework_file),"",$this->framework_file);
			$this->framework_url = str_replace(ABSPATH,trailingslashit(get_option( 'siteurl' )),$this->framework_path);
		
		
			// Add Actions
			add_filter( 'init', array( $this, 'plugin_init_locale' ) );
			
			add_action('admin_init', array(&$this,'plugin_register_options') );			  // Register the options array variable with the site
			add_action('admin_init', array(&$this,'plugin_options_meta_boxes') );		  // Call the overloaded function to register the plugin metaboxes
			add_action('admin_init', array(&$this,'plugin_register_default_meta_boxes') ); // Add the support and forum sidebar metaboxes to options page
			add_action('admin_init', array(&$this,'plugin_register_admin_scripts') );	  // Register the scripts and styles for the plugin options page
			add_action('admin_menu', array(&$this,'plugin_add_options_page') ); 			  // Add the plugins options page
			add_action('admin_menu', array(&$this, 'plugin_post_meta_boxes')); // Add meta boxes for the custom post types
			
			
			if ($this->debug_mode) {add_action('admin_notices', array(&$this,'plugin_debug_admin_notice'));}
			
			add_action ( 'in_plugin_update_message-'.$this->filename , array ( &$this , 'plugin_changelog' ), null, 2 ); // add the changelog details to the upgrade screen
			
			// Add filters
			add_filter('screen_layout_columns', array(&$this, 'plugin_layout_columns'), 10, 2);	// Set plugin page to two columns
			
			// Register the activation/deactivation hooks
			register_activation_hook($this->filename, array( &$this, 'plugin_activate' ) );
			register_deactivation_hook($this->filename, array( &$this, 'plugin_deactivate' ) );
			
			// Setup the ajax callback for autocomplete widget
			add_action('wp_ajax_suggest_action', array(&$this,'plugin_suggest_callback'));
			
			add_action('save_post', array(&$this,'save_post_meta')); // Save the custom post data
			
			$this->after_construction();
		} // function
			
		// overloaded by parent class	
		public function after_construction() {}
		
		// when the plugin is activated update the settings
		public function plugin_activate() {
			update_option( $this->options_name, $this->options);
		} // function
		
		// when the plugin is deactivated update the settings
		public function plugin_deactivate() {

		} // function
		
		// register the setting to contain the plugin options
		public function plugin_register_options() {
			register_setting( $this->options_group, $this->options_name , array(&$this, 'plugin_validate_options' ));
		}
		
		// Plugin Options Page Input Validation
		function plugin_validate_options($opts){ 
			$valid = array();
			// Fix for autcomplete entries
			foreach ($opts as $key => $value) {
				$valid[$key]= $this->suggest_to_id($value);
			}
			return $valid;
		} // function
		
		// Create the Options page for the plugin
		public function plugin_add_options_page() {
			// Change where the page is created if we are using a theme
			if ($this->theme == true) {
			
			} else {
			
			}
		
			$this->page = add_options_page(__($this->options_page_title), __($this->options_page_name), 'manage_options', $this->slug, array($this, 'plugin_create_options_page'));
			add_filter( 'plugin_action_links', array(&$this, 'plugin_add_settings_link'), 10, 2 );
			// Run stuff when the plugin options page loads
			add_action('load-'.$this->page,  array(&$this, 'plugin_loading_options_page'));
		} // function
		
		// Runs only on the plugin page load hook, enables the options screen boxes
		function plugin_loading_options_page() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');
		} // function
		  
		// Return the plugin settings or the defaults if not yet set - Technique by Aaron D. Campbell
		public function setting( $field, $default = false ) {
			if (empty($this->options)) {
				$this->plugin_load_settings();
			}
			if ( isset($this->options[$field]) ) {
				return $this->options[$field];
			} else {
				return $default;
			}
		}
		
		// Load up the saved options into the class settings variable - Technique by Aaron D. Campbell
		private function plugin_load_settings() {
			if (empty($this->options)) {
				$this->options = get_option( $this->options_name );
			}
			if ( !is_array( $this->options ) ) {
				$this->options = array();
			}
			$this->options = wp_parse_args($this->options, $this->defaults);
		} // function
		
		
		// Add a settings link to the plugin list page
		function plugin_add_settings_link($links, $file) {
			if ( $file ==  $this->filename  ){
				$settings_link = '<a href="options-general.php?page=' .$this->slug . '">' . __('Settings', $this->slug) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		} // function
			
		// on the plugin page make sure there are two columns
		function plugin_layout_columns($columns, $screen) {
			if ($screen == $this->page) {
				$columns[$this->page] = 2;
			}
			return $columns;
		} // function
			
			
		// Create the options page form
		public function plugin_create_options_page() {
			global $screen_layout_columns;
			$data = array();
			?>
			<div class="wrap">
				<?php screen_icon('options-general'); ?>
				<h2><?php print $this->options_page_title; ?></h2>
				<form id="settings" action="options.php" method="post" enctype="multipart/form-data">
			
					<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
					<?php settings_fields($this->options_group); ?>
					<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
						<div id="side-info-column" class="inner-sidebar">
							<?php do_meta_boxes($this->page, 'side', $data); ?>
						</div>
						<div id="post-body" class="has-sidebar">
							<div id="post-body-content" class="has-sidebar-content">
								<?php do_meta_boxes($this->page, 'normal', $data); ?>
								<br/>
								<p>
									<input type="submit" value="Save Changes" class="button-primary" name="Submit"/>	
								</p>
							</div>
						</div>
						<br class="clear"/>				
					</div>	
				</form>
			</div>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					<?php // $('.if-js-closed').removeClass('if-js-closed').addClass('closed'); ?>
					postboxes.add_postbox_toggles('<?php echo $this->page; ?>');
				});
				//]]>
			</script>
			<?php
		} // function
		
		
		
		function plugin_debug_admin_notice(){
			echo '<div class="updated">';
			echo "\n\n<pre>\n" . print_r($this,true) . "\n</pre>\n\n" . $plugin;
			echo '</div>';
		}

		
		
		/***********************************************
		*  Changelog Message During Update - from XavisysPlugin Framework
		*/
		public function plugin_changelog ($pluginData, $newPluginData) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

			$plugin = plugins_api( 'plugin_information', array( 'slug' => $newPluginData->slug ) );

			if ( !$plugin || is_wp_error( $plugin ) || empty( $plugin->sections['changelog'] ) ) {
				return;
			}

			$changes = $plugin->sections['changelog'];
			$pos = strpos( $changes, '<h4>' . preg_replace('/[^\d\.]/', '', $pluginData['Version'] ) );
			if ( $pos !== false ) {
				$changes = trim( substr( $changes, 0, $pos ) );
			}

			$replace = array(
				'<ul>'	=> '<ul style="list-style: disc inside; padding-left: 15px; font-weight: normal;">',
				'<h4>'	=> '<h4 style="margin-bottom:0;">',
			);
			echo str_replace( array_keys($replace), $replace, $changes );
		} // function
		
		
		
		
		/***********************************************
		*  Language Functions
		*/
		
		// Load the textdomain  for the plugin
		public function plugin_init_locale() {
			$lang_dir = basename(dirname(__FILE__)) . '/languages';
			load_plugin_textdomain( $this->slug, 'wp-content/plugins/' . $lang_dir, $lang_dir);
		}
		
		
		
		
		
		/***********************************************
		*  Options Page Scripts & Styles
		*/
		
		// Register the admin scripts
		function plugin_register_admin_scripts() {
			wp_enqueue_script( 'jquery' );
			// Add custom scripts and styles to this page only
			add_action('admin_print_scripts-' . $this->page, array(&$this, 'plugin_admin_scripts'));
			add_action('admin_print_styles-' . $this->page,array(&$this,  'plugin_admin_styles'));
			if ($this->has_post_meta){
				// Add custom scripts and styles to the post editor pages
				add_action('admin_print_scripts-post.php', array(&$this, 'plugin_post_new_scripts'));
				add_action('admin_print_scripts-post-new.php',array(&$this,  'plugin_post_new_scripts'));
				add_action('admin_print_styles-post.php', array(&$this, 'plugin_admin_styles'));
				add_action('admin_print_styles-post-new.php',array(&$this,  'plugin_admin_styles'));	
			}
		} // function
		
		// Add custom scripts to this plugins options page only
		function plugin_admin_scripts() {
		
			wp_register_script('tiptip',  $this->framework_url .'tooltip/jquery.tipTip.js', array('jquery','framework'));
			wp_enqueue_script('tiptip');
		
			//  Register & enqueue  our admin.js file along with its dependancies
			wp_register_script('framework',  $this->framework_url .'framework.js', array('jquery','media-upload','thickbox','editor'));
			wp_enqueue_script('framework');
			
			
			
			wp_enqueue_script('farbtastic');  
			wp_enqueue_script('suggest');  // Allow cool select boxes
			
			wp_register_script('chosen', $this->framework_url . 'chosen/chosen.jquery.min.js', array('jquery','framework'));
			wp_enqueue_script('chosen'); // Allow autocomplet
			
			wp_tiny_mce( false );	// setup the wysiwyg editor
		} // function

		// Add custom styles to this plugins options page only
		function plugin_admin_styles() {
			// used by media upload
			wp_enqueue_style('thickbox');
			// Register & enqueue our admin.css file 
			wp_register_style('framework', $this->framework_url .'framework.css');
			wp_enqueue_style('framework');
			
			wp_register_style('tiptip', $this->framework_url .'tooltip/tipTip.css');
			wp_enqueue_style('tiptip');
			
			wp_enqueue_style( 'farbtastic' );
			
			if (is_file($this->framework_path .'chosen/chosen.jquery.min.js')) {
				wp_register_style('chosen', $this->framework_url . 'chosen/chosen.css');
				wp_enqueue_style('chosen');
			}
		} // function
	
		
		// Add scripts globally to all post and post-new admin screens
		function plugin_post_new_scripts() {
		
			wp_register_script('tiptip',  $this->framework_url .'tooltip/jquery.tipTip.js', array('jquery','framework'));
			wp_enqueue_script('tiptip');
		
			wp_register_script('framework',  $this->framework_url .'framework.js', array('jquery','media-upload','thickbox','editor'));
			wp_enqueue_script('framework');
			
			
			if (is_file($this->framework_path .'chosen/chosen.jquery.min.js')) {
				wp_register_script('chosen',  $this->framework_url .'chosen/chosen.jquery.min.js', array('jquery'));
				wp_enqueue_script('chosen'); // Allow autocomplete
			}	
			wp_enqueue_script('farbtastic');  
			wp_enqueue_script('suggest');  // Allow Jquery Chosen
			
		}
		
		/***********************************************
		* Default Options Page Meta Boxes
		*/
		
		function plugin_register_default_meta_boxes() {
			// add some sections
			add_settings_section('admin-section-support', '', array(&$this, 'section_null'), $this->page );
			add_settings_section('admin-section-forum', '', array(&$this, 'section_null'), $this->page );
			//  Define the sidebar meta boxes
			add_meta_box('admin-section-support','Support', array(&$this, 'plugin_support_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-support'));
			add_meta_box('admin-section-forum','Forum Posts', array(&$this, 'plugin_forum_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-forum'));
		} // function
		
		// Meta box for the support info
		function plugin_support_metabox() {
			print "<ul id='admin-section-support-wrap'>";
			print "<li><a id='framework-support' href='http://www.memberbuddy.com/docs/" . $this->slug . "' target='_blank' style=''>Plugin Documentation</a></li>";
			print "<li><a id='framework-support' href='http://www.memberbuddy.com/forum/" . $this->slug . "' target='_blank' style=''>Support Forum</a></li>";
			print "<li><a id='framework-support' href='http://www.memberbuddy.com/forum/" . $this->slug . "' target='_blank' style=''>Report A Bug</a></li>";
			print "<li><a id='framework-support' href='http://www.memberbuddy.com/forum/" . $this->slug . "' target='_blank' style=''>Suggest A Feature</a></li>";
			print "</ul>"; 
		} // function
		
		
		// Output the recent forum posts in the sidebar
		function plugin_forum_metabox() {
			$args = array(
				'url'			=> $this->feed_url,
				'items'			=> '5',
			);
			echo '<div class="rss-widget">';
				wp_widget_rss_output( $args );
			echo "</div>";
		} // function
		
		
		/***********************************************
		*  Metabox Build Functions
		*/
		
		// build the meta box content using the section definition
		function admin_section_builder($data,$args) {echo '<div class="options-description" style="padding:10px; line-height: 1.6;">' . $args['args']['description']  . '</div><table class="form-table">'; do_settings_fields(  $this->page, $args['args']['section'] );  echo '</table>';}
		
		// Null callback used in section creation
		function section_null () {}
		
		// Overloaded function to register metaboxes
		function plugin_options_meta_boxes() {}
		
		
		

		/***********************************************
		*  Metabox Widget Helper Functions
		*/
		
		// If a height is specified return the inline style to set it
		function height($h) {
			return ((!empty($h)) ? ' height:'.$h. 'px;' : '');
		} // function
		
		// If a width is specified return the inline style to set it
		function width($w) {
			return  ((!empty($w)) ? ' width:'.$w. 'px;' : '');
		} // function
		 
		// If a description is given then return the html to display it
		function description($d) {
			return ( (!empty($d)) ? '<br />' . '<span class="description">'.$d. '</span>' : '');
		} // function
		
		// If any placeholder text is specified then add the html attribute to the element
		function placeholder($p) {
			return ( (!empty($p)) ? 'placeholder="' . $p . '"' : '');
		} // function
		
			// If any tooltip
		function tooltip($t) {
			return ( (!empty($t)) ? 'title="' . $t . '"' : '');
		} // function
		
		function multiselect($s) {
			return ( ($s === true) ? 'multiple="true"' : '');
		} // function
		
		
		
		
		// Apply the default args and flip the args around depnding in which context the function is called
		function apply_default_args($args) {
			$defaults = array(
				'width'=>'',
				'height'=>'',
				'placeholder'=>'',
				'description'=>''
			);
			$args = wp_parse_args( $args, $defaults );
			return $args;	
		} // function
		
		// Allow the field widgets to be used from another context
		function apply_name_fix($args) {
		
			if ($args['plain']) { 
				$args['formname']  = $args['id']; // plain format called from tiny MCE popups etc
			
			} else { 
				$args['formname'] = "$this->options_name[" . $args['id']. "]"; // format used on the plugin options page
				$args['value'] = $this->options[$args['id']]; // set the value to whatever is saved in the options table
			}
				return $args;
		} // function
		
		
		/***********************************************
		*  Metabox Widgets Definitions
		*/
			
		// Build a textarea on options page
		function textarea($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			echo "<textarea " .$this->tooltip($args['tooltip'])  . " name='" . $args['formname']  . "' style='" . $this->width($args['width']) . " " .  $this->height($args['height']) . "' rows='7' cols='50' type='textarea'>" . $args['value'] . "</textarea>";			
			$this->description($args['description']);
			} // function
		
		// Build a textarea on options page
		function wysiwyg($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			echo "<div class='postarea'>";
			the_editor($args['value']  , $args['formname']);
			echo "</div>";
			$this->description($args['description']);
		} // function
		
		// Build a text input on options page
		function text($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			echo "<input type='text' size='57' " .$this->tooltip($args['tooltip'])  . " style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['formname'] . "' value='" . $args['value']  . "'/>";					
			$this->description($args['description']);
		} // function
		
		function suggest($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			$args['tooltip'] = 'Start typing and then select the ' . ucwords($args['suggesturl']) . ' you want from the list below.';
			$args['placeholder'] = 'Search for a '  . ucwords($args['suggesturl']) ;
			echo "<input type='text'  class='suggest' data-suggest='" . $args['suggesturl']  . "'  size='57' " .$this->tooltip($args['tooltip'])  . " style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['formname'] . "' value='" . $this->get_suggest_title($args['value'])  . "'/>";					
			
			$this->description($args['description']);
		} // function
		
		
	
		// Build a text input on options page
		function color($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;

			echo "<div class='inline-rel'>";
			echo "<span style='background:" . (!empty($args['value']) ? $args['value'] : "url(" . $this->framework_url . "swatch.png" . ");")  ."' class='swatch'></span><input id='$id' class=\"picker\" type='text' " .$this->tooltip($args['tooltip'])  . " size='57'" . $this->placeholder('None') . "' name='" . $args['formname'] . "' value='" . $args['value']  . "'/>";				
			echo "<div  id='" . $id  . "_picker' class='picker' style=''></div>";
			$this->description($args['description']); // print a description if there is one
			echo "</div>";
		} // function
	
		
		function get_attachment_id ($image_src) {
			global $wpdb;
			$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
			$id = $wpdb->get_var($query);
			return $id;
		}
		
		// Build an attachment upload form  adda a try catch to anything that causes a wp-error
		function attachment($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			echo "<div><input class='attachment' id='" . $args['id'] . "' style='" .$this->width($args['width']) . "' " .$this->tooltip($args['tooltip'])  . " type='text' size='57' " . $this->placeholder($args['placeholder'] ) . " name='" . $args['formname'] . "' value='" . $args['value']. "' />";
			echo "<input class='attachment_upload button-secondary' id='$formname _button' type='button' value='Upload'/>";
			
			
			// show a preview
			$this->attachment_preview($args['value']);
			$this->description($args['description']);
			
		  } // function
		
		// Generate or display a thumbnail of the chosen file, needs a good cleanup
		function attachment_preview($original) {
			$file = str_replace(get_site_url().'/','' ,$original);
			$file = str_replace('//','/',ABSPATH . $file);
			// check if file exists
			if (file_exists($file) && ($file != ABSPATH)) {
				$thumb = wp_get_attachment_image( $this->get_attachment_id($original), array(80,80),1);
				
				$ext = pathinfo($original, PATHINFO_EXTENSION);
				// If the file hasnt been upload through wordpress
				if (($this->get_attachment_id($original) == '') && ( in_array($ext ,$this->image_extensions))) {
						
					$size = getimagesize($file);
				
					if (($size[0] < 80) && ( $size[1] < 80)) {
						$thumb = "<img src='" . $original . "' />";
					} else {
						$thumb =  "<img src='" . wp_create_thumbnail( $file, 40 ) . "' />";
					}
					//print var_export(wp_create_thumbnail( $file, 4 ),true);
				
				} 
				print "<div class='option_preview' ><a href='" . $original . "'>" . $this->filetourl($thumb) . "<br/>" .basename($original) . "</a></div>";
			}
		}
		
		// given a file return it as a url
		function filetourl($file) {
			return str_replace(ABSPATH , get_site_url().'/' ,$file);
		}
		
		// Render a selectbox or multi selectbox
		function select($args)  {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			if ($args['multiple']) {
				echo "<select class='optselect chzn-select' " .$this->tooltip($args['tooltip'])  . " multiple='true' style='" .$this->width($args['width'])  . "' name='" . $args['formname'] . "" . "[]'>";
				foreach ($args['select'] as $key => $value) {
					echo "<option " . (array_search($value , $args['value']) === false ? '' : 'selected' ). " value='" . $key . "'>" . $value . "</option>";	
				}	
				echo "</select>";
			} else {
				echo "<select  class='optselect chzn-select' " .$this->tooltip($args['tooltip'])  . " style='" .$this->width($args['width'])  . "' name='" . $args['formname'] . "'>";
				foreach ($args['select'] as $key => $value) {
					echo "<option " . ($args['value'] == $key ? 'selected' : '' ). " value='" . $key . "'>" . $value . "</option>";	
				}	
				echo "</select>";
			}
			$this->description($args['description']);
		} // function
		
		// Build A Checkbox On The Options Page
		function checkbox($args) {
			$args = $this->apply_name_fix($this->apply_default_args($args)) ;
			echo "<input name='" . $args['formname']  . "' type='checkbox' " .$this->tooltip($args['tooltip'])  . " value='1' ";
			checked('1', $args['value']); 
			echo " /> <span  class='description'>" . $args['description'] . "</span>" ;
			
		} // function
		function fontfamily($args) {
		$args['select'] = array('Tahoma'=>'Tahoma', 'Verdana'=>'Verdana', 'Arial Black'=>'Arial Black', 'Comic Sans MS'=>'Comic Sans MS', 'Lucida Console'=>'Lucida Console',
      'Palatino Linotype'=>'Palatino Linotype', 'MS Sans Serif'=>'MS Sans Serif', 'System'=>'System',  'Georgia'=>'Georgia',  'Impact'=>'Impact', 'Courier'=>'Courier', 'Symbol'=>'Symbol');
		$args['multiple'] = false;
		$args['width'] = '150';
		$args['tooltip'] = 'Select which font family to use';
		$this->select($args) ;
	} // function
	
	
	// Build a text input on options page
	function range($args) {
		$args = $this->apply_name_fix($this->apply_default_args($args)) ;
		echo "<div style='line-height:24px;'><input style=' float:left;" . $this->width($args['width'])  . "' type='range' " .$this->tooltip($args['tooltip'])  . " min='" . $args['min'] . "' max='" . $args['max'] . "' class='range' size='57' " . $this->placeholder($args['placeholder'] )  . " name='" . $args['formname']  . "' value='" . $args['value'] . "'/>";				
		
		echo "<span style=' float:left; margin-left:10px;' class='rangeval'>" .   $args['value']. "</span><span>" .  $args['unit']. "</span</div>";
	
		$this->description($args['description']);
		} // function
	
	
	// convenience method for font size widget
	function fontunit($args) {
		$args['select'] = array('px'=>'px','pt'=>'pt', 'em'=>'em');
		$args['multiple'] = false;
		$args['width'] = '60';
		$args['tooltip'] = 'Choose the units';
		$this->select($args);
	}
		// convenience method for font size widget
	function backgroundpositions($args) {
		$args['select'] = $this->background_positions;
		$args['multiple'] = false;
		$args['width'] = '125';
		$args['tooltip'] = 'Choose the background position';
		$this->select($args);
	}
	
	function backgroundrepeat($args) {
		$args['select'] = $this->background_repeat;
		$args['multiple'] = false;
		$args['width'] = '100';
		$args['tooltip'] = 'Background repeating directions';
		$this->select($args);
	}
		// convenience method for font size widget
	function htmltags($args) {
		$args['select'] = $this->html_tags;
		$args['multiple'] = false;
		$args['width'] = '65';
		$args['tooltip'] = 'Choose the HTML tag to use';
		$this->select($args);
	}
	
	
	// convenience method for font size widget
	function fontsize($args) {
		$args['select'] = array('6'=>'6','7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '11'=>'11','12'=>'12', '14'=>'14', '16'=>'16', '18'=>'18');
		$args['multiple'] = false;
		$args['width'] = '60';
		$args['tooltip'] = 'Choose the font size';
		$this->select($args);
	}
	
		// convenience method for font size widget
	function lineheight($args) {
		$args['select'] = array('6'=>'6px','7'=>'7px', '8'=>'8px', '9'=>'9px', '10'=>'10px', '11'=>'11px','12'=>'12px', '14'=>'14px', '16'=>'16px', '18'=>'18px');
		$args['multiple'] = false;
		$args['width'] = '70';
		$args['tooltip'] = 'Choose the line height';
		$this->select($args);
	}
	
	
	// convenience method for border style
	function bordertype($args) {
		$args['select'] = array('none'=>'None','hidden'=>'Hidden', 'dotted'=>'Dotted', 'dashed'=>'Dashed', 'solid'=>'Solid', 'groove'=>'Groove','ridge'=>'Ridge', 'inset'=>'Inset', 'outset'=>'Outset', 'double'=>'Double');
		$args['multiple'] = false;
		$args['width'] = '95';
			$args['tooltip'] = 'Choose the border type';
		$this->select($args) ; 
	}
	
	// convenience method for alignment
	function align($args) {
		$args['select'] = array('left'=>'Left','center'=>'Center','right'=>'Right');
		$args['multiple'] = false;
		$args['width'] = '85';
		$args['tooltip'] = 'Choose the alignment';
		$this->select($args) ; 
	}
	
	// when calling aggregated widget from post metaboc the form id prefix needs changing
	function metabox_id_fix($args){
		return ( $args['plain'] ? $args['id'] : "$this->options_name[" . $args['id'] . "]" );
	}
	 
	 function metabox_value_fix($args){
		return ( $args['plain'] ? $args['value']: $this->options[ $args['id']] );
	}
	 
	 
	 function padding($args) {
		
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
			
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode

		$args['id'] = 	$form_prefix . "[top]";
		$args['value'] = $form_value['top'];
		$args['tooltip'] = 'Top Padding Value';
		$args['width'] = '75';
		$this->select($args);
		
		// add font size
		$args['id'] = $form_prefix . "[right]";
		$args['value'] = $form_value['right'];
		$args['tooltip'] = 'Right Padding Value';
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = $form_prefix . "[bottom]";
		$args['value'] = $form_value['bottom'];
		$args['tooltip'] = 'Bottom Padding Value';
		$args['width'] = '75';
		$this->select($args);
		
		// Add the color widget
		$args['id'] = $form_prefix . "[left]";
		$args['value'] = $form_value['left'];
		$args['tooltip'] = 'Left Padding Value';
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = $form_prefix . "[unit]";
		$args['value'] = $form_value['unit'];
		$args['width'] = '65';
		$args['tooltip'] = 'Choose the units you wish to use';
		$this->fontunit($args);
		
		$this->description($description);
	
		//$args['id'] = $save_id . "-line";
		//$this->lineheight($args);
	}
	 
	 
	 function boxshadow($args) {
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
		
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		$args['id'] = $form_prefix . "[offset-x]";
		$args['value'] = $form_value['offset-x'];
		$args['width'] = '75';
		$args['tooltip'] = 'The X Direction Offset Value';
		$this->select($args);
		
		$args['id'] = $form_prefix . "[offset-y]";
		$args['value'] = $form_value['offset-y'];
		$args['width'] = '75';
		$args['tooltip'] = 'The Y Direction Offset Value';
		$this->select($args);
		
		$args['id'] = $form_prefix . "[blur]";
		$args['value'] = $form_value['blur'];
		$args['width'] = '75';
		$args['tooltip'] = 'Amount of blur';
		$this->select($args);
		
		$args['id'] = $form_prefix . "[spread]";
		$args['value'] = $form_value['spread'];
		$args['width'] = '75';
		$args['tooltip'] = 'Shadow Spread';
		$this->select($args);
		
		$args['id'] = $form_prefix . "[color]";
		$args['value'] = $form_value['color'];
		$args['width'] = '75';
		$args['tooltip'] = 'Shadow Color';
		$this->color($args);
		
		
		$this->description($description);
	}
	 
	 
	 function margin($args) {
		
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
		
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		$args['id'] = $form_prefix . "[top]";
		$args['value'] = $form_value['top'];
		$args['width'] = '75';
		$args['tooltip'] = 'Top Margin Value';
		$this->select($args);
		
		// add font size
		$args['id'] = $form_prefix . "[right]";
		$args['value'] = $form_value['right'];
		$args['width'] = '75';
		$args['tooltip'] = 'Right Margin Value';
		$this->select($args);
		
		// add font units
		$args['id'] = $form_prefix . "[bottom]";
		$args['value'] = $form_value['bottom'];
		$args['width'] = '75';
		$args['tooltip'] = 'Bottom Margin Value';
		$this->select($args);
		
		// Add the color widget
		$args['id'] = $form_prefix . "[left]";
		$args['value'] = $form_value['left'];
		$args['width'] = '75';
		$args['tooltip'] = 'Left Margin Value';
		$this->select($args);
		
		// add font units
		$args['id'] = $form_prefix . "[unit]";
		$args['value'] = $form_value['unit'];
		$args['width'] = '65';
		$args['tooltip'] = 'Choose the units';
		$this->fontunit($args);
		
		$this->description($description);
		
	}
	  
	 function background($args) {
	
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
		
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode

		// add font size
		$args['id'] = $form_prefix . "[background-color]";
		$args['value'] = $form_value['background-color'];
		$args['width'] = '75';
		$args['tooltip'] = 'Background Color';
		$this->color($args);
		
		if ($args['gradient']) {
			// add font units
			$args['id'] = $form_prefix . "[gradient]";
			$args['value'] = $form_value['gradient'];
			$args['width'] = '75';
			$args['tooltip'] = 'Gradient End Point Color';
			$this->color($args);
		}
		
		$args['id'] = $form_prefix . "[position]";
		$args['value'] = $form_value['position'];
		$args['width'] = '135';
		$this->backgroundpositions($args);
		
		$args['id'] = $form_prefix . "[repeat]";
		$args['value'] = $form_value['repeat'];
		$args['width'] = '135';
		$this->backgroundrepeat($args);
		
		print "<div style='height:3px;'></div>";
		// add font units
		$args['id'] = $form_prefix . "[image]";
		$args['value'] = $form_value['image'];
		$args['width'] = '400';
		$args['placeholder'] = 'Upload a background image';
		$args['tooltip'] = 'Upload an image or enter a URL to use as the backround. The image must be hosted on the same domain as this website.';
		$this->attachment($args);
		
		$this->description($description);
	}
	 
	 function border($args) {
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
		
		
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		$args['id'] = $form_prefix . "[size]";
		$args['value'] =$form_value[size];
		$args['width'] = '75';
		$args['tooltip'] = 'Choose the border size';
		$this->select($args);
		
		// add font units
		$args['id'] = $form_prefix . "[unit]";
		$args['value'] = $form_value['unit'];
		$args['width'] = '65';
			$args['tooltip'] = 'Choose the border size units';
		$this->fontunit($args);
		
		// add font size
		$args['id'] = $form_prefix . "[type]";
		$args['value'] = $form_value[type];
		$args['width'] = '165';
		$args['tooltip'] = 'Choose the border type';
		$this->bordertype($args);
		
		// add font units
		$args['id'] = $form_prefix . "[color]";
		$args['value'] = $form_value[color];
		$args['width'] = '75';
		$args['tooltip'] = 'Choose the border color';
		$this->color($args);

		$this->description($description);
	}
	
	function font($args) {
		// kill off descriptions till the end
		$description = $args['description'];
		unset($args['description']);
		
		// if plain is true at this point we must have come from a post meta box so do some switching of the args around
		$form_prefix = $this->metabox_id_fix($args);
		$form_value = $this->metabox_value_fix($args);
		
		$args['plain']=true; // switch to plain mode
		
		// Add the color widget
		$args['id'] =  $form_prefix . "[color]";
		$args['value'] = $form_value['color'];
		$args['tooltip'] = 'Font Color';
		$this->color($args);
		
		$args['id'] =  $form_prefix . "[family]";
		$args['value'] =  $form_value['family'];
		$args['width'] = '170';
		$this->fontfamily($args);
		
		// add font size
		$args['id'] =  $form_prefix . "[size]";
		$args['value'] = $form_value['size'];
		$args['width'] = '90';
		$this->fontsize($args);
		
		// add font units
		$args['id'] =  $form_prefix . "[unit]";
		$args['value'] = $form_value['unit'];
		$args['width'] = '65';
		$this->fontunit($args);
		
		
		$this->description($description);
		

	}
	 
		
		/*******************************************************
		* Autocomplete widget functions
		*/
		
		// Given an id show it along with the title in the autocmoplete textbox
		function get_suggest_title($id) {
			if (empty($id)) { return "";   }
			return get_the_title($id) . " [#". $id ."]";
		}
		
		// Ajax callback function to return list of post types.
		function plugin_suggest_callback() {
			global $wpdb, $post;
			
			$posttype =  $wpdb->escape($_GET['type']);
			$in =  $wpdb->escape($_GET['q']);

			$query = "SELECT ID from $wpdb->posts where post_type = '$posttype' AND post_title like '%$in%' ";
			$mypostids = $wpdb->get_col($query);

			foreach ($mypostids as $key => $value) {
				print get_the_title($value) . " [#" .  $value . "]" . "\n";
			}
			die(); // this is required to return a proper result
		} // function
		
		// return a list of posts in a post tpyes
		function get_by_type($type) {
			$output = array();
			$posts_array = get_posts( 'post_type=' . $type ); 
			foreach( $posts_array as $post ) {
				setup_postdata($post); 
				$output[$post->ID] = $post->post_title ;
			}
			return $output;
		}
		/***********************************************
		* Helper functions for quick adding things
		*/
		
		// easy custom post type label definitions
		function easy_labels($title, $single, $plural) {
				$labels = array(
					'name' => _x($title, 'post type general name'),
					'singular_name' => _x($single, 'post type singular name'),
					'add_new' => _x('Add New ', '2CO IPN'),
					'add_new_item' => __('Add New ' . $single),
					'edit_item' => __('Edit ' . $single),
					'new_item' => __('New ' . $single),
					'all_items' => __('All ' . $plural),
					'view_item' => __('View ' . $single),
					'search_items' => __('Search ' .  $plural),
					'not_found' =>  __('No ' . $plural . ' found'),
					'not_found_in_trash' => __('No ' . $plural . ' found in Trash'), 
					'parent_item_colon' => '',
					'menu_name' => $single
				);
			return $labels;
		}
	
		// quick add a metabox section and box
		function easy_options_metabox($id,$title) {
			add_settings_section($id, '', array(&$this, 'section_null'), $this->page );
			add_meta_box($id,$title, array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => $id));
			
		}
		
		// quick add a field
		function easy_options_field($args) {
			add_settings_field($args['id'], $args['title'], array(&$this, $args['type']), $this->page , $args['section'],$args	);
		}
		
		function easy_post_metabox( $id,$title,$post_type) {
			$this->has_post_meta = true;
			add_meta_box( $id, $title,  array(&$this,'post_meta_builder'), $post_type, 'normal', 'high', array('fields' => $this->postmeta[$post_type][$id]) );
		}
		
		function easy_post_field($args) {
			$this->postmeta[$args['post_type']][$args['section']][$args['id']] = $args;
		}
		
		/***********************************************
		* General Miscellaneous helper functions 
		*/
		
		// given a file return its local directory path
		function file_to_dir($file) {
			return str_replace(basename($file),'',$file);
		}
		
		// given a file return it as a url
		function file_to_url($file) {
			return str_replace(ABSPATH , get_site_url().'/' ,$file);
		}
		
		/***********************************************
		* Post Meta Box Generator
		*/
		
		function do_field_widget($name,$value) {
		
		
		}
		
		function plugin_post_meta_boxes() {
		 
		}
		
		// post meta box builder
		function post_meta_builder($data,$args) {
			global $post;
			
			// print var_export($args,true);
			$fields=$args['args']['fields'] ;
			foreach( $fields as $meta_box) {
				$meta_box_value = get_post_meta($post->ID, $meta_box['id'].'_value', true);
			
				if($meta_box_value == "") {	$meta_box_value = $meta_box['std']; };
	 
				echo'<input type="hidden" name="'.$meta_box['id'].'_noncename" id="'.$meta_box['id'].'_noncename" value="'.wp_create_nonce( plugin_basename($this->filename) ).'" />';
		
				echo "<table class='form-table'><tr><th scope='row'><strong>" . $meta_box['title'] . "</strong></th><td>";		
			
				$args=$meta_box;
				
				$args['plain'] = true ;
				$args['id'] = $meta_box['id'] . '_value';
				$args['value']=$meta_box_value;
					
				$this->{$meta_box['type']}($args);
				
				
				echo "</td></tr></table>";
			} // end for
		}
		
		// check that the autocomplete id given is valid
		function suggest_to_id($data) {
			global $wpdb;
			// Convert autosuggest value to a post id
				if (strlen(strstr($data,'[#'))>0) {
					preg_match('/.*\[#(.*)\]/', $data, $matches);
					$data =  $matches[1];
					$result = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts AS wposts  WHERE wposts.ID = '" . $data . "'");
					if ($result == 0) {
						$data='';
					}
				}
				return $data;
		} // function

	// Save post meta data
	function save_post_meta( $post_id ) {
		global $post, $new_meta_boxes;
		// print var_export($_POST,true);
		// only save if we have something to save
		if (isset($_POST['post_type'])  && $_POST['post_type'] && $this->postmeta[$_POST['post_type']]  ) {
			// print var_export($_POST,true);
			// go through each of the registered post metaboxes
			foreach ($this->postmeta[$_POST['post_type']] as $cur) {
			
				// save fields only for the current custom post type.	
				foreach($cur as $meta_box) {
				// Verify
				if ( !wp_verify_nonce( $_POST[$meta_box['id'].'_noncename'], plugin_basename($this->filename) )) {
					return $post_id;
				}
	
				// Check some permissions
				if ( 'page' == $_POST['post_type'] ) {
					if ( !current_user_can( 'edit_page', $post_id ))
					return $post_id;
				} else {
					if ( !current_user_can( 'edit_post', $post_id ))
					return $post_id;
				}
		 
				$data = $_POST[$meta_box['id'].'_value'];
			
			
				// Convert autosuggest value to a post id
				$data= $this->suggest_to_id($data);
				
				// if no post id is found then kill the autocomplete thinger
		 
				if(get_post_meta($post_id, $meta_box['id'].'_value') == "")
					add_post_meta($post_id, $meta_box['id'].'_value', $data, true);
				elseif($data != get_post_meta($post_id, $meta_box['id'].'_value', true))
					update_post_meta($post_id, $meta_box['id'].'_value', $data);
				elseif($data == "")
					delete_post_meta($post_id, $meta_box['id'].'_value', get_post_meta($post_id, $meta_box['id'].'_value', true));
				}
			}
		} //end if isset
	} // function
		

	} // absctract class
} // endif class exists
?>