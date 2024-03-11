declare namespace AmeEditorApi {

	const configDataAdapter: {
		getPath<D>(path: string | string[], defaultValue?: D): D | null | any;
		setPath(path: string | string[], value: any): void;
		mapSettingIdToPath(settingId: string): string | string[] | null;
		getKnownPrefixes(): string[];
	};

	function updateItemEditor(containerNode: JQuery): void;

	function forEachMenuItem(
		callback: (menuItem: Record<string, any>, $containerNode: JQuery) => boolean | void,
		skipSeparators?: boolean
	): void;

	function getFieldValue(entry: object, fieldName: string, defaultValue: any, containerNode: JQuery): any;

	function formatMenuTitle(title: string): string;
}
