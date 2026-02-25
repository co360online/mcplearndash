<?php
/**
 * Plugin Name: CO360 – LearnDash MCP AI Engine
 * Description: Motor MCP para generación y ejecución de cursos LearnDash con IA y reglas pedagógicas controladas.
 * Version: 0.1.0
 * Author: CO360
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: co360-learndash-mcp-ai
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Define plugin constants.
define( 'CO360_LDMCP_VERSION', '0.1.0' );
define( 'CO360_LDMCP_PLUGIN_FILE', __FILE__ );
define( 'CO360_LDMCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CO360_LDMCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoload plugin classes.
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/utils/class-utils.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/logs/class-mcp-logger.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/core/class-mcp-definition.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/core/class-mcp-schema.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/core/class-mcp-versioning.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/core/class-mcp-registry.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/core/class-mcp-resolver.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/engine/class-learndash-engine.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/engine/class-course-factory.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/engine/class-lesson-factory.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/engine/class-topic-factory.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/engine/class-quiz-factory.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/ai/class-openai-client.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/ai/class-prompt-library.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/ai/class-mcp-ai-generator.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/admin/class-generate-mcp-page.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/admin/class-execute-mcp-page.php';
require_once CO360_LDMCP_PLUGIN_DIR . 'includes/admin/class-templates-page.php';

/**
 * Boot plugin.
 */
function co360_ldmcp_boot() {
$registry = CO360_LDMCP_MCP_Registry::instance();
$definition = new CO360_LDMCP_MCP_Definition();
$registry->register( $definition->get_identity( 'LearnDashCourseMCP' ), $definition );

new CO360_LDMCP_Admin_Menu();
}
add_action( 'plugins_loaded', 'co360_ldmcp_boot' );

