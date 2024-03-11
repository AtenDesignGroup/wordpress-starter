/*
 * TypeScript definitions for the internal popup slider. It's exposed in the global
 * scope as "AmePopupSlider".
 */

declare class AmePopupSlider {
	constructor($container: JQuery, options?: AmePopupSliderOptions);

	showForInput($input: JQuery): void;

	static createSlider($container: JQuery, options?: AmePopupSliderOptions): AmePopupSlider;
}

declare type AmePopupSliderRanges = Record<string, {
	min: number;
	max: number;
	step: number;
}>;

declare interface AmePopupSliderOptions {
	positionParentSelector?: string;
	verticalOffset?: number;
	/**
	 * A dictionary of slider ranges. Overrides the ranges specified in the HTML if present.
	 */
	ranges?: AmePopupSliderRanges|null;
	/**
	 * A jQuery object that represents the slider element, if it has already been created.
	 */
	sliderElement?: JQuery|null;
	positionWithinClosest?: string;
}