<?php
class DFPADMAN_Common{

    /**
    * constructor
    */
    function __construct(){
        $this->dfpadman_version = '092';
        $this->dfp_lib_url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
        $this->dfp_lib_dir = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

        add_action('init', array($this, 'set_main'));
        add_action('init', array($this, 'set_conditionals'));
        add_action('init', array($this, 'set_positions'));
        add_action('widgets_init', array($this, 'register_widget'));
        add_action('after_setup_theme', array($this, 'determine_hooks'));
    }

    /**
    * sets the available hooks based on which theme we're using
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @return array $hooks the available hooks
    * @todo make this work in situations where tha hooks aren't in use
    */
    function determine_hooks(){
        if(get_theme_support('tha_hooks')){
            $hooks = self::get_tha_hooks();
        }

        else if(get_theme_support('genesis-structural-wraps')){
            $hooks = self::get_genesis_hooks();
        }

        else
            return false;

        array_unshift($hooks, __('Widgetize This Tag', 'dfpadman'));

        $this->theme_hooks = $hooks;
        //more to come...
    }

    /**
    * registers the widget class
    * @since 0.1-alpha
    * @author Russell Fair
    * @package WordPress
    * @subpackage DFPAdManager
    */
    function register_widget(){
        require_once($this->dfp_lib_dir . '/widget.php');
        register_widget('DFPADMAN_Widget');
    }


    /**
    * sets the available conditionals
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @return array $conditionals the available conditionals
    */
    function get_conditionals(){
        $conditionals = array(
            'is_home' => 'Home Page',
            'is_archive' => 'Archives',
            'is_tax' => 'Taxonomy Archives',
            'is_author' => 'Author Archives',
            'is_single' => 'Single Posts',
            'is_page' => 'Pages',
            'is_404' => '404 Page',
            );

        return $conditionals;
    }

    /**
    * gets the available theme hook alliance hooks
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @return array $tha_hooks the available hooks (defaults to all)
    * @todo allow for theme support of individual hooks only
    */
    function get_tha_hooks(){
        $tha_hooks = array(
            'tha_html_before',
            'tha_body_top',
            'tha_body_bottom',
            'tha_head_top',
            'tha_head_bottom',
            'tha_header_before',
            'tha_header_after',
            'tha_header_top',
            'tha_header_bottom',
            'tha_content_before',
            'tha_content_after',
            'tha_content_top',
            'tha_content_bottom',
            'tha_entry_before',
            'tha_entry_after',
            'tha_entry_top',
            'tha_entry_bottom',
            'tha_comments_before',
            'tha_comments_after',
            'tha_sidebars_before',
            'tha_sidebars_after',
            'tha_sidebar_top',
            'tha_sidebar_bottom',
            'tha_footer_before',
            'tha_footer_after',
            'tha_footer_top',
            'tha_footer_bottom',
        );

        $tha_support = get_theme_support( 'tha_hooks');
        if($tha_support[0][0] == 'all')
            $hooks = $tha_hooks;
        else
            $hooks = $tha_support[0];

       return $hooks;
    }

    /**
    * gets the available theme hook alliance hooks
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    * @return array $gehesis_hooks the available hooks
    */
    function get_genesis_hooks(){
        $genesis_hooks = array(
            'genesis_before',
            'genesis_before_header',
            'genesis_header',
            'genesis_site_title',
            'genesis_site_description',
            'genesis_header_right',
            'genesis_after_header',
            'genesis_before_content_sidebar_wrap',
            'genesis_before_content',
            'genesis_before_loop',
            'genesis_loop',
            'genesis_before_entry',
            'genesis_entry_header',
            'genesis_entry_content',
            'genesis_entry_footer',
            'genesis_after_entry',
            'genesis_after_endwhile',
            'genesis_after_loop',
            'genesis_before_sidebar_widget_area',
            'genesis_after_sidebar_widget_area',
            'genesis_after_content',
            'genesis_after_content_sidebar_wrap',
            'genesis_before_footer',
            'genesis_footer',
            'genesis_after_footer',
            'genesis_after',
            );

        return $genesis_hooks;
    }

    /**
    * sets the dfp main options
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @return array $options the main options
    */
    function set_main(){
        $main = get_option('dfpadman_main');
        if(!$main)
            $main = update_option('dfpadman_main', array());

        $this->main = $main;
    }

    /**
    * sets the dfp conditionals
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @return array $options the main options
    */
    function set_conditionals(){
        $this->conditionals = self::get_conditionals();
    }

    /**
    * sets the registered positions
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function set_positions(){
        $positions = get_option('dfpadman_positions');

        if(!$positions)
            $positions = update_option('dfpadman_positions', array());

        $this->positions = $positions;
    }
}
