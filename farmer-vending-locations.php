<?php
/**
 * Plugin Name: Farmer Vending Locations
 * Plugin URI: https://www.zapt.ai
 * Description: Interactive Google Maps integration showing vending machine locations with detailed management and review system.
 * Version: 1.0.0
 * Author: ZAPT
 * Author URI: https://www.zapt.ai
 * Text Domain: farmer-vending-locations
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('FVL_VERSION', '1.0.0');
define('FVL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FVL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FVL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Activation and deactivation hooks
register_activation_hook(__FILE__, 'fvl_activate');
register_deactivation_hook(__FILE__, 'fvl_deactivate');

/**
 * Plugin activation function
 */
function fvl_activate() {
    // Create custom tables for reviews if they don't exist
    require_once FVL_PLUGIN_DIR . 'includes/class-fvl-activator.php';
    FVL_Activator::activate();
}

/**
 * Plugin deactivation function
 */
function fvl_deactivate() {
    // Cleanup tasks
    require_once FVL_PLUGIN_DIR . 'includes/class-fvl-deactivator.php';
    FVL_Deactivator::deactivate();
}

/**
 * Load the required dependencies and start the plugin
 */
function fvl_init() {
    // Include necessary files
    require_once FVL_PLUGIN_DIR . 'includes/class-fvl-loader.php';
    
    // Initialize the plugin
    $plugin = new FVL_Loader();
    $plugin->run();
}

// Initialize plugin after WordPress is fully loaded
add_action('plugins_loaded', 'fvl_init');