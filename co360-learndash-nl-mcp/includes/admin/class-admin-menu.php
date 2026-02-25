<?php
/**
 * Admin menu loader.
 */
class CO360_LDMCP_Admin_Menu {
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register admin pages.
     *
     * @return void
     */
    public function register_menu(): void {
        add_menu_page(
            __( 'CO360 MCP', 'co360-learndash-nl-mcp' ),
            __( 'CO360 MCP', 'co360-learndash-nl-mcp' ),
            'manage_options',
            'co360-ldmcp',
            [ $this, 'render_overview' ],
            'dashicons-admin-generic',
            56
        );

        $settings = new CO360_LDMCP_Settings_Page();
        $generate = new CO360_LDMCP_Generate_MCP_Page();
        $execute  = new CO360_LDMCP_Execute_MCP_Page();
        $templates = new CO360_LDMCP_Templates_Page();

        $settings->register_page();
        $generate->register_page();
        $execute->register_page();
        $templates->register_page();
    }

    /**
     * Overview page.
     */
    public function render_overview(): void {
        echo '<div class="wrap"><h1>CO360 – LearnDash MCP AI</h1><p>Motor MCP para generación y ejecución de cursos LearnDash.</p></div>';
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_assets(): void {
        wp_enqueue_style( 'co360-ldmcp-admin', CO360_LDMCP_PLUGIN_URL . 'assets/admin.css', [], CO360_LDMCP_VERSION );
        wp_enqueue_script( 'co360-ldmcp-admin', CO360_LDMCP_PLUGIN_URL . 'assets/admin.js', [ 'jquery' ], CO360_LDMCP_VERSION, true );
    }
}
