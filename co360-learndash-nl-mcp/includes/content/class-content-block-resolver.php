<?php
/**
 * Resolves and validates content_block instructions against real resources.
 */
class CO360_LDNLMCP_Content_Block_Resolver {

    /**
     * Elementor scanner.
     *
     * @var CO360_LDNLMCP_Elementor_Scanner
     */
    protected $elementor;

    /**
     * ACF scanner.
     *
     * @var CO360_LDNLMCP_ACF_Scanner
     */
    protected $acf;

    /**
     * Gravity Forms scanner.
     *
     * @var CO360_LDNLMCP_GravityForms_Scanner
     */
    protected $gf;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->elementor = new CO360_LDNLMCP_Elementor_Scanner();
        $this->acf       = new CO360_LDNLMCP_ACF_Scanner();
        $this->gf        = new CO360_LDNLMCP_GravityForms_Scanner();
    }

    /**
     * Validate a topic content_block exists and enrich with metadata.
     *
     * @param array $content_block Content block.
     * @return array|WP_Error
     */
    public function validate_and_enrich( $content_block ) {
        if ( empty( $content_block['type'] ) || 'elementor_template' !== $content_block['type'] ) {
            return new WP_Error( 'co360_ldnlmcp_cb_type', __( 'El content_block debe ser de tipo elementor_template.', 'co360-ldnlmcp' ) );
        }

        $template = $this->elementor->get_template( $content_block['template_id'] );
        if ( ! $template ) {
            return new WP_Error( 'co360_ldnlmcp_cb_template', __( 'El template Elementor indicado no existe.', 'co360-ldnlmcp' ) );
        }

        $available_fields = $this->acf->get_fields_for_template( $template['id'] );
        $submitted_fields = isset( $content_block['acf_fields'] ) && is_array( $content_block['acf_fields'] ) ? $content_block['acf_fields'] : array();

        foreach ( $submitted_fields as $field_key => $value ) {
            if ( ! isset( $available_fields[ $field_key ] ) ) {
                return new WP_Error( 'co360_ldnlmcp_cb_acf_field', sprintf( __( 'El campo ACF %s no existe en el template.', 'co360-ldnlmcp' ), esc_html( $field_key ) ) );
            }
            if ( is_array( $value ) ) {
                return new WP_Error( 'co360_ldnlmcp_cb_acf_value', __( 'Los campos ACF deben ser valores escalares.', 'co360-ldnlmcp' ) );
            }
        }

        $form = null;
        if ( isset( $content_block['gravity_form_id'] ) && '' !== $content_block['gravity_form_id'] && null !== $content_block['gravity_form_id'] ) {
            $form = $this->gf->get_form( $content_block['gravity_form_id'] );
            if ( ! $form ) {
                return new WP_Error( 'co360_ldnlmcp_cb_gf', __( 'El formulario Gravity Forms no existe.', 'co360-ldnlmcp' ) );
            }
        }

        $content_block['template']       = $template;
        $content_block['acf_field_meta'] = $available_fields;
        $content_block['gravity_form']   = $form;

        return $content_block;
    }
}
