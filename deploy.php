<?php

// ############################################################
// #######
// ####### Drop in Client Deployment for WP2Static
// ####### v.0.1
// #######
// ############################################################

// Hide Static Settings
add_action( 'plugins_loaded', 'hideWP2Static' );

function hideWP2Static(){

	// only show WP2Static menu to this user
	$showforusername = "tjhole";

	// get current username
	$current_user = wp_get_current_user(); 
	if ( !($current_user instanceof WP_User) ) 
	return; 

	$current_user = $current_user->user_login; 

	function remove_menus() {
		remove_menu_page( 'wp2static' ); // WP2Static
	}

	if ( $current_user != $showforusername ){
		add_action( 'admin_menu', 'remove_menus', 11 );
	}

}

// Create Deploy Button in Wordpress Header

function add_deploy_button($wp_admin_bar){

	$deploy = admin_url('admin.php?page=deploy-site');

	$args = array(
	'id' => 'deploy',
	'title' => 'Deploy',
	'href' => $deploy,
	'meta' => array(
	'class' => 'deploy-class'
	)
	);

	$wp_admin_bar->add_node($args);

}
add_action('admin_bar_menu', 'add_deploy_button', 50);

// Create Deploy Button in Wordpress Sidebar

function deploy_button_menu(){
	add_menu_page('Deploy', 'Deploy', 'edit_posts', 'deploy-site', 'deploy_button_admin_page', 'dashicons-awards', 90);
}

add_action('admin_menu', 'deploy_button_menu');

// Create Client Facing Page

function deploy_button_admin_page() {

  // This function creates the output for the admin page.

  // The check_admin_referer is a WordPress function that does some security
  // checking and is recommended good practice.
  if (!(current_user_can('editor') || current_user_can('administrator')))  {
    wp_die( __('You do not have sufficient privileges to access this page.')    );
  }

  // Start building the page

  echo '<div class="wrap" style="max-width:100%;width:600px;">';

  echo '<h2>Deploy</h2>';
  // Check whether the button has been pressed AND also check the nonce
  if ( isset($_POST['deploy_button']) && check_admin_referer('deploy_button_clicked')) {

    deploy_button_action();
 
  } else {

  	// Format what you want your client to see here.
	echo '<p>For security and speed the admin area is isolated from the live site.</p>';
	echo '<p>To make changes you have made here visible on the live site you will need to <em>push</em> the site to the production server. Click deploy to start the process. The script will take a few minutes to complete.</p>';

	echo '<div class="postbox"><div class="inside">';
	echo '<h3>Auto Deployment</h3>';
	echo '<p>Automatically deploy the site when Wordpress publishes a scheduled post.</p>';

	if ( isset($_POST['autodeploysitestatus']) && check_admin_referer('autodeploysitestatusnonce')) {
	    update_option('autodeploysitestatus', $_POST['autodeploysitestatus']);
		echo '<div class="notice notice-success updated fade"><p>Status updated</p></div>';
	}
	echo '<form action="admin.php?page=deploy-site" method="post">';
		wp_nonce_field('autodeploysitestatusnonce');
	   	echo '<input class="postbox" type="checkbox" id="autodeploysitestatus" name="autodeploysitestatus" value="1" ' . checked(1, get_option('autodeploysitestatus'), false) . '/>'; 
		echo '<label for="autodeploysitestatus">Enable autodeploy</label>';
		echo '<input type="hidden" value="true" name="autodeploysitestatus" />';
		submit_button('Save Settings');
	echo '</form>';
	echo '</div></div>';

	echo '<div class="postbox"><div class="inside">';
	echo '<h3>Manual Deploy</h3>';
	echo '<p>Immediately deploy the site. The process will take several minutes.</p>';
	echo '<form action="admin.php?page=deploy-site" method="post">';
	// Add NONCE to stop any nonsense. 
	wp_nonce_field('deploy_button_clicked');
		echo '<input type="hidden" value="true" name="deploy_button" />';
		submit_button('Deploy');
	echo '</form>';
	echo '</div></div>';

  }


}

// CREATE & CLEAR LOCK FILES
function write_lock() {

	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', '1');

	if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
	if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
	if(!defined('STDERR')) define('STDERR', fopen('php://stdout', 'w'));

	$mu_path = plugin_dir_path( __FILE__ );
	$mu_url = plugin_dir_url( __FILE__ );
	// CREATE LOCK FILE FOR BASH SCRIPT TO RUN OFF
	$lockfilepath = $mu_path.".run";
	$lockfile = fopen($lockfilepath, "w") or die("Unable to open file!");
	$locktxt = null;
	fwrite($lockfile, $locktxt);
	fclose($lockfile);

	// CLEAR OLD LOG FILE
	$logfilepath = $mu_path."log.txt";
	$logfilepathpublic = $mu_url."log.txt";
	$logfile = fopen($logfilepath, "w") or die("Unable to open file!");
	$logtxt = date("Y-m-d") . " " . date("G:i:s") . " :: Queuing Deployment"; // can be blank
	fwrite($logfile, $logtxt);
	fclose($logfile);
}

// DEPLOY FUNCTION

function deploy_button_action() {

	echo '<div id="deployend" class="notice notice-success updated fade"><p>Starting Deployment.</p></div>';


	write_lock();

	$mu_path = plugin_dir_path( __FILE__ );
	$mu_url = plugin_dir_url( __FILE__ );
	$logfilepath = $mu_path."log.txt";
	$logfilepathpublic = $mu_url."log.txt";

	echo "<div id='log'></div>";

	echo "

		<style>

	    #log { 
			font-family: 'Courier New', Courier, 'Lucida Sans Typewriter', 'Lucida Typewriter', monospace;
			font-size: 14px;
			font-style: normal;
			font-variant: normal;
			font-weight: 700;
			line-height: 24px;
		    background: #f9f9fa;
		    border: 1px solid rgba(51, 51, 51, 0.1);
		    margin: 32px 0;
		    padding:16px;
	        white-space: pre-line;
		}

		</style>

		<script>
        jQuery.ajax({
            url : '".$logfilepathpublic."',
            dataType: 'text',
            success : function (data) {
                jQuery('#log').html(data);
            }
        });
		// loop
		setInterval(function(){
	        jQuery.ajax({
	            url : '".$logfilepathpublic."',
	            dataType: 'text',
	            success : function (data) {
	                jQuery('#log').html(data);
	            }
	        });
        }, 1000);
		</script>

	";

}  

// HOOK INTO SCHDULED POSTS
// ###
// publish_future_post is an action triggered when a post is transitioned from 'future' to 'publish' status
// ###

add_action( 'publish_future_post', 'auto_deploy_on_scheduled_posts' );

function auto_deploy_on_scheduled_posts( $post_id ) {

	// only trigger if user has enabled autodeployment
	if(get_option('autodeploysitestatus')){
		// to autodeploy we simply have the plugin write the lockfile so the cronjob can pick it up and auto-deploy using WP-CLI
		write_lock();
	}

}
