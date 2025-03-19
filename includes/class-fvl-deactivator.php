<?php
/**
 * Class for plugin deactivation
 */
class FVL_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
        
        // Any other cleanup tasks
    }
}