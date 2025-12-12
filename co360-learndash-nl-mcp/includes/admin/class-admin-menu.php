<?php
/**
 * Registers admin menu entries.
 */
class CO360_LDNLMCP_Admin_Menu {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    /**
     * Adds top-level and submenus.
     */
    public function register_menu() {
        add_menu_page(
            __( 'CO360', 'co360-ldnlmcp' ),
            __( 'CO360', 'co360-ldnlmcp' ),
            'manage_options',
            'co360-ldnlmcp',
            array( $this, 'render_main_redirect' ),
            'dashicons-welcome-learn-more'
        );

        add_submenu_page(
            'co360-ldnlmcp',
            __( 'Crear curso con IA', 'co360-ldnlmcp' ),
            __( 'Crear curso con IA', 'co360-ldnlmcp' ),
            'manage_options',
            'co360-ldnlmcp-nl-course',
            array( $this, 'render_nl_page' )
        );

        add_submenu_page(
            'co360-ldnlmcp',
            __( 'Ajustes IA', 'co360-ldnlmcp' ),
            __( 'Ajustes IA', 'co360-ldnlmcp' ),
            'manage_options',
            'co360-ldnlmcp-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'co360-ldnlmcp',
            __( 'Ejecutar MCP', 'co360-ldnlmcp' ),
            __( 'Ejecutar MCP', 'co360-ldnlmcp' ),
            'manage_options',
            'co360-ldnlmcp-execute',
            array( $this, 'render_execute_page' )
        );
    }

    /**
     * Redirect main menu to NL page.
     */
    public function render_main_redirect() {
        wp_safe_redirect( admin_url( 'admin.php?page=co360-ldnlmcp-nl-course' ) );
        exit;
    }

    /**
     * Render natural language course builder page.
     */
    public function render_nl_page() {
        $page = new CO360_LDNLMCP_NL_Course_Page();
        $page->render();
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        $page = new CO360_LDNLMCP_Settings_Page();
        $page->render();
    }

    /**
     * Render execute MCP page.
     */
    public function render_execute_page() {
        $page = new CO360_LDNLMCP_Execute_MCP_Page();
        $page->render();
    }
}
