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
class TextBuilder
{
	/**  */
	private $text;
	/**  */
	private $flex;
	/**  */
	private $margin;
	/**  */
	private $size;
	/**  */
	private $align;
	/**  */
	private $gravity;
	/**  */
	private $wrap;
	/**  */
	private $weight;
	/**  */
	private $color;
	/**  */
	private $action;


	private $margin_size = array(
		 1 => "none"
		,2 => "xs"
		,3 => "sm"
		,4 => "md"
		,5 => "lg"
		,6 => "xl"
		,7 => "xxl"
	);

	private $font_size = array(
		 1 => "none"
		,2 => "xs"
		,3 => "sm"
		,4 => "md"
		,5 => "lg"
		,6 => "xl"
		,7 => "xxl"
		,8 => "3xl"
		,9 => "4xl"
		,10 => "5xl"
	);

	private $template;

	
	public function __construct($text,$flex,$margin,$size,$align,$gravity,$wrap,$weight,$color,$action)
	{
		$this->text = $text;
		$this->flex = $flex;
		$this->margin = $margin;
		$this->size = $size;
		$this->align = $align;
		$this->gravity = $gravity;
		$this->wrap = $wrap;
		$this->weight = $weight;
		$this->color = $color;
		$this->action = $action;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$this->template['type'] = "text";
		$this->template['text'] = $this->text;

		if (!empty($this->flex)) {
			$this->template['flex'] = $this->flex;
		}

		if (!empty($this->margin)) {
			$this->template['margin'] = $this->margin_size[$this->margin];
		}

		if (!empty($this->size)) {
			$this->template['size'] = $this->font_size[$this->size];
		}

		if (!empty($this->align)) {
			$this->template['align'] = $this->align;
		}

		if (!empty($this->gravity)) {
			$this->template['gravity'] = $this->gravity;
		}

		if (!empty($this->wrap)) {
			$this->template['wrap'] = $this->wrap;
		}

		if (!empty($this->weight)) {
			$this->template['weight'] = $this->weight;
		}

		if (!empty($this->color)) {
			$this->template['color'] = $this->color;
		}

		if (!empty($this->action)) {
			$this->template['action'] = $this->action->buildTemplateAction();
		}

		

		return $this->template;
	}
}
