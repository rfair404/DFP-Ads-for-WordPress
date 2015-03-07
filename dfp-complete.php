<?php
/*
Plugin Name: Dfp-complete
Version: 0.1
Description: A Complete DFP Integration that requires zero code editing
Author: Russell Fair
Author URI: http://q21.co
Plugin URI: http://q21.co/dfp-complete
Text Domain: dfp-complete
Domain Path: /languages
*/

class DFP_Complete{

	public $status = array('version' => '0.1');

	function __construct() {
		add_action('plugins_loaded', array($this, 'load'));
		add_action('plugins_loaded', array($this, 'init'));

	}

	public function load(){
		require_once('includes/filters.php');
		require_once('includes/settings.php');
		require_once('includes/tag-factory.php');
	}

	public function init(){
		$this->filters = new DFP_Complete_Filters();
		$this->settings = new DFP_Complete_Settings();
	}


}
new DFP_Complete();
