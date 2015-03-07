<?php

class TestDFP_Complete extends WP_UnitTestCase {

	function setUp(){
		parent::setUp();
		$this->dfp = new DFP_Complete();
	}

	function testReality() {
		// replace this with some actual testing code
		$this->assertTrue( true );
		$this->assertFalse( false );
	}

	function testPluginLoaded(){
//		assertArrayHasKey('version', $this->dfp_complete);
	}

	function test_plugin_slug(){

	}

	function testFiltersClassLoaded(){
		$this->assertTrue(class_exists('DFP_Complete_Filters'));
	}

	function testSettingsClassLoaded(){
		$this->assertTrue(class_exists('DFP_Complete_Settings'));
	}

	function testTagFactoryClassLoaded(){
		$this->assertTrue(class_exists('DFP_Complete_Tag_Factory'));
	}

}

