<?php

declare(strict_types=1);

namespace Acme\Starter\Field;

use TotalCMS\Domain\Admin\FormField\FormField;
use TotalCMS\Domain\Rendering\Utilities\HTMLUtils;

/**
 * Simple color picker field using the native HTML color input.
 *
 * Usage in a schema:
 *   "accentColor": { "type": "string", "field": "colorpicker", "label": "Accent Color" }
 */
class ColorPickerField extends FormField
{
	protected string $defaultInputType = 'color';
	protected string $defaultFieldType = 'colorpicker';
	protected bool $icon = false;

	public function buildFormField(): string
	{
		$attributes = $this->formFieldAttributes();

		// Ensure the value is a valid hex color, default to black
		$value = $this->value;
		if (!is_string($value) || $value === '' || !str_starts_with($value, '#')) {
			$value = $this->default !== '' ? (string) $this->default : '#000000';
		}
		$attributes['value'] = $value;

		$input = HTMLUtils::inlineElement('input', $attributes);

		// Show the current hex value next to the picker
		$hexDisplay = HTMLUtils::element('span', $value, [
			'class' => 'color-hex-value',
			'style' => 'font-family:monospace; margin-left:0.5rem; color:oklch(var(--totalform-darkgray))',
		]);

		return HTMLUtils::element('div', $input . $hexDisplay, [
			'style' => 'display:flex; align-items:center',
		]);
	}
}
