<?php

class DFPADMAN_Widget extends WP_Widget{

    /**
    * constructor
    */
    function DFPADMAN_Widget(){
        $widget_ops = array( 'classname' => 'dfpadman-widget', 'description' => __('DFP Ad Widget', 'dfpadman') );
        $control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'dfpadman-ad' );
        $this->WP_Widget( 'dfpadman-ad', __('DFP Ad Widget', 'dfpadman'), $widget_ops, $control_ops );
    }

    /**
    * The Widget Output Code
    * @package WordPress
    * @subpackage DFPAdManager
    * @author Russell Fair
    * @since 0.1-alpha
    * @param array $args the widget args
    * @param array $instance this widget instance attributes
    */
    function widget($args, $instance){
        extract($args);
        $main = get_option('dfpadman_main');
        $pos = get_option('dfpadman_positions');

        if(!$instance['position'] || !isset($pos[$instance['position']]))
            return false;

        echo $before_widget;
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
        if($title){
            echo $before_title .  $title . $after_title;
        }

        if($main['dfp_version'] == '2'){
            echo apply_filters("dfpadman_tag_before");
            ?><script>GA_googleFillSlot("<?php echo $instance['position']; ?>");</script><?php
            echo apply_filters("dfpadman_tag_after");
        }
        if($main['dfp_version'] == '3'){
            $parts = explode(', ', $pos[$instance['position']]['dfp_rawcode']);
            echo apply_filters("dfpadman_v3tag_before", "", $parts[3]);
            //echo var_dump($parts);
            //clever but won't work on multiple widgetized tags
            // do_action('widgetize_this_tag');
            ?><script type='text/javascript'>googletag.cmd.push(function() { googletag.display(<?php echo $parts[3]; ?>); });</script><?php

            echo apply_filters("dfpadman_v3tag_after", "", $parts[3]);
        }

        echo $after_widget;
    }
    /**
    * The widget save function
    * @package WordPress
    * @subpackage DFPAdManager
    * @author Russell Fair
    * @since 0.1-alpha
    * @param array $new_instance the updated options
    * @param array $old_instance the previous options
    * @return array $new_instance the saved instance options
    */
    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    /**
    * The Widget Form
    * @package WordPress
    * @subpackage DFPAdManager
    * @author Russell Fair
    * @since 0.1-alpha
    * @param array $instance this widget options
    */
     function form($instance) {
        $instance = wp_parse_args((array)$instance, array(
            'title' => '',
            'position' => false,
        ));
        $instance['title'] = (!empty($instance['title'])) ? $instance['title'] : '' ; ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'dfpadman'); ?>:</label>
        <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" size="20" style="float: right;" /><br />
        <span class="howto" style="clear:both;"><?php _e('Enter the widget title as you wish it to appear on the site', 'dfpadman'); ?></span></p>

        <p><label for="<?php echo $this->get_field_id('position'); ?>"><?php _e('DFP Ad Tag', 'dfpadman'); ?>:</label>
        <select id="<?php echo $this->get_field_id('position'); ?>" name="<?php echo $this->get_field_name('position'); ?>">
            <option value="<?php echo esc_attr( $instance['position'] ); ?>"><?php echo esc_attr( $instance['position'] ); ?></option>
        <?php
            $positions = get_option('dfpadman_positions');
            foreach ($positions as $position){
                if($position['theme_hook'] == 'widgetize_this_tag')
                    printf('<option value="%s">%s</option>', $position['dfp_position'], $position['dfp_position']);
            }
            ?>

        </select>
        <span class="howto" style="clear:both;"><?php _e('Select the DFP Ad Tag you wish to display in this widget', 'dfpadman'); ?></span></p>
        <?php

    }
}
