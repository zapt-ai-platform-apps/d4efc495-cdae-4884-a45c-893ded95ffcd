<?php
/**
 * Class for handling reviews
 */
class FVL_Reviews {

    /**
     * Submit a new review
     */
    public static function submit_review($location_id, $user_id, $rating, $review_text) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        // Check if user has already reviewed this location
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE location_id = %d AND user_id = %d",
            $location_id,
            $user_id
        ));
        
        if ($existing) {
            // Update existing review
            $result = $wpdb->update(
                $table_name,
                array(
                    'rating' => $rating,
                    'review' => $review_text,
                    'approved' => 0, // Set to unapproved when updating
                    'date_modified' => current_time('mysql')
                ),
                array(
                    'location_id' => $location_id,
                    'user_id' => $user_id
                )
            );
            
            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Failed to update your review.', 'farmer-vending-locations')
                );
            }
            
            return array(
                'success' => true,
                'message' => __('Your review has been updated and is pending approval.', 'farmer-vending-locations')
            );
        } else {
            // Insert new review
            $result = $wpdb->insert(
                $table_name,
                array(
                    'location_id' => $location_id,
                    'user_id' => $user_id,
                    'rating' => $rating,
                    'review' => $review_text,
                    'approved' => 0, // Default to unapproved
                    'date_created' => current_time('mysql'),
                    'date_modified' => current_time('mysql')
                )
            );
            
            if (!$result) {
                return array(
                    'success' => false,
                    'message' => __('Failed to submit your review.', 'farmer-vending-locations')
                );
            }
            
            return array(
                'success' => true,
                'message' => __('Your review has been submitted and is pending approval.', 'farmer-vending-locations')
            );
        }
    }
    
    /**
     * Get reviews for a location
     */
    public static function get_location_reviews($location_id, $approved_only = true) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        // Prepare query
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE location_id = %d",
            $location_id
        );
        
        // Add approved filter if needed
        if ($approved_only) {
            $query .= " AND approved = 1";
        }
        
        // Add order
        $query .= " ORDER BY date_created DESC";
        
        // Get the reviews
        $reviews = $wpdb->get_results($query);
        
        // Add user data to each review
        foreach ($reviews as &$review) {
            $user = get_userdata($review->user_id);
            if ($user) {
                $review->user_name = $user->display_name;
                $review->user_avatar = get_avatar_url($user->ID);
            } else {
                $review->user_name = __('Anonymous', 'farmer-vending-locations');
                $review->user_avatar = '';
            }
        }
        
        return $reviews;
    }
    
    /**
     * Get the average rating for a location
     */
    public static function get_location_average_rating($location_id) {
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
    
    /**
     * Approve a review
     */
    public static function approve_review($review_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        $result = $wpdb->update(
            $table_name,
            array('approved' => 1),
            array('id' => $review_id)
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a review
     */
    public static function delete_review($review_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $review_id)
        );
        
        return $result !== false;
    }
}