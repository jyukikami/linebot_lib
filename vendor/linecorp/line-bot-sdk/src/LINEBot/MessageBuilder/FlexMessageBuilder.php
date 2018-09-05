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
    /** @var array */
    private $quickReplys;

    /**
     * TemplateMessageBuilder constructor.
     * @param string $altText
     * @param TemplateBuilder $contentsBuilder
     */
    public function __construct($altText, $contentsBuilder,$quickReplys=array())
    {
        $this->altText = $altText;
        $this->contentsBuilder = $contentsBuilder;
        $this->quickReplys = $quickReplys;
    }

    /**
     * Builds template message structure.
     *
     * @return array
     */
    public function buildMessage()
    {
        $actions = array();
        if (!empty($this->quickReplys)) {
            foreach ($this->quickReplys as $key => $action) {
                $actions[] = [
                    'type' => 'action',
                    'imageUrl' => $action["icon"],
                    'action' => $action["action"]->buildTemplateAction()
                ];
            }
        }
        $message = [
            'type' => MessageType::FLEX,
            'altText' => $this->altText,
            'contents' => $this->contentsBuilder->buildTemplate(),
        ];
        if (!empty($actions)) {
            $message['quickReply']['items'] = $actions;
        }
        return [$message];
    }
}
