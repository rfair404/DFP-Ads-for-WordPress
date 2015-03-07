<?php
class DFPADMAN_Init{

    /**
    * constructor
    */
    function __construct(){
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
    * Initializes the various classes of the plugin
    * @package WordPress
    * @subpackage DFPAdManager
    * @author Russell Fair
    * @since 0.1-alpha
    * @todo place require onces and conditionals here
    */
    function init(){
        $dfpadman_common = new DFPADMAN_Common();

        if(is_admin()){
            $dfpadman_admin = new DFPADMAN_Admin($dfpadman_common);
        }
        else
        {
            $dfpadman_display = new DFPADMAN_Display($dfpadman_common);
        }
    }
}

