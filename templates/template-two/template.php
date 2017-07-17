<div id="genie-target">
    <div id="jarvis_body">
        <button id="btn-close-modal" value="x" class="jarvis_close close-genie-target">x</button>
        <?php //echo do_shortcode("[qc_jarvis]");
        ?>
        <?php $grid_data = htmlspecialchars_decode(stripslashes(get_option('grid_items'))); ?>
        <?php $shortcodes = extract_shortcode_from_content($grid_data);
        foreach ($shortcodes as $shortcode) {
            $shortcodeToHtml[$shortcode] = do_shortcode('[' . $shortcode . ']');
            $grid_data = str_replace('[' . $shortcode . ']', $shortcodeToHtml[$shortcode], $grid_data);
        }
        echo $grid_data;
        // echo "<pre>";
        //var_dump( $shortcodeToHtml);
        // echo "</pre>";
        ?>
        <?php //echo do_shortcode(extract_shortcode_from_content(get_option('grid_items')));
        ?>

    </div>
</div>
