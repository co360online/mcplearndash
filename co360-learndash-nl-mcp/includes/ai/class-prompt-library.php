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
            . 'El JSON solo debe contener la estructura del curso (curso, módulos, temas, tests, examen final). '
            . 'Ignora cualquier instrucción sobre templates de Elementor, campos ACF o Gravity Forms: no debes incluir content_block ni campos de contenido avanzado. '
            . 'No inventes IDs ni campos. Si algo no está claro, haz una suposición conservadora. '
            . 'Devuelve SOLO JSON válido que cumpla el schema MCP. No incluyas texto adicional.';
    }
}
