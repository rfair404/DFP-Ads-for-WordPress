<?php
class DFPADMAN_Display{

    private $complete_tags;
    private $conditionals;
    private $positions;

    /**
    * the class constructor
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
     function __construct(DFPADMAN_Common $dfpadman_common){
        $this->dfpadman_common = $dfpadman_common;
        add_action('wp_head', array($this, 'head_scripts'));
        add_action('wp', array($this, 'set_conditionals'));
        add_action('template_redirect', array($this,'create_ads'));
        add_action('template_redirect', array($this,'setup_filters'));
    }

    /**
    * adds the markup filters before and after the tags
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    */
    function setup_filters(){
        $main = $this->dfpadman_common->main;

        if($main['dfp_version'] == '2'){
            add_filter('dfpadman_v2tag_before', array($this, 'v2_markup_before'), 10, 2);
            add_filter('dfpadman_v2tag_after', array($this, 'v2_markup_after'), 20, 2);
        }
        if($main['dfp_version'] == '3'){
            add_filter('dfpadman_v3tag_before', array($this, 'v3_markup_before'), 10, 2);
            add_filter('dfpadman_v3tag_after', array($this, 'v3_markup_after'), 20, 2);
        }

    }

    /**
    * prints the "other" scripts
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function head_scripts(){
        $main = $this->dfpadman_common->main;

        if(!$main)
            return false;

        if($main['dfp_version'] == '2')
            self::v2_head();

        else if($main['dfp_version'] == '3')
            self::v3_head();
    }

     /**
    * prints the version 2 script tags
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function v2_head(){
        $main = $this->dfpadman_common->main;
        $positions = $this->dfpadman_common->positions;
        /* V2 TEMPLATE */
        ?>
<script type='text/javascript' src='http://partner.googleadservices.com/gampad/google_service.js'>;
</script>
<script type='text/javascript'>
GS_googleAddAdSenseService("<?php echo $main['dfp_partner_id'];?>");
GS_googleEnableAllServices();
</script>
<script type='text/javascript'>
<?php foreach ($positions as $position){ ?>
    GA_googleAddSlot("<?php echo $main['dfp_partner_id']; ?>", "<?php echo $position['dfp_rawcode']; ?>");
<?php } ?>
</script>
<script type='text/javascript'>
GA_googleFetchAds();
</script>
        <?php
    }

    /**
    * prints the version 2 script tags
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function v3_head(){
        $main = $this->dfpadman_common->main;
        $positions = $this->dfpadman_common->positions;

        /* V3 TEMPLATE */
        ?>
<script type='text/javascript'>
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
(function() {
var gads = document.createElement('script');
gads.async = true;
gads.type = 'text/javascript';
var useSSL = 'https:' == document.location.protocol;
gads.src = (useSSL ? 'https:' : 'http:') +
'//www.googletagservices.com/tag/js/gpt.js';
var node = document.getElementsByTagName('script')[0];
node.parentNode.insertBefore(gads, node);
})();
</script>
<script type='text/javascript'>
googletag.cmd.push(function() {
<?php if(is_array($positions)){
	foreach ($positions as $position){ ?>googletag.defineSlot(<?php echo $position['dfp_rawcode']; ?>).addService(googletag.pubads());
	<?php }
	} ?>
googletag.pubads().enableSingleRequest();
googletag.enableServices();
});
</script>
    <?php
    }

    /**
    * sets the conditional tag array
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function set_conditionals(){
        $conditionals = array();

        if(is_home())
            $conditionals[] = 'is_home';
        if(is_archive())
            $conditionals[] = 'is_archive';
        if(is_tax())
            $conditionals[] = 'is_tax';
        if(is_author())
            $conditionals[] = 'is_author';
        if(is_single())
            $conditionals[] = 'is_single';
        if(is_page())
            $conditionals[] = 'is_page';

        $this->conditionals = apply_filters('dfpadman_conditionals', $conditionals);
    }

    /**
    * adds the ad tags to the proper position
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    */
    function create_ads(){
        $main = $this->dfpadman_common->main;
        $positions = $this->dfpadman_common->positions;
        $conditionals = $this->conditionals;
		if(!$positions || !is_array($positions))
	        return;

        foreach ($positions as $tag=>$position){
            $pos = $position['dfp_rawcode'];

            $display = true;
            if(is_array($position['conditionals'])){
                foreach($conditionals as $condition){
                if(in_array($condition, $position['conditionals']))
                    $display = false;
                }
            }


           if($display){
                if($main['dfp_version'] == '2'){
                    add_action($position['theme_hook'], create_function('', 'echo apply_filters("dfpadman_v2tag_before", "", '.$pos.') ."<script>GA_googleFillSlot(\"' . $pos . '\");</script>" . apply_filters("dfpadman_v2tag_after");'));
                }

                if($main['dfp_version'] == '3'){
                    $parts = explode(', ', $pos);
                    add_action($position['theme_hook'], create_function('', 'echo apply_filters("dfpadman_v3tag_before", "", ' . $parts[3]. ');'));
                    add_action($position['theme_hook'], create_function('', 'echo "<script>googletag.cmd.push(function() { googletag.display(' . $parts[3] . '); });</script>";'));
                    add_action($position['theme_hook'], create_function('', 'echo apply_filters("dfpadman_v3tag_after", "", ' . $parts[3]. ');'));
                }
            }

        }
    }

    /**
    * generates the html that appears before the ads in v2
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    * @author Russell Fair
    */
    function v2_markup_before($markup, $tag){
        $markup .= sprintf('<!--start dfpadman tag --><div class="dfpadman_adtag" id="dfpadman_tag-%s">', $tag);
        return $markup;
    }

    /**
    * generates the html that appears after the ads in v2
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    * @author Russell Fair
    */
    function v2_markup_after($markup, $tag){
       $markup .= '</div><!--end dfpadman tag-->';
       return $markup;
    }

    /**
    * generates the html that appears before the ads in v3
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    */
    function v3_markup_before($markup, $tag){
        $tag = esc_attr(str_replace("'", "", $tag));
        $markup .= sprintf('<!--start dfpadman tag --><div class="dfpadman_adtag" id="%s">', $tag);
        return $markup;
    }

    /**
    * generates the html that appears after the ads in v3
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.2
    * @author Russell Fair
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    */
    function v3_markup_after($markup, $tag){
       $markup .= '</div><!--end dfpadman tag-->';
       return $markup;
    }


    /**
    * prints the ad code for a given tag
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    */
    function do_single_tag($markup, $tag){
        $positions = $this->dfpadman_common->positions;

        if(!$positions)
            return false;

        $markup .= self::markup_code($positions[$tag]);

        return $markup;
    }

    /**
    * checks if the ad should appear given conditionals
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param string $markup the html to filter
    * @param string $tag the dfp tag position
    * @return string $markup the filtered html
    * @deprecated since 0.1-alpha
    */
    function check_conditionals($markup, $tag){
        $conditionals = $this->conditionals;
        $positions = $this->dfpadman_common->positions;

        foreach($conditionals as $condition){
        if(in_array($condition, $positions[$tag]['conditionals']))
            $markup = sprintf('<!-- %s -->', __('dfpadman tag removed due to conditional block', 'dfpadman'));
        }

        return $markup;
    }

    /**
    * marks up an individual ad tag script code in html
    * @package WordPress
    * @subpackage DFPAdManager
    * @since 0.1-alpha
    * @author Russell Fair
    * @param array $position the position tag array
    * @return string $code the script tag code
    * @deprecated since 0.1-alpha
    */
    function markup_code($position){
        $main = $this->dfpadman_common->main;

        if(!$main)
            return false;

        $code = sprintf('<script>GA_googleFillSlot("%s", "%s");</script>', $main['dfp_partner_id'], $position['dfp_position']);
        return esc_js($code);
    }

}
