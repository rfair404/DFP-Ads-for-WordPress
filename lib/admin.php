<?php
class DFPADMAN_Admin{

    private $dfpadman_common;
    /**
    * the constructor
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function __construct(DFPADMAN_Common $dfpadman_common){
        $this->dfpadman_common = $dfpadman_common;
        //add_action('init', array($this, 'reset'));
        add_action('admin_menu', array($this, 'admin_menu'), 10);
        add_action( 'admin_notices', array($this, 'admin_notices'), 10);
        add_action('admin_init', array($this, 'register_settings'), 10);
        add_action('admin_init', array($this, 'add_sections'), 15);
        add_action( 'admin_print_styles-settings_page_dfpadman', array( $this, '_admin_css' ), 10);
        add_action('switch_theme', array($this, 'reset_positions'));
        add_action( 'admin_enqueue_scripts', array($this, 'pointer_loader'), 10, 1);
    }

    /**
    * registers the admin submenu page for dfpadman
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function admin_menu(){
        $page = add_submenu_page(  'options-general.php',
            __('DFP Ad Manager', 'dfpadman'), // The page title
            __('DFP Ad Manager', 'dfpadman'), // Menu Item Text
            'manage_options',  // minimum role required
            'dfpadman',   // slug
            array($this, '_admin_form')   // callback
        );

        add_action('load-' . $page, array($this, 'admin_help'), 10);
        //put your hook above mine when you merge this please
        add_filter( 'admin_pointers_' . $page, array($this, 'register_pointers'));

    }

    function admin_help(){

        get_current_screen()->add_help_tab( array(
             'id' => 'overview',
             'title' => __('DFP Ad Manager Overview', 'dfpadman'),
             'content' => '<br/ ><p>' . __('Welcome to the the DFP Ad Manager created by CheriMedia! This plugin will integrate Google Adsense ads from DoubleClick For Publishers (DFP) into supported themes.', 'dfpadman') . '</p>'
              . '<p>' . __('Follow the instructions in the <b>Header Script</b> and <b>Ad Units</b> tabs below to complete the DFP integration.', 'dfpadman'),
             ));
        get_current_screen()->add_help_tab( array(
             'id' => 'header_script',
             'title' => 'Header Script',
             'content' => '<p>' . __('Copy and paste the DFP generated <b>Document Header</b> code into the text area provided.', 'dfpadman') . '</p>' .
             '<p>' . __('It should look like something similar to this : ', 'dfpadman') . '</p>' .
             '<code>' .
             esc_html('<script type="text/javascript">') . '<br />' .
             esc_html('var googletag = googletag || {}; googletag.cmd = googletag.cmd || []; (function() {var gads = document.createElement("script"); gads.async = true;
gads.type = "text/javascript";') . '<br />' .
             esc_html('var useSSL = "https:" == document.location.protocol; gads.src = (useSSL ? "https:" : "http:") + "//www.googletagservices.com/tag/js/gpt.js";') . '<br />' .
             esc_html('var node = document.getElementsByTagName("script")[0]; node.parentNode.insertBefore(gads, node);})();') . '<br />' .
             esc_html('</script>') . '<br />' .
             esc_html('<script type="text/javascript">') . '<br />' .
             esc_html('googletag.cmd.push(function() {') . '<br />' .
             esc_html('googletag.defineSlot("/[publisher id]/[ad_unit_length]", "div-gpt-ad-1380121299676-0").addService(googletag.pubads());googletag.defineSlot("/[publisher id]/[ad_unit_length]", "div-gpt-d-1380121299676-1").addService(googletag.pubads());googletag.defineSlot("/[publisher id]/[ad_unit_length]", "div-gpt-ad-1380121299676-2").addService(googletag.pubads());
googletag.defineSlot("/[publisher id]/[ad_unit]", "div-gpt-ad-1380121299676-3").addService(googletag.pubads());googletag.pubads().enableSingleRequest();googletag.enableServices();
});') . '<br />' .
             esc_html('</script>') . '</code>'
             ));

    get_current_screen()->add_help_tab( array(
             'id' => 'ad_units',
             'title' => 'Ad Units',
             'content' => '<p>' . __('Use the options provided to configure individual Ad Units.', 'dfpadman') . '</p>' .
                        '<p>' . __('<b>Theme Location</b>: Select the desired location (hook) from the ones provided by your theme. ', 'dfpadman') .
                        '<p>' . __(' <b>Conditionals</b>: Select sections of your site to exclude the Ad Unit.', 'dfpadman')
             ));
    }

    /**
    * register the settings w/ wp api
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function register_settings(){
        register_setting('dfpadman_main', 'dfpadman_main', array($this, '_validate_main'));
        register_setting('dfpadman_positions', 'dfpadman_positions', array($this, '_validate_positions'));
        register_setting('dfpadman_support', 'dfpadman_support', array($this, '_validate_support'));
    }

    /**
    * register the pointer js, and localizes required pointers
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.5
    * @author Russell Fair
    * @param string $hook_suffix the hook to enqueue on
    */
    function pointer_loader( $hook_suffix ) {
        // Get the screen ID
        $screen = get_current_screen();
        $screen_id = $screen->id;

        // Get pointers for this screen
        $pointers = apply_filters( 'admin_pointers_' . $screen_id, array() );

        // No pointers? Then we stop.
        if ( ! $pointers || ! is_array( $pointers ) )
            return;

        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $valid_pointers = array();

        // Check pointers and remove dismissed ones.
        foreach ( $pointers as $pointer_id => $pointer ) {

            // Sanity check
            if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
                continue;

            $pointer['pointer_id'] = $pointer_id;

            // Add the pointer to $valid_pointers array
            $valid_pointers['pointers'][] = $pointer;
        }

        // No valid pointers? Stop here.
        if ( empty( $valid_pointers ) )
            return;

        // Add pointers style to queue.
        wp_enqueue_style( 'wp-pointer' );

        // Add pointers script to queue. Add custom script.
        wp_enqueue_script( 'dfpadman-pointer', plugins_url( 'js/admin-pointer.js', __FILE__ ), array( 'wp-pointer' ) );

        // Add pointer options to script.
        wp_localize_script( 'dfpadman-pointer', 'adminPointer', $valid_pointers );


    }

    /**
    * Registers the pointer seen when the plugin is initially activated
    */
    function register_pointers( $p ) {
        $p['welcome_' . $this->dfpadman_common->dfpadman_version] = array(
            //'target' => '.dfpadman-settings-screen .submit .button',
            'target' => '.tab-main',
            // 'target' => '#menu-settings',
            'options' => array(
                'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                    __( 'Congratulations' ,'dfpadman'),
                    __( '<p>You have installed the <b>DFP Ad Manager Plugin</b>.','dfpadman') .
                    __( '<p>Follow the instructions on this page to insert the DFP header script.','dfpadman')
                ),
                'position' => array( 'edge' => 'top', 'align' => 'left' )
            )
        );

         $p['set_positions_' . $this->dfpadman_common->dfpadman_version] = array(
            //'target' => '.dfpadman-settings-screen .submit .button',
            'target' => '.tab-positions',
            // 'target' => '#menu-settings',
            'options' => array(
                'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                    __( 'Step 2' ,'dfpadman'),
                    __( '<p>Congratulations - your <b>Header Script</b> was successfully added.','dfpadman') .
                    __( '<p>Follow the instructions on this page to configure Ad Units.','dfpadman')
                ),
                'position' => array( 'edge' => 'top', 'align' => 'left' )
            )
        );

        return $p;
    }

    /**
    * register the settings section w/ wp api
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function add_sections(){
        //the _main section
        add_settings_section(
                'dfpadman_head', // ID
                'DFP Ad Manager Main Options', // Title
                array($this, '_main_cb'), // Callback
                'dfpadman_main' // Where
        );

        add_settings_field(
            'dfpadman_header_code',       // ID
            __('Insert DFP Header Script', 'dfpadman'),  // label
             array($this, '_head_code_cb'),   // callback
            'dfpadman_main',   // The page
            'dfpadman_head',   // Section ID
            array(          // Args.
                __('Insert DFP Header Script', 'dfpadman')
            )
        );

        //the _positions section
        add_settings_section(
                'dfpadman_positions', // ID
                'DFP Ad Unit Options', // Title
                array($this, '_positions_cb'), // Callback
                'dfpadman_positions' // Where
        );

        add_settings_field(
            'dfpadman_positions_loop',       // ID
            __('Configure DFP Ad Units', 'dfpadman'),  // label
             array($this, '_positions_loop_cb'),   // callback
            'dfpadman_positions',   // The page
            'dfpadman_positions',   // Section ID
            array(          // Args.
                __('Configure DFP Ad Units', 'dfpadman')
            )
        );

         //the _help section
        add_settings_section(
                'dfpadman_support', // ID
                'DFP Ad Manager Help', // Title
                array($this, '_help_cb'), // Callback
                'dfpadman_support' // Where
        );

        add_settings_field(
            'dfpadman_reset',       // ID
            __('Reset DFP Ad Data', 'dfpadman'),  // label
             array($this, '_reset_cb'),   // callback
            'dfpadman_support',   // The page
            'dfpadman_support',   // Section ID
            array(          // Args.
                __('Check the box to reset DFP Ad Data', 'dfpadman')
            )
        );

    }

    /**
    * the display callback for the dfpadman main settings section information
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function _main_cb($opts){
        echo '<p>';
        _e('Copy and paste the "Header Code" provided by DFP into the box below.', 'dfpadman');
        echo '<p>';
        _e('To generate the header code follow these steps:', 'dfpadman');
        echo '<ol>';
        printf('<li>%s</li>', __('Log into your DFP account and navigate to <b>Inventory</b> then <b>Generate tags</b> page', 'dfpadman'));
        printf('<li>%s</li>', __('Select the ad units you wish to include and click <b>Generate tags</b>', 'dfpadman'));
        printf('<li>%s</li>', __('Copy the contents of the <b>Document header</b> section', 'dfpadman'));
        echo '</ol>';

    }

    /**
    * the positions callback for the dfpadman positions settings section information
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function _positions_cb($opts){
        echo '<p>';
        _e('Configure each <b>Ad Unit</b> selecting the desired options below.', 'dfpadman');
        echo '<p>';
        _e('Please note that the currently active theme must support <b>Theme Hook Alliance</b> or use the <b>Genesis Theme Framework</b>', 'dfpadman');

    }

    /**
    * the support callback for the dfpadman support settings section information
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    */
    function _help_cb($opts){
        _e('Ad Trouble? ', 'dfpadman');
        printf('<a href="%s" title="debug DFP Ads">Click Here to Debug</a>', home_url( '?google_ad_impl=fallback&google_debug') );
        // echo var_dump(get_option('dfpadman_positions'));
    }

    /**
    * validates the main options
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param array $opts the incoming options
    * @return array $opts the options to pass on for saving
    * @todo properly vaidate and (eventually set to be enqueue'd) the head script
    */
    function _validate_main($opts){
       //do something "super duper special" with the head code please
        $options = array();

        if(isset($opts['head'])){
            $dfp_version = self::determine_version($opts['head']);
            $options['dfp_version'] = $dfp_version;
            $options['head_raw'] = self::sanatize_header_script($opts['head'], 'raw');
            $options['head_safe'] = self::sanatize_header_script($opts['head'], 'safe');
            $options['dfp_partner_id'] = self::get_partner_id($opts['head'], $dfp_version);
            $options['dfp_positions'] = self::get_positions($opts['head'], $dfp_version);
        }

        if(!$options['dfp_version']){
            add_settings_error( 'dfpadman-error', 'id-validation', __('There was a problem parsing the head code you provided, the DFP script version could not be determined.', 'dfpadman'), 'error' );
            return false;
        }

        if(!$options['dfp_partner_id'] ){
            add_settings_error( 'dfpadman-error', 'id-validation', __('There was a problem parsing the head code you provided, the DFP partner ID could not be determined.', 'dfpadman'), 'error' );
            return false;
        }

        if(!is_array($options['dfp_positions'])){
            add_settings_error( 'dfpadman-error', 'find-positions', __('There was a problem parsing the head code you provided, no DFP ad positions were found.', 'dfpadman'), 'error' );
            return false;
        }

        self::reset_positions();

        return $options;
    }

    /**
     * Displays error messages related to dfpadman
     * @package WordPress
     * @subpackage DFPAdManager
     * @since 0.3
     * @author Russell Fair
     */
    function admin_notices() {
        settings_errors( 'dfpadman-error' );
    }

    /**
    * validates the positions options
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param array $opts the incoming options
    * @return array $opts the options to pass on for saving
    * @todo properly vaidate the settings...
    */
    function _validate_positions($opts){
        $main = $this->dfpadman_common->main;

        $options = array();

        if($main['dfp_version'] == '2'){
            if(is_array($opts)){
                foreach($opts as $key=>$opt){
                    $options[$key] = array(
                        'dfp_position' => $key,
                        'dfp_rawcode' => $key,
                        'theme_hook' => $opt['theme_location'],
                        'conditionals' => isset($opt['conditionals']) ? array_keys($opt['conditionals']) : false,
                        );
                }
                $opts = $options;
            }
        }
        else if($main['dfp_version'] == '3'){
            if(is_array($opts)){
                foreach($opts as $key=>$opt){
                    $options[$key] = array(
                        'dfp_position' => $key,
                        'dfp_rawcode' => self::get_slotcode($main['head_raw'], $key),
                        'theme_hook' => $opt['theme_location'],
                        'conditionals' => isset($opt['conditionals']) ? array_keys($opt['conditionals']) : false,
                    );
                }

            $opts = $options;
            }
        }

        return $opts;
    }

    /**
    * validates the support options
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    * @param array $opts the incoming options
    * @return array $opts the options to pass on for saving
    */
    function _validate_support($opts){
        $options = array();

        if(isset($opts['reset']) && $opts['reset'] == '1'){
            self::reset_main();
            self::reset_positions();
        }

        return $options;
    }


    /**
    * grabs the raw code from the head script
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    * @param string $script the raw script
    * @param string $tag the specific tag/slot to find
    */
    function get_slotcode($script, $tag){

         preg_match_all(
                "~googletag.defineSlot\((.*?)\)~i",
                $script,
                $positions
        );

         if(!isset($positions[1]))
            return false;

        foreach ($positions[1] as $position){
            if(strpos($position, $tag))
                $tag = $position;
        }

        return $tag;
    }


    /**
    * displays the form on the admin settings page
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function _admin_form(){ ?>
        <div class="wrap dfpadman-settings-screen">
            <div id="icon-dfp" class="icon32">
            </div>
            <h2><?php _e('DFP Ad Manager Options', 'dfpadman'); ?></h2>

            <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'dfpadman_main';  ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=dfpadman&tab=dfpadman_main" class="tab-main nav-tab <?php echo $active_tab == 'dfpadman_main' ? 'nav-tab-active' : ''; ?>"><?php _e('Header Script', 'cmia'); ?></a>
                <a href="?page=dfpadman&tab=dfpadman_positions" class="tab-positions nav-tab <?php echo $active_tab == 'dfpadman_positions' ? 'nav-tab-active' : ''; ?>"><?php _e('Ad Units', 'dfpadman'); ?></a>
                <a href="?page=dfpadman&tab=dfpadman_support" class="tab-help nav-tab <?php echo $active_tab == 'dfpadman_support' ? 'nav-tab-active' : ''; ?>"><?php _e('Help', 'dfpadman'); ?></a>
           </h2>
            <form method="post" action="options.php">
            <?php settings_fields( $active_tab ); ?>
            <?php do_settings_sections( $active_tab ); ?>
            <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
    * the head code field
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function _head_code_cb($opts){
        $main = $this->dfpadman_common->main;
        printf('<textarea name="dfpadman_main[head]" id="dfpadman_main_head">%s</textarea>', esc_textarea($main['head_raw']));
    }

    /**
    * the positions fields loop
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @todo add proper error reporting in instances where main is not set or theme positions are disabled
    */
    function _positions_loop_cb($opts){
        $main = $this->dfpadman_common->main;

        if(!$main || !isset($main['dfp_positions'])){
            printf('<p class="scary">%s</p>', __('No positions available, please confirm that the main options are set properly', 'dfpadman'));
            return;
        }

        $hooks = $this->dfpadman_common->theme_hooks;
        $conditionals = $this->dfpadman_common->conditionals;

        $set = get_option('dfpadman_positions');
		$counter = (int) 0;

        foreach ($main['dfp_positions'] as $dfp_position){
            echo '<div class="dfp_position_wrap">';
            printf('<p><b>%s</b> %s</p>',  $dfp_position, __('ad unit', 'dfpadman'));

            if($hooks){
                //first hooks
                echo '<div class="dfpadman_position_block dfp_position_block_hooks">';
                printf('<label for="dfpadman_positions_location_%s">%s</label>' , $counter, __('<b>Theme Location</b> - Display Ad Unit at', 'dfpadman'));
                printf('<br /><select id="dfpadman_positions_location_%s" name="dfpadman_positions[%s][theme_location]">', $counter, $dfp_position);

                foreach ($hooks as $hook){
                        $selected = ($set[$dfp_position]['theme_hook'] == $hook) ? ' selected="selected"' : '';
                        printf('<option value="%s"%s>%s</option>', str_replace(" ", "_", strtolower($hook)), $selected, $hook);
                }
                echo '</select>';
                echo '</div>';
            }//end hooks

            //now conditionals

            if($conditionals){
                echo '<div class="dfpadman_position_block dfp_position_block_conditionals">';
                printf('<p>%s</p>', __('<b>Conditionals</b> - Exclude Ad Unit on', 'dfpadman'));
                foreach($conditionals as $conditional => $name){
                    $checked = (is_array($set[$dfp_position]['conditionals']) && in_array($conditional, $set[$dfp_position]['conditionals'])) ? ' checked="checked"' : '';
                    printf('<span class="check-wrap"><label for="dfpadman_positions_conditional_%s"></label><input type="checkbox" id="dfpadman_positions_conditional_%s" name="dfpadman_positions[%s][conditionals][%s]" value="1"%s>%s</input></span>', $counter, $counter, $dfp_position, $conditional, $checked, $name);
                }
                echo '</div>';
            }
            echo '</div>';
	        $counter++;
        }
    }

    /**
    * the reset field
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    */
    function _reset_cb($opts){
        printf('<label for="dfpadman_support_reset">%s</label><input type="checkbox" name="dfpadman_support[reset]" id="dfpadman_support_reset" value="1">%s</input>', __('Reset DFP Ad Data', 'dfpadman'), sprintf('<br /><span class="note howto scary">%s</span>', __('If checked, your DFP Ad Manager data will be removed upon save', 'dfpadman')));
        printf('<p class="dfpadman-github">%s %s</p>', __('Questions, Isues, Bugs?', 'dfpadman'), sprintf('<a href="https://github.com/CheriMedia/DFP-Ad-Manager" title="%s">%s</a>', __('DFP Ad Manager on Github', 'dfpadman'), __('DFP Ad Manager on Github')));
    }


    /**
    * sanatizes the header script for safe consumption into the database, and front end display
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $script the head script tag
    * @param string $method the method for filtering
    * @todo make this a filter
    */
    function sanatize_header_script($script, $method = 'safe'){
        switch ($method){
            case 'raw':
                $script = $script;
                break;
            case 'safe':
                $script = esc_html($script);
                break;
        }

        return $script;
    }

    /**
    * do some fancy regex work on the dfp head script to get the dfp implementation version
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $script the DFP version
    * @return string $srv_url the service url
    */
    function determine_version($script){

        //check for version #3 first
        preg_match_all(
            '~document.createElement(.*?);~',
            $script,
            $id
        );

        if(isset($id[1]) && "('script')" == $id[1][0])
            $version = (int) '3';

        if(!$version){
            preg_match_all(
                '~GS_googleAddAdSenseService([^;]*)"(.*?)"([^>]*)~',
                $script,
                $id
            );

            if(isset($id[2]))
                $version = (int) '2';
        }

        return ($version) ? $version : false;
    }

    /**
    * do some fancy regex work on the dfp head script to get the partner ID
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $script the DFP ad head script
    * @return string $ID the partner id
    */
    function get_partner_id($script, $dfp_version){

        if($dfp_version == '3'){
            preg_match_all(
             "~googletag.defineSlot\('/(.*?)/(.*?)'~", $script, $id);

              if(isset($id[1]))
                $id = $id[1][0];
        }

        else if($dfp_version == '2'){
            preg_match_all(
                '~GS_googleAddAdSenseService([^;]*)"(.*?)"([^>]*)~',
                $script,
                $id
            );

            if(isset($id[2]))
                $id = $id[2][0];
        }

        return ($id) ? $id : false;
    }

    /**
    * do some fancy regex work on the dfp head script to get the positions
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $script the DFP ad head script
    * @return array $positions the array of positions
    */
    function get_positions($script, $dfp_version){

        if($dfp_version == '2'){
            preg_match_all(
                '~GA_googleAddSlot([^;]*)", "(.*?)"~',
                $script,
                $positions
            );

            if(!$positions || !isset($positions[2]))
                return false;
            else
                $dfp_pos = array();


            foreach ($positions[2] as $pos){
                $dfp_pos[] = $pos;
            }
        }
        else if($dfp_version == '3'){
            preg_match_all(
                "~googletag.defineSlot\('/(.*?)/(.*?)'~",
                $script,
                $positions
            );

            if(!$positions || !isset($positions[2]))
                return false;

            else
                $dfp_pos = array();

             foreach ($positions[2] as $pos){
                $dfp_pos[] = $pos;
            }
        }
       return $dfp_pos;
    }

    /**
    * resets the dfpadman positions option
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    */
    function reset_positions(){
        delete_option('dfpadman_positions');
    }

    /**
    * resets the dfpadman positions option
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    */
    function reset_main(){
        delete_option('dfpadman_main');
    }

    /**
    * Add a custom stylesheet to make the settings page look decent
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @todo enqueue something decent
    */
    function _admin_css() {
            $main = $this->dfpadman_common;
            wp_enqueue_style('dfpadman-admin-css', $main->dfp_lib_url . '/css/dfpadman-admin.css', array(), $main->dfpadman_version, 'all');
    }

}

