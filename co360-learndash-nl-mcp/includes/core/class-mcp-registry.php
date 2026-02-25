<?php
/**
 * Registry for MCP definitions.
 */
class CO360_LDMCP_MCP_Registry {
    /**
     * Instance.
     *
     * @var self
     */
    private static $instance;

    /**
     * MCP definitions.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Singleton.
     *
     * @return self
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register MCP definition.
     *
     * @param array                        $identity Identity array.
     * @param CO360_LDMCP_MCP_Definition   $definition Definition class.
     * @return void
     */
    public function register( array $identity, CO360_LDMCP_MCP_Definition $definition ): void {
        $this->definitions[ $identity['mcp'] ] = [
            'identity'   => $identity,
            'definition' => $definition,
        ];
    }

    /**
     * Get definition by MCP name.
     *
     * @param string $mcp MCP key.
     * @return CO360_LDMCP_MCP_Definition|null
     */
    public function get( string $mcp ): ?CO360_LDMCP_MCP_Definition {
        return $this->definitions[ $mcp ]['definition'] ?? null;
    }
}
