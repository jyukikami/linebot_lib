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
class BoxBuilder
{
	/** バブルコンテナの配列 */
	private $layout;
	/**  */
	private $contents;
	/**  */
	private $flex;
	/**  */
	private $spacing;
	/**  */
	private $margin;
	

	private $spacing_size = array(
		 1 => "none"
		,2 => "xs"
		,3 => "sm"
		,4 => "md"
		,5 => "lg"
		,6 => "xl"
		,7 => "xxl"
	);

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

	
	public function __construct($layout,$contents,$flex,$spacing,$margin)
	{
		$this->layout = $layout;
		$this->contents = $contents;
		$this->flex = $flex;
		$this->spacing = $spacing;
		$this->margin = $margin;
	}

	/**
	 * Builds button template message structure.
	 *
	 * @return array
	 */
	public function buildTemplate()
	{
		$objects = array();
		foreach ((array)$this->contents as $key => $object) {
			$objects[] = $object->buildTemplate();
		}

		$this->template['type'] = "box";
		$this->template['contents'] = $objects;
		$this->template['layout'] = $this->layout;

		if (!empty($this->flex)) {
			$this->template['flex'] = $this->flex;
		}

		if (!empty($this->spacing)) {
			$this->template['spacing'] = $this->spacing_size[$this->spacing];
		}

		if (!empty($this->margin)) {
			$this->template['margin'] = $this->margin_size[$this->margin];
		}

		return $this->template;
	}
}
