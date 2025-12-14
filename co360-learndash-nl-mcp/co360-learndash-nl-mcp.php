<?php
/**
 * Plugin Name: CO360 – LearnDash Natural Language Course Builder (MCP + AI)
 * Plugin URI: https://example.com
 * Description: Create LearnDash courses from controlled natural language using OpenAI governed by a Model Context Protocol (MCP).
 * Version: 1.1.0
 * Author: CO360
 * Text Domain: co360-ldnlmcp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
if ( ! defined( 'CO360_LDNLMCP_PATH' ) ) {
    define( 'CO360_LDNLMCP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CO360_LDNLMCP_URL' ) ) {
    define( 'CO360_LDNLMCP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CO360_LDNLMCP_VERSION' ) ) {
    define( 'CO360_LDNLMCP_VERSION', '1.1.0' );
}

// Simple autoloader for plugin classes.
spl_autoload_register( function ( $class ) {
    if ( false === strpos( $class, 'CO360_LDNLMCP_' ) ) {
        return;
    }

    $file = strtolower( str_replace( 'CO360_LDNLMCP_', '', $class ) );
    $file = str_replace( '_', '-', $file );
    $paths = array(
        CO360_LDNLMCP_PATH . 'includes/nl/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/mcp/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/ai/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/engine/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/admin/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/logs/class-' . $file . '.php',
        CO360_LDNLMCP_PATH . 'includes/content/class-' . $file . '.php',
    );

    foreach ( $paths as $path ) {
        if ( file_exists( $path ) ) {
            include $path;
            return;
        }
    }
} );

// Initialize plugin.
function co360_ldnlmcp_init() {
    // Admin pages.
    if ( is_admin() ) {
        new CO360_LDNLMCP_Admin_Menu();
    }
}
add_action( 'plugins_loaded', 'co360_ldnlmcp_init' );
