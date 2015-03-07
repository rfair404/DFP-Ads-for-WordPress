<?php
/**
 * Created by PhpStorm.
 * User: russell
 * Date: 4/18/14
 * Time: 4:19 PM
 */

class DFP_Complete_Tag_Factory {

	function __construct(){
		add_filter('dfp_complete_tag_markup', array($this, 'generic_tag_markup'), 10, 4);
	}

	public static function generic_tag_markup($tag, $slot, $id, $size = false){
		$tag = (string) '';
		$tag .= sprintf("<!-- %s -->", $slot);
		$tag .= PHP_EOL;

		$size = (is_array($size)) ? sprintf(" style='width:%dpx; height:%dpx;'", $size['width'], $size['height']) : "";
		$tag .= sprintf( "<div id='div-gpt-ad-%s'%s>", $id, $size);
		$tag .= PHP_EOL;
		$tag .= "<script type='text/javascript'>";
		$tag .= PHP_EOL;
		$tag .= sprintf("googletag.cmd.push(function() { googletag.display('div-gpt-ad-%s'); });", $id);
		$tag .= PHP_EOL;
		$tag .= "</script>";
		$tag .= PHP_EOL;
		$tag .= "</div>";

		return $tag;
	}

	public static function generate_tag($slot, $id, $size = false){
		return apply_filters('dfp_complete_tag_markup', '', $slot, $id, $size);
	}
} 