<?php
/**
 * Class for admin functionality
 */
class FVL_Admin {

    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style('fvl-admin', FVL_PLUGIN_URL . 'admin/css/fvl-admin.css', array(), FVL_VERSION, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        // Google Maps script
        $api_key = get_option('fvl_google_maps_api_key');
        if (!empty($api_key)) {
            wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places', array(), FVL_VERSION, true);
        }
        
        // Media uploader
        wp_enqueue_media();
        
        // Admin scripts
        wp_enqueue_script('fvl-admin', FVL_PLUGIN_URL . 'admin/js/fvl-admin.js', array('jquery'), FVL_VERSION, true);
        
        // Pass data to the script
        wp_localize_script('fvl-admin', 'fvlAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fvl_admin_nonce')
        ));
    }
    
    /**
     * Add menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Farmer Vending Locations', 'farmer-vending-locations'),
            __('Vending Locations', 'farmer-vending-locations'),
            'manage_options',
            'farmer-vending-locations',
            array($this, 'display_dashboard_page'),
            'dashicons-location',
            20
        );
        
        // Dashboard submenu
        add_submenu_page(
            'farmer-vending-locations',
            __('Dashboard', 'farmer-vending-locations'),
            __('Dashboard', 'farmer-vending-locations'),
            'manage_options',
            'farmer-vending-locations',
            array($this, 'display_dashboard_page')
        );
        
        // Reviews submenu
        add_submenu_page(
            'farmer-vending-locations',
            __('Reviews', 'farmer-vending-locations'),
            __('Reviews', 'farmer-vending-locations'),
            'manage_options',
            'fvl-reviews',
            array($this, 'display_reviews_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'farmer-vending-locations',
            __('Settings', 'farmer-vending-locations'),
            __('Settings', 'farmer-vending-locations'),
            'manage_options',
            'fvl-settings',
            array($this, 'display_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings
        register_setting('fvl_general_settings', 'fvl_google_maps_api_key');
        register_setting('fvl_general_settings', 'fvl_map_default_zoom', array(
            'default' => 10,
            'sanitize_callback' => 'absint'
        ));
        register_setting('fvl_general_settings', 'fvl_enable_user_submissions', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));
        register_setting('fvl_general_settings', 'fvl_auto_approve_reviews', array(
            'default' => 0,
            'sanitize_callback' => 'absint'
        ));
        
        // Map settings
        register_setting('fvl_map_settings', 'fvl_map_height', array(
            'default' => '500px',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('fvl_map_settings', 'fvl_map_marker_icon', array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw'
        ));
        register_setting('fvl_map_settings', 'fvl_map_style', array(
            'default' => 'default',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        // Display settings
        register_setting('fvl_display_settings', 'fvl_locations_per_page', array(
            'default' => 10,
            'sanitize_callback' => 'absint'
        ));
        register_setting('fvl_display_settings', 'fvl_show_location_categories', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));
        register_setting('fvl_display_settings', 'fvl_show_location_ratings', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));
        register_setting('fvl_display_settings', 'fvl_show_location_address', array(
            'default' => 1,
            'sanitize_callback' => 'absint'
        ));
    }
    
    /**
     * Display the dashboard page
     */
    public function display_dashboard_page() {
        include FVL_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }
    
    /**
     * Display the reviews page
     */
    public function display_reviews_page() {
        include FVL_PLUGIN_DIR . 'admin/templates/reviews.php';
    }
    
    /**
     * Display the settings page
     */
    public function display_settings_page() {
        include FVL_PLUGIN_DIR . 'admin/templates/settings.php';
    }
    
    /**
     * Add meta boxes for vending locations
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fvl_location_details',
            __('Location Details', 'farmer-vending-locations'),
            array($this, 'render_location_details_meta_box'),
            'vending_location',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fvl_location_hours',
            __('Opening Hours', 'farmer-vending-locations'),
            array($this, 'render_location_hours_meta_box'),
            'vending_location',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fvl_location_gallery',
            __('Photo Gallery', 'farmer-vending-locations'),
            array($this, 'render_location_gallery_meta_box'),
            'vending_location',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fvl_location_map',
            __('Location Map', 'farmer-vending-locations'),
            array($this, 'render_location_map_meta_box'),
            'vending_location',
            'side',
            'default'
        );
    }
    
    /**
     * Render the location details meta box
     */
    public function render_location_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('fvl_save_location_details', 'fvl_location_details_nonce');
        
        // Get the current values
        $address = get_post_meta($post->ID, '_fvl_address', true);
        $phone = get_post_meta($post->ID, '_fvl_phone', true);
        $email = get_post_meta($post->ID, '_fvl_email', true);
        $website = get_post_meta($post->ID, '_fvl_website', true);
        
        include FVL_PLUGIN_DIR . 'admin/templates/meta-boxes/location-details.php';
    }
    
    /**
     * Render the opening hours meta box
     */
    public function render_location_hours_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('fvl_save_location_hours', 'fvl_location_hours_nonce');
        
        // Get the current values
        $hours = get_post_meta($post->ID, '_fvl_hours', true);
        
        // Set default hours if empty
        if (empty($hours) || !is_array($hours)) {
            $hours = array(
                'monday' => array('open' => '', 'close' => ''),
                'tuesday' => array('open' => '', 'close' => ''),
                'wednesday' => array('open' => '', 'close' => ''),
                'thursday' => array('open' => '', 'close' => ''),
                'friday' => array('open' => '', 'close' => ''),
                'saturday' => array('open' => '', 'close' => ''),
                'sunday' => array('open' => '', 'close' => ''),
            );
        }
        
        include FVL_PLUGIN_DIR . 'admin/templates/meta-boxes/location-hours.php';
    }
    
    /**
     * Render the gallery meta box
     */
    public function render_location_gallery_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('fvl_save_location_gallery', 'fvl_location_gallery_nonce');
        
        // Get the current gallery images
        $gallery_ids = get_post_meta($post->ID, '_fvl_gallery', true);
        
        if (empty($gallery_ids)) {
            $gallery_ids = array();
        } else {
            $gallery_ids = explode(',', $gallery_ids);
        }
        
        include FVL_PLUGIN_DIR . 'admin/templates/meta-boxes/location-gallery.php';
    }
    
    /**
     * Render the map meta box
     */
    public function render_location_map_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('fvl_save_location_map', 'fvl_location_map_nonce');
        
        // Get the current values
        $latitude = get_post_meta($post->ID, '_fvl_latitude', true);
        $longitude = get_post_meta($post->ID, '_fvl_longitude', true);
        $address = get_post_meta($post->ID, '_fvl_address', true);
        
        include FVL_PLUGIN_DIR . 'admin/templates/meta-boxes/location-map.php';
    }
    
    /**
     * Save the meta box data
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if our nonces are set
        if (
            !isset($_POST['fvl_location_details_nonce']) || 
            !isset($_POST['fvl_location_hours_nonce']) || 
            !isset($_POST['fvl_location_gallery_nonce']) ||
            !isset($_POST['fvl_location_map_nonce'])
        ) {
            return;
        }
        
        // Verify the nonce before proceeding
        if (
            !wp_verify_nonce($_POST['fvl_location_details_nonce'], 'fvl_save_location_details') ||
            !wp_verify_nonce($_POST['fvl_location_hours_nonce'], 'fvl_save_location_hours') ||
            !wp_verify_nonce($_POST['fvl_location_gallery_nonce'], 'fvl_save_location_gallery') ||
            !wp_verify_nonce($_POST['fvl_location_map_nonce'], 'fvl_save_location_map')
        ) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Don't save during autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if this is the right post type
        if ('vending_location' !== $post->post_type) {
            return;
        }
        
        // Save the location details
        if (isset($_POST['fvl_address'])) {
            update_post_meta($post_id, '_fvl_address', sanitize_text_field($_POST['fvl_address']));
        }
        
        if (isset($_POST['fvl_phone'])) {
            update_post_meta($post_id, '_fvl_phone', sanitize_text_field($_POST['fvl_phone']));
        }
        
        if (isset($_POST['fvl_email'])) {
            update_post_meta($post_id, '_fvl_email', sanitize_email($_POST['fvl_email']));
        }
        
        if (isset($_POST['fvl_website'])) {
            update_post_meta($post_id, '_fvl_website', esc_url_raw($_POST['fvl_website']));
        }
        
        // Save the latitude and longitude
        if (isset($_POST['fvl_latitude']) && isset($_POST['fvl_longitude'])) {
            update_post_meta($post_id, '_fvl_latitude', (float) $_POST['fvl_latitude']);
            update_post_meta($post_id, '_fvl_longitude', (float) $_POST['fvl_longitude']);
        }
        
        // Save the opening hours
        if (isset($_POST['fvl_hours'])) {
            $hours = array();
            
            foreach ($_POST['fvl_hours'] as $day => $time) {
                $hours[$day] = array(
                    'open' => sanitize_text_field($time['open']),
                    'close' => sanitize_text_field($time['close']),
                );
            }
            
            update_post_meta($post_id, '_fvl_hours', $hours);
        }
        
        // Save the gallery
        if (isset($_POST['fvl_gallery'])) {
            update_post_meta($post_id, '_fvl_gallery', sanitize_text_field($_POST['fvl_gallery']));
        }
    }
    
    /**
     * AJAX handler for geocoding an address
     */
    public function geocode_address() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fvl_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'farmer-vending-locations')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'farmer-vending-locations')));
        }
        
        // Check if address is set
        if (!isset($_POST['address']) || empty($_POST['address'])) {
            wp_send_json_error(array('message' => __('No address provided.', 'farmer-vending-locations')));
        }
        
        // Geocode the address
        $result = FVL_Geocoding::geocode_address($_POST['address']);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for approving a review
     */
    public function approve_review() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fvl_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'farmer-vending-locations')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'farmer-vending-locations')));
        }
        
        // Check if review ID is set
        if (!isset($_POST['review_id']) || empty($_POST['review_id'])) {
            wp_send_json_error(array('message' => __('No review ID provided.', 'farmer-vending-locations')));
        }
        
        // Approve the review
        $result = FVL_Reviews::approve_review((int) $_POST['review_id']);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Review approved successfully.', 'farmer-vending-locations')));
        } else {
            wp_send_json_error(array('message' => __('Failed to approve review.', 'farmer-vending-locations')));
        }
    }
    
    /**
     * AJAX handler for deleting a review
     */
    public function delete_review() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fvl_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'farmer-vending-locations')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'farmer-vending-locations')));
        }
        
        // Check if review ID is set
        if (!isset($_POST['review_id']) || empty($_POST['review_id'])) {
            wp_send_json_error(array('message' => __('No review ID provided.', 'farmer-vending-locations')));
        }
        
        // Delete the review
        $result = FVL_Reviews::delete_review((int) $_POST['review_id']);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Review deleted successfully.', 'farmer-vending-locations')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete review.', 'farmer-vending-locations')));
        }
    }
}