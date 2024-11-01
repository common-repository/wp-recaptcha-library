<?php
/*
Plugin Name: WP reCAPTCHA Library
Plugin URI: http://mattwalters.net/projects/wp-recaptcha-library/
Description: Provides functions to easily display and validate a reCAPTCHA
Author: Matt Walters
Version: 1.0.2
Author URI: http://mattwalters.net/
*/ 

require_once('recaptchalib.php');

if (!class_exists('msw_WPreCAPTCHA')) {
	class msw_WPreCAPTCHA {
		var $options = array();

		function msw_WPreCAPTCHA() { $this->__construct(); } // PHP4 compatibility

		function __construct() {
			if (function_exists('add_action')) {
				add_action("admin_menu", array(&$this, 'add_admin_pages')); // Call to add menu option in admin
			}
			// Assumes your language files will be in the format: wordpress_file_monitor-locationcode.mo
			$wp_recaptcha_locale = get_locale();
			$wp_recaptcha_mofile = dirname(MSW_WPFM_FILE) . '/languages/wordpress_file_monitor-' . $wp_recaptcha_locale . '.mo';
			load_textdomain("wp_recaptcha", $wp_recaptcha_mofile);

			add_action('admin_init', array($this, 'options_init'));

			$this->options = maybe_unserialize(get_option('msw_wprecaptcha_option')); // Set options to users preferences
		}

		function plugin_action_links($links, $file) { // Add 'Settings' link to plugin listing page in admin
			$plugin_file = 'wp-recaptcha-library/'.basename(__FILE__);
			if ($file == $plugin_file) {
				$settings_link = '<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=WP%20reCAPTCHA">'.__('Settings', 'wp_recaptcha').'</a>';
				array_unshift($links, $settings_link);
			}
			return $links;
		}

		function options_init(){
			register_setting('msw_wprecaptcha_options', 'msw_wprecaptcha_option', array($this, 'msw_wprecaptcha_options_validate'));
		}

		function msw_wprecaptcha_options_validate($input) {
			$input['public_key'] =  wp_filter_nohtml_kses($input['public_key']);
			$input['private_key'] =  wp_filter_nohtml_kses($input['private_key']);
			return $input;
		}


		function output_sub_admin_page_0() {
			?>
			<div class="wrap">
				<h2>WP reCAPTCHA Library Options</h2>
				<form method="post" action="options.php" style="float: left">
					<?php settings_fields('msw_wprecaptcha_options'); ?>
					<?php $options = get_option('msw_wprecaptcha_option'); ?>
					<table class="form-table">
						<tr valign="top"><th scope="row">Public Key</th>
							<td><input name="msw_wprecaptcha_option[public_key]" type="text" value="<?php echo $options['public_key']; ?>" /></td>
						</tr>
						<tr valign="top"><th scope="row">Private Key</th>
							<td><input type="text" name="msw_wprecaptcha_option[private_key]" value="<?php echo $options['private_key']; ?>" /></td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>
				<script type="text/javascript">
				var WPHC_AFF_ID = '14317';
				var WPHC_WP_VERSION = '<?php global $wp_version; echo $wp_version; ?>';
				</script>
				<script type="text/javascript"
					src="http://cloud.wphelpcenter.com/wp-admin/0002/deliver.js">
				</script>
				<style type="text/css" media="screen">
					div.metabox-holder {
						float: right;
						width: 256px;
					}
				</style>
			</div>
			<?php
		}

		function add_admin_pages() { // Add menu option in admin
			add_submenu_page('options-general.php', "WP reCAPTCHA", "WP reCAPTCHA", 10, "WP reCAPTCHA", array(&$this,"output_sub_admin_page_0"));
		}

		function msw_recap_displayCaptcha() {
			$publickey = "6LfV8AkAAAAAAL566EReb6hi6gLGHGrojM5qKQ9n"; // you got this from the signup page
			echo recaptcha_get_html($this->options['public_key']);
		}

		function msw_recap_validateCatcha() {
			$privatekey = "6LfV8AkAAAAAAGlk80B3329aJhbs5irLYpzMEb2I";
			$resp = recaptcha_check_answer($this->options['private_key'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) { return false; } else { return true; }
		}
	}
}

if (!$msw_WPreCAPTCHA && function_exists('add_action')) { $msw_WPreCAPTCHA = new msw_WPreCAPTCHA(); } // Create object if needed

if (function_exists('add_filter')) {
	add_filter('plugin_action_links', array(&$msw_WPreCAPTCHA, 'plugin_action_links'), 10, 2); // Add settings link to plugin listing
}