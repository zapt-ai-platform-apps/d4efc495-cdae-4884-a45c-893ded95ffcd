<?php
/**
 * Class for registering and handling shortcodes
 */
class FVL_Shortcodes {

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('fvl_map', array($this, 'map_shortcode'));
        add_shortcode('fvl_location_list', array($this, 'location_list_shortcode'));
        add_shortcode('fvl_location_details', array($this, 'location_details_shortcode'));
    }
    
    /**
     * Shortcode for displaying the locations map
     */
    public function map_shortcode($atts) {
        $atts = shortcode_atts(array(
            'height' => '500px',
            'width' => '100%',
            'zoom' => '10',
            'category' => '',
            'payment' => '',
        ), $atts, 'fvl_map');
        
        // Enqueue the Google Maps script
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . get_option('fvl_google_maps_api_key') . '&libraries=places', array(), FVL_VERSION, true);
        wp_enqueue_script('fvl-maps-script');
        
        // Generate a unique ID for this map
        $map_id = 'fvl-map-' . uniqid();
        
        // Pass data to the script
        wp_localize_script('fvl-maps-script', 'fvlMapData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fvl_map_nonce'),
            'mapId' => $map_id,
            'category' => $atts['category'],
            'payment' => $atts['payment'],
            'zoom' => $atts['zoom']
        ));
        
        // Return the map container
        return '<div id="' . esc_attr($map_id) . '" class="fvl-map-container" style="height: ' . esc_attr($atts['height']) . '; width: ' . esc_attr($atts['width']) . ';"></div>';
    }
    
    /**
     * Shortcode for displaying the list of locations
     */
    public function location_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'category' => '',
            'payment' => '',
            'orderby' => 'title',
            'order' => 'ASC',
        ), $atts, 'fvl_location_list');
        
        // Query arguments
        $args = array(
            'post_type' => 'vending_location',
            'posts_per_page' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Add taxonomy query if categories or payment methods are specified
        $tax_query = array();
        
        if (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_category',
                'field'    => 'slug',
                'terms'    => explode(',', $atts['category']),
            );
        }
        
        if (!empty($atts['payment'])) {
            $tax_query[] = array(
                'taxonomy' => 'payment_method',
                'field'    => 'slug',
                'terms'    => explode(',', $atts['payment']),
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        // Get the locations
        $locations_query = new WP_Query($args);
        
        // Start output buffering
        ob_start();
        
        // Display the locations
        if ($locations_query->have_posts()) {
            echo '<div class="fvl-locations-list">';
            
            while ($locations_query->have_posts()) {
                $locations_query->the_post();
                
                // Get location meta
                $address = get_post_meta(get_the_ID(), '_fvl_address', true);
                $lat = get_post_meta(get_the_ID(), '_fvl_latitude', true);
                $lng = get_post_meta(get_the_ID(), '_fvl_longitude', true);
                
                // Get rating
                $rating = $this->get_location_average_rating(get_the_ID());
                
                // Display location card
                include FVL_PLUGIN_DIR . 'public/templates/location-card.php';
            }
            
            echo '</div>';
            
            // Reset post data
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No vending locations found.', 'farmer-vending-locations') . '</p>';
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Shortcode for displaying details of a specific location
     */
    public function location_details_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'fvl_location_details');
        
        // If no ID is provided, try to get it from the current post
        $location_id = (int) $atts['id'];
        if ($location_id === 0 && is_singular('vending_location')) {
            $location_id = get_the_ID();
        }
        
        // If we still don't have an ID, return a message
        if ($location_id === 0) {
            return '<p>' . __('No vending location specified.', 'farmer-vending-locations') . '</p>';
        }
        
        // Check if the post exists and is of the correct type
        $location = get_post($location_id);
        if (!$location || $location->post_type !== 'vending_location') {
            return '<p>' . __('Vending location not found.', 'farmer-vending-locations') . '</p>';
        }
        
        // Start output buffering
        ob_start();
        
        // Load the location details template
        include FVL_PLUGIN_DIR . 'public/templates/location-details.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Get the average rating for a location
     */
    private function get_location_average_rating($location_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        $query = $wpdb->prepare(
            "SELECT AVG(rating) as average_rating, COUNT(*) as count 
            FROM {$table_name} 
            WHERE location_id = %d AND approved = 1",
            $location_id
        );
        
        $result = $wpdb->get_row($query);
        
        return array(
            'average' => $result->average_rating ? round($result->average_rating, 1) : 0,
            'count' => (int) $result->count
        );
    }
}