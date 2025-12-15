<?php
/**
 * Manages Content Binding instructions separate from MCP payloads.
 */
class CO360_LDNLMCP_Content_Binding {

    /**
     * Binding data keyed by module/lesson indexes.
     *
     * @var array
     */
    private $binding = array();

    /**
     * Constructor.
     *
     * @param array $binding Initial binding map.
     */
    public function __construct( $binding = array() ) {
        $this->binding = is_array( $binding ) ? $binding : array();
    }

    /**
     * Build instance from encoded binding payload.
     *
     * @param string $encoded Base64 encoded JSON binding.
     * @return self
     */
    public static function from_encoded( $encoded ) {
        $decoded = json_decode( base64_decode( $encoded ), true );
        if ( ! is_array( $decoded ) ) {
            $decoded = array();
        }
        return new self( $decoded );
    }

    /**
     * Persist binding as encoded string for hidden fields.
     *
     * @return string
     */
    public function to_encoded() {
        if ( empty( $this->binding ) ) {
            return '';
        }
        return base64_encode( wp_json_encode( $this->binding ) );
    }

    /**
     * Attach a validated content block to a topic index.
     *
     * @param int   $module_index Module index.
     * @param int   $lesson_index Lesson index.
     * @param array $content_block Content block data (type/template/acf/gravity_form_id).
     *
     * @return true|WP_Error
     */
    public function bind_topic( $module_index, $lesson_index, $content_block ) {
        $resolver      = new CO360_LDNLMCP_Content_Block_Resolver();
        $validated     = $resolver->validate_and_enrich( $content_block );
        if ( is_wp_error( $validated ) ) {
            return $validated;
        }

        if ( ! isset( $this->binding['modules'] ) || ! is_array( $this->binding['modules'] ) ) {
            $this->binding['modules'] = array();
        }

        if ( ! isset( $this->binding['modules'][ $module_index ]['lessons'] ) || ! is_array( $this->binding['modules'][ $module_index ]['lessons'] ) ) {
            $this->binding['modules'][ $module_index ]['lessons'] = array();
        }

        $this->binding['modules'][ $module_index ]['lessons'][ $lesson_index ]['content_block'] = $validated;
        return true;
    }

    /**
     * Retrieve content block for indexes if exists.
     *
     * @param int $module_index Module index.
     * @param int $lesson_index Lesson index.
     * @return array|null
     */
    public function get_content_block( $module_index, $lesson_index ) {
        if ( isset( $this->binding['modules'][ $module_index ]['lessons'][ $lesson_index ]['content_block'] ) ) {
            return $this->binding['modules'][ $module_index ]['lessons'][ $lesson_index ]['content_block'];
        }
        return null;
    }

    /**
     * Return full binding array.
     *
     * @return array
     */
    public function to_array() {
        return $this->binding;
    }
}
