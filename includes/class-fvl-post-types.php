<?php
/**
 * Class for registering custom post types and taxonomies
 */
class FVL_Post_Types {

    /**
     * Register the 'vending_location' custom post type
     */
    public function register_post_types() {
        $labels = array(
            'name'                  => _x('Vending Locations', 'Post type general name', 'farmer-vending-locations'),
            'singular_name'         => _x('Vending Location', 'Post type singular name', 'farmer-vending-locations'),
            'menu_name'             => _x('Vending Locations', 'Admin Menu text', 'farmer-vending-locations'),
            'name_admin_bar'        => _x('Vending Location', 'Add New on Toolbar', 'farmer-vending-locations'),
            'add_new'               => __('Add New', 'farmer-vending-locations'),
            'add_new_item'          => __('Add New Vending Location', 'farmer-vending-locations'),
            'new_item'              => __('New Vending Location', 'farmer-vending-locations'),
            'edit_item'             => __('Edit Vending Location', 'farmer-vending-locations'),
            'view_item'             => __('View Vending Location', 'farmer-vending-locations'),
            'all_items'             => __('All Vending Locations', 'farmer-vending-locations'),
            'search_items'          => __('Search Vending Locations', 'farmer-vending-locations'),
            'parent_item_colon'     => __('Parent Vending Locations:', 'farmer-vending-locations'),
            'not_found'             => __('No vending locations found.', 'farmer-vending-locations'),
            'not_found_in_trash'    => __('No vending locations found in Trash.', 'farmer-vending-locations'),
            'featured_image'        => _x('Location Featured Image', 'Overrides the "Featured Image" phrase', 'farmer-vending-locations'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'farmer-vending-locations'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'farmer-vending-locations'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'farmer-vending-locations'),
            'archives'              => _x('Vending Location archives', 'The post type archive label used in nav menus', 'farmer-vending-locations'),
            'insert_into_item'      => _x('Insert into vending location', 'Overrides the "Insert into post" phrase', 'farmer-vending-locations'),
            'uploaded_to_this_item' => _x('Uploaded to this vending location', 'Overrides the "Uploaded to this post" phrase', 'farmer-vending-locations'),
            'filter_items_list'     => _x('Filter vending locations list', 'Screen reader text for the filter links heading on the post type listing screen', 'farmer-vending-locations'),
            'items_list_navigation' => _x('Vending Locations list navigation', 'Screen reader text for the pagination heading on the post type listing screen', 'farmer-vending-locations'),
            'items_list'            => _x('Vending Locations list', 'Screen reader text for the items list heading on the post type listing screen', 'farmer-vending-locations'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'vending-location'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-location',
            'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );

        register_post_type('vending_location', $args);
    }

    /**
     * Register 'product_category' and 'payment_method' taxonomies
     */
    public function register_taxonomies() {
        // Product Category Taxonomy
        $cat_labels = array(
            'name'                       => _x('Product Categories', 'taxonomy general name', 'farmer-vending-locations'),
            'singular_name'              => _x('Product Category', 'taxonomy singular name', 'farmer-vending-locations'),
            'search_items'               => __('Search Product Categories', 'farmer-vending-locations'),
            'popular_items'              => __('Popular Product Categories', 'farmer-vending-locations'),
            'all_items'                  => __('All Product Categories', 'farmer-vending-locations'),
            'parent_item'                => __('Parent Product Category', 'farmer-vending-locations'),
            'parent_item_colon'          => __('Parent Product Category:', 'farmer-vending-locations'),
            'edit_item'                  => __('Edit Product Category', 'farmer-vending-locations'),
            'view_item'                  => __('View Product Category', 'farmer-vending-locations'),
            'update_item'                => __('Update Product Category', 'farmer-vending-locations'),
            'add_new_item'               => __('Add New Product Category', 'farmer-vending-locations'),
            'new_item_name'              => __('New Product Category Name', 'farmer-vending-locations'),
            'separate_items_with_commas' => __('Separate product categories with commas', 'farmer-vending-locations'),
            'add_or_remove_items'        => __('Add or remove product categories', 'farmer-vending-locations'),
            'choose_from_most_used'      => __('Choose from the most used product categories', 'farmer-vending-locations'),
            'not_found'                  => __('No product categories found.', 'farmer-vending-locations'),
            'no_terms'                   => __('No product categories', 'farmer-vending-locations'),
            'menu_name'                  => __('Product Categories', 'farmer-vending-locations'),
            'items_list_navigation'      => __('Product Categories list navigation', 'farmer-vending-locations'),
            'items_list'                 => __('Product Categories list', 'farmer-vending-locations'),
            'back_to_items'              => __('Back to product categories', 'farmer-vending-locations'),
        );

        $cat_args = array(
            'labels'            => $cat_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true,
        );

        register_taxonomy('product_category', array('vending_location'), $cat_args);

        // Payment Method Taxonomy
        $payment_labels = array(
            'name'                       => _x('Payment Methods', 'taxonomy general name', 'farmer-vending-locations'),
            'singular_name'              => _x('Payment Method', 'taxonomy singular name', 'farmer-vending-locations'),
            'search_items'               => __('Search Payment Methods', 'farmer-vending-locations'),
            'popular_items'              => __('Popular Payment Methods', 'farmer-vending-locations'),
            'all_items'                  => __('All Payment Methods', 'farmer-vending-locations'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit Payment Method', 'farmer-vending-locations'),
            'view_item'                  => __('View Payment Method', 'farmer-vending-locations'),
            'update_item'                => __('Update Payment Method', 'farmer-vending-locations'),
            'add_new_item'               => __('Add New Payment Method', 'farmer-vending-locations'),
            'new_item_name'              => __('New Payment Method Name', 'farmer-vending-locations'),
            'separate_items_with_commas' => __('Separate payment methods with commas', 'farmer-vending-locations'),
            'add_or_remove_items'        => __('Add or remove payment methods', 'farmer-vending-locations'),
            'choose_from_most_used'      => __('Choose from the most used payment methods', 'farmer-vending-locations'),
            'not_found'                  => __('No payment methods found.', 'farmer-vending-locations'),
            'no_terms'                   => __('No payment methods', 'farmer-vending-locations'),
            'menu_name'                  => __('Payment Methods', 'farmer-vending-locations'),
            'items_list_navigation'      => __('Payment Methods list navigation', 'farmer-vending-locations'),
            'items_list'                 => __('Payment Methods list', 'farmer-vending-locations'),
            'back_to_items'              => __('Back to payment methods', 'farmer-vending-locations'),
        );

        $payment_args = array(
            'labels'            => $payment_labels,
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true,
        );

        register_taxonomy('payment_method', array('vending_location'), $payment_args);
    }
}