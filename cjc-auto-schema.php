<?php
/**
 * Plugin Name: CJC Auto Schema
 * Plugin URI: https://curtisjcooks.com
 * Description: Auto-generates Recipe and FAQ schema markup for CurtisJCooks.com
 * Version: 1.0.0
 * Author: CurtisJCooks
 * Author URI: https://curtisjcooks.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cjc-auto-schema
 */

// Prevent direct access
defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'CJC_AUTO_SCHEMA_VERSION', '1.0.0' );
define( 'CJC_AUTO_SCHEMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'CJC_AUTO_SCHEMA_URL', plugin_dir_url( __FILE__ ) );

// Recipe category IDs
define( 'CJC_RECIPE_CATEGORY_ID', 26 );
define( 'CJC_RECIPE_CHILD_CATEGORIES', array( 859, 860, 861, 862, 866, 873, 874 ) );

// Pillar page slugs (more reliable than IDs across environments)
define( 'CJC_PILLAR_PAGE_SLUGS', array(
    'guide-hawaiian-poke',
    'guide-plate-lunch',
    'guide-hawaiian-ingredients',
    'hawaiian-breakfast-guide',
    'hawaiian-drinks-guide',
    'hawaiian-desserts-guide',
    'hawaiian-pupus-guide',
) );

// Load plugin classes
require_once CJC_AUTO_SCHEMA_PATH . 'includes/class-recipe-parser.php';
require_once CJC_AUTO_SCHEMA_PATH . 'includes/class-recipe-schema.php';
require_once CJC_AUTO_SCHEMA_PATH . 'includes/class-faq-schema.php';
require_once CJC_AUTO_SCHEMA_PATH . 'includes/class-schema-output.php';

// Load admin class
if ( is_admin() ) {
    require_once CJC_AUTO_SCHEMA_PATH . 'admin/class-admin-settings.php';
}

/**
 * Initialize the plugin
 */
function cjc_auto_schema_init() {
    new CJC_Schema_Output();

    // Initialize admin settings
    if ( is_admin() ) {
        new CJC_Admin_Settings();
    }
}
add_action( 'init', 'cjc_auto_schema_init' );

/**
 * Plugin activation hook
 */
function cjc_auto_schema_activate() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cjc_auto_schema_activate' );

/**
 * Plugin deactivation hook
 */
function cjc_auto_schema_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cjc_auto_schema_deactivate' );
