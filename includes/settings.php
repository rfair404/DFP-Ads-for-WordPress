<?php
/**
 * Created by PhpStorm.
 * User: russell
 * Date: 4/18/14
 * Time: 3:39 PM
 */

class DFP_Complete_Settings {

	public $settings;

	function __construct() {
		add_action('admin_init', array($this, 'register_settings'));
		add_filter('dfp_complete_default_options', array($this, 'get_default_options'));
	}

	function register_settings(){
		$option_name = 'dfp_complete';
		$registered = register_setting('dfp_complete_options',$option_name, array($this, 'sanatize_options'));
		$this->settings['option_name'] = $option_name;
	}

	function sanatize_options($options){
		return $options;
	}

	function get_default_options(){
		$defaults = array(
			'enabled' => false,
			'synchronous' => false,
			'google_app_id' => false,
		);

		return $defaults;
	}

	function set_default_options(){
		$defaults = apply_filters('dfp_complete_default_options', array());
		return update_option($this->settings['option_name'], $defaults);
	}

	function get_settings(){
		return get_option($this->settings['option_name']);
	}
}