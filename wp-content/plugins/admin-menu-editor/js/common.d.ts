///<reference path="knockout.d.ts"/>

type AmeDictionary<T> = Record<string, T>;

// noinspection JSUnusedGlobalSymbols
type KeysMatchingType<T, V> = { [K in keyof T]: T[K] extends V ? K : never }[keyof T];

type AmeCssBorderStyle = 'none' | 'solid' | 'dashed' | 'dotted' | 'double' | 'groove' | 'ridge' | 'outset';

interface AmeCssBorderSettings {
	style: AmeCssBorderStyle;
	color: string;
	width: number;
}

type AmeObservablePropertiesOf<T> = {
	[P in keyof T]: KnockoutObservable<T[P]>;
}

type AmeRecursiveObservablePropertiesOf<T> = {
	[P in keyof T]: T[P] extends object ? AmeRecursiveObservablePropertiesOf<T[P]> : KnockoutObservable<T[P]>;
}

type Constructor<T> = new (...args: any[]) => T;

type WithRequiredKey<T, K extends keyof T> = Omit<T, K> & Required<Pick<T, K>>;

interface JQuery {
	//My jQuery typings are out of date, and are missing this signature for the "off" method
	//where the callback takes additional arguments.
	off(events: string, handler: (eventObject: JQueryEventObject, ...args: any[]) => any): JQuery;
}

/**
 * Partial type definition for the WordPress "wp" global.
 * Sure would be nice if WordPress provided this.
 */
interface AmePartialWpGlobal {
	codeEditor: {
		//See /wp-admin/js/code-editor.js for basic method documentation.
		initialize: (textarea: string|JQuery|Element, options: object) => any;
	};
	media: {
		(attributes: {
			button: { text: string };
			library: { type: string };
			multiple: boolean;
			title: string
		}): any;

		attachment(id: number): any;
	};
	editor: {
		remove: (id: string) => void;
		initialize: (id: string, settings: Partial<WpEditorInitSettings>) => any;
	};
	hooks: {
		addFilter: (filterName: string, namespace: string, callback: (value: unknown, ...args: unknown[]) => unknown, priority?: number) => void;
		addAction: (actionName: string, namespace: string, callback: (...args: unknown[]) => unknown, priority?: number) => void;
		removeFilter: (filterName: string, namespace: string) => void;
		removeAction: (actionName: string, namespace: string) => void;
		applyFilters: (filterName: string, value: unknown, ...args: unknown[]) => unknown;
		doAction: (actionName: string, ...args: unknown[]) => void;
	};
}

declare const wp: AmePartialWpGlobal;

/**
 * Incomplete type definition for the settings object that can be passed to wp.editor.initialize().
 */
interface WpEditorInitSettings {
	tinymce: boolean | {
		wpautop: boolean;
		toolbar1?: string,
		toolbar2?: string,
		toolbar3?: string,
		toolbar4?: string,
	};
	quicktags: boolean | {
		buttons?: string;
	};
	mediaButtons: boolean;
}

/**
 * Webpack will replace this with a boolean literal. Note that
 * when not using Webpack, it will be undefined, so a typeof check
 * is recommended.
 */
declare const AME_IS_PRODUCTION: boolean;