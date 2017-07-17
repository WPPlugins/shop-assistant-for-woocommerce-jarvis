<?php

class QC_Widget_Jarvis
{

    public static function get($atts)
    {
        global $woocommerce;

        if (class_exists('WC_Shortcodes') && method_exists('WC_Shortcodes', 'shortcode_wrapper')) {
            return WC_Shortcodes::shortcode_wrapper(array(__CLASS__, 'output'), $atts, array("class" => "",
                'before' => " ",
                'after' => " "
            ));
        } else {
            return $woocommerce->shortcode_wrapper(array(__CLASS__, 'output'), $atts, array("class" => "",
                'before' => " ",
                'after' => " "
            ));
        }
    }

    /**
     * Output the shortcode.
     *
     * @access public
     *
     * @param array $atts
     *
     * @return void
     */
    public static function output($atts)
    {
        global $woocommerce;

        extract(shortcode_atts(array(
            'title' => "",
            'font_size' => false,
            'secondary_font_size' => ''
        ), $atts));

        $form_fields = get_option('jarvis-terms-fields');
        $button_text = get_option('jarvis-search-button-text');
        if ((!isset($button_text)) || ($button_text == "")) {
            $button_text = "Find Them!";
        }

        if (empty($form_fields)) {
            return;
        } else {
            $form_fields = unserialize($form_fields);
        }
        $i = 0;
        ?>

        <div class="widget widget_qcld_woojarvis">

            <div class="woocommerce-jarvis">

                <?php if ($title != ""): ?>

                    <h2 class="widget-title" <?php if ($font_size) {
                        echo "style='font-size: " . $font_size . "px;'";
                    } ?>> <?php echo $title; ?></h2>
                    <br>
                <?php endif; ?>

                <div class="jarvis-phrase"
                     style="font-size: <?php echo $atts['jarvis_secondary_font_size'] . "px !important;" ?>">
                    <div class="widget-jarvis-inner" style="line-height: 45px;">
                        <?php
                        $field_count = 0;
                        foreach ($form_fields as $form_field) {

                            $text = $form_field["text"];
                            $filter = $form_field["filter"];
                            $priceone = $form_field["priceone"];
                            $pricetwo = $form_field["pricetwo"];
                            $label = $form_field["label"];
                            $sanitized_label = sanitize_title($label);


                            //var_dump($_REQUEST);
                            if ($_REQUEST['sa_min_price'] >= 1) {
                                $priceone = $_REQUEST['sa_min_price'];
                            }


                            if ($_REQUEST['sa_max_price'] >= 1) {
                                $pricetwo = $_REQUEST['sa_max_price'];
                            }


                            if ($filter != "") {

                                $field_count++; ?>
                                <?php echo '<span class="jarvis-phrase-' . $field_count . '">' . $text . '</span>'; ?>
                                <?php
                                if ($filter == "product_cat") {
                                    $woo_categories = get_terms("product_cat", array("hide_empty" => 1));


                                    foreach ($woo_categories as $term) {
                                        $custom_cats[$term->term_id] = $term->name;

                                    }


                                    // var_dump($custom_cats);
                                    //var_dump($_REQUEST['sa_product_cat']);
                                    $data_new_value = "";
                                    $selected_catid = trim($_REQUEST['sa_product_cat']);
                                    if ($selected_catid != "") {
                                        $label = $custom_cats[$selected_catid];
                                        $data_new_value = ' data-new-value="' . $label . '"';
                                    }


                                    ?>
                                    <?php if ($woo_categories) { ?>
                                        <span
                                                class="jarvis-field jarvis-field-select jarvis-field-type-product-category"
                                                data-original-value="<?php echo $label; ?>" <?php if ($color != "#8e9396") {
                                            echo "style='color: $color;'";
                                        }
                                        echo $data_new_value; ?> > <a class="jarvis-field-type-product-category-label"
                                                                      href "#"><?php echo $label; ?></a>
                                            <ul class="jarvis-select">
                                                <li class="jarvis-field-type-product-category-0"><a href="#"
                                                                                                    data-value="any">
                                                        <?php _e("Any", "shop-jarvis"); ?>
                                                    </a></li>
                                                <?php
                                                $term_count = 0;
                                                foreach ($woo_categories as $term) {
                                                    if ($selected_catid == $term->term_id) {
                                                        $extra_class = ' selected';
                                                    }


                                                    $term_count++; ?>
                                                    <li <?php echo (sanitize_title($term->name) == $sanitized_label) ? 'class="selected original jarvis-field-type-product-category-' . $term_count . '"' : 'class="jarvis-field-type-product-category-' . $term_count . $extra_class . '"'; ?> >
                                                        <a
                                                                href "#" data-value="<?php echo $term->term_id; ?>
                                                        "><?php echo $term->name; ?></a> </li>
                                                <?php } ?>
                                            </ul>
        </span>
                                    <?php } ?>
                                    <?php
                                } elseif ($filter == "product_tag") {

                                    if ($_REQUEST['sa_product_tag'] != "") {
                                        $label = $_REQUEST['sa_product_tag'];
                                    }


                                    $woo_tags = get_terms("product_tag", array("hide_empty" => 1));
                                    ?>
                                    <?php if ($woo_tags) { ?>
                                        <span class="jarvis-field jarvis-field-select jarvis-field-type-product-tag"
                                              data-original-value="<?php echo $label; ?>" <?php if ($color != "#8e9396") {
                                            echo "style='color: $color;'";
                                        } ?> > <a class="jarvis-field-type-product-tag-label"
                                                  href "#"><?php echo $label; ?></a>
                                            <ul class="jarvis-select">
                                                <li class="jarvis-field-type-product-tag-0"><a href="#"
                                                                                               data-value="any">
                                                        <?php _e("Any", "shop-jarvis"); ?>
                                                    </a></li>
                                                <?php
                                                $term_count = 0;
                                                foreach ($woo_tags as $term) {
                                                    $term_count++; ?>
                                                    <li <?php echo (sanitize_title($term->name) == $sanitized_label) ? 'class="selected original jarvis-field-type-product-tag-' . $term_count . '"' : 'class="jarvis-field-type-product-tag-' . $term_count . '"'; ?> >
                                                        <a
                                                                href "#" data-value="<?php echo $term->term_id; ?>
                                                        "><?php echo $term->name; ?></a> </li>
                                                <?php } ?>
                                            </ul>
        </span>
                                    <?php } ?>
                                    <?php
                                } elseif ($filter == "price") {
                                    ?>
                                    <span class="jarvis-field jarvis-field-input" <?php if ($color != "#8e9396") {
                                        echo "style='color: $color;'";
                                    } ?> > <span
                                                class="jarvis-field-label"><?php echo get_woocommerce_currency_symbol(); ?></span>
        <input id="jarvis-from-amount" name='sa_min_price' value="<?php echo $priceone; ?>"
               data-original-value="<?php echo $priceone; ?>">
        </span> & <span class="jarvis-field jarvis-field-input" <?php if ($color != "#8e9396") {
                                        echo "style='color: $color;'";
                                    } ?> > <span
                                                class="jarvis-field-label"><?php echo get_woocommerce_currency_symbol(); ?></span>
        <input id="jarvis-to-amount" name='sa_max_price' value="<?php echo $pricetwo; ?>"
               data-original-value="<?php echo $pricetwo; ?>">
        </span>
                                    <?php
                                } else {
                                    $woo_attributes = wc_get_attribute_taxonomies();
                                    $filter_name = false;
                                    if ($woo_attributes) {
                                        foreach ($woo_attributes as $attribute) {
                                            if ($filter == $attribute->attribute_id) {
                                                $filter_name = $attribute->attribute_name;
                                                break;
                                            }
                                        }
                                    }
                                    $attribute_taxonomy_name = wc_attribute_taxonomy_name($filter_name);

                                    $terms = get_terms($attribute_taxonomy_name, array("hide_empty" => 1));
                                    ?>
                                    <span class="jarvis-field jarvis-field-select jarvis-field-type-attribute"
                                          data-original-value="<?php echo $label; ?>" <?php if ($color != "#8e9396") {
                                        echo "style='color: $color;'";
                                    } ?> > <a class="jarvis-field-type-attribute-label"
                                              href "#"><?php echo $label; ?></a>
                                        <ul class="jarvis-select" data-name="sa_filter_<?php echo $filter_name; ?>">
                                            <li><a class="jarvis-field-type-attribute-0" href="#"
                                                   data-value="any">
                                                    <?php _e("Any", "shop-jarvis"); ?>
                                                </a></li>
                                            <?php if ($terms) {
                                                $term_count = 0; ?>
                                                <?php foreach ($terms as $term) {
                                                    $term_count++; ?>
                                                    <li <?php echo (sanitize_title($term->name) == $sanitized_label) ? "class='selected jarvis-field-type-attribute-" . $term_count . "'" : "class='jarvis-field-type-attribute-" . $term_count . "'"; ?>>
                                                        <a
                                                                href "#" data-value="<?php echo $term->term_id; ?>
                                                        "><?php echo $term->name; ?></a></li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
        </span>
                                    <?php
                                }
                                ?>
                                <?php
                            }

                        }
                        ?>
                        <span class="jarvis-find"
                              style="line-height: 100%;padding: 5px 15px;background: #00a2ca;color: #fff;font-weight: 600;"><?php echo $button_text; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


}



