<?php
/**
 * The main plugin class
 */
class FVL_Loader {

    /**
     * The array of actions registered with WordPress.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     */
    protected $filters;
    
    /**
     * Initialize the collections used to maintain the actions and filters.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_post_types();
    }
    
    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Core plugin classes
        require_once FVL_PLUGIN_DIR . 'includes/class-fvl-post-types.php';
        require_once FVL_PLUGIN_DIR . 'includes/class-fvl-shortcodes.php';
        require_once FVL_PLUGIN_DIR . 'includes/class-fvl-geocoding.php';
        require_once FVL_PLUGIN_DIR . 'includes/class-fvl-reviews.php';
        
        // Admin specific
        require_once FVL_PLUGIN_DIR . 'admin/class-fvl-admin.php';
        
        // Public facing
        require_once FVL_PLUGIN_DIR . 'public/class-fvl-public.php';
    }
    
    /**
     * Register the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new FVL_Admin();
        
        // Admin scripts and styles
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Admin menu
        $this->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        
        // Admin init for settings
        $this->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Meta boxes for location details
        $this->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->add_action('save_post', $plugin_admin, 'save_meta_boxes', 10, 2);
        
        // AJAX handlers for admin
        $this->add_action('wp_ajax_fvl_approve_review', $plugin_admin, 'approve_review');
        $this->add_action('wp_ajax_fvl_delete_review', $plugin_admin, 'delete_review');
        $this->add_action('wp_ajax_fvl_geocode_address', $plugin_admin, 'geocode_address');
    }
    
    /**
     * Register the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new FVL_Public();
        
        // Public scripts and styles
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Shortcode registration
        $shortcodes = new FVL_Shortcodes();
        $this->add_action('init', $shortcodes, 'register_shortcodes');
        
        // AJAX handlers for public
        $this->add_action('wp_ajax_fvl_submit_review', $plugin_public, 'submit_review');
        $this->add_action('wp_ajax_nopriv_fvl_submit_review', $plugin_public, 'submit_review');
        $this->add_action('wp_ajax_fvl_get_locations', $plugin_public, 'get_locations');
        $this->add_action('wp_ajax_nopriv_fvl_get_locations', $plugin_public, 'get_locations');
    }
    
    /**
     * Register custom post types and taxonomies
     */
    private function define_post_types() {
        $post_types = new FVL_Post_Types();
        $this->add_action('init', $post_types, 'register_post_types');
        $this->add_action('init', $post_types, 'register_taxonomies');
    }
    
    /**
     * Add a new action to the collection to be registered with WordPress.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * Add a new filter to the collection to be registered with WordPress.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        
        return $hooks;
    }
    
    /**
     * Run the plugin.
     */
    public function run() {
        // Register all actions
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
        
        // Register all filters
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}