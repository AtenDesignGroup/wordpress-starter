/// <reference path="jquery.d.ts" />

interface AjaxFormOptions {
	url?: string;
	type?: string;
	dataType?: string;
	beforeSerialize?: (jqForm: JQuery, options: AjaxFormOptions) => boolean;
	beforeSubmit?: (formData: any[], jqForm: JQuery, options: AjaxFormOptions) => boolean;
	clearForm?: boolean;
	forceSync?: boolean;
	iframe?: boolean;
	resetForm?: boolean;
	semantic?: boolean;
	target?: string | JQuery;
	timeout?: number;
	success?: (response: any, statusText: string, xhr: JQueryXHR, jqForm: JQuery) => void;
	error?: (xhr: JQueryXHR, statusText: string, errorThrown: string) => void;
	complete?: (xhr: JQueryXHR, statusText: string) => void;
}

interface JQuery {
	//These method are added by the jquery-form plugin.
	ajaxForm: (options: AjaxFormOptions) => JQuery;
	resetForm: () => JQuery;
}