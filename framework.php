<?php
/*  Copyright 2011  Rob Holmes  (email : rob@onemanonelaptop.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* To do

	update the meta post attachment widgets
	add optional word count to textboxes / text inputs
	add visibility widget
	get_files_in_folder check path framework url
	
*/

// Define the king of all classes
if (!class_exists('Framework')) {
abstract class Framework {

	// define some internal variables
	
	public $name = '';						// Store the plugin slug e.g. my-plugin
	public $options = '';					// Store the name of the plugins options array
	public $filename = '';					// Store the filename of the plugin e.g. my-plugin/my-plugin.php
	public $url = '';						// Store the full path to the plugins root folder 
	public $secure = false;			
	public $theme = false;					// Is this being used for a theme? 
	public $framework_url	= '';			// the url to the directory containing framework.php
	public $post_types = array();			// Store a list of available blog post types

	// constructor
	function Framework() {
		$this->__construct();	
	} // function

	// construct
	function __construct() {
		// Plugin Specific construction todo: check if function exists first
		$this->plugin_construct();	

		// Fill in the blanks automagically 
		if ($this->name == '') {$this->name = str_replace(' ','-',strtolower($this->title));}
		if ($this->options == '') {$this->options = str_replace('-','_',$this->name) . '_options';}
		
		if ($this->filename == '') {$this->filename = $this->name . "/" . $this->name . ".php";}
		if ($this->url == '') {$this->url = WP_PLUGIN_URL.'/'.str_replace(basename( $this->filename),"",plugin_basename($this->filename));}
		
		if ($this->optionstitle == '') {$this->optionstitle = $this->title  . ' Settings'; }
		$this->optionsarray = get_option($this->options);
		// Store the path to the location of the framework.php file use to load javasctip and css
		$this->framework_uri = $this->filetodir(__FILE__);
		$this->framework_url = $this->filetourl($this->filetodir(__FILE__));
		
		// Default widgets used by settings fields mod
		$this->yesno = array ('1'=>'Yes','0'=>'No');
		$this->image_extensions = array('png','gif','jpg','ico');
		$this->html_tags = array('h1','h2','h3','h4','h5','h6','div','span','p');
		$this->background_positions = array('center center'=>'Center Center','center top'=>'Center Top','center bottom'=>'Center Bottom','left center'=>'Left Center','left top'=>'Left Top',);
		$this->background_repeat = array('no-repeat'=>'No Repeat','repeat-x'=>'Repeat X','repeat-y'=>'Repeat Y','repeat'=>'Repeat All');
		 
		
		
		// Add the options page
		add_action('admin_menu',  array(&$this, 'plugin_add_opt_page'));

		// Admin inititation page hooks
		add_action('admin_init', array(&$this,  'plugin_admin_init') );
	
		// Set plugin page to two columns
		add_filter('screen_layout_columns', array(&$this, 'layout_columns'), 10, 2);
		
		// Activation and deactivation hooks
		register_activation_hook($this->filename, array(&$this,'plugin_activate'));
		register_deactivation_hook($this->filename,  array(&$this,'plugin_deactivate'));
		
		
		// Setup the ajax callback for autocomplete widget
		add_action('wp_ajax_suggest_action', array(&$this,'plugin_suggest_callback'));

		add_action('init', array(&$this, 'find_post_types'));
		
		// runs stuff at the end of the construct method (to be overloaded)
		$this->after_plugin_construct();
	} // function
	

	
	
	// Called by the plugins generic 'admin_init' hook defined but usually overloaded
	function plugin_initiate() {
		
	}
	
	// runs at the end of the construct method (to be overloaded)
	function after_plugin_construct() {
		
	}
	
	// Add scripts globally to all post and post-new admin screens
	function plugin_post_new_scripts() {
		wp_register_script('quantum',  $this->framework_url .'js/admin.js', array('jquery','media-upload','thickbox','editor'));
		wp_enqueue_script('quantum');
		
		wp_register_script('chosen',  $this->framework_url .'js/chosen/chosen.jquery.min.js');
		wp_enqueue_script('chosen'); // Allow autocomplete
		wp_enqueue_script('suggest');  // Allow Jquery Chosen
		
	}
	
	
	// Add custom scripts to this plugins options page only
	function plugin_admin_scripts() {
		//  Register & enqueue  our admin.js file along with its dependancies
		wp_register_script('quantum',  $this->framework_url .'js/admin.js', array('jquery','media-upload','thickbox','editor'));
		wp_enqueue_script('quantum');
		wp_register_script('chosen', $this->framework_url . 'js/chosen/chosen.jquery.min.js');
		wp_enqueue_script('chosen'); // Allow autocomplet
		
		wp_enqueue_script('farbtastic');  // Allow Jquery Chosen
		wp_enqueue_script('suggest');  // Allow cool select boxes
		wp_tiny_mce( false );	// setup the wysiwyg editor
	} // function

	// Add custom styles to this plugins options page only
	function plugin_admin_styles() {
		// used by media upload
		wp_enqueue_style('thickbox');
		// Register & enqueue our admin.css file 
		wp_register_style('quantum', $this->framework_url .'css/admin.css');
		wp_enqueue_style('quantum');
			 wp_enqueue_style( 'farbtastic' );
		wp_register_style('chosen', $this->framework_url . 'js/chosen/chosen.css');
		wp_enqueue_style('chosen');
	} // function
	
	
	
	// Run on de-activation
	function plugin_deactivate() {
     
	} // function

	// Load the defaults when the plugin activates, maybe should be on the install hook?
	function plugin_activate() {
		// Define default option settings
		$options = get_option($this->options);
		$defaults = $this->plugin_defaults();
		update_option($this->options, $defaults);	
	} // function

	//
	function plugin_admin_init() {
	
		// Add custom scripts and styles to this page only
		add_action('admin_print_scripts-' . $this->page, array(&$this, 'plugin_admin_scripts'));
		add_action('admin_print_styles-' . $this->page,array(&$this,  'plugin_admin_styles'));
	
	
		// Add custom scripts and styles to the post editor pages
		add_action('admin_print_scripts-post.php', array(&$this, 'plugin_post_new_scripts'));
		add_action('admin_print_scripts-post-new.php',array(&$this,  'plugin_post_new_scripts'));
		add_action('admin_print_styles-post.php', array(&$this, 'plugin_admin_styles'));
		add_action('admin_print_styles-post-new.php',array(&$this,  'plugin_admin_styles'));
	
	
		// Register the options array and define the validation callback
		register_setting( $this->options, $this->options,array(&$this, 'plugin_validate_options' ));
		
		//  Define the sidebar meta boxes
		add_meta_box('admin-section-support','Support', array(&$this, 'framework_support'), $this->page, 'side', 'core',array('section' => 'admin_section_support'));
		add_meta_box('admin-section-forum','Forum Posts', array(&$this, 'framework_forum'), $this->page, 'side', 'core',array('section' => 'admin_section_forum'));
	
		// Do some plugin specific stuff
		$this->plugin_initiate();
		
	} // function

	// Add menu page
	function plugin_add_opt_page() {
		
		// build the opt page function name in parts becuase it breaks the theme checker although never used if theme is set to true
		$func = "add" . "_options_" . "page";
		
		// create the options page in location depending on plugin or theme
		if ($this->theme == false) {
			$this->page = $func($this->title , $this->title , 'manage_options',  $this->filename ,  array(&$this, 'plugin_settings_form'));
			// add a link on the plugins list for the options page
			add_filter( 'plugin_action_links', array(&$this, 'plugin_add_settings_link'), 10, 2 );
		} else {
			$this->page = add_theme_page($this->title , $this->title , 'edit_themes', basename($this->filename), array(&$this, 'plugin_settings_form')); 
		}
		 
		// run stuff on the plugins page
		add_action('load-'.$this->page,  array(&$this, 'plugin_load_page'));
	

	} // function
	
	// Add a settings link to the plugin list page
	function plugin_add_settings_link($links, $file) {
	
		if ( $file ==  $this->filename  ){
			$settings_link = '<a href="options-general.php?page=' .$this->filename . '">' . __('Settings', 'framework') . '</a>';
			array_unshift( $links, $settings_link );
			$settings_link = '<a href="http://www.memberbuddy.com/forum/' . $this->name . '">' . __('Support', 'framework') . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	} // function
	
	// Plugin Options Page Input Validation
	function plugin_validate_options($opts){ 
		// check api key for validity if external
	
		return $opts;
	} // function

	function layout_columns($columns, $screen) {
		if ($screen == $this->page) {
			$columns[$this->page] = 2;
		}
		return $columns;
	} // function
	
	// Null Callback for the section creation
	function section_cb () {} // Deprecated
	function section_null () {}
	
	// post meta box builder
	function post_meta_builder($data,$args) {
		global $post;
		$fields=$args['args']['fields'] ;
		foreach( $fields as $meta_box) {
			$meta_box_value = get_post_meta($post->ID, $meta_box['name'].'_value', true);
 		
			if($meta_box_value == "") {	$meta_box_value = $meta_box['std']; };
 
			echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
	
			echo "<table class='form-table'><tr><th scope='row'><strong>" . $meta_box['title'] . "</strong></th><td>";		
			if ($meta_box['type'] == 'textarea') {
				// Textarea widget
 				echo'<textarea  name="'.$meta_box['name'].'_value" >' . $meta_box_value . '</textarea>';
				
			} else if ($meta_box['type'] == 'checkbox') {
				// Checkbox widget
 				echo "<input name='".$meta_box['name']."_value' type='checkbox' value='1' ";
				checked('1', $meta_box_value); 
				echo " />" ;
				
 			} else if ($meta_box['type'] == 'text') {
				// Text widget
 				echo'<input type="text" name="'.$meta_box['name'].'_value" value="'.$meta_box_value.'" size="55" /><br />';
				
 			} else if ($meta_box['type'] == 'select') {
				// selectbox widget
				echo '<select name="'.$meta_box['name'].'_value" >';
				foreach ($meta_box['options'] as $key =>  $value) {
					echo "<option " . ( $meta_box_value == $key ? 'selected' : '' ). " value='" . $key . "'>" . $value . "</option>";	
				}
				echo "</select>";
				
			}  else if ($meta_box['type'] == 'suggest') {
				// autocomplete widget
				echo'<input type="text" name="'.$meta_box['name'].'_value" value="'.$this->get_suggest_title($meta_box_value).'" size="55" class="suggest" data-suggest="' . $meta_box['suggesturl']. '" /><br />';
			
			} else if ($meta_box['type'] == 'attachment'){
				// attachement widget
				echo "<div class='post-attachment'><input class='attachment' id='" . $args['id'] . "' type='text' size='57' name='".$meta_box['name']."_value' value='" . $meta_box_value . "' />";
				echo "<input class='attachment_upload button-secondary' id='" . $meta_box['name'] . "_button' type='button' value='Upload'/>";
				// show a preview
				$this->attachment_preview($meta_box_value);
			}
			echo'<p><label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></p>';
			echo "</td></tr></table>";
		} // end for
	}

	
	// Save post meta data
	function save_post_meta( $post_id ) {
		global $post, $new_meta_boxes;
	// print var_export($_POST,true);
		// only save if we have something to save
		if (isset($_POST['post_type'])  && $_POST['post_type'] && $this->postmeta[$_POST['post_type']]  ) {
			
			// go through each of the registered post metaboxes
			foreach ($this->postmeta[$_POST['post_type']] as $cur) {
			
				// save fields only for the current custom post type.	
				foreach($cur as $meta_box) {
				// Verify
				if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
					return $post_id;
				}
				
				// check autosave
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
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
		 
				$data = $_POST[$meta_box['name'].'_value'];
			
			
				// Convert autosuggest value to a post id
				if (strlen(strstr($data,'[#'))>0) {
					preg_match('/.*\[#(.*)\]/', $data, $matches);
					$data =  $matches[1];
				}
				
		 
				if(get_post_meta($post_id, $meta_box['name'].'_value') == "")
					add_post_meta($post_id, $meta_box['name'].'_value', $data, true);
				elseif($data != get_post_meta($post_id, $meta_box['name'].'_value', true))
					update_post_meta($post_id, $meta_box['name'].'_value', $data);
				elseif($data == "")
					delete_post_meta($post_id, $meta_box['name'].'_value', get_post_meta($post_id, $meta_box['name'].'_value', true));
				}
			}
		} //end if isset
	} // function
	
	
	
	// build the meta box content using the section definition
	function admin_section_builder($data,$args) {echo '<div class="options-description" style="padding:10px;">' . $args['args']['description']  . '</div><table class="form-table">'; do_settings_fields(  $this->page, $args['args']['section'] );  echo '</table>';}
	
	
	// Runs only on the plugin page load hook, enables the options screen boxes
	function plugin_load_page() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
	} // function
	
	
	// Main options form
	function plugin_settings_form() {
		global $screen_layout_columns;
		$data = array();
		?>
		<div class="wrap">
			<?php screen_icon('options-general'); ?>
			<h2><?php print $this->optionstitle; ?></h2>
			<form id="settings" action="options.php" method="post" enctype="multipart/form-data">
		
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				<input type="hidden" name="action" value="save_howto_metaboxes_general" />
				<?php settings_fields($this->options); ?>
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
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			
				postboxes.add_postbox_toggles('<?php echo $this->page; ?>');
			});
			//]]>
		</script>
		<?php
	} // function

	// given a file return its local directory path
	function filetodir($file) {
		return  str_replace(basename($file),'',$file);
	}
	
	// given a file return it as a url
	function filetourl($file) {
		return str_replace(ABSPATH , get_site_url().'/' ,$file);
	}
	
	// Meta box for the support info
	function framework_support() {
		print "<ul id='admin-section-support-wrap'>";
		print "<li><a id='framework-support' href='http://www.memberbuddy.com/' target='_blank' style=''>Plugin Documentation</a></li>";
		print "<li><a id='framework-support' href='http://www.memberbuddy.com/' target='_blank' style=''>Support Forum</a></li>";
		print "<li><a id='framework-support' href='http://www.memberbuddy.com/' target='_blank' style=''>About The Author</a></li>";
		print "<li><a id='framework-support' href='http://www.memberbuddy.com/' target='_blank' style=''>Report A Bug</a></li>";
		print "<li><a id='framework-support' href='http://www.memberbuddy.com/' target='_blank' style=''>Suggest A Feature</a></li>";
		print "</ul>"; 
	} // function
	
	
	function framework_forum() {
		include_once(ABSPATH . WPINC . '/feed.php');

		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed('http://memberbuddy.com/feed');
		if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
			// Figure out how many total items there are, but limit it to 5. 
			$maxitems = $rss->get_item_quantity(5); 

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items(0, $maxitems); 
		endif;
		?>

		<ul>
			<?php if ($maxitems == 0) echo '<li>No items.</li>';
			else
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) : ?>
			<li>
				<a href='<?php echo esc_url( $item->get_permalink() ); ?>'
				title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
				<?php echo esc_html( $item->get_title() ); ?></a>
			</li>
			<?php endforeach; ?>
		</ul> <?
	} // function
	
	// Build a textarea on options page
	function textarea($args) {
		$args = $this->shiftargs($args);
		echo "<textarea name='" . $args['formname']  . "' style='" . $this->width($args['width']) . " " .  $this->height($args['height']) . "' rows='7' cols='50' type='textarea'>" . $args['value'] . "</textarea>";			
		$this->description($args['description']);
		} // function
	
	// Build a textarea on options page
	function wysiwyg($args) {
		$args = $this->shiftargs($args);
		echo "<div class='postarea'>";
		the_editor($args['value']  , $args['formname']);
		echo "</div>";
		$this->description($args['description']);
	} // function
	
	// Build a text input on options page
	function text($args) {
		$args = $this->shiftargs($args);
		echo "<input type='text' size='57' style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['formname'] . "' value='" . $args['value']  . "'/>";					
		$this->description($args['description']);
	} // function
	
	// Build a text input on options page
	function color($args) {
		$args = $this->shiftargs($args);
		echo "<div class='inline-rel'>";
		echo "<span style='background:" . $args['value']  .";' class='swatch'></span><input id='$id' class=\"picker\" type='text' size='57'" . $this->placeholder('None') . "' name='" . $args['formname'] . "' value='" . $args['value']  . "'/>";				
		echo "<div  id='" . $id  . "_picker' class='picker' style=''></div>";
		$this->description($args['description']); // print a description if there is one
		echo "</div>";
	} // function
	
	
	// Apply the default args and flip the args around depnding in which context the function is called
	function shiftargs($args) {
		$defaults = array(
			'width'=>'',
			'height'=>'',
			'placeholder'=>'',
			'description'=>'',
			'multiple'=>false,
			'select'=>'',
			'plain'=>false
		);
		
		$args =  array_merge($defaults, $args);
		
		$options = get_option($this->options);
		if ($args['plain']) { 
			$args['formname']  = $args['id']; // plain format called from tiny MCE popups etc
		} else { 
			$args['formname'] = "$this->options[" . $args['id']. "]"; // format used on the plugin options page
			$args['value'] = $options[$args['id']]; // set the value to whatever is saved in the options table
		}
		
		return $args;	
	} // function
	 
	function fullfont($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		// Add the color widget
		$args['id'] = "$this->options[" . $saved_id . "][color]";
		$args['value'] = $options[$saved_id]['color'];
		$this->color($args);
		
		$args['id'] = "$this->options[" . $saved_id . "][family]";
		$args['value'] = $options[$saved_id]['family'];
		$args['width'] = '170';
		$this->fontfamily($args);
		
		// add font size
		$args['id'] = "$this->options[" . $saved_id . "][size]";
		$args['value'] = $options[$saved_id]['size'];
		$args['width'] = '90';
		$this->fontsize($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][unit]";
		$args['value'] = $options[$saved_id]['unit'];
		$args['width'] = '65';
		$this->fontunit($args);
		
		
		$this->description($saved_desc);
		

	}
	 
	 
	 function padding($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		$args['id'] = "$this->options[" . $saved_id . "][top]";
		$args['value'] = $options[$saved_id]['top'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font size
		$args['id'] = "$this->options[" . $saved_id . "][right]";
		$args['value'] = $options[$saved_id]['right'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][bottom]";
		$args['value'] = $options[$saved_id]['bottom'];
		$args['width'] = '75';
		$this->select($args);
		
		// Add the color widget
		$args['id'] = "$this->options[" . $saved_id . "][left]";
		$args['value'] = $options[$saved_id]['left'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][unit]";
		$args['value'] = $options[$saved_id]['unit'];
		$args['width'] = '65';
		$this->fontunit($args);
		
		$this->description($saved_desc. " (Top, Right, Bottom, Left, Units)");
	
		//$args['id'] = $save_id . "-line";
		//$this->lineheight($args);
	}
	 
	 
	 function boxshadow($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		
	
		
		$args['id'] = "$this->options[" . $saved_id . "][offset-x]";
		$args['value'] = $options[$saved_id]['offset-x'];
		$args['width'] = '75';
		$this->select($args);
		
		
		$args['id'] = "$this->options[" . $saved_id . "][offset-y]";
		$args['value'] = $options[$saved_id]['offset-y'];
		$args['width'] = '75';
		$this->select($args);
		
		$args['id'] = "$this->options[" . $saved_id . "][blur]";
		$args['value'] = $options[$saved_id]['blur'];
		$args['width'] = '75';
		$this->select($args);
		
		$args['id'] = "$this->options[" . $saved_id . "][spread]";
		$args['value'] = $options[$saved_id]['spread'];
		$args['width'] = '75';
		$this->select($args);
		
		$args['id'] = "$this->options[" . $saved_id . "][color]";
		$args['value'] = $options[$saved_id]['color'];
		$args['width'] = '75';
		$this->color($args);
		
		
		print "<br /><span class='description'>" . $saved_desc . " (Top, Right, Bottom, Left, Units)</span>";
	}
	 
	 
	 function margin($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		$args['id'] = "$this->options[" . $saved_id . "][top]";
		$args['value'] = $options[$saved_id]['top'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font size
		$args['id'] = "$this->options[" . $saved_id . "][right]";
		$args['value'] = $options[$saved_id]['right'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][bottom]";
		$args['value'] = $options[$saved_id]['bottom'];
		$args['width'] = '75';
		$this->select($args);
		
		// Add the color widget
		$args['id'] = "$this->options[" . $saved_id . "][left]";
		$args['value'] = $options[$saved_id]['left'];
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][unit]";
		$args['value'] = $options[$saved_id]['unit'];
		$args['width'] = '65';
		$this->fontunit($args);
		
			$this->description($saved_desc. " ");
		//$args['id'] = $save_id . "-line";
		//$this->lineheight($args);
	}
	  
	 function background($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
	
		
		// add font size
		$args['id'] = "$this->options[" . $saved_id . "][background-color]";
		$args['value'] = $options[$saved_id]['background-color'];
		$args['width'] = '75';
		$this->color($args);
		
		if ($args['gradient']) {
			// add font units
			$args['id'] = "$this->options[" . $saved_id . "][gradient]";
			$args['value'] = $options[$saved_id]['gradient'];
			$args['width'] = '75';
			$this->color($args);
		}
		
		
			
		
		$args['id'] = "$this->options[" . $saved_id . "][position]";
		$args['value'] = $options[$saved_id]['position'];
		$args['width'] = '135';
		$this->backgroundpositions($args);
		
		$args['id'] = "$this->options[" . $saved_id . "][repeat]";
		$args['value'] = $options[$saved_id]['repeat'];
		$args['width'] = '135';
		$this->backgroundrepeat($args);
		
		$this->description($saved_desc. " ");
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][image]";
		$args['value'] = $options[$saved_id]['image'];
		$args['width'] = '65';
		$this->attachment($args);
		
			
		
		//$args['id'] = $save_id . "-line";
		//$this->lineheight($args);
	}
	 
	 function border($args) {
		$options = get_option($this->options);
		$saved_id = $args['id']; // save the ID as we are going to change it
		$saved_desc = $args['description'];
		$args['select'] = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17');
		$args['plain']=true; // switch to plain mode
		
		unset($args['description']); // kill off descriptions till the end
		$args['id'] = "$this->options[" . $saved_id . "][size]";
		$args['value'] = $options[$saved_id][radius];
		$args['width'] = '75';
		$this->select($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][unit]";
		$args['value'] = $options[$saved_id]['unit'];
		$args['width'] = '65';
		$this->fontunit($args);
		
		// add font size
		$args['id'] = "$this->options[" . $saved_id . "][type]";
		$args['value'] = $options[$saved_id][type];
		$args['width'] = '165';
		$this->bordertype($args);
		
		// add font units
		$args['id'] = "$this->options[" . $saved_id . "][color]";
		$args['value'] = $options[$saved_id][color];
		$args['width'] = '75';
		$this->color($args);
		
		
		
	
		
		$this->description($saved_desc. " ");
		//$args['id'] = $save_id . "-line";
		//$this->lineheight($args);
	}
	 
	 
	// If a height is specified return the inline style to set it
	function height($h) {
		return ( (isset($h) && ($h != '')) ? ' height:'.$h. 'px;' : '');
	
	} // function
	
	// If a width is specified return the inline style to set it
	function width($w) {
		return ( (isset($w) && ($w != '')) ? ' width:'.$w. 'px;' : '');
	
	} // function
	 
	// If a description is given then return the html to display it
	function description($d) {
		return ( ($d != '') ? '<br />' . '<span class="description">'.$d. '</span>' : '');
	
	} // function
	
	// If any placeholder text is specified then add the html attribute to the element
	function placeholder($p) {
		return ( ($p != '') ? 'placeholder="' . $p . '"' : '');
	} // function
	 
	function fontfamily($args) {
		$args['select'] = array('Tahoma'=>'Tahoma', 'Verdana'=>'Verdana', 'Arial Black'=>'Arial Black', 'Comic Sans MS'=>'Comic Sans MS', 'Lucida Console'=>'Lucida Console',
      'Palatino Linotype'=>'Palatino Linotype', 'MS Sans Serif'=>'MS Sans Serif', 'System'=>'System',  'Georgia'=>'Georgia',  'Impact'=>'Impact', 'Courier'=>'Courier', 'Symbol'=>'Symbol');
		$args['multiple'] = false;
		$this->select($args) ;
	} // function
	
	// Build a text input on options page
	function range($args) {
		$args = $this->shiftargs($args);
		echo "<div style='line-height:24px;'><input style=' float:left;" . $this->width($args['width'])  . "' type='range' min='" . $args['min'] . "' max='" . $args['max'] . "' class='range' size='57' " . $this->placeholder($args['placeholder'] )  . " name='" . $args['formname']  . "' value='" . $args['value'] . "'/>";				
		
		echo "<span style=' float:left; margin-left:10px;' class='rangeval'>" .   $args['value']. "</span><span>" .  $args['unit']. "</span</div>";
	
		$this->description($args['description']);
		} // function
	
	
	// convenience method for font size widget
	function fontunit($args) {
		$args['select'] = array('px'=>'px','pt'=>'pt', 'em'=>'em');
		$args['multiple'] = false;
		$this->select($args);
	}
		// convenience method for font size widget
	function backgroundpositions($args) {
		$args['select'] = $this->background_positions;
		$args['multiple'] = false;
		$this->select($args);
	}
	
	function backgroundrepeat($args) {
		$args['select'] = $this->background_repeat;
		$args['multiple'] = false;
		$this->select($args);
	}
		// convenience method for font size widget
	function htmltags($args) {
		$args['select'] = $this->html_tags;
		$args['multiple'] = false;
		$this->select($args);
	}
	
	
	// convenience method for font size widget
	function fontsize($args) {
		$args['select'] = array('6'=>'6','7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '11'=>'11','12'=>'12', '14'=>'14', '16'=>'16', '18'=>'18');
		$args['multiple'] = false;
		$this->select($args);
	}
	
		// convenience method for font size widget
	function lineheight($args) {
		$args['select'] = array('6'=>'6px','7'=>'7px', '8'=>'8px', '9'=>'9px', '10'=>'10px', '11'=>'11px','12'=>'12px', '14'=>'14px', '16'=>'16px', '18'=>'18px');
		$args['multiple'] = false;
		$this->select($args);
	}
	
	
	// convenience method for border style
	function bordertype($args) {
		$args['select'] = array('none'=>'none','hidden'=>'hidden', 'dotted'=>'dotted', 'dashed'=>'dashed', 'solid'=>'solid', 'groove'=>'groove','ridge'=>'ridge', 'inset'=>'inset', 'outset'=>'outset', 'double'=>'double');
		$args['multiple'] = false;
		$this->select($args) ; 
	}
	
	// convenience method for alignment
	function align($args) {
		$args['select'] = array('left'=>'left','center'=>'center','right'=>'right');
		$args['multiple'] = false;
		$this->select($args) ; 
	}
	
	// Build A Checkbox On The Options Page
	function checkbox($args) {
		$args = $this->shiftargs($args);
		echo "<input name='" . $args['formname']  . "' type='checkbox' value='1' ";
		checked('1', $args['value']); 
		echo " /> <span  class='description'>" . $args['description'] . "</span>" ;
		
	} // function
	
	function get_attachment_id ($image_src) {
		global $wpdb;
		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
		$id = $wpdb->get_var($query);
		return $id;
	}
	
	// Build an attachment upload form  adda a try catch to anything that causes a wp-error
	function attachment($args) {
		$args = $this->shiftargs($args);
		echo "<div><input class='attachment' id='" . $args['id'] . "' style='" .$this->width($args['width']) . "' type='text' size='57' " . $this->placeholder($args['placeholder'] ) . " name='" . $args['formname'] . "' value='" . $args['value']. "' />";
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
	
	// Render a selectbox or multi selectbox
	function select($args)  {
		$args = $this->shiftargs($args);
	    if ($args['multiple']) {
			echo "<select class='optselect' multiple style='" .$this->width($args['width'])  . "' name='" . $args['formname'] . "" . "[]'>";
			foreach ($args['select'] as $key => $value) {
				echo "<option " . (array_search($value , $args['value']) === false ? '' : 'selected' ). " value='" . $key . "'>" . $value . "</option>";	
			}	
			echo "</select>";
		} else {
			echo "<select  class='optselect' style='" .$this->width($args['width'])  . "' name='" . $args['formname'] . "'>";
			foreach ($args['select'] as $key => $value) {
				echo "<option " . ($args['value'] == $key ? 'selected' : '' ). " value='" . $key . "'>" . $value . "</option>";	
			}	
			echo "</select>";
		}
		$this->description($args['description']);
	} // function


	// quick add a metabox section and box
	function easy_meta($id,$title) {
		add_settings_section($id, '', array(&$this, 'section_null'), $this->page );
		add_meta_box($id,$title, array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => $id));
		
	}
	
	// quick add a field
	function easy_field($args) {
		add_settings_field($args['id'], $args['title'], array(&$this, $args['type']), $this->page , $args['section'],$args	);
	}

	// Given an id show it along with the title in the autocmoplete textbox
	function get_suggest_title($id) {
		return get_the_title($id) . " [#". $id ."]";
	}
	
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
	
	function get_files_in_folder($folder,$none = 0) {
		$files = array();

		if ($none == 1) {$files[] = 'Disabled';}
		if ($handle = opendir(get_template_directory()  .'/js/'. $folder)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
			   $files[$file] = $file;
			  
			}
		}
		closedir($handle);
		
		}
	return $files;
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
		
	
} // class
} // if





?>