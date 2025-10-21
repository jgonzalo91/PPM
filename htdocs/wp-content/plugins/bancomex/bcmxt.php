<?php
/*
Plugin Name: Bancomext Plugin (custom)
Plugin URI: http://bancomext.gob.mx
Description: This creates....
Version: 1.0
Author: Bancomext 2017	
Author URI: http://bancomext.gob.mx
*/


if( !class_exists('bcmxt') ):

class bcmxt
{

	
	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions, filters and functions for the ACF plugin to work
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
			
		// vars
		$this->settings = array(
			'version'			=> '4.4.11',
			'upgrade_version'	=> '3.4.1',
			'include_3rd_party'	=> false
		);
		
		
		
		// actions
		add_action('init', array($this, 'init'), 1);/**/
		add_action('init', array($this, 'load_bancomext_translation'));

		//Setup Admin Page
		add_action('admin_menu', 'users_notification_setup_menu');
		add_action('admin_menu', 'queries_selection_setup_menu');
		add_action('admin_menu', 'bulk_date_setup_menu');
		add_action('phpmailer_init', array( $this, 'fix' ) );


		// Post Contact from Public site
		add_action('wp_enqueue_scripts', 'addStyles_PostContact');
		add_shortcode('POST_CONTACT', 'PostContact_form');
		add_shortcode('send_mail_new_contact_publish', 'send_mail_new_contact');

		// Users Notifications
		add_action('wp_enqueue_scripts', 'addStyles_Bancomext_Users');
		add_shortcode('bancomext_subscription', 'shortcode_bancomext_users');
		add_action('wp_footer', 'bancomext_top_proyect');
		add_shortcode('bancomext_subscription_down', 'shortcode_bancomext_users_down');
		add_filter( 'cptch_add_form', 'add_my_forms' );
		add_action('wp_ajax_send_data_bu', 'send_data_bu');
		add_action('wp_ajax_nopriv_send_data_bu', 'send_data_bu');
		add_shortcode('send_mail_new_user_to_proyect', 'send_mail_new_suscription'); // shortcode to send new user suscription
		add_shortcode('send_mail_down_user_to_proyect', 'send_mail_down_suscription');
		add_shortcode('send_mail_down_user_to_proyect_all', 'send_mail_down_suscription_all');

		//Notification Process
		add_shortcode('send_mail_action', 'send_mails_shortcode');
		add_shortcode('check_mail_posts', 'get_mail_preferences_shortcode');
		add_shortcode('send_down_page', 'deleteme_page');
        add_shortcode('weekly_project_update','Save_projects_update');
        add_shortcode('view_project_update','view_projects_upd');
        add_shortcode('view_project_update_en','view_projects_upd_en');
        add_shortcode('num_proyectos_vehiculos','num_proyectos');

		$this->include_before_theme();

		
		
	}

	function fix($phpmailer) {

	  	$phpmailer->Sender = $phpmailer->From;
	}

	/*
	*  load_bancomext_translation
	*
	*  This function will load the translations of each plugin.
	*  
	*  @param	N/A
	*  @return	N/A
	*/

	function load_bancomext_translation()
    {	
    	// Load translation for PostContact plugin
        load_plugin_textdomain('postcontact', FALSE, dirname(plugin_basename(__FILE__)).'/postcontact/languages/'); 
        load_plugin_textdomain('users', FALSE, dirname(plugin_basename(__FILE__)).'/users/languages/'); 
        load_plugin_textdomain('notification-process', FALSE, dirname(plugin_basename(__FILE__)).'/notification-process/languages/'); 
    }
    
        
	/*
	*  include_before_theme
	*
	*  This function will include core files before the theme's functions.php file has been excecuted.
	*  
	*  @type	action (plugins_loaded)
	*  @date	3/09/13
	*  @since	4.3.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function include_before_theme()
	{	
		include_once('notification-process/classes/wp-async-task.php');
		include_once('notification-process/classes/wp-background-task.php');
		include_once('notification-process/classes/bmxt-async-task.php');
		include_once('notification-process/classes/bmxt-background-task.php');
		include_once('notification-process/classes/bmxt-main-task.php');
		include_once('notification-process/classes/bmxt-async-preferences.php');
		include_once('notification-process/classes/bmxt-background-preferences.php');
		include_once('notification-process/classes/bmxt-main-preferences.php');
		
		// === NUEVO: proyectos nuevos ===
  include_once('notification-process/classes/bmxt-async-new-projects.php');
  include_once('notification-process/classes/bmxt-main-new-projects.php');
  // ===============================
  
  // === NUEVO: runner TTL (SIN token, SIN admin, usa ?value=...) ===
    include_once('notification-process/classes/bmxt-ttl-new-projects.php');  // <-- AQUI
    // ==

		
		

		// incudes
		include_once('postcontact/postcontact.php');
		include_once('users/bmxt_users_admin.php');
		include_once('users/users.php');
		include_once('notification-process/notification-back-mail.php');
		include_once('notification-process/weekly-project-update.php');
		include_once('notification-process/numero_proyectos.php');
		include_once('queries/queries_select.php');
		include_once('bulk/bulk.php');

	}


	/*
	*  init
	*
	*  This function is called during the 'init' action and will do things such as:
	*  create post_type, register scripts, add actions / filters
	*
	*  @type	action (init)
	*  @date	23/06/12
	*  @since	1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function init()
	{
		add_action('wp_enqueue_scripts', 'addStyles_PostContact');
		
		// admin only
		if( is_admin() )
		{
			//add_action('admin_menu', array($this,'admin_menu'));
			//add_action('admin_head', array($this,'admin_head'));
			//add_filter('post_updated_messages', array($this, 'post_updated_messages'));
		}
	}


}
/*
*  bcmxt
*
*  The main function responsible for returning the one true bcmxt Instance to functions everywhere.
*  Use this function like you would a global variable, except without needing to declare the global.
*
*  Example: <?php $bcmxt = bcmxt(); ?>
*
*  @type	function
*  @date	4/09/13
*  @since	4.3.0
*
*  @param	N/A
*  @return	(object)
*/

function bcmxt()
{
	global $bcmxt;
	
	if( !isset($bcmxt) )
	{
		$bcmxt = new bcmxt();
	}
	
	return $bcmxt;
}


// initialize
bcmxt();


endif; // class_exists check

?>
