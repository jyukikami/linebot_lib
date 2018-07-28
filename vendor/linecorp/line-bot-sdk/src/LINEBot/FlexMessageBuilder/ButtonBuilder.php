<?php

/**
 * ボタンコンポーネント
 * ボタンを描画するコンポーネントです。ユーザーがタップすると、指定したアクションが実行されます
 */

namespace LINE\LINEBot\MessageBuilder\FlexBuilder;

use LINE\LINEBot\TemplateActionBuilder;

/**
 * A builder class for button template message.
 *
 * @package LINE\LINEBot\MessageBuilder\TemplateBuilder
 */
class ButtonBuilder
{
	/**  */
	private $action;
	/**  */
	private $flex;
	/**  */
	private $margin;
	/**  */
	private $height;
	/**  */
	private $style;
	/**  */
	private $color;
	/**  */
	private $gravity;
	


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

	
	public function __construct($action,$flex,$margin,$height,$style,$color,$gravity)
	{
		$this->action = $action;
		$this->flex = $flex;
		$this->margin = $margin;
		$this->height = $height;
		$this->style = $style;
		$this->color = $color;
		$this->gravity = $gravity;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$this->template['type'] = "button";
		$this->template['action'] = $this->action->buildTemplateAction();

		if (!empty($this->flex)) {
			$this->template['flex'] = $this->flex;
		}

		if (!empty($this->margin)) {
			$this->template['margin'] = $this->margin_size[$this->margin];;
		}

		if (!empty($this->height)) {
			$this->template['height'] = $this->height;
		}

		if (!empty($this->style)) {
			$this->template['style'] = $this->style;
		}

		if (!empty($this->color)) {
			$this->template['color'] = $this->color;
		}

		if (!empty($this->gravity)) {
			$this->template['gravity'] = $this->gravity;
		}

		return $this->template;
	}
}
