<?php

/**
 * カメラアクション
 * クイックリプライ専用アクション
 */

namespace LINE\LINEBot\TemplateActionBuilder;

use LINE\LINEBot\TemplateActionBuilder;

/**
 *
 *
 * @package LINE\LINEBot\TemplateActionBuilder
 */
class CameraTemplateActionBuilder implements TemplateActionBuilder
{
    /** @var string */
    private $label;

    /**
     *
     *
     * @param string $label Label of action.
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * Builds URI action structure.
     *
     * @return array Built URI action structure.
     */
    public function buildTemplateAction()
    {
        return [
            'type' => 'camera',
            'label' => $this->label,
        ];
    }
}
