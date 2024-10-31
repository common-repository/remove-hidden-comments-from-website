<?php
/*
Plugin Name: Remove Unnecessary hidden Comments
Description: With this plugin you can remove hidden comments (like <!-- something comment by XYZ  --> ) fron your site's Front-End output - Automatically or Manually.
Version: 1.1
Author: Mitch, TazoTodua
Author URI: http://www.protectpages.com/profile
Plugin URI: http://www.protectpages.com/
Donate link: http://paypal.me/tazotodua
*/
define('version__RUHCFS', 1.9);
define('pluginpage__RUHCFS', 			'ruhcfs_opts_page');
define('pluginadmin__RUHCFS',  			(is_multisite()  ? 'settings.php' : 'options-general.php' )  ) ;
define('pluginsaveopts__RUHCFS',  		(is_multisite()  ? 'settings.php' : 'options.php' )  ) ;
define('plugin_settings_page__RUHCFS', 	(is_multisite()  ? network_admin_url('settings.php') : admin_url( 'options-general.php') ). '?page='.pluginpage__RUHCFS  );
define('ruhcfs_dtrans', "ruhcfs_trn");
									
								

// ================================================== General variables ===============================================
define('HOME_URL__RUHCFS', 		(home_url('/','relative'))	); 
define('PLUGIN_URL__RUHCFS',	plugin_dir_url(__FILE__)	);
function get_opts__RUHCFS()		{  return $GLOBALS['RUHCFS_OPTS']=get_site_option('ruhcfs_opts', array()); }
function get_fields__RUHCFS()	{  return $GLOBALS['RUHCFS_FIELDS']=get_site_option('ruhcfs_fields', array()); }
function validate_pageload__RUHCFS($value, $action_name){ if ( !wp_verify_nonce($value, $action_name) ) { die( "go back&refresh page.  (". __FILE__ );	}  	}	


// ==========================================================================================================================

register_activation_hook( __FILE__, 'First_Time_Install__RUHCFS' );
function First_Time_Install__RUHCFS(){	}


add_action('plugins_loaded', 'refresh_options__RUHCFS',1);
function refresh_options__RUHCFS(){
	$opts = $old_opts = get_opts__RUHCFS(); 
	$array =  array( 'plugin_enabled'=>1, 'any_comments'=>1,  );
	foreach($array as $name=>$value){ if(!array_key_exists($name,$opts)){ $opts[$name]=$array[$name]; } }
	$opts['vers']= version__RUHCFS; 
	if($old_opts != $opts) { update_site_option('ruhcfs_opts', $opts );  }
	return $opts;
}
// ==================================================  #### PLUGIN ACTIVATION  HOOK ==============================================



function buffer_start__RUHCFS() {
    ob_start("callback__RUHCFS");
}
	function callback__RUHCFS($buffer) {	
		if($GLOBALS['RUHCFS_OPTS']['any_comments']) {
			//$buffer= preg_replace('/<!--(.|s)*?-->/', '', $buffer);	
			preg_match_all('/<!--(.*?)-->/', $buffer, $result);
			if (!empty($result[0])) {
				foreach ($result[0] as $each){
					//avoid markup removal
					if(strpos($each, '<!--[if lt') ===false && strpos($each, '<![endif]-->') ===false ) {
						$buffer=str_replace($each,'',$buffer);
					}
				}
			}
		}
		else{
			$opts2= get_fields__RUHCFS();
			foreach($opts2 as $each){
				$buffer = str_replace($each,'', $buffer);
			}
		}
		return $buffer;
	}
	
function buffer_end__RUHCFS() {
    ob_end_flush();
}

add_action('plugins_loaded', function(){
	add_action('get_header',	'buffer_start__RUHCFS',	1);
	add_action('wp_footer',		'buffer_end__RUHCFS',	999);
}, 99);






	
// =================================================     ADD PAGE IN SETTINGS menu ================================================= 
add_action((is_multisite() ? 'network_admin_menu' : 'admin_menu')  ,  function() {
	add_submenu_page(pluginadmin__RUHCFS, 'Remove hidden comments', 'Remove hidden comments', 'edit_others_posts', pluginpage__RUHCFS, 'ruhcfs__RUHCFS' );
} );
function ruhcfs__RUHCFS(){		global $wpdb;
	if(isset($_GET['isactivation']) && stripos($_SERVER['HTTP_REFERRER'], 'isactivation') ===false ) { echo '<script>alert("If you are using multi-site, you should set these options per sub-site one-by-one");</script>'; }
	
	if(!empty($_POST['ruhcfs_opts'])){
		check_admin_referer('nonce_ruhcfs');
		$opts1= get_opts__RUHCFS();
		
		$opts1['any_comments'] = !empty($_POST['ruhcfs_opts']['any_comments']) ? 1 : 0;
		foreach (array_filter($_POST['ruhcfs_fields']) as $each){
			$each = str_replace( array('<!--', '-->'), array('xyzzyx991199', 'xyzzyx992299'), $each);
			$each = str_replace(array('<?','< ','<\\','<script','<s'), '', $each);
			$each = stripslashes($each);
			$each = sanitize_text_field($each);
			$each = str_replace( array('xyzzyx991199', 'xyzzyx992299'),  array('<!--', '-->'), $each);
			$opts2[] =  $each;
		}
		update_site_option('ruhcfs_opts',  $opts1);
		update_site_option('ruhcfs_fields', $opts2);
	}
	$opts1= get_opts__RUHCFS();
	$opts2= get_fields__RUHCFS();
?>
<style>
.eachFieldX{ width:100%;}
.form-table th { width: 50%; }
</style>
<script>
// http://github.com/tazotodua
// =================================== hide content if chosen radio box not chosen  ===============================
	function radiobox_onchange_hider(selector, desiredvalue, target_hidding_selector, SHOW_or_hide, Hide_or_Opacity){
		SHOW_or_hide	= SHOW_or_hide || false;
		Hide_or_Opacity	= Hide_or_Opacity || false;
		if( typeof dropdown_objs == 'undefined') { dropdown_objs = {}; } 
		if( typeof dropdown_objs[selector] == 'undefined' ){
			dropdown_objs[selector] = true; var funcname= arguments.callee.name;
			jQuery(selector).change(function() { window[funcname](selector,desiredvalue, target_hidding_selector);	});
		}
		var x = jQuery(target_hidding_selector);
		if( jQuery(selector+':checked').val() == desiredvalue )	{ 
			if(SHOW_or_hide){  if (Hide_or_Opacity) x.show();   else  x.css("opacity","1");}
			else 			{  if (Hide_or_Opacity) x.hide();   else  x.css("opacity","0.3");  }
		} 
		else 	{ 
			if(SHOW_or_hide){ if (Hide_or_Opacity) x.dide();   else  x.css("opacity","0.3"); }
			else 			{ if (Hide_or_Opacity) x.show();   else  x.css("opacity","1"); }
		} 
	}
// ===========================================================================================
</script>
<div class="clear"></div>
<div id="welcome-panel" class="welcome-panel">
	<div class="welcome-panel-content">
	<h3><?php echo __('Plugin Settings Page!', ruhcfs_dtrans);?></h3>
	<p class="about-description"><?php echo __('Welcome. This plugin is mainly concentrated for removing the HTML hidden comments from your website output. (However, there exist other useful plugins too, like <a href="https://wordpress.org/plugins/head-cleaner/" target="_blank">Head Cleaner</a> or etc...', ruhcfs_dtrans);?> </p>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column" style="width:80%;">
			<h4>_</h4>
			<form method="post" action="">
			<?php 
			//$opts1	= get_opts__RUHCFS();
			$fields	= array_filter(get_fields__RUHCFS());
			?>
		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row"><?php echo __('Remove any <code>&lt;!--  ... --&gt;</code> comment automatically:', ruhcfs_dtrans);?> </th>
		        <td><input type="radio" name="ruhcfs_opts[any_comments]" value="1" <?php checked($opts1['any_comments'], 1) ; ?> /></td>
		        </tr>
		        <tr valign="top">
		        <th scope="row"><?php echo __('Remove only the following list of comments:', ruhcfs_dtrans);?></th>
		        <td><input type="radio" name="ruhcfs_opts[any_comments]" value="0" <?php checked($opts1['any_comments'], 0) ; ?> /></td>
		        </tr>
		        <tr valign="top">
		        <th scope="row"> </th>
		        <td>
				<div id="fields_block_rh">
					<div id="fields_holder_rh">
					<?php 
					function each_filed_out($value, $name= false){ 
						return '<input type="text" class="eachFieldX" name="ruhcfs_fields['. ($name ?: '').']" value="'.htmlentities($value).'"  placeholder="<!-- example -->"/>';
					}
					foreach ($fields as $name=>$value){
						echo each_filed_out($value);
					}
					echo each_filed_out('');
					?>
					</div>
					<button type="button" class="button-small" onclick="add_new_filed();" ><?php echo __('add field', ruhcfs_dtrans);?></button>
				</div>
				<script>
					radiobox_onchange_hider('input[name="ruhcfs_opts[any_comments]"]', 1, '#fields_block_rh', false , false );
					function add_new_filed(){
						var parent = document.getElementById("fields_holder_rh");
						var fields = document.getElementsByClassName("eachFieldX");
						parent.appendChild( fields[fields.length-1].cloneNode(true) );
					}
				</script>
				</td>
		        </tr>
		    </table>
		    <?php 
			wp_nonce_field( 'nonce_ruhcfs' );
			submit_button(  __('Save Settings', ruhcfs_dtrans), 'primary', 'xyz-save-settings', true,  $attrib= array( 'id' => 'xyz-submit-button' )   );
		    ?>
			</form>
		</div>
	</div>
	</div>
</div>
<?php 
} // END PLUGIN PAGE


								
								//===========  links in Plugins list ==========//
								add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), function ( $links ) {   $links[] = '<a href="'.plugin_settings_page__RUHCFS.'">'.__('Settings', ruhcfs_dtrans).'</a>'; /*$links[] = '<a href="http://paypal.me/tazotodua">'.__('Donate', ruhcfs_dtrans).'</a>'; */ return $links; } );
								//REDIRECT SETTINGS PAGE (after activation)
								add_action( 'activated_plugin', function($plugin ) { if( $plugin == plugin_basename( __FILE__ ) ) { exit( wp_redirect( plugin_settings_page__RUHCFS.'&isactivation'  ) ); } } );

?>