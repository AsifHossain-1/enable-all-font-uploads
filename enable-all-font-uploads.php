<?php
/**
 * Plugin Name: Enable All Font Uploads
 * Plugin URI: https://asifsweb.com/plugin/
 * Description: Allows uploading restricted font files (WOFF, WOFF2, TTF, OTF) to WordPress
 * Version: 1.0.0
 * Author: Asif Hossain
 * Author URI: https://asifsweb.com/
 * Text Domain: enable-all-font-uploads
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.8
 */

if (!defined('ABSPATH')) {
    exit;
}

class EnableAllFontUploads {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_filter('upload_mimes', array($this, 'allow_font_upload_mimes'));
        add_filter('wp_check_filetype_and_ext', array($this, 'check_font_filetypes'), 10, 4);
        
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
    }
    
    public function plugin_activation() {
        add_option('enable_all_font_uploads', '0');
    }
    
    public function plugin_deactivation() {
        delete_option('enable_all_font_uploads');
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Enable All Font Uploads',
            'Enable All Font Uploads',
            'manage_options',
            'enable-all-font-uploads',
            array($this, 'admin_page_content')
        );
    }
    
    public function admin_init() {
        register_setting(
            'enable_all_font_uploads', 
            'enable_all_font_uploads', 
            array(
                'sanitize_callback' => array($this, 'sanitize_setting')
            )
        );
    }
    
    public function sanitize_setting($input) {
        return $input === '1' ? '1' : '0';
    }
    
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'settings_page_enable-all-font-uploads') {
            return;
        }
        
        wp_enqueue_style(
            'enable-all-font-uploads-css',
            plugin_dir_url(__FILE__) . 'assets/enable-all-font-uploads.css',
            array(),
            '1.0.0'
        );
    }
    
    public function admin_page_content() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'enable-all-font-uploads'));
        }
        ?>
        <div class="enable-all-font-uploads-container">
            <div class="enable-all-font-uploads-content">
                <h1 class="enable-all-font-uploads-title"><?php esc_html_e('Enable All Font Uploads', 'enable-all-font-uploads'); ?></h1>
                
                <form method="post" action="options.php" class="enable-all-font-uploads-form">
                    <?php settings_fields('enable_all_font_uploads'); ?>
                    
                    <div class="enable-all-font-uploads-control-group">
                        <label class="enable-all-font-uploads-toggle">
                            <input type="checkbox" name="enable_all_font_uploads" value="1" 
                                <?php checked(1, get_option('enable_all_font_uploads'), true); ?> />
                            <span class="enable-all-font-uploads-slider"></span>
                        </label>
                        <span class="enable-all-font-uploads-control-label"><?php esc_html_e('Allow All Font Uploads (WOFF, WOFF2, TTF, OTF)', 'enable-all-font-uploads'); ?></span>
                    </div>
                    
                    <div class="enable-all-font-uploads-warning">
                        <p><?php esc_html_e('Security Warning: This feature is intended for temporary use only. Upload trusted files and disable the option immediately afterwards.', 'enable-all-font-uploads'); ?></p>
                    </div>
                    
                    <?php submit_button(__('Save Changes', 'enable-all-font-uploads'), 'submit', 'submit', false); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    public function allow_font_upload_mimes($mimes) {
        if (get_option('enable_all_font_uploads')) {
            $mimes['woff']  = 'font/woff';
            $mimes['woff2'] = 'font/woff2';
            $mimes['ttf']   = 'font/ttf';
            $mimes['otf']   = 'font/otf';
        }
        return $mimes;
    }
    
    public function check_font_filetypes($checked, $file, $filename, $mimes) {
        if (!get_option('enable_all_font_uploads')) {
            return $checked;
        }
        
        $font_extensions = array('woff', 'woff2', 'ttf', 'otf');
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $font_extensions)) {
            $valid_mime_types = array(
                'woff'  => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf'   => 'font/ttf',
                'otf'   => 'font/otf'
            );
            
            if (empty($checked['type'])) {
                $checked['type'] = $valid_mime_types[$ext];
                $checked['ext'] = $ext;
            }
        }
        
        return $checked;
    }
}

new EnableAllFontUploads();
?>