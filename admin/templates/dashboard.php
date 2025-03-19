<div class="wrap fvl-admin-container">
    <div class="fvl-admin-header">
        <h1 class="fvl-admin-title"><?php _e('Vending Locations Dashboard', 'farmer-vending-locations'); ?></h1>
        <a href="<?php echo admin_url('post-new.php?post_type=vending_location'); ?>" class="button button-primary"><?php _e('Add New Location', 'farmer-vending-locations'); ?></a>
    </div>
    
    <div class="fvl-admin-card">
        <h2><?php _e('Quick Stats', 'farmer-vending-locations'); ?></h2>
        
        <?php
        // Get stats
        $locations_count = wp_count_posts('vending_location')->publish;
        
        // Get total reviews
        global $wpdb;
        $reviews_table = $wpdb->prefix . 'fvl_reviews';
        $total_reviews = $wpdb->get_var("SELECT COUNT(*) FROM {$reviews_table}");
        $pending_reviews = $wpdb->get_var("SELECT COUNT(*) FROM {$reviews_table} WHERE approved = 0");
        
        // Get categories
        $categories = get_terms(array(
            'taxonomy' => 'product_category',
            'hide_empty' => false,
        ));
        
        // Get payment methods
        $payment_methods = get_terms(array(
            'taxonomy' => 'payment_method',
            'hide_empty' => false,
        ));
        ?>
        
        <div class="fvl-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div class="fvl-stat-card" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h3><?php _e('Total Locations', 'farmer-vending-locations'); ?></h3>
                <div class="fvl-stat-value" style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $locations_count; ?></div>
                <a href="<?php echo admin_url('edit.php?post_type=vending_location'); ?>" style="display: inline-block; margin-top: 10px;"><?php _e('View All', 'farmer-vending-locations'); ?></a>
            </div>
            
            <div class="fvl-stat-card" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h3><?php _e('Total Reviews', 'farmer-vending-locations'); ?></h3>
                <div class="fvl-stat-value" style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $total_reviews; ?></div>
                <a href="<?php echo admin_url('admin.php?page=fvl-reviews'); ?>" style="display: inline-block; margin-top: 10px;"><?php _e('View All', 'farmer-vending-locations'); ?></a>
            </div>
            
            <div class="fvl-stat-card" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h3><?php _e('Pending Reviews', 'farmer-vending-locations'); ?></h3>
                <div class="fvl-stat-value" style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $pending_reviews; ?></div>
                <a href="<?php echo admin_url('admin.php?page=fvl-reviews&filter=pending'); ?>" style="display: inline-block; margin-top: 10px;"><?php _e('View Pending', 'farmer-vending-locations'); ?></a>
            </div>
            
            <div class="fvl-stat-card" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h3><?php _e('Product Categories', 'farmer-vending-locations'); ?></h3>
                <div class="fvl-stat-value" style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo count($categories); ?></div>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=product_category&post_type=vending_location'); ?>" style="display: inline-block; margin-top: 10px;"><?php _e('Manage Categories', 'farmer-vending-locations'); ?></a>
            </div>
            
            <div class="fvl-stat-card" style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <h3><?php _e('Payment Methods', 'farmer-vending-locations'); ?></h3>
                <div class="fvl-stat-value" style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo count($payment_methods); ?></div>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=payment_method&post_type=vending_location'); ?>" style="display: inline-block; margin-top: 10px;"><?php _e('Manage Payments', 'farmer-vending-locations'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="fvl-admin-card">
        <h2><?php _e('Recent Locations', 'farmer-vending-locations'); ?></h2>
        
        <?php
        // Get recent locations
        $recent_locations = get_posts(array(
            'post_type' => 'vending_location',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        
        if ($recent_locations) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . __('Title', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Address', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Categories', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Payment Methods', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Date', 'farmer-vending-locations') . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($recent_locations as $location) {
                $address = get_post_meta($location->ID, '_fvl_address', true);
                $categories = wp_get_post_terms($location->ID, 'product_category', array('fields' => 'names'));
                $payments = wp_get_post_terms($location->ID, 'payment_method', array('fields' => 'names'));
                
                echo '<tr>';
                echo '<td><a href="' . get_edit_post_link($location->ID) . '">' . get_the_title($location->ID) . '</a></td>';
                echo '<td>' . esc_html($address) . '</td>';
                echo '<td>' . implode(', ', $categories) . '</td>';
                echo '<td>' . implode(', ', $payments) . '</td>';
                echo '<td>' . get_the_date('', $location->ID) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            
            echo '<p><a href="' . admin_url('edit.php?post_type=vending_location') . '" class="button">' . __('View All Locations', 'farmer-vending-locations') . '</a></p>';
        } else {
            echo '<p>' . __('No locations found.', 'farmer-vending-locations') . '</p>';
        }
        ?>
    </div>
    
    <div class="fvl-admin-card">
        <h2><?php _e('Recent Reviews', 'farmer-vending-locations'); ?></h2>
        
        <?php
        // Get recent reviews
        $recent_reviews = $wpdb->get_results(
            "SELECT * FROM {$reviews_table} ORDER BY date_created DESC LIMIT 5"
        );
        
        if ($recent_reviews) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . __('Location', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('User', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Rating', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Review', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Status', 'farmer-vending-locations') . '</th>';
            echo '<th>' . __('Date', 'farmer-vending-locations') . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($recent_reviews as $review) {
                $location_title = get_the_title($review->location_id);
                $user = get_userdata($review->user_id);
                $username = $user ? $user->display_name : __('Anonymous', 'farmer-vending-locations');
                $status = $review->approved ? __('Approved', 'farmer-vending-locations') : __('Pending', 'farmer-vending-locations');
                
                echo '<tr>';
                echo '<td><a href="' . get_edit_post_link($review->location_id) . '">' . $location_title . '</a></td>';
                echo '<td>' . esc_html($username) . '</td>';
                echo '<td>';
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $review->rating ? '★' : '☆';
                }
                echo '</td>';
                echo '<td>' . substr(esc_html($review->review), 0, 100) . (strlen($review->review) > 100 ? '...' : '') . '</td>';
                echo '<td>' . $status . '</td>';
                echo '<td>' . date_i18n(get_option('date_format'), strtotime($review->date_created)) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            
            echo '<p><a href="' . admin_url('admin.php?page=fvl-reviews') . '" class="button">' . __('View All Reviews', 'farmer-vending-locations') . '</a></p>';
        } else {
            echo '<p>' . __('No reviews found.', 'farmer-vending-locations') . '</p>';
        }
        ?>
    </div>
    
    <div class="fvl-admin-card">
        <h2><?php _e('Plugin Information', 'farmer-vending-locations'); ?></h2>
        
        <p><?php _e('Farmer Vending Locations is a plugin to manage and display vending machine locations on your WordPress site.', 'farmer-vending-locations'); ?></p>
        
        <h3><?php _e('Available Shortcodes', 'farmer-vending-locations'); ?></h3>
        
        <ul>
            <li><code>[fvl_map]</code> - <?php _e('Displays an interactive map of all vending locations.', 'farmer-vending-locations'); ?></li>
            <li><code>[fvl_location_list]</code> - <?php _e('Displays a list of all vending locations.', 'farmer-vending-locations'); ?></li>
            <li><code>[fvl_location_details id="123"]</code> - <?php _e('Displays details for a specific location.', 'farmer-vending-locations'); ?></li>
        </ul>
    </div>
</div>