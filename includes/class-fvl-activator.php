<?php
/**
 * Class for plugin activation
 */
class FVL_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wpdb;
        
        // Create database tables
        self::create_tables();
        
        // Register custom post type
        self::register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Reviews table
        $table_name = $wpdb->prefix . 'fvl_reviews';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            location_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            rating tinyint(1) NOT NULL,
            review text NOT NULL,
            approved tinyint(1) NOT NULL DEFAULT 0,
            date_created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            date_modified datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Register custom post types
     */
    private static function register_post_types() {
        // This will be called from the main plugin class on init
        // Just defining it here for organization, but actual registration
        // will be done on WordPress init
    }
}