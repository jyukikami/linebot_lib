<?php

/**
 * カルーセルコンテナ
 * 複数のバブルコンテナ、つまり複数のメッセージバブルを構成するコンテナです。バブルは横にスクロールして順番に表示できます。
 */

namespace LINE\LINEBot\MessageBuilder\FlexBuilder;


/**
 * A builder class for button template message.
 *
 * @package LINE\LINEBot\MessageBuilder\TemplateBuilder
 */
class ContentsBuilder
{
	/** バブルコンテナの配列 */
	private $contents;

	private $template;

	
	public function __construct($contents)
	{
		$this->contents = $contents;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$bubbles = array();
		if (!empty($this->contents)) {
			foreach ((array)$this->contents as $key => $bubble) {
				$bubbles[] = $bubble->buildTemplate();
			}
		}

		$this->template['type'] = "carousel";
		$this->template['contents'] = $bubbles;

		return $this->template;
	}
}
