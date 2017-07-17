<?php

/**
 * @param $type
 * Display recently viewed products
 */

function jarvis_options_field_display($type)
{

    switch ($type) {

        case 'input' :
            ?>

            <input type="text" name="jarvis-settings[<?php echo esc_attr($args['type']) ?>]"
                   value="<?php echo esc_attr($settings[$args['type']]) ?>" class="regular-text"/>
            <?php
            break;
        case 'small-input' :
            ?>
            <input type="text" name="jarvis-settings[<?php echo esc_attr($args['type']) ?>]"
                   value="<?php echo esc_attr($settings[$args['type']]) ?>"
                   class="small-text"/>
            <?php _e('px', 'shop-jarvis') ?>
            <?php
            break;
        case 'filter' :
            global $woocommerce; ?>
            <div class="row">
                <div class="col-sm-4">
                    <?php _e("Text", "jarvis"); ?>
                    <a
                            class="header-help help_tip"
                            data-tip="<?php _e('Place your natural language text here.', 'jarvis'); ?>"
                            href="#">[?]</a>
                </div>
                <div class="col-sm-4">
                    <?php _e("Option", "jarvis"); ?>
                    <a
                            class="header-help help_tip"
                            data-tip="<?php _e('Option could be price, category, terms etc.', 'shop-jarvis'); ?>"
                            href="#">[?]</a>
                </div>
                <div class="col-sm-4">
                    <?php _e("Placeholder", "jarvis"); ?>
                    <a
                            class="header-help help_tip"
                            data-tip="<?php _e('Placeholder for option which will be displayed in frontend', 'jarvis'); ?>"
                            href="#">[?]</a>
                </div>
            </div>
            <div class="jarvis_sentence_builder">

                <div class="repeatable">
                    <?php LanguageBuilder::repopulate(); ?>
                </div>
                <div class="row form-group span4">
                    <input type="button" value="<?php _e("Add New Term", "jarvis"); ?>"
                           class="btn button btn-default add"/>
                </div>
            </div>
            <script type="text/template" id="jarvis_sentence_builder">
                <?php echo LanguageBuilder::get_template(); ?>
            </script>
            <?php
            break;
    }
}


// Register the shortcode
add_shortcode("jarvis-recently-viewed-products", "wsaj_recently_viewed_products");
add_shortcode("jarvis-recently-viewed-product-widget", "wsaj_recently_viewed_product_widget");
add_shortcode("jarvis-cart-products", "jarvis_get_cart_products");
add_shortcode("jarvis-recommended-products", "jarvis_get_recommended_products");
add_shortcode("jarvis_featured_products", "jarvis_get_featured_products");


function jarvis_get_featured_products()
{
    global $post;
    $args = array('post_type' => 'product', 'meta_key' => '_featured', 'posts_per_page' => 6, 'columns' => '3', 'meta_value' => 'yes');
    $loop = new WP_Query($args);
    $html .= '<div class="jarvis-featured-products">';
    $html .= '<h2 class="jarvis_title">Featured Products</h2>';
    $html .= '<ul class="jarvis_products">';

    while ($loop->have_posts()) : $loop->the_post();
        global $product;
        $html .= '<li class="jarvis-product">';
        $html .= '<a href="' . get_permalink($loop->post->ID) . '" title="' . esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID) . '">';
        $html .= get_the_post_thumbnail($loop->post->ID, 'shop_catalog') . '</a>
        <div class="jarvis-product-summary">
        <div class="jarvis-product-table">
        <div class="jarvis-product-table-cell">
        <h3 class="jarvis_product_title"><a href="' . get_permalink($loop->post->ID) . '" title="' . esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID) . '">' . get_the_title() . '</a></h3>
        <div class="price">' . $product->get_price_html() . '</div>
        <a href="' . get_permalink($loop->post->ID) . '" title="' . esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID) . '" class="jarvis-button jarvis-button-cart">View Details</a>
        </div>
        </div>
        </div>
        </li>';
    endwhile;
    $html .= '</ul>';
    $html .= '</div>';
    wp_reset_query();

    return $html;
}


function jarvis_get_recommended_products()
{
    if (get_option('recommended_products') != ''):
        $html = '<div class="recommended-products">';
        $html .= '<h2 class="jarvis_title">Featured Products</h2>';
        $html .= do_shortcode("[featured_products per_page='12' columns='6']");
        $html .= '</div>';

        return $html;
    endif;
}

function jarvis_get_cart_products()
{
    $html = '
    <div class="qcld_cart-products">
        <h2 class="jarvis_title">Your Basket</h2>
        <div class="qcld_cart_prod_table" id="qcld_cart_prod_table">


            <div class="qcld_cart_head"><span class="qcld_cart_title">Name</span><span
                        class="qcld_cart_price">Price</span><span
                        class="qcld_cart_qty">Qty</span></div>
            <div class="qcld_cart_prod_table_body">
               ';
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $itemCount = count($items);
    if ($itemCount >= 1) {

        $html .= '
                    <table width="100%" border="0">
                        <tbody>';

        foreach ($items as $item => $values) {
            $_product = $values['data']->post;
            //product image
            $getProductDetail = wc_get_product($values['product_id']);
            $price = get_post_meta($values['product_id'], '_price', true);
            $html .= '
                            <tr>
                                <td class="cartImg">' . $getProductDetail->get_image() . '</td>
                                <td class="cartTitle">' . $_product->post_title . '</td>
                                <td class="cartPrice">' . get_woocommerce_currency_symbol() . $price . '</td>
                                <td class="qcld_cart_qty">' . $values['quantity'] . '</td>
                            </tr>';
        }
        $html .= '</tbody>
                    </table>';
    } else {
        $html .= '<div class="qcld_no_cartprods">You do not have any products in the cart</div>';
    }
    $html .= '</div>

<div class="qcld_checkout_buttons"><a href="' . $woocommerce->cart->get_cart_url() . '"
                                              class="jarvis-button jarvis-button-cart">Cart</a><a
                    href="' . $woocommerce->cart->get_checkout_url() . '" class="jarvis-button jarvis-button-checkout">Checkout</a>
        </div>
        </div>
        
    </div>';

    if (get_option('cart_products')) {
        return $html;
    }

}

function wsaj_track_product_view()
{
    if (!is_singular('product')) {
        return;
    }

    global $post;

    if (empty($_COOKIE['woocommerce_recently_viewed']))
        $viewed_products = array();
    else
        $viewed_products = (array)explode('|', $_COOKIE['woocommerce_recently_viewed']);

    if (!in_array($post->ID, $viewed_products)) {
        $viewed_products[] = $post->ID;
    }

    if (sizeof($viewed_products) > 15) {
        array_shift($viewed_products);
    }

    // Store for session only
    wc_setcookie('woocommerce_recently_viewed', implode('|', $viewed_products));
}

add_action('template_redirect', 'wsaj_track_product_view', 20);
?>
<?php
function extract_shortcode_from_content($the_content)
{

    $shortcode = "";
    $pattern = get_shortcode_regex();
    preg_match_all('/' . $pattern . '/uis', $the_content, $matches);

    for ($i = 0; $i < 40; $i++) {

        if (isset($matches[0][$i])) {
            $shortcode .= $matches[0][$i];
        }

    }
    $shortcode = str_replace("][", "#", $shortcode);
    $shortcode = str_replace("[", "", $shortcode);
    $shortcode = str_replace("]", "", $shortcode);
    $shortcode = explode("#", $shortcode);
    return $shortcode;

}


function wsaj_recently_viewed_products($atts = array(), $content = null, $tag)
{
    // Get WooCommerce Global
    global $post, $woocommerce, $product;
    $recently_viewed_title = get_option('recently_viewed_products_title') ? get_option('recently_viewed_products_title') : 'Recently Viewed Products';
    $font_size = get_option('global_widget_font_size') ? get_option('global_widget_font_size') : '20';
    // Get recently viewed product cookies data
    $viewed_products = !empty($_COOKIE['woocommerce_recently_viewed']) ? (array)explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();
    $viewed_products = array_filter(array_map('absint', $viewed_products));


    // If no data, quit

    //var_dump($viewed_products);
    if (!empty($viewed_products)) {
        $wp_query = new WP_Query(array(
            'posts_per_page' => 10,
            'no_found_rows' => 1,
            'post_status' => 'publish',


            'post_type' => 'product',
            'post__in' => $viewed_products,


        ));

        ob_start();


        $html = '<div class="jarvis_recently_viewed_products">
<h2 class="jarvis_title" style="font-size:' . $font_size . 'px ">' . $recently_viewed_title . '</h2>
<div class="qcld_cart_prod_table" >
<div class="qcld_jarvis_recently_viewed_body">
<ul class="jarvis_product_list">';
        while ($wp_query->have_posts()) : $wp_query->the_post();
            global $post, $product;
            $html .= '<li class="jarvis_product">
            <a href="' . esc_url(get_permalink($product->id)) . '"
               title="' . esc_attr($product->get_title()) . '">
                ' . $product->get_image() . '
            </a>
             <a href="' . esc_url(get_permalink($product->id)) . '"
               title="' . esc_attr($product->get_title()) . '">
                <h3 class="jarvis_product_title">' . $product->get_title() . '</h3>
                </a>
             <span class="price">' . $product->get_price_html() . '</span>';

            if ($product->is_type('simple')) {
                $html .= '<a rel="nofollow" href="' . get_site_url() . '?add-to-cart=' . $product->id . '" data-quantity="1" data-product_id="' . $product->id . '" data-product_sku="" class="add_to_cart_button ajax_add_to_cart jarvis-button jarvis-button-cart">Add to cart</a>';
            } elseif ($product->is_type('variable')) {
                $html .= '<a href="' . esc_url(get_permalink($product->id)) . '" title="' . esc_attr($product->get_title()) . '" class="jarvis-button jarvis-button-cart">View Detail</a>';
            }


            $html .= '<!--<a rel="nofollow" href="' . esc_url(get_permalink($product->id)) . '"
               class="jarvis-button jarvis-button-cart">Add to Cart</a>-->
            </li>';


        endwhile;
        $html .= '</ul></div></div></div>';
        if (get_option('recent_products')) {
            return $html;
        } else {
            $html = '';
            return $html;
        }
        wp_reset_query();
        wp_reset_postdata();
    } else {
        $html = '<div class="jarvis_recently_viewed_products">
<h2 class="jarvis_title" style="font-size:' . $font_size . 'px ">' . $recently_viewed_title . '</h2>
<div class="qcld_cart_prod_table" >
<div class="qcld_jarvis_recently_viewed_body">';

        $html .= '<p style="text-align: center">You have not viewed any products yet !';
        $html .= '</div></div></div>';

        return $html;
    }
}

function wsaj_recently_viewed_product_widget($atts = array(), $content = null, $tag)
{

    shortcode_atts(array(
        'jarvis_container_font_size' => '',
        'title' => '',
        'font_size' => '14px',
        'jarvis_num_recent_products' => ''
    ), $atts);

    // Get WooCommerce Global
    global $post, $woocommerce, $product;
    $recently_viewed_title = get_option('recently_viewed_products_title') ? get_option('recently_viewed_products_title') : 'Recently Viewed Products';
    $font_size = get_option('global_widget_font_size') ? get_option('global_widget_font_size') : '20';
    // Get recently viewed product cookies data
    $viewed_products = !empty($_COOKIE['woocommerce_recently_viewed']) ? (array)explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();
    $viewed_products = array_filter(array_map('absint', $viewed_products));


    // If no data, quit

    //var_dump($viewed_products);
    if (!empty($viewed_products)) {
        $wp_query = new WP_Query(array(
            'posts_per_page' => $atts['jarvis_num_recent_products'],
            'no_found_rows' => 1,
            'post_status' => 'publish',


            'post_type' => 'product',
            'post__in' => $viewed_products,


        ));

        ob_start();


        $html = '<div class="jarvis_recently_viewed_products" style="font-size: 40pt !important">
    <h2 class="jarvis_title" style="font-size:' . $atts['font_size'] . 'pt ">' . $recently_viewed_title . '</h2>
    <div class="qcld_cart_prod_table" >
    <div class="qcld_jarvis_recently_viewed_body">
    <ul class="jarvis_product_list">';
        while ($wp_query->have_posts()) : $wp_query->the_post();
            global $post, $product;
            $html .= '<li class="jarvis_product">
            <a href="' . esc_url(get_permalink($product->id)) . '"
               title="' . esc_attr($product->get_title()) . '">
                ' . $product->get_image() . '
            </a>
             <a href="' . esc_url(get_permalink($product->id)) . '"
               title="' . esc_attr($product->get_title()) . '">
                <h3 class="jarvis_product_title" style="font-size: '.$atts['jarvis_container_font_size'].'pt">' . $product->get_title() . '</h3>
                </a>
             <span class="price" style="font-size: '.$atts['jarvis_container_font_size'].'pt">' . $product->get_price_html() . '</span>';

            if ($product->is_type('simple')) {
                $html .= '<a rel="nofollow" href="' . get_site_url() . '?add-to-cart=' . $product->id . '" data-quantity="1" data-product_id="' . $product->id . '" data-product_sku="" class="add_to_cart_button ajax_add_to_cart jarvis-button jarvis-button-cart">Add to cart</a>';
            } elseif ($product->is_type('variable')) {
                $html .= '<a href="' . esc_url(get_permalink($product->id)) . '" title="' . esc_attr($product->get_title()) . '" class="jarvis-button jarvis-button-cart">View Detail</a>';
            }


            $html .= '<!--<a rel="nofollow" href="' . esc_url(get_permalink($product->id)) . '"
               class="jarvis-button jarvis-button-cart">Add to Cart</a>-->
            </li>';


        endwhile;
        $html .= '</ul></div></div></div>';
        if (get_option('recent_products')) {
            return $html;
        } else {
            $html = '';
            return $html;
        }
        wp_reset_query();
        wp_reset_postdata();
    } else {
        $html = '<div class="jarvis_recently_viewed_products">
    <h2 class="jarvis_title" style="font-size:' . $atts['font_size'] . 'pt ">' . $recently_viewed_title . '</h2>
    <div class="qcld_cart_prod_table" >
    <div class="qcld_jarvis_recently_viewed_body">';

        $html .= '<p style="text-align: center">You have not viewed any products yet !';
        $html .= '</div></div></div>';

        return $html;
    }

}


add_action('wp_footer', 'woojarvis_load_footer_html');
function woojarvis_load_footer_html()
{
    ?>

    <?php if (get_option('disable_jarvis') == 1): ?>

    <a id="genie-lamp" href="#genie-target"><img
                src="<?php echo QC_JARVIS_IMG_URL . '/' . get_option('jarvis_icon'); ?>" alt=""> </a>
    <span class="tooltip-effect-4 qcld_jarvis_tooltip" id="qcld_jarvis_tooltip" data-loop=""><!--<span class="tooltip-item">quasar</span>--><span
                class="tooltip-content">
<ul class="qcld_jarvis_msg" id="qcld_jarvis_msg"
    data-global-timer="<?php echo get_option('global_notification_delay_time'); ?>">
    <?php if (get_option('message_one') != ''): ?>
        <li class="jarvisMsgItem" data-timer=""><?php echo get_option('message_one'); ?></li>
    <?php endif; ?>
    <?php if (get_option('message_two') != ''): ?>
        <li class="jarvisMsgItem" data-timer=""><?php echo get_option('message_two'); ?></li>
    <?php endif; ?>
    <?php if (get_option('message_three') != ''): ?>
        <li class="jarvisMsgItem" data-timer=""><?php echo get_option('message_three'); ?></li>
    <?php endif; ?>
</ul>
</span></span>
    <?php
    include_once('templates/template-three/template.php');
    ?>

<?php endif;
}


add_filter('woocommerce_add_to_cart_fragments', 'qcld_jarvis_ajax_add_to_cart');


do_action('woocommerce_set_cart_cookies', TRUE);


add_action('wp_ajax_get_cart_products', 'qcld_jarvis_ajax_add_to_cart');
add_action('wp_ajax_nopriv_get_cart_products', 'qcld_jarvis_ajax_add_to_cart');
function qcld_jarvis_ajax_add_to_cart()
{
    global $woocommerce;
    $html = '<div class="qcld_cart_head"><span class="qcld_cart_title">Name</span><span
                                    class="qcld_cart_price">Price</span><span
                                    class="qcld_cart_qty">Qty</span></div>
                        <div class="qcld_cart_prod_table_body">';

    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $itemCount = count($items);
    if ($itemCount >= 1) {


        $html .= ' <table width="100%" border="0">
                                    <tbody>';
        foreach ($items as $item => $values) {
            $_product = $values['data']->post;
            //product image
            $getProductDetail = wc_get_product($values['product_id']);
            $price = get_post_meta($values['product_id'], '_price', true);

            $html .= '<tr>
                                            <td class="cartImg">' . $getProductDetail->get_image() . '</td>
                                            <td class="cartTitle">' . $_product->post_title . '</td>
                                            <td class="cartPrice">' . get_woocommerce_currency_symbol() . '' . $price . '</td>
                                            <td class="qcld_cart_qty">' . $values['quantity'] . '</td>
                                        </tr>';
        }
        $html .= '</tbody>
                                </table>';
    } else {
        $html .= '<div class="qcld_no_cartprods">You do not have any products in the cart</div>';
    }
    $html .= '</div>';
    echo $html;


    wp_die();
}