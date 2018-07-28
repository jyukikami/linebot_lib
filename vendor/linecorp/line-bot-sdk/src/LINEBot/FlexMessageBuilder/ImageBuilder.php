<?php

/**
 * ボックスコンポーネント
 * 子要素のレイアウトを定義するコンポーネントです。ボックスにボックスを含めることもできます。
 */

namespace LINE\LINEBot\MessageBuilder\FlexBuilder;


/**
 * A builder class for button template message.
 *
 * @package LINE\LINEBot\MessageBuilder\TemplateBuilder
 */
class ImageBuilder
{
	/**  */
	private $url;
	/**  */
	private $margin;
	/**  */
	private $flex;
	/**  */
	private $align;
	/**  */
	private $gravity;
	/**  */
	private $size;
	/**  */
	private $aspectRatio;
	/**  */
	private $aspectMode;
	/**  */
	private $backgroundColor;
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

	private $width_size = array(
		 1 => "xxs"
		,2 => "xs"
		,3 => "sm"
		,4 => "md"
		,5 => "lg"
		,6 => "xl"
		,7 => "xxl"
		,8 => "3xl"
		,9 => "4xl"
		,10 => "5xl"
		,11 => "full"
	);

	private $template;

	
	public function __construct($url,$margin,$flex,$align,$gravity,$size,$aspectRatio,$aspectMode,$backgroundColor,$action)
	{
		$this->url = $url;
		$this->margin = $margin;
		$this->flex = $flex;
		$this->align = $align;
		$this->gravity = $gravity;
		$this->size = $size;
		$this->aspectRatio = $aspectRatio;
		$this->aspectMode = $aspectMode;
		$this->backgroundColor = $backgroundColor;
		$this->action = $action;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$this->template['type'] = "image";
		$this->template['url'] = $this->url;

		if (!empty($this->margin)) {
			$this->template['margin'] = $this->margin_size[$this->margin];
		}

		if (!empty($this->flex)) {
			$this->template['flex'] = $this->flex;
		}

		if (!empty($this->align)) {
			$this->template['align'] = $this->align;
		}

		if (!empty($this->gravity)) {
			$this->template['gravity'] = $this->gravity;
		}

		if (!empty($this->size)) {
			$this->template['size'] = $this->width_size[$this->size];
		}

		if (!empty($this->aspectRatio)) {
			$this->template['aspectRatio'] = $this->aspectRatio;
		}

		if (!empty($this->aspectMode)) {
			$this->template['aspectMode'] = $this->aspectMode;
		}

		if (!empty($this->backgroundColor)) {
			$this->template['backgroundColor'] = $this->backgroundColor;
		}

		if (!empty($this->action)) {
			$this->template['action'] = $this->action->buildTemplateAction();
		}

		return $this->template;
	}
}
