<?php
/**
 * Provides prompt templates.
 */
class CO360_LDNLMCP_Prompt_Library {

    /**
     * System prompt per requirements.
     *
     * @return string
     */
    public function system_prompt() {
        return 'Eres un intérprete de lenguaje natural que convierte descripciones humanas en payloads MCP LearnDashCourseMCP v1.1. '
            . 'Reconoces instrucciones sobre templates Elementor, campos ACF y formularios Gravity Forms. '
            . 'No inventes IDs ni campos: solo utiliza los que el usuario haya descrito. '
            . 'Si algo no está claro, haz una suposición conservadora. '
            . 'Devuelve SOLO JSON válido que cumpla el schema MCP. No incluyas texto adicional.';
    }
}
