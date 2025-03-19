<?php
/**
 * Class for public-facing functionality
 */
class FVL_Public {

    /**
     * Register the stylesheets for the public-facing side of the site
     */
    public function enqueue_styles() {
        wp_enqueue_style('fvl-public', FVL_PLUGIN_URL . 'public/css/fvl-public.css', array(), FVL_VERSION, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site
     */
    public function enqueue_scripts() {
        // Google Maps script
        $api_key = get_option('fvl_google_maps_api_key');
        if (!empty($api_key)) {
            wp_enqueue_script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places", array(), FVL_VERSION, true);
        }
        
        // Main script
        wp_enqueue_script('fvl-public', FVL_PLUGIN_URL . 'public/js/fvl-public.js', array('jquery'), FVL_VERSION, true);
        
        // Pass data to the script
        wp_localize_script('fvl-public', 'fvlPublic', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fvl_public_nonce'),
            'mapMarkerIcon' => get_option('fvl_map_marker_icon'),
            'mapStyle' => get_option('fvl_map_style', 'default'),
            'mapZoom' => get_option('fvl_map_default_zoom', 10),
        ));
    }
    
    /**
     * AJAX handler for submitting a review
     */
    public function submit_review() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fvl_public_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'farmer-vending-locations')));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to submit a review.', 'farmer-vending-locations')));
        }
        
        // Check required fields
        if (!isset($_POST['location_id']) || empty($_POST['location_id'])) {
            wp_send_json_error(array('message' => __('No location specified.', 'farmer-vending-locations')));
        }
        
        if (!isset($_POST['rating']) || empty($_POST['rating'])) {
            wp_send_json_error(array('message' => __('Please provide a rating.', 'farmer-vending-locations')));
        }
        
        // Get the current user ID
        $user_id = get_current_user_id();
        
        // Submit the review
        $result = FVL_Reviews::submit_review(
            (int) $_POST['location_id'],
            $user_id,
            (int) $_POST['rating'],
            isset($_POST['review']) ? sanitize_textarea_field($_POST['review']) : ''
        );
        
        if ($result['success']) {
            wp_send_json_success(array('message' => $result['message']));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
    
    /**
     * AJAX handler for getting locations for the map
     */
    public function get_locations() {
        // Check nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'fvl_public_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'farmer-vending-locations')));
        }
        
        // Build the query arguments
        $args = array(
            'post_type' => 'vending_location',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        
        // Add taxonomy filters if set
        $tax_query = array();
        
        if (isset($_REQUEST['category']) && !empty($_REQUEST['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_category',
                'field'    => 'slug',
                'terms'    => explode(',', $_REQUEST['category']),
            );
        }
        
        if (isset($_REQUEST['payment']) && !empty($_REQUEST['payment'])) {
            $tax_query[] = array(
                'taxonomy' => 'payment_method',
                'field'    => 'slug',
                'terms'    => explode(',', $_REQUEST['payment']),
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        // Get the locations
        $query = new WP_Query($args);
        
        $locations = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get location data
                $latitude = get_post_meta(get_the_ID(), '_fvl_latitude', true);
                $longitude = get_post_meta(get_the_ID(), '_fvl_longitude', true);
                
                // Skip if no coordinates
                if (empty($latitude) || empty($longitude)) {
                    continue;
                }
                
                // Get other location details
                $address = get_post_meta(get_the_ID(), '_fvl_address', true);
                $phone = get_post_meta(get_the_ID(), '_fvl_phone', true);
                $hours = get_post_meta(get_the_ID(), '_fvl_hours', true);
                
                // Get featured image
                $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                
                // Get categories
                $categories = wp_get_post_terms(get_the_ID(), 'product_category', array('fields' => 'names'));
                
                // Get payment methods
                $payments = wp_get_post_terms(get_the_ID(), 'payment_method', array('fields' => 'names'));
                
                // Get rating
                $rating = FVL_Reviews::get_location_average_rating(get_the_ID());
                
                // Add to locations array
                $locations[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                    'address' => $address,
                    'permalink' => get_permalink(),
                    'thumbnail' => $thumbnail ? $thumbnail : '',
                    'categories' => $categories,
                    'payments' => $payments,
                    'rating' => $rating['average'],
                    'reviewCount' => $rating['count'],
                    'phone' => $phone,
                    'excerpt' => get_the_excerpt(),
                );
            }
            
            // Reset post data
            wp_reset_postdata();
        }
        
        wp_send_json_success(array('locations' => $locations));
    }
}