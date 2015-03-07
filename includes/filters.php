<?php
/**
 * Created by PhpStorm.
 * User: russell
 * Date: 4/18/14
 * Time: 3:34 PM
 */

class DFP_Complete_Filters {

	function __construct() {
		add_filter('the_content', 'dfp_complete_content_tag_ad');
	}

	function content_tag_ad($content){
		return $content;
	}
}