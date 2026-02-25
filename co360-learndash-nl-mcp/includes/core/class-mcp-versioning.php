<?php
/**
 * Handle MCP versioning.
 */
class CO360_LDMCP_MCP_Versioning {
    /**
     * Get supported versions.
     *
     * @return array
     */
    public function get_versions(): array {
        return [ '1.0' ];
    }

    /**
     * Check version is compatible.
     *
     * @param string $version Version string.
     * @return bool
     */
    public function is_supported( string $version ): bool {
        return in_array( $version, $this->get_versions(), true );
    }
}
