/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/integrations/index.js":
/*!**************************************!*\
  !*** ./src/js/integrations/index.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var scss_integrations_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! scss/integrations.scss */ \"./src/scss/integrations.scss\");\n/**\n * Internal dependencies\n */\n\n\n/**\n * Disconnect from Integration.\n *\n * @since 4.0\n *\n * @param {Event} e\n */\nconst disconnect = e => {\n  const $button = e.target,\n    integration = e.target.dataset.integration;\n  e.preventDefault();\n\n  // If button is disabled, previous request is processing.\n  if ($button.disabled) {\n    return;\n  }\n\n  // Disable button.\n  $button.disabled = true;\n\n  // Build request params.\n  const requestParams = {\n    action: 'fg_fillablepdfs_integration_disconnect',\n    integration,\n    nonce: fg_fillablepdfs_integrations_strings.nonce\n  };\n  (async () => {\n    const request = await fetch(ajaxurl, {\n      body: new URLSearchParams(requestParams).toString(),\n      method: 'POST',\n      headers: {\n        'Content-Type': 'application/x-www-form-urlencoded'\n      }\n    });\n    const response = await request.json();\n    if (response.success) {\n      window.location.reload();\n    } else {\n      alert(response.data);\n      $button.disabled = false;\n    }\n  })();\n};\n\n/**\n * Custom live dependency for Enable Integration feed setting.\n *\n * @since 4.0\n *\n * @param {object} rule Live dependency rule.\n *\n * @returns {boolean}\n */\nwindow.fg_fillablepdfs_integration_enable = rule => document.getElementById(`_gform_setting_${rule.field}`).checked;\nwindow.addEventListener('load', () => {\n  const $disconnectButtons = document.querySelectorAll('button.fillablepdfs-integration-auth__action--disconnect');\n  if (!$disconnectButtons) {\n    return;\n  }\n  $disconnectButtons.forEach($button => {\n    $button.addEventListener('click', disconnect);\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvaW50ZWdyYXRpb25zL2luZGV4LmpzLmpzIiwibWFwcGluZ3MiOiI7O0FBQUE7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFHQTtBQUNBO0FBQ0E7QUFBQTtBQUFBO0FBQ0E7QUFHQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBRUE7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBRUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9mb3JncmF2aXR5LWZpbGxhYmxlcGRmcy8uL3NyYy9qcy9pbnRlZ3JhdGlvbnMvaW5kZXguanM/MjUzMSJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEludGVybmFsIGRlcGVuZGVuY2llc1xuICovXG5pbXBvcnQgJ3Njc3MvaW50ZWdyYXRpb25zLnNjc3MnO1xuXG4vKipcbiAqIERpc2Nvbm5lY3QgZnJvbSBJbnRlZ3JhdGlvbi5cbiAqXG4gKiBAc2luY2UgNC4wXG4gKlxuICogQHBhcmFtIHtFdmVudH0gZVxuICovXG5jb25zdCBkaXNjb25uZWN0ID0gKCBlICkgPT4ge1xuXG5cdGNvbnN0ICRidXR0b24gICAgID0gZS50YXJnZXQsXG5cdCAgICAgIGludGVncmF0aW9uID0gZS50YXJnZXQuZGF0YXNldC5pbnRlZ3JhdGlvbjtcblxuXHRlLnByZXZlbnREZWZhdWx0KCk7XG5cblx0Ly8gSWYgYnV0dG9uIGlzIGRpc2FibGVkLCBwcmV2aW91cyByZXF1ZXN0IGlzIHByb2Nlc3NpbmcuXG5cdGlmICggJGJ1dHRvbi5kaXNhYmxlZCApIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHQvLyBEaXNhYmxlIGJ1dHRvbi5cblx0JGJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cblx0Ly8gQnVpbGQgcmVxdWVzdCBwYXJhbXMuXG5cdGNvbnN0IHJlcXVlc3RQYXJhbXMgPSB7XG5cdFx0YWN0aW9uOiAnZmdfZmlsbGFibGVwZGZzX2ludGVncmF0aW9uX2Rpc2Nvbm5lY3QnLFxuXHRcdGludGVncmF0aW9uLFxuXHRcdG5vbmNlOiAgZmdfZmlsbGFibGVwZGZzX2ludGVncmF0aW9uc19zdHJpbmdzLm5vbmNlXG5cdH07XG5cblx0KCBhc3luYyAoKSA9PiB7XG5cdFx0Y29uc3QgcmVxdWVzdCA9IGF3YWl0IGZldGNoKFxuXHRcdFx0YWpheHVybCxcblx0XHRcdHtcblx0XHRcdFx0Ym9keTogICAgbmV3IFVSTFNlYXJjaFBhcmFtcyggcmVxdWVzdFBhcmFtcyApLnRvU3RyaW5nKCksXG5cdFx0XHRcdG1ldGhvZDogICdQT1NUJyxcblx0XHRcdFx0aGVhZGVyczogeyAnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZCcgfVxuXHRcdFx0fVxuXHRcdCk7XG5cblx0XHRjb25zdCByZXNwb25zZSA9IGF3YWl0IHJlcXVlc3QuanNvbigpO1xuXG5cdFx0aWYgKCByZXNwb25zZS5zdWNjZXNzICkge1xuXHRcdFx0d2luZG93LmxvY2F0aW9uLnJlbG9hZCgpO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHRhbGVydCggcmVzcG9uc2UuZGF0YSApO1xuXHRcdFx0JGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdH1cblx0fSApKCk7XG5cbn07XG5cbi8qKlxuICogQ3VzdG9tIGxpdmUgZGVwZW5kZW5jeSBmb3IgRW5hYmxlIEludGVncmF0aW9uIGZlZWQgc2V0dGluZy5cbiAqXG4gKiBAc2luY2UgNC4wXG4gKlxuICogQHBhcmFtIHtvYmplY3R9IHJ1bGUgTGl2ZSBkZXBlbmRlbmN5IHJ1bGUuXG4gKlxuICogQHJldHVybnMge2Jvb2xlYW59XG4gKi9cbndpbmRvdy5mZ19maWxsYWJsZXBkZnNfaW50ZWdyYXRpb25fZW5hYmxlID0gKCBydWxlICkgPT4gKCBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggYF9nZm9ybV9zZXR0aW5nXyR7IHJ1bGUuZmllbGQgfWAgKS5jaGVja2VkICk7XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnbG9hZCcsICgpID0+IHtcblxuXHRjb25zdCAkZGlzY29ubmVjdEJ1dHRvbnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnYnV0dG9uLmZpbGxhYmxlcGRmcy1pbnRlZ3JhdGlvbi1hdXRoX19hY3Rpb24tLWRpc2Nvbm5lY3QnICk7XG5cblx0aWYgKCAhICRkaXNjb25uZWN0QnV0dG9ucyApIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHQkZGlzY29ubmVjdEJ1dHRvbnMuZm9yRWFjaCggKCAkYnV0dG9uICkgPT4ge1xuXHRcdCRidXR0b24uYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgZGlzY29ubmVjdCApO1xuXHR9ICk7XG5cbn0gKTtcbiJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./src/js/integrations/index.js\n");

/***/ }),

/***/ "./src/scss/integrations.scss":
/*!************************************!*\
  !*** ./src/scss/integrations.scss ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvc2Nzcy9pbnRlZ3JhdGlvbnMuc2Nzcy5qcyIsIm1hcHBpbmdzIjoiO0FBQUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9mb3JncmF2aXR5LWZpbGxhYmxlcGRmcy8uL3NyYy9zY3NzL2ludGVncmF0aW9ucy5zY3NzPzUzMTYiXSwic291cmNlc0NvbnRlbnQiOlsiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/scss/integrations.scss\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/integrations/index.js");
/******/ 	
/******/ })()
;