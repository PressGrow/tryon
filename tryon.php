<?php

/**
 * Plugin Name: Tryon
 * Plugin URI:  http://www.thesoftsol.com
 * Description: Virtual Tryon.
 * Version:     1.0.0
 * Author:      Bipole
 * Author URI:  http://www.thesoftsol.com
 * Text Domain: tryon
 */
if (!defined('ABSPATH')) {
    exit; // disable direct access
}

/* Init Tryon */
add_action('init', 'tryon_init');

function tryon_init() {
    if (is_admin()) { // admin actions
        add_action('admin_menu', 'admin_register_tryon_settings');
        wp_enqueue_script('tryon_style', plugins_url('/assets/admin.js', __FILE__), array(), '1.0.0');
    } else {
        /* Adding Code in Footer */
        add_action('wp_footer', 'tryon_footer_func');
        /* Adding Scripts & Styles */
        add_action('wp_enqueue_scripts', 'tryon_scripts_func');
        /* Adding Tryon Button After Add_to_Cart Button in Product Page */
        add_action('woocommerce_after_add_to_cart_button', 'show_tryon_button_func');
        /* Adding Tryon Button After Add_to_Cart Button in List Page */
        add_action('woocommerce_after_shop_loop_item', 'show_tryon_button_list_func', 20);
        /*Adding shortcode for tryon button*/
        add_shortcode( 'tryon', 'show_tryon_button_short_func' );
    }
}

register_activation_hook(__FILE__, 'tryon_rewrite_flush');

function tryon_rewrite_flush() {
    tryon_init();
    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}

/* Register TRyon in Admin menu */

function admin_register_tryon_settings() {
    add_menu_page(
            'Tryon', 'Tryon', 'manage_options', 'tryon_settings', 'admin_tryon_page_callback', ''
    );
}

/**
 * Disply callback for the Unsub page.
 */
function admin_tryon_page_callback() {
    $errors = admin_upload_settings();
    require_once('form.php');
}

/* Admin upload post settings */

function admin_upload_settings() {
    $error = array();
    if (!isset($_POST['tryon_nonce_field'])) {
        return $error;
    }
    if (empty($_POST) || !wp_verify_nonce($_POST['tryon_nonce_field'], 'tryon_content')) {
        $error[] = 'Sorry, your nonce did not verify.';
    } else {
        $target_dir = dirname(__FILE__);
        $target_file = $target_dir . "/license.dat";
        $targetFileType = pathinfo(basename($_FILES["license"]["name"]), PATHINFO_EXTENSION);
        if ($targetFileType != 'dat') {
            $error[] = 'License must be in dat format.';
        } else if (!upload_tryon_license($_FILES["license"]["tmp_name"], $target_file)) {
            $error[] = 'Sorry, there was an error uploading your license.';
        }
    }
    return $error;
}

/* Upload Tryon License file */

function upload_tryon_license($src, $des) {
    if (file_exists($des)) {
        @unlink($des);
    }
    return @move_uploaded_file($src, $des);
}

/**
 * Tryon button code
 */
function tryon_image_data($id = null) {
    global $product;

    if(!$id) {
        $id = $product->id;
    }
    $tryon_status = get_post_meta($id, 'tryon_status', true);
    if($tryon_status == 'disabled') {
        return;
    }
    $tryonImgId = get_post_meta($id, '_tryon_image_id', true);
    return wp_get_attachment_url($tryonImgId);
}
function show_tryon_button_func() {
    $tryonImg = tryon_image_data();
    if ($tryonImg) {
        echo '<button type="button" class="button alt" onclick="applyFrame(\'' . $tryonImg . '\')">Try on</button>';
    }
}

function show_tryon_button_list_func() {
    $tryonImg = tryon_image_data();
    if ($tryonImg) {
        echo '<button type="button" class="button alt" onclick="applyFrame(\'' . $tryonImg . '\')">Try on</button>';
    }
}
function show_tryon_button_short_func($attr) {
    $data = shortcode_atts( array(
        'id' => null,
        'title' => 'Try on',
        'class' => 'button alt',
    ), $attr );
    
    $tryonImg = tryon_image_data($data['id']);
    if ($tryonImg) {
        return '<button type="button" class="'.$data['class'].'" onclick="applyFrame(\'' . $tryonImg . '\')">'.$data['title'].'</button>';
    }
}

/**
 * Tryon code in footer
 */
function tryon_footer_func() {
    echo '<div id="tryonBox">
                <!--    TryOn div    -->
                <div id="tryonpreview" class="softsol-tryon"></div>
                <div id="tryonbar" class="softsol-tryon-bar"></div>
                <!--    TryOn div End -->
	            <a id="hide_tryon" onclick="return close_tryon();"></a>
            </div>
            <div id="black_overlay"></div>
            <script>var vpath="' . plugins_url('/', __FILE__) . '";var siteajaxpath="' . admin_url('admin-ajax.php') . '";</script>';
}

/**
 * Adding scripts and styles for VTO.
 */
function tryon_scripts_func() {
    wp_enqueue_style('tryon_style', plugins_url('/assets/styles/style.css', __FILE__), array(), '1.0.1');
    wp_enqueue_script('tryon', plugins_url('/assets/scripts/app.min.js', __FILE__), array(), '1.0.1', true);
    wp_enqueue_script('tryon_settings', plugins_url('/assets/settings.js', __FILE__), array(), '1.0.1', true);
    wp_enqueue_script('tryon_init', plugins_url('/assets/init.js', __FILE__), array(), '1.0.1', true);
}

/* Tryon Meta box code start */
add_action('add_meta_boxes', 'tryon_image_add_metabox');

function tryon_image_add_metabox() {
    add_meta_box('tryonimagediv', __('Tryon Image', 'text-domain'), 'tryon_image_metabox', 'product', 'side', 'default');
}

/* Show tryon metabox image in admin product page */

function tryon_image_metabox($post) {
    global $content_width, $_wp_additional_image_sizes;

    $image_id = get_post_meta($post->ID, '_tryon_image_id', true);

    $old_content_width = $content_width;
    $content_width = 254;

    if ($image_id && get_post($image_id)) {

        if (!isset($_wp_additional_image_sizes['post-thumbnail'])) {
            $thumbnail_html = wp_get_attachment_image($image_id, array($content_width, $content_width));
        } else {
            $thumbnail_html = wp_get_attachment_image($image_id, 'post-thumbnail');
        }

        if (!empty($thumbnail_html)) {
            $content = $thumbnail_html;
            $content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_tryon_image_button" >' . esc_html__('Remove Tryon image', 'text-domain') . '</a></p>';
            $content .= '<input type="hidden" id="upload_tryon_image" name="_tryon_cover_image" value="' . esc_attr($image_id) . '" />';
        }

        $content_width = $old_content_width;
    } else {
        $content = '<img src="" style="width:' . esc_attr($content_width) . 'px;height:auto;border:0;display:none;" />';
        $content .= '<p class="hide-if-no-js"><a title="' . esc_attr__('Set Tryon image', 'text-domain') . '" href="javascript:;" id="upload_tryon_image_button" id="set-tryon-image" data-uploader_title="' . esc_attr__('Choose an image', 'text-domain') . '" data-uploader_button_text="' . esc_attr__('Set Tryon image', 'text-domain') . '">' . esc_html__('Set Tryon image', 'text-domain') . '</a></p>';
        $content .= '<input type="hidden" id="upload_tryon_image" name="_tryon_cover_image" value="" />';
    }

    $tryon_status = get_post_meta($post->ID, 'tryon_status', true);
	$selected = isset( $tryon_status ) ? esc_attr( $tryon_status ) : '';
    $content .= '<p><label for="tryon_status">'.esc_html__('Tryon is', 'text-domain').'</label>
                <select name="tryon_status" id="tryon_status">
                    <option value="enabled"'. selected( $selected, 'enabled' ,false) .'>'.esc_html__('Enabled', 'text-domain').'</option>
                    <option value="disabled"'. selected( $selected, 'disabled' ,false) .'>'.esc_html__('Disabled', 'text-domain').'</option>
                </select></p>';
    echo $content;
}

/* Tryon image save in admin metabox */
add_action('save_post', 'tryon_image_save', 10, 1);

function tryon_image_save($post_id) {
    if (isset($_POST['_tryon_cover_image'])) {
        $image_id = (int) $_POST['_tryon_cover_image'];
        update_post_meta($post_id, '_tryon_image_id', $image_id);
    }
    if( isset( $_POST['tryon_status'] ) )
        update_post_meta( $post_id, 'tryon_status', $_POST['tryon_status'] );  
}

/* Tryon Meta box code end */

/* Ajax Start */

/*Save Tryon Image*/
function ajax_tryon_save_func() {
    define('UPLOAD_URL_DIR', plugins_url('/TryonSavings/', __FILE__));
    require_once('save.php');
    die;
}
/*Share Tryon Image*/
function ajax_tryon_share_func() {
    require_once('share.php');
    die;
}

add_action('wp_ajax_tryon_save', 'ajax_tryon_save_func');
add_action( 'wp_ajax_nopriv_tryon_save', 'ajax_tryon_save_func' );
add_action('wp_ajax_tryon_share', 'ajax_tryon_share_func');
add_action( 'wp_ajax_nopriv_tryon_share', 'ajax_tryon_share_func' );

/* Ajax End */
