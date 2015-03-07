<?php
/**
 * Created by PhpStorm.
 * User: russell
 * Date: 4/18/14
 * Time: 3:56 PM
 */

class TestDFP_Complete_Filters extends WP_UnitTestCase {
	function setUp(){
		parent::setUp();
	}

	function testContentFilterExists(){
		$this->assertEquals(10, has_filter('the_content', 'dfp_complete_content_tag_ad'));
	}

}
 