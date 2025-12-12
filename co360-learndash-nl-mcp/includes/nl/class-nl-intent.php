<?php
/**
 * Represents user intent in controlled natural language.
 */
class CO360_LDNLMCP_NL_Intent {

    /**
     * Description text.
     *
     * @var string
     */
    public $description;

    /**
     * Course type.
     *
     * @var string
     */
    public $course_type;

    /**
     * Level string.
     *
     * @var string
     */
    public $level;

    /**
     * Language.
     *
     * @var string
     */
    public $language;

    /**
     * Constructor.
     *
     * @param string $description Description.
     * @param string $course_type Course type.
     * @param string $level Level.
     * @param string $language Language.
     */
    public function __construct( $description, $course_type = '', $level = '', $language = '' ) {
        $this->description = $description;
        $this->course_type = $course_type;
        $this->level       = $level;
        $this->language    = $language;
    }
}
