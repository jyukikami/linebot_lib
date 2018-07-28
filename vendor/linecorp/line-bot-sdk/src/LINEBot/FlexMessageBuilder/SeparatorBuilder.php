<?php

/**
 * テキストコンポーネント
 * テキストを描画するコンポーネントです。テキストに書式を設定できます。
 */

namespace LINE\LINEBot\MessageBuilder\FlexBuilder;


/**
 * A builder class for button template message.
 *
 * @package LINE\LINEBot\MessageBuilder\TemplateBuilder
 */
class SeparatorBuilder
{
	/**  */
	private $margin;
	/**  */
	private $color;


	private $margin_size = array(
		 1 => "none"
		,2 => "xs"
		,3 => "sm"
		,4 => "md"
		,5 => "lg"
		,6 => "xl"
		,7 => "xxl"
	);
	

	private $template;

	
	public function __construct($margin,$color)
	{
		$this->margin = $margin;
		$this->color = $color;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$this->template['type'] = "separator";

		if (!empty($this->margin)) {
			$this->template['margin'] = $this->margin_size[$this->margin];
		}

		if (!empty($this->color)) {
			$this->template['color'] = $this->color;
		}

		return $this->template;
	}
}
