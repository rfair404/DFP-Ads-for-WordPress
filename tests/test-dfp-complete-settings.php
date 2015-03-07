<?php
/**
 * Created by PhpStorm.
 * User: russell
 * Date: 4/18/14
 * Time: 3:56 PM
 */

class TestDFP_Complete_Settings extends WP_UnitTestCase {
	function setUp(){
		parent::setUp();
		$this->dfp_settings = new DFP_Complete_Settings();
		do_action('admin_init');
	}

	function testContentSettingsExists(){
		$this->assertEquals($this->dfp_settings->settings['option_name'], 'dfp_complete');
	}

	function testCanGetDefaultOptions(){
		$this->assertTrue(is_array($this->dfp_settings->get_default_options()));
		$this->assertArrayHasKey('enabled', $this->dfp_settings->get_default_options());
		$this->assertArrayHasKey('synchronous', $this->dfp_settings->get_default_options());
		$this->assertArrayHasKey('google_app_id', $this->dfp_settings->get_default_options());
	}

	function testCanSetOptionsDefault(){
		$this->assertTrue($this->dfp_settings->set_default_options());
	}

	function testCanGetOptions(){
		$this->dfp_settings->set_default_options();
		$this->assertTrue(is_array($this->dfp_settings->get_settings()));
	}



}
 