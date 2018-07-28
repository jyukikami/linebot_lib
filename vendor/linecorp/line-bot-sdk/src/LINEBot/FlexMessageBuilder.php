<?php

namespace LINE\LINEBot\MessageBuilder;

use LINE\LINEBot\Constant\MessageType;
use LINE\LINEBot\MessageBuilder;

/**
 * A builder class for template message.
 *
 * @package LINE\LINEBot\MessageBuilder
 */
class FlexMessageBuilder implements MessageBuilder
{
    /** @var string */
    private $altText;
    /** @var TemplateBuilder */
    private $contentsBuilder;

    /**
     * TemplateMessageBuilder constructor.
     * @param string $altText
     * @param TemplateBuilder $contentsBuilder
     */
    public function __construct($altText, $contentsBuilder)
    {
        $this->altText = $altText;
        $this->contentsBuilder = $contentsBuilder;
    }

    /**
     * Builds template message structure.
     *
     * @return array
     */
    public function buildMessage()
    {
        return [
            [
                'type' => MessageType::FLEX,
                'altText' => $this->altText,
                'contents' => $this->contentsBuilder->buildTemplate(),
            ]
        ];
    }
}
