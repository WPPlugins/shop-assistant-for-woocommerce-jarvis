<?php
/**
 * Plugin Name: WooCommerce Shop Assistant JARVIS
 * Plugin URI: https://wordpress.org/plugins/shop-assistant-for-woocommerce-jarvis/
 * Description: WooCommerce shops now have JARVIS - unique shop assistant to increase sales! PopUp Message, Recently Viewed, Advanced Search all in 1 window!
 * Version: 1.3.6
 * Author: QuantumCloud
 * Author URI: https://www.quantumcloud.com/
 * Requires at least: 3.0
 * Tested up to: 4.7.5
 * Text Domain: jarvis
 * Domain Path: /language/
 * License: GPL2
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('QC_JARVIS_VERSION', '1.3.1');
define('QC_JARVIS_REQUIRED_WOOCOMMERCE_VERSION', 2.2);
define('QC_JARVIS_PLUGIN_DIR_PATH', basename(plugin_dir_path(__FILE__)));
define('QC_JARVIS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QC_JARVIS_IMG_URL', QC_JARVIS_PLUGIN_URL . "/images");

require_once("functions.php");


/**
 * Main Class.
 */
class WC_Jarvis
{

    private $id = 'jarvis';

    private static $instance;

    /**
     *  Get Instance creates a singleton class that's cached to stop duplicate instances
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     *  Construct empty on purpose
     */

    private function __construct()
    {
    }

    function register_widgets()
    {

        register_widget('WC_Widget_jarvis');
        register_widget('WC_Widget_Jarvis_Recent_Viewed_Products');
    }

    /**
     *  Init behaves like, and replaces, construct
     */

    public function init()
    {

        // Check if WooCommerce is active, and is required WooCommerce version.
        if (!class_exists('WooCommerce') || version_compare(get_option('woocommerce_db_version'), QC_JARVIS_REQUIRED_WOOCOMMERCE_VERSION, '<')) {
            add_action('admin_notices', array($this, 'woocommerce_inactive_notice'));
            return;
        }

        $this->general_includes();

        add_action('admin_menu', array($this, 'wsaj_admin_menu'));

        add_action('widgets_init', array($this, 'register_widgets'));

        if ((!empty($_GET["page"])) && ($_GET["page"] == "jarvis")) {

            add_action('woocommerce_init', array($this, 'wsaj_backend_includes'));

            add_action('admin_init', array($this, 'wsaj_save_options'));
        }

        add_action('admin_enqueue_scripts', array($this, 'wsaj_admin_scripts'));

        if (!is_admin()) {

            add_action('wp_enqueue_scripts', array($this, 'wsaj_frontend_scripts'));
        }

        add_shortcode('qc_jarvis', array($this, 'wsaj_frontend_shortcode'));
        add_shortcode('qc_jarvis_widget', array($this, 'wsaj_widget_shortcode'));

        add_filter('pre_get_posts', array($this, 'wsaj_search_query'));

        add_filter('loop_shop_post_in', array($this, 'wsaj_price_filter'));
    }


    /**
     * Add a submenu item to the WooCommerce menu
     */
    public function wsaj_admin_menu()
    {

        add_submenu_page('woocommerce',
            __('JARVIS-Woo', 'jarvis'),
            __('JARVIS-Woo', 'jarvis'),
            'manage_woocommerce',
            $this->id,
            array($this, 'wsaj_admin_page'));

    }

    /**
     * Include backend required files.
     *
     * @return void
     */
    public function wsaj_backend_includes()
    {
        include_once('classes/jarvis-class-language-builder.php');


    }

    /**
     * Include general required files.
     *
     * @return void
     */
    public function general_includes()
    {

        // Shortcode Class
        include_once('classes/shortcodes/class-shortcode-qc-jarvis.php');
        include_once('classes/shortcodes/class-widget-shortcode.php');

        //Widget Class
        include_once('classes/widgets/class-wc-widget-jarvis.php');
        include_once('classes/widgets/class-wc-widget-jarvis-recent-viewed-products.php');


    }


    /**
     * Include admin scripts
     */
    public function wsaj_admin_scripts($hook)
    {
        global $woocommerce, $wp_scripts;

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if (((!empty($_GET["page"])) && ($_GET["page"] == "jarvis")) || ($hook == "widgets.php")) {

            wp_enqueue_style('wp-color-picker');


            wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');

            wp_register_style('jarvis-backend-style', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/admin-style.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('jarvis-backend-style');

            wp_register_style('font-awesome', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/font-awesome.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('font-awesome');

            wp_register_style('font-awesome-animation', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/font-awesome-animation.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('font-awesome-animation');

            wp_register_style('sweetalert2-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/sweetalert2.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('sweetalert2-css');

            wp_register_style('select2-style', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/select2.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('select2-style');

            wp_register_style('grideditor', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/grideditor.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('grideditor');

            wp_register_style('tabs-jarvis', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/tabs-jarvis.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('tabs-jarvis');

            wp_register_style('grideditor-demo', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/grideditor-demo.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('grideditor-demo');



            wp_register_script('cbpFWTabs', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/cbpFWTabs.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('cbpFWTabs');

            wp_register_script('sweetatert2-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/sweetalert2.min.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('sweetatert2-js');

            wp_register_script('modernizr-custom', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/modernizr.custom.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('modernizr-custom');

            wp_register_script('jquery-ui', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery-ui.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('jquery-ui');

            wp_register_script('jquery-grideditor', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.grideditor.js', basename(__FILE__)), array('jquery'), true);
            wp_enqueue_script('jquery-grideditor');

            wp_register_script('select2', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/select2.full.min.js', basename(__FILE__)), array('jquery'), true);
            wp_enqueue_script('select2');


            wp_register_script('grideditor-demo', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.grideditor-demo.min.js', basename(__FILE__)), array('jquery'), true);
            wp_enqueue_script('grideditor-demo');


            wp_register_script('bootstrap-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/bootstrap.js', basename(__FILE__)), array('jquery'), true);
            wp_enqueue_script('bootstrap-js');

            wp_register_style('qc-layout', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/qc-layout.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('qc-layout');

            wp_register_style('bootstrap-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/bootstrap.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('bootstrap-css');

            wp_register_script('jarvis-repeatable', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.repeatable.js', basename(__FILE__)), array('jquery'));
            wp_enqueue_script('jarvis-repeatable');

            wp_register_script('jarvis-admin', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jarvis-admin.js', basename(__FILE__)), array('jquery', 'wp-color-picker'), true);
            wp_enqueue_script('jarvis-admin');

            wp_register_script('jquery-tiptip', $woocommerce->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array('jquery'), $woocommerce->version, true);
            wp_enqueue_script('jquery-tiptip');

            $jarvis_params = array(
                'currency_symbol' => get_woocommerce_currency_symbol(),
                'default_button_text' => __("Find Them!", "jarvis")
            );

            wp_localize_script('shop-jarvis', 'qc_jarvis_params', $jarvis_params);

        }

    }


    public function wsaj_frontend_scripts()
    {
        global $woocommerce, $wp_scripts;

        if (get_option("permalink_structure") == "") {
            $shop_url = get_post_type_archive_link('product');
        } else {
            $shop_url = get_permalink(get_option('woocommerce_shop_page_id'));
        }
        $notifications = array(
            array('message' => get_option('message_one'), 'delay' => get_option('notification_delay_one')),
            array('message' => get_option('message_two'), 'delay' => get_option('notification_delay_two')),
            array('message' => get_option('message_three'), 'delay' => get_option('notification_delay_three')),

        );

        $jarvis_params = array(
            'shop_url' => $shop_url,
            'jarvis_pop_up_form_effect' => get_option("jarvis_form_animation"),
            'position_x' => get_option('position_x'),
            'position_y' => get_option('position_y'),
            'jarvis_notifications' => json_encode($notifications)
        );


        wp_register_script('animatedModal.min', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/animatedModal.min.js', basename(__FILE__)), array('jquery'), QC_JARVIS_VERSION, true);
        wp_enqueue_script('animatedModal.min');

        wp_register_script('jarvis-frontend', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jarvis-frontend.js', basename(__FILE__)), array('jquery'), QC_JARVIS_VERSION, true);
        wp_enqueue_script('jarvis-frontend');

        wp_register_script('slimscroll-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.slimscroll.min.js', basename(__FILE__)), array('jquery'), QC_JARVIS_VERSION, true);
        wp_enqueue_script('slimscroll-js');

        wp_register_script('jquery-cookie', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.cookie.js', basename(__FILE__)), array('jquery'), QC_JARVIS_VERSION, true);
        wp_enqueue_script('jquery-cookie');


        wp_localize_script('jarvis-frontend', 'qc_jarvis_params', $jarvis_params);

        wp_localize_script('jarvis-frontend', 'ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php')));


        wp_register_style('qc-layout', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/qc-layout.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
        wp_enqueue_style('qc-layout');


        wp_register_style('animate-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/animate.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
        wp_enqueue_style('animate-css');


        wp_register_style('animatemodal-min-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/animatemodal.min.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
        wp_enqueue_style('animatemodal-min-css');


 
        wp_register_style('tooltip-classic-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/tooltip-classic.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
        wp_enqueue_style('tooltip-classic-css');


        wp_register_style('jarvis-frontend', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/frontend-style.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
        wp_enqueue_style('jarvis-frontend');

        $theme_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', get_option('jarvis_theme'));
        if ($theme_name == 'theme-one') {
            wp_register_style('jarvis-template', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/jarvis-template-01.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('jarvis-template');

        } else if ($theme_name == 'theme-two') {
            wp_register_style('jarvis-template', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/jarvis-template-02.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('jarvis-template');

        } else if ($theme_name == 'theme-three') {
            wp_register_style('jarvis-template', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/jarvis-template-03.css', basename(__FILE__)), '', QC_JARVIS_VERSION, 'screen');
            wp_enqueue_style('jarvis-template');

        }

        wp_localize_script('jarvis-frontend', 'qc_jarvis_params', $jarvis_params);

    }


    /**
     * Render the admin page
     */
    public function wsaj_admin_page()
    {

        global $woocommerce;

        $action = 'admin.php?page=jarvis'; ?>
        <div class="jarvis-wrap wrap">

            <div class="icon32"><br></div>
            <h2><?php echo __('Jarvis Control Panel', 'jarvis'); ?></h2>

            <form action="<?php echo esc_attr($action); ?>" method="POST" enctype="multipart/form-data">

                <div class="container form-container">
                    <section class="jarvis-tab-container-inner">
                        <div class="tabs-jarvis tabs-jarvis-style-flip">
                            <nav>
                                <ul>
                                    <li><a href="#section-flip-4"><i class="fa fa-search"></i> <span> NATURAL SEARCH SETTINGS</span></a>
                                    </li>
                                    <li><a href="#section-flip-2"><i
                                                    class="fa fa-toggle-on"></i><span> OTHERS SETTINGS</span></a></li>
                                    <li><a href="#section-flip-3"><i class="fa fa-gear faa-spin animated"></i><span> ICONS & THEME SETTINGS</span></a>
                                    </li>
                                    <li><a href="#section-flip-5"><i
                                                    class="fa fa-bell"></i><span> MESSAGE CENTER</span></a></li>
                                    <li><a href="#section-flip-5"><i
                                                    class="fa fa-square-o"></i><span> CUSTOM ELEMENTS</span></a></li>
                                </ul>
                            </nav>
                            <div class="content-wrap">
                                <section id="section-flip-2">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <p class="qc-opt-description">
                                                <?php _e('Construct your search query here. Add search terms here. You can connect each terms with natural language using the text fields', 'jarvis'); ?>
                                            </p>
                                            <td>
                                                <div class="cxsc-settings-blocks">
                                                    <div class="phrase-example-holder">
                                                        <div class="phrase-example">
                                                            <div class="search-phrase btn alert alert-success"></div>
                                                            <div class="phrase-example-none pre-search-phrase">
                                                                <?php _e('Start building your search terms here', 'jarvis'); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="cxsc-settings-block" id="cxsc-settings-block-general">
                                                        <?php jarvis_options_field_display('filter'); ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    <?php _e('Rename Search Button Text', 'jarvis'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <?php
                                                    $jarvis_button_text = get_option('jarvis-search-button-text');
                                                    if ((!isset($jarvis_button_text)) || ($jarvis_button_text == "")) {
                                                        $jarvis_button_text = __("Find Them!", "jarvis");
                                                    } ?>
                                                    <input type="text" class="jarvis-search-button-text"
                                                           name="jarvis-search-button-text"
                                                           id="jarvis-search-button-text"
                                                           value="<?php echo htmlentities($jarvis_button_text, ENT_QUOTES); ?>">
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </section>
                                <section id="section-flip-3">
                                    <table class="table table-bordered striped">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    <?php _e('Display Cart Items On Pop Up Window', 'jarvis'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <fieldset>
                                                        <input id="ham" type="checkbox" name="cart_products"
                                                               value="cart_products" <?php echo(get_option('cart_products') == 'cart_products' ? 'checked' : ''); ?>>
                                                        <label for="ham">Enable Cart Items</label>


                                                    </fieldset>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    <?php _e('Display Featured Products On Pop Up Window', 'jarvis'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input id="pepperoni" type="checkbox" name="recommended_products"
                                                           value="recommended_products" <?php echo(get_option('recommended_products') == 'recommended_products' ? 'checked' : ''); ?>>
                                                    <label for="pepperoni">Enable Featured Products</label>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    <?php _e('Display Recently Viewed Products On Pop Up Window', 'jarvis'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input id="mushrooms" type="checkbox" name="recent_products"
                                                           value="recent_products" <?php echo(get_option('recent_products') == 'recent_products' ? 'checked' : ''); ?>>
                                                    <label for="mushrooms">Enable Recent Viewed Products</label>
                                                </div>
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    Repeat pop up notification through out user's stay on the website -
                                                    <strong class="label label-danger">Pro
                                                        version only</strong>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input id="loop_notification" disabled="disabled" type="checkbox"
                                                           value="loop_notification">
                                                    <label for="loop_notification">Enable Notification Loop</label>
                                                </div>
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    Disable Jarvis Icon
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input id="disable_jarvis" type="checkbox" name="disable_jarvis"
                                                           value="1" <?php echo(get_option('disable_jarvis') == '' ? 'checked' : ''); ?>>
                                                    <label for="disable_jarvis">Disable JARVIS Floating Icon</label>
                                                </div>
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    Display last purchased product - <strong class="label label-danger">Pro
                                                        version only</strong>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input id="sold_product" type="checkbox" disabled="disabled"
                                                           name="last_purchased_product"
                                                           value="last_purchased_product">
                                                    <label for="sold_product">Show Last Purchased Product</label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="qc-opt-description">
                                                    Override Icon Position - <strong class="label label-danger">Pro
                                                        version only</strong>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input type="number" class=""
                                                           name="position_x" disabled="disabled"
                                                           id=""
                                                           value=""
                                                           placeholder="From Right In Px"> px
                                                    <input type="number" class=""
                                                           name="position_y"
                                                           id=""
                                                           value=""
                                                           disabled="disabled"
                                                           placeholder="From Bottom In Px"> px
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </section>
                                <section id="section-flip-4">
                                    <table class="table table-bordered striped">
                                        <tbody>
                                        <tr>
                                            <td><br><br>
                                                <ul class="radio-list">
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-0.png" alt="">
                                                        <input type="radio"
                                                               name="jarvis_icon" <?php echo(get_option('jarvis_icon') == 'icon-0.png' ? 'checked' : ''); ?>
                                                               value="icon-0.png">Icon - 0
                                                    </li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-1.png" alt="">
                                                        <input type="radio"
                                                               name="jarvis_icon" <?php echo(get_option('jarvis_icon') == 'icon-1.png' ? 'checked' : ''); ?>
                                                               value="icon-1.png">Icon - 1
                                                    </li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-2.png" alt="">
                                                        <input type="radio"
                                                               name="jarvis_icon" <?php echo(get_option('jarvis_icon') == 'icon-2.png' ? 'checked' : ''); ?>
                                                               value="icon-2.png">Icon - 2
                                                    </li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-3.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 3
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-4.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 4
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-5.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 5
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-6.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 6
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-7.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 7
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-8.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 8
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-9.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 9
                                                        <strong
                                                                class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-10.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 10
                                                        <strong class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-11.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 11
                                                        <strong class="label label-danger">Pro version</strong></li>
                                                    <li><img src="<?php echo QC_JARVIS_IMG_URL; ?>/icon-12.png" alt="">
                                                        <input type="radio" disabled="disabled" value="">Icon - 12
                                                        <strong class="label label-danger">Pro version</strong></li>

                                                </ul>
                                            </td>
                                        </tr>
                                        <br><br>
                                        <tr>
                                            <td><p class="qc-opt-description">

                                                    <strong>Upload custom Icon</strong> - <strong
                                                            class="label label-danger">Pro version only</strong>
                                                </p>

                                            </td>
                                        </tr>


                                        <tr>
                                            <td>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <img class="thumbnail"
                                                             src="<?php echo QC_JARVIS_IMG_URL; ?>/theme-one.jpg"
                                                             alt="">
                                                        <input type="radio"
                                                               name="jarvis_theme" <?php echo(get_option('jarvis_theme') == 'theme-one.jpg' ? 'checked' : ''); ?>
                                                               value="theme-one.jpg">Theme One

                                                    </div>

                                                    <div class="col-sm-4">
                                                        <img class="thumbnail"
                                                             src="<?php echo QC_JARVIS_IMG_URL; ?>/theme-two.jpg"
                                                             alt=""> <input type="radio"
                                                                            name="jarvis_theme" <?php echo(get_option('jarvis_theme') == 'theme-two.jpg' ? 'checked' : ''); ?>
                                                                            value="theme-two.jpg">Theme Two

                                                    </div>

                                                    <div class="col-sm-4">
                                                        <img class="thumbnail"
                                                             src="<?php echo QC_JARVIS_IMG_URL; ?>/theme-three.jpg"
                                                             alt="">
                                                        <input type="radio"
                                                               name="jarvis_theme" <?php echo(get_option('jarvis_theme') == 'theme-three.jpg' ? 'checked' : ''); ?>
                                                               value="theme-three.jpg">Theme Three
                                                    </div>
                                                </div>


                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </section>
                                <section id="section-flip-5">

                                    <h5>Global Notification Delay Time : <input name="global_notification_delay_time"
                                                                                type="text"
                                                                                class="text-control input-sm"
                                                                                value="<?php echo (get_option('global_notification_delay_time')) ? get_option('global_notification_delay_time') : ''; ?>">
                                        in sec
                                    </h5>
                                    <hr>
                                    <table class="table table-bordered striped">
                                        <tbody>
                                        <tr>
                                            <td>

                                                <h3 class="alert alert-success">SET NOTIFICATIONS FOR JARVIS</h3>


                                            </td>
                                        </tr>
                                        <tr>
                                            <td><p class="qc-opt-description"></p>
                                                <div class="cxsc-settings-blocks">
                                                    <p>
                                                        <?php $settings = array('textarea_name' =>
                                                            'message_one',
                                                            'textarea_rows' => 20,
                                                            'editor_height' => 100,
                                                            'editor_class' => 'customNotificationClass',
                                                            'disabled' => 'disabled',
                                                            'media_buttons' => false
                                                        );
                                                        wp_editor(get_option(html_entity_decode(stripcslashes('message_one'))), 'message_one', $settings); ?>


                                                </div>
                                                <br>
                                                <p>Message display duration time - <strong class="label label-danger">Pro
                                                        version only</strong></p>
                                                <p><input class="form-control input-sm" type="text"
                                                          value="" disabled="disabled"
                                                          name="notification_delay_one"> <strong> in second</strong></p>
                                            </td>


                                        </tr>
                                        <tr>
                                            <td><p class="qc-opt-description"></p>
                                                <div class="cxsc-settings-blocks">
                                                    <p>
                                                        <?php $settings = array('textarea_name' =>
                                                            'message_two',
                                                            'textarea_rows' => 20,
                                                            'editor_height' => 100,
                                                            'editor_class' => 'customNotificationClass',
                                                            'disabled' => 'disabled',
                                                            'media_buttons' => false
                                                        );
                                                        wp_editor(get_option(html_entity_decode(stripcslashes('message_two'))), 'message_two', $settings); ?>


                                                </div>
                                                <br>
                                                <p>Message display duration time - <strong class="label label-danger">Pro
                                                        version only</strong></p>
                                                <p><input class="form-control input-sm" type="text"
                                                          value="" disabled="disabled"
                                                          name="notification_delay_two"> <strong> in second</strong></p>
                                            </td>


                                        </tr>
                                        <tr>
                                            <td><p class="qc-opt-description"></p>
                                                <div class="cxsc-settings-blocks">
                                                    <p>
                                                        <?php $settings = array('textarea_name' =>
                                                            'message_three',
                                                            'textarea_rows' => 20,
                                                            'editor_height' => 100,
                                                            'editor_class' => 'customNotificationClass',
                                                            'media_buttons' => false
                                                        );

                                                        wp_editor(get_option('message_three'), 'message_three', $settings); ?>
                                                </div>
                                                <br>
                                                <p>Message display duration time - <strong class="label label-danger">Pro
                                                        version only</strong></p>
                                                <p><input class="form-control input-sm" type="text"
                                                          value=""
                                                          disabled="disabled"
                                                          name="notification_delay_three"> <strong> in second</strong>
                                                </p>

                                            </td>
                                        </tr>


                                        </tbody>
                                    </table>

                                </section>
                                <section id="section-flip-1">
                                    <div id="jarvisGrid">
                                        <?php if ((get_option('grid_items')) != '') { ?>
                                            <?php echo htmlspecialchars_decode(stripslashes(get_option('grid_items'))); ?>
                                            <hr>
                                            <div class="alert alert-danger">
                                                <strong>Pro feature: </strong> Add, edit and delete grid elements are
                                                available only on pro version.
                                            </div>
                                        <?php } ?>

                                    </div> <!-- /#myGrid -->
                                </section>
                            </div><!-- /content -->
                        </div><!-- /tabs-jarvis -->
                    </section>
                    <input type="hidden" name="action" value="jarvis-submitted"/>
                    <input type="submit" class="btn btn-primary submit-button" name="submit"
                           id="submit" value="<?php _e('Save Settings', 'jarvis'); ?>"/>
                </div>


                <?php wp_nonce_field('jarvis'); ?>
            </form>


        </div>


        <?php

    }

    /**
     * Create shortcode for Jarvis
     */
    public function wsaj_frontend_shortcode($atts)
    {

        global $woocommerce;

        if (class_exists('WC_Shortcodes') && method_exists('WC_Shortcodes', 'shortcode_wrapper')) {
            return WC_Shortcodes::shortcode_wrapper(array('QC_Shortcode_Jarvis', 'output'), $atts);
        } else {
            return $woocommerce->shortcode_wrapper(array('QC_Shortcode_Jarvis', 'output'), $atts);
        }
    }

    public function wsaj_widget_shortcode($atts)
    {

        global $woocommerce;

        if (class_exists('WC_Shortcodes') && method_exists('WC_Shortcodes', 'shortcode_wrapper')) {
            return WC_Shortcodes::shortcode_wrapper(array('QC_Widget_Jarvis', 'output'), $atts);
        } else {
            return $woocommerce->shortcode_wrapper(array('QC_Widget_Jarvis', 'output'), $atts);
        }
    }

    /**
     * Save Options
     */


    function wsaj_save_options()
    {


        global $woocommerce;

        wp_verify_nonce($_POST['_wpnonce'], 'jarvis');


        // Check if the form is submitted or not

        if (isset($_POST['submit'])) {

            if (isset($_POST["jarvis-terms-fields"])) {

                $filter_fields = $_POST["jarvis-terms-fields"];
                if ($filter_fields) {
                    $formatted_filter_fields = array();
                    foreach ($filter_fields as $filter_field) {
                        if ($filter_field["filter"] != "") {
                            $formatted_filter_fields[] = stripslashes_deep(array_map("woocommerce_clean", $filter_field));
                        }
                    }
                    $serialized_filter_fields = serialize($formatted_filter_fields);

                    update_option('jarvis-terms-fields', $serialized_filter_fields);

                }
            } else {
                if (($_GET["page"] == "jarvis") && (isset($_POST["action"]))) {
                    delete_option('jarvis-terms-fields');
                }
            }

            if (isset($_POST["jarvis-search-button-text"])) {
                $button_text = stripslashes(($_POST["jarvis-search-button-text"]));
                update_option('jarvis-search-button-text', $button_text);
            }


            if (isset($_POST["message_two"])) {
                $message_two = ($_POST["message_two"]);
                update_option('message_two', stripslashes($message_two));
            }
            if (isset($_POST["message_three"])) {
                $message_three = ($_POST["message_three"]);
                update_option('message_three', stripslashes($message_three));
            }

            // Global Message Delay timer


            if (isset($_POST["global_notification_delay_time"])) {
                $global_notification_delay_time = sanitize_text_field(($_POST["global_notification_delay_time"]));
                update_option('global_notification_delay_time', $global_notification_delay_time);
            }


            $cart_products = $_POST['cart_products'] ? $_POST['cart_products'] : '';
            update_option('cart_products', sanitize_text_field($cart_products));


            $recommended_products = $_POST['recommended_products'] ? $_POST['recommended_products'] : '';
            update_option('recommended_products', sanitize_text_field($recommended_products));


            $jarvis_icon = $_POST['jarvis_icon'] ? $_POST['jarvis_icon'] : 'icon-0.png';
            update_option('jarvis_icon', sanitize_text_field($jarvis_icon));


            $jarvis_theme = $_POST['jarvis_theme'] ? $_POST['jarvis_theme'] : 'theme-one.jpg';
            update_option('jarvis_theme', sanitize_text_field($jarvis_theme));


            $recent_products = $_POST['recent_products'] ? $_POST['recent_products'] : '';
            update_option('recent_products', sanitize_text_field($recent_products));


            $disable_jarvis = $_POST["disable_jarvis"] ? '' : 1;
            update_option('disable_jarvis', $disable_jarvis);

        }
    }

    function wsaj_search_query($query)
    {
        global $woocommerce, $wp_query, $wpdb;

        if (!is_admin() && $query->is_main_query()) {

            $woocommerce_current_page_id = (version_compare($woocommerce->version, '2.1', '<')) ? wc_get_page_id('shop') : wc_get_page_id('shop');

            if (is_shop() || ($query->is_page() && 'page' == get_option('show_on_front') && $query->get('page_id') == $woocommerce_current_page_id)) {

                $product_categories = (isset($_GET['sa_product_cat'])) ? $_GET['sa_product_cat'] : null;
                $product_tags = (isset($_GET['sa_product_tag'])) ? $_GET['sa_product_tag'] : null;

                if (isset($product_categories) || ($product_tags)) {
                    add_filter("woocommerce_is_filtered", array($this, 'woocommerce_is_filtered'));
                }

                $tax_query = false;

                // Product Attributes
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                $selected_attributes = false;

                if ($attribute_taxonomies) {

                    foreach ($attribute_taxonomies as $tax) {

                        $attribute = sanitize_title($tax->attribute_name);
                        $taxonomy = wc_attribute_taxonomy_name($attribute);

                        // create an array of product attribute taxonomies
                        $_attributes_array[] = $taxonomy;

                        $name = 'sa_filter_' . $attribute;

                        if (!empty($_GET[$name]) && taxonomy_exists($taxonomy)) {
                            add_filter("woocommerce_is_filtered", array($this, 'woocommerce_is_filtered'));
                            $selected_attributes[$taxonomy]['terms'] = $_GET[$name];

                        }
                    }

                    if ($selected_attributes) {

                        foreach ($selected_attributes as $key => $value) {

                            $tax_query[] = array(
                                'taxonomy' => $key,
                                'field' => 'id',
                                'terms' => $value["terms"],
                                'operator' => 'IN'
                            );

                        }

                    }
                }

                // Product Categories
                if ((isset($product_categories)) && ($product_categories != "")) {

                    $tax_query[] = array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $product_categories,
                        'operator' => 'IN'
                    );

                }

                // Product Tags
                if ((isset($product_tags)) && ($product_tags != "")) {

                    $tax_query[] = array(
                        'taxonomy' => 'product_tag',
                        'field' => 'id',
                        'terms' => $product_tags,
                        'operator' => 'IN'
                    );

                }

                if ($tax_query) {
                    add_filter("woocommerce_page_title", array($this, "wsaj_jarvis_title"));
                    $tax_query['relation'] = 'AND';
                    $query->set('tax_query', $tax_query);
                }

            }
        }
        return $query;
    }

    function woocommerce_is_filtered()
    {

        return true;
    }

    function wsaj_jarvis_title($title)
    {

        return __("Search Results:", "jarvis");
    }

    /**
     * Price Filter post filter
     */
    function wsaj_price_filter($filtered_posts)
    {
        global $wpdb;

        if (isset($_GET['sa_max_price']) && isset($_GET['sa_min_price'])) {

            add_filter("woocommerce_is_filtered", array($this, 'woocommerce_is_filtered'));

            $matched_products = array();
            $min = floatval($_GET['sa_min_price']);
            $max = floatval($_GET['sa_max_price']);

            $matched_products_query = $wpdb->get_results($wpdb->prepare("
	        	SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta ON ID = post_id
				WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish' AND meta_key = %s AND meta_value BETWEEN %d AND %d
			", '_price', $min, $max), OBJECT_K);

            if ($matched_products_query) {
                foreach ($matched_products_query as $product) {
                    if ($product->post_type == 'product')
                        $matched_products[] = $product->ID;
                    if ($product->post_parent > 0 && !in_array($product->post_parent, $matched_products))
                        $matched_products[] = $product->post_parent;
                }
            }

            // Filter the id's
            if (sizeof($filtered_posts) == 0) {
                $filtered_posts = $matched_products;
                $filtered_posts[] = 0;
            } else {
                $filtered_posts = array_intersect($filtered_posts, $matched_products);
                $filtered_posts[] = 0;
            }

        }
        return (array)$filtered_posts;
    }

    /**
     * Display Notifications on specific criteria.
     *
     * @since    2.14
     */
    public static function woocommerce_inactive_notice()
    {
        if (current_user_can('activate_plugins')) :
            if (!class_exists('WooCommerce')) :
                deactivate_plugins(plugin_basename(__FILE__));
                wp_die('You need to activate WooCommerce first.');
                ?>
                <div id="message" class="error">
                    <p>
                        <?php
                        printf(
                            __('%sWooCommerce Shop Assistant JARVIS REQUIRES WooCommerce%s %sWooCommerce%s must be active for JARVIS to work. Please install & activate WooCommerce.', 'jarvis'),
                            '<strong>',
                            '</strong><br>',
                            '<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank" >',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
                <?php
            elseif (version_compare(get_option('woocommerce_db_version'), WSAJ_REQUIRED_WOOCOMMERCE_VERSION, '<')) :
                ?>
                <div id="message" class="error">
                    <!--<p style="float: right; color: #9A9A9A; font-size: 13px; font-style: italic;">For more information <a href="http://cxthemes.com/plugins/update-notice.html" target="_blank" style="color: inheret;">click here</a></p>-->
                    <p>
                        <?php
                        printf(
                            __('%sJARVIS for WooCommerce is inactive%s This version of JARVIS requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', 'jarvis'),
                            '<strong>',
                            '</strong><br>',
                            WSAJ_REQUIRED_WOOCOMMERCE_VERSION
                        );
                        ?>
                    </p>
                    <div style="clear:both;"></div>
                </div>
                <?php
            endif;
        endif;
        activate_plugin(plugin_basename(__FILE__));
    }

}

/**
 * Instantiate plugin.
 *
 */

if (!function_exists('wsaj_init_jarvis')) {
    function wsaj_init_jarvis()
    {

        global $wc_jarvis;

        $wc_jarvis = WC_Jarvis::get_instance();
    }
}
add_action('plugins_loaded', 'wsaj_init_jarvis');


register_activation_hook(__FILE__, 'jarvis_insert_demo_content');
function jarvis_insert_demo_content()
{

    $demo_data = array(
        0 => array(
            'text' => 'Find Products In',
            'filter' => 'product_cat',
            'priceone' => '',
            'pricetwo' => '',
            'label' => 'Category'
        ),
        1 => array(
            'text' => 'Between Price',
            'filter' => 'price',
            'priceone' => '100',
            'pricetwo' => '500',
            'label' => ''
        )
    );


    $default_shortcode_templates = '<div class="qc-row"><div class="qc-col-sm-12  column qc-col-md-3"><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"><p>[jarvis-recently-viewed-products]<br></p></div><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"></div></div><div class="qc-col-sm-12 column qc-col-md-6"><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"><p>[qc_jarvis]<br></p></div><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"></div></div><div class="qc-col-sm-12 column qc-col-md-3"><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"><p>[jarvis-cart-products]<br></p></div><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"></div></div></div><div class="qc-row"><div class="qc-col-md-12 qc-col-sm-12 qc-col-xs-12 column"><div class="ge-content ge-content-type-summernote" data-ge-content-type="summernote"><p>[jarvis_featured_products]</p></div></div></div>';


    if (get_option('jarvis-terms-fields') != '') {
        update_option('jarvis-terms-fields', serialize($demo_data));
    }

    if (get_option('jarvis_theme') == '') {
        update_option('jarvis_theme', 'theme-one.jpg');
    }

    update_option('cart_products', 'cart_products');
    update_option('recommended_products', 'recommended_products');
    update_option('recent_products', 'recent_products');
    update_option('jarvis_form_animation', 'bounce');
    update_option('grid_items', $default_shortcode_templates);
    update_option('jarvis_icon', 'icon-0.png');
    update_option('message_one', 'Hi, I am JARVIS. What can I help you with today?');
    update_option('global_notification_delay_time', 5);
    update_option('disable_jarvis', 1);


}


