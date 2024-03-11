interface JQueryStatic {
	//These methods are added by the jquery-cookie plugin.
	cookie: (name: string, value?: string, options?: {}) => string;
	removeCookie: (name: string, options?: {}) => boolean;
}

declare class WsAmePreferenceCookie {
	constructor(name: string, expirationInDays: number, jsonEncodingEnabled: boolean);
	read(defaultValue: any): any;
	write(value: any): void;
	removeCookie(): boolean;
	readAndRefresh<D>(defaultValue: D): D|any;
}