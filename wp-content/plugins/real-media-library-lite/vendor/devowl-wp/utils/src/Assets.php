<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use Requests_IDNAEncoder;
use Requests_IRI;
use WpOrg\Requests\Iri;
use WpOrg\Requests\IdnaEncoder;
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
/**
 * Base asset management class for frontend scripts and styles.
 * @internal
 */
trait Assets
{
    /**
     * For future implementations and updates of this class you can differ from BUMP version.
     * Increment, if needed.
     */
    public static $ASSETS_BUMP = 4;
    /**
     * Localize the plugin with additional options.
     *
     * @param string $context
     * @return array
     */
    public abstract function overrideLocalizeScript($context);
    /**
     * Enqueue scripts and styles depending on the type. This function is called
     * from both admin_enqueue_scripts and wp_enqueue_scripts. You can check the
     * type through the $type parameter. In this function you can include your
     * external libraries from public/lib, too.
     *
     * @param string $type The type (see Assets constants)
     * @param string $hook_suffix The current admin page
     */
    public abstract function enqueue_scripts_and_styles($type, $hook_suffix = null);
    /**
     * Localize the WordPress backend and frontend.
     *
     * @param string $context
     * @return mixed
     */
    public function localizeScript($context)
    {
        /**
         * The REST query array defines the query parameters which are passed to all our executed
         * REST API requests. This could be useful e.g. when using plugins like WPML or PolyLang and
         * pass the correct language via GET parameters.
         *
         * @hook DevOwl/Utils/RestQuery
         * @param {array} $restQuery
         * @since 1.12.5
         */
        $restQuery = \apply_filters('DevOwl/Utils/RestQuery', [
            '_v' => $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION),
            // Always request the users locale settings (https://core.trac.wordpress.org/ticket/44758)
            '_locale' => 'user',
        ]);
        $slug = $this->getPluginConstant(Constants::PLUGIN_CONST_SLUG);
        $file = $this->getPluginConstant(Constants::PLUGIN_CONST_FILE);
        $fileDir = $file === null ? null : \dirname($file);
        $chunksLanguageFolder = null;
        if ($file !== null) {
            $chunksLanguageFolder = PackageLocalization::getParentLanguageFolder($fileDir . '/' . Constants::LOCALIZATION_PUBLIC_JSON_I18N, \false, 'url');
        }
        return [
            'slug' => $slug,
            'textDomain' => $this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN),
            'version' => $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION),
            'restUrl' => $this->getAsciiUrl(Service::getUrl($this)),
            'restNamespace' => Service::getNamespace($this),
            'restPathObfuscateOffset' => Service::getObfuscateOffset(),
            'restRoot' => $this->getAsciiUrl(\get_rest_url()),
            'restQuery' => $restQuery,
            'restNonce' => \wp_installing() && !\is_multisite() ? '' : \wp_create_nonce('wp_rest'),
            'restRecreateNonceEndpoint' => \admin_url('admin-ajax.php?action=rest-nonce'),
            'publicUrl' => $file !== null ? \trailingslashit(\plugins_url('public', $file)) : null,
            'chunkFolder' => $file !== null ? \basename($this->getPublicFolder()) : null,
            'chunksLanguageFolder' => $chunksLanguageFolder,
            'chunks' => $file !== null ? $this->getChunkTranslationMap() : null,
            // We put custom variables to "others" because if you put for example
            // a boolean to the first-level it is interpreted as "1" instead of `true`.
            'others' => $this->overrideLocalizeScript($context),
        ];
    }
    /**
     * Allows to enable advanced enqueue features for a given handle.
     *
     * @param string|string[] $handles
     * @param string[] $features See $ADVANCED_ENQUEUE_FEATURE_* constants or `null` for all features
     * @param string $type Can be `script` or `style`
     */
    public function enableAdvancedEnqueue($handles, $features = null, $type = 'script')
    {
        $handles = \is_array($handles) ? $handles : [$handles];
        // Add `vendor-` also to the handles for `probablyEnqueueChunk` compatibility
        foreach ($handles as $handle) {
            \array_unshift($handles, \sprintf('vendor-%s', $handle));
            \array_unshift($handles, \sprintf('%s-vendor-%s', $this->getPluginConstant(Constants::PLUGIN_CONST_SLUG), $handle));
        }
        if (($features === null || \in_array(Constants::ASSETS_ADVANCED_ENQUEUE_FEATURE_DEFER, $features, \true)) && $type === 'script') {
            $this->enableDeferredEnqueue($handles);
        }
        if (($features === null || \in_array(Constants::ASSETS_ADVANCED_ENQUEUE_FEATURE_ASYNC, $features, \true)) && $type === 'script') {
            $this->enableAsyncEnqueue($handles);
        }
        if ($features === null || \in_array(Constants::ASSETS_ADVANCED_ENQUEUE_FEATURE_PRELOADING, $features, \true)) {
            $this->enablePreloadEnqueue($handles, $type);
        }
        if ($features === null || \in_array(Constants::ASSETS_ADVANCED_ENQUEUE_FEATURE_PRIORITY_QUEUE, $features, \true)) {
            $this->enablePriorityQueue($handles, $type);
        }
    }
    /**
     * Enable `defer` attribute for given handle(s) (only scripts are supported, see https://stackoverflow.com/a/25890780).
     *
     * @param string|string[] $handles
     * @see https://stackoverflow.com/a/56128726/5506547
     */
    public function enableDeferredEnqueue($handles)
    {
        $handles = \is_array($handles) ? $handles : [$handles];
        \add_filter('script_loader_tag', function ($tag, $handle) use($handles) {
            if (\in_array($handle, $handles, \true) && \stripos($tag, 'defer') === \false) {
                // see https://regex101.com/r/0whi5s/1
                // phpcs:disable PHPCompatibility.ParameterValues.RemovedPCREModifiers.Removed
                return \preg_replace(\sprintf('/(%s=[\'"]?)/m', 'src'), 'defer $1', $tag);
                // phpcs:enable PHPCompatibility.ParameterValues.RemovedPCREModifiers.Removed
            }
            return $tag;
        }, 10, 2);
    }
    /**
     * Enable `async` attribute for given handle(s) (only scripts are supported).
     *
     * @param string|string[] $handles
     */
    public function enableAsyncEnqueue($handles)
    {
        $handles = \is_array($handles) ? $handles : [$handles];
        \add_filter('script_loader_tag', function ($tag, $handle) use($handles) {
            if (\in_array($handle, $handles, \true) && \stripos($tag, 'async') === \false) {
                // see https://regex101.com/r/0whi5s/1
                // phpcs:disable PHPCompatibility.ParameterValues.RemovedPCREModifiers.Removed
                return \preg_replace(\sprintf('/(%s=[\'"]?)/m', 'src'), 'async $1', $tag);
                // phpcs:enable PHPCompatibility.ParameterValues.RemovedPCREModifiers.Removed
            }
            return $tag;
        }, 10, 2);
    }
    /**
     * Enable `<link rel="preload" />` HTML tag for given handle(s).
     *
     * @param string|string[] $handles
     * @param string $type Can be `script` or `style`
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
     */
    public function enablePreloadEnqueue($handles, $type = 'script')
    {
        $handles = \is_array($handles) ? $handles : [$handles];
        $wp_dependencies = $type === 'script' ? \wp_scripts() : \wp_styles();
        \add_action('wp_head', function () use($handles, $type, $wp_dependencies) {
            foreach ($handles as $handle) {
                $script = $wp_dependencies->query($handle);
                if ($script !== \false) {
                    // Build URL
                    // See https://developer.wordpress.org/reference/classes/wp_styles/do_item/
                    // and https://developer.wordpress.org/reference/classes/wp_scripts/do_item/
                    $src = $script->src;
                    $ver = $script->ver;
                    if (!empty($ver)) {
                        $src = \add_query_arg('ver', $ver, $src);
                    }
                    $src = \apply_filters('script_loader_src', $src, $handle);
                    \printf('<link rel="preload" href="%s" as="%s" />
', \esc_url($src), $type);
                }
            }
        }, 2);
    }
    /**
     * Enable scripts and styles to be appear at the top of `<head`.
     *
     * @param string|string[] $handles
     * @param string $type Can be `script` or `style`
     */
    public function enablePriorityQueue($handles, $type = 'script')
    {
        $handles = \is_array($handles) ? $handles : [$handles];
        $wp_dependencies = $type === 'script' ? \wp_scripts() : \wp_styles();
        \add_action('wp_head', function () use($handles, $type, $wp_dependencies) {
            foreach ($handles as $handle) {
                $script = $wp_dependencies->query($handle);
                if ($script !== \false) {
                    if ($wp_dependencies->do_item($handle, \false)) {
                        $wp_dependencies->done[] = $handle;
                    }
                    unset($wp_dependencies->to_do[$handle]);
                }
            }
        }, 3);
    }
    /**
     * Get a map of available translations for all available chunks.
     */
    public function getChunkTranslationMap()
    {
        $inc = $this->getPluginConstant(Constants::PLUGIN_CONST_INC);
        $path = $inc . '/base/others/cachebuster.php';
        $result = [];
        if (empty($inc)) {
            // There is no `inc` folder available for the current package / plugin.
            return $result;
        }
        if (\file_exists($path)) {
            // Store cachebuster once
            static $cachebuster = null;
            if ($cachebuster === null) {
                $cachebuster = (include $path);
            }
            foreach (\array_keys($cachebuster) as $scriptPath) {
                $basename = \basename($scriptPath);
                if (\substr($basename, 0, 6) === 'chunk-') {
                    $suffix = $this->getTranslationSuffixByBasename($basename);
                    if (\count($suffix) > 0) {
                        $result[$basename] = $suffix;
                    }
                }
            }
        }
        return (object) $result;
    }
    /**
     * Get the suffix for `chunks` localized variable including dependencies.
     *
     * @param string $basename
     */
    protected function getTranslationSuffixByBasename($basename)
    {
        $result = [];
        static $locale = null;
        if ($locale === null) {
            $locale = \determine_locale();
        }
        $textDomain = $this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN);
        $path = \trailingslashit($this->getPluginConstant(Constants::PLUGIN_CONST_PATH));
        $languageFolder = PackageLocalization::getParentLanguageFolder($path . Constants::LOCALIZATION_PUBLIC_JSON_I18N);
        static $dependencyMap = null;
        if ($dependencyMap === null) {
            $dependencyMap = [];
            $dependencyMapFiles = \glob($path . $this->getPublicFolder() . 'i18n-dependency-map-*.json');
            if ($dependencyMapFiles !== \false) {
                foreach ($dependencyMapFiles as $dependencyMapFile) {
                    $dependencyMap = \array_merge($dependencyMap, \json_decode(\file_get_contents($dependencyMapFile), ARRAY_A));
                }
            }
        }
        // Chunk entry dependencies
        if (isset($dependencyMap[$basename])) {
            $dependencies = $dependencyMap[$basename];
            foreach ($dependencies as $dependency) {
                $suffix = $locale . '-' . \md5($dependency);
                $jsonFile = $languageFolder . '/' . $textDomain . '-' . $suffix . '.json';
                if (\file_exists($jsonFile)) {
                    $result[] = $suffix;
                }
            }
        }
        return $result;
    }
    /**
     * When using WordPress < 6.6 we need to enqueue the react/jsx-runtime UMD bundle to make the
     * `ReactJSXRuntime` external work.
     *
     * @see https://make.wordpress.org/core/2024/06/06/jsx-in-wordpress-6-6/
     * @see https://core.trac.wordpress.org/ticket/61324
     * @see https://github.com/WordPress/gutenberg/issues/62202#issuecomment-2156796649
     * @see https://app.clickup.com/t/86959qqq1?comment=90120059024238
     */
    public function enqueueReactJsxRuntime()
    {
        if (\wp_script_is(Constants::ASSETS_HANDLE_REACT_JSX_RUNTIME, 'registered')) {
            return;
        }
        $useNonMinifiedSources = $this->useNonMinifiedSources();
        $this->enqueueLibraryScript(Constants::ASSETS_HANDLE_REACT_JSX_RUNTIME, [[$useNonMinifiedSources, 'react-jsx-runtime/react-jsx-runtime.js'], 'react-jsx-runtime/react-jsx-runtime.min.js'], [Constants::ASSETS_HANDLE_REACT]);
    }
    /**
     * Enqueue utils and return an array of dependencies.
     */
    public function enqueueUtils()
    {
        $this->enqueueMobx();
        $this->enqueueReactJsxRuntime();
        $scriptDeps = [Constants::ASSETS_HANDLE_REACT, Constants::ASSETS_HANDLE_REACT_DOM, Constants::ASSETS_HANDLE_REACT_JSX_RUNTIME, Constants::ASSETS_HANDLE_MOBX, 'wp-i18n', 'jquery', 'wp-polyfill'];
        $handleUtils = $this->enqueueComposerScript('utils', $scriptDeps);
        \array_push($scriptDeps, $handleUtils);
        return $scriptDeps;
    }
    /**
     * Enqueue mobx state management library.
     */
    public function enqueueMobx()
    {
        $useNonMinifiedSources = $this->useNonMinifiedSources();
        $alreadyRegistered = \wp_script_is(Constants::ASSETS_HANDLE_MOBX, 'registered');
        $this->enqueueLibraryScript(Constants::ASSETS_HANDLE_MOBX, [[$useNonMinifiedSources, 'mobx/lib/mobx.umd.js'], 'mobx/lib/mobx.umd.min.js']);
        // Add inline script only once (this allows multiple instances in mobx)
        if (!$alreadyRegistered) {
            \wp_add_inline_script(Constants::ASSETS_HANDLE_MOBX, 'document.addEventListener("DOMContentLoaded", function () {
    mobx.configure({ isolateGlobalState: true });
});');
        }
    }
    /**
     * Checks if a `vendor-` file is created for a given script and enqueue it.
     *
     * @param string $handle
     * @param boolean $isLib
     * @param string $src
     * @param string[] $deps
     * @param boolean $in_footer
     * @param string $media
     */
    protected function probablyEnqueueChunk($handle, $isLib, $src, &$deps, $in_footer, $media)
    {
        if (!$isLib) {
            $handle = $this->enqueue('vendor-' . $handle, 'vendor-' . $src, $deps, \false, 'script', $in_footer, $media);
            if ($handle !== \false) {
                \array_push($deps, $handle);
            }
        }
    }
    /**
     * Enqueue helper for entry points and libraries. See dependents for more documentation.
     *
     * @param string $handle
     * @param mixed $src
     * @param string[] $deps
     * @param boolean $isLib
     * @param string $type Can be 'script' or 'style'
     * @param boolean $in_footer
     * @param string $media
     * @return string|boolean The used handle
     */
    protected function enqueue($handle, $src, $deps = [], $isLib = \false, $type = 'script', $in_footer = \true, $media = 'all')
    {
        $useHandle = $isLib ? $handle : $this->getPluginConstant(Constants::PLUGIN_CONST_SLUG) . '-' . $handle;
        if (!\is_array($src)) {
            $src = [$src];
        }
        $publicFolder = $this->getPublicFolder($isLib);
        foreach ($src as $s) {
            // Allow to skip e. g. SCRIPT_DEBUG files
            if (\is_array($s) && $s[0] !== \true) {
                continue;
            }
            $useSrc = \is_array($s) ? $s[1] : $s;
            $publicSrc = $publicFolder . $useSrc;
            $path = \trailingslashit($this->getPluginConstant(Constants::PLUGIN_CONST_PATH)) . $publicSrc;
            if (\file_exists($path)) {
                $url = \plugins_url($publicSrc, $this->getPluginConstant(Constants::PLUGIN_CONST_FILE));
                // In dev environment, the cachebuster is not created; it is only created at build time
                $isDevEnv = \defined('DEVOWL_WP_DEV') && \constant('DEVOWL_WP_DEV');
                $cachebuster = $this->getCachebusterVersion($publicSrc, $isLib, $isDevEnv ? \filemtime($path) : null);
                // Never overwrite e.g. `react` or `mobx`
                // TODO: perhaps provide another function `$this->allowDeregisterWhenNewVersion($handle)`
                // $this->probablyDeregisterWhenNewerVersion($useHandle, $cachebuster, $type);
                if ($type === 'script') {
                    $this->probablyEnqueueChunk($useHandle, $isLib, $useSrc, $deps, $in_footer, $media);
                    \wp_enqueue_script($useHandle, $url, $deps, $cachebuster, $in_footer);
                    // Only set translations for our own entry points, libraries handle localization usually in another way
                    if (!$isLib) {
                        $this->setLazyScriptTranslations($useHandle, $this->getPluginConstant(Constants::PLUGIN_CONST_TEXT_DOMAIN), \trailingslashit($this->getPluginConstant(Constants::PLUGIN_CONST_PATH)) . Constants::LOCALIZATION_PUBLIC_JSON_I18N);
                    }
                } else {
                    \wp_enqueue_style($useHandle, $url, $deps, $cachebuster, $media);
                }
                return $useHandle;
            }
        }
        return \false;
    }
    /**
     * Registers the script if $src provided (does NOT overwrite), and enqueues it. Use this wrapper
     * method instead of wp_enqueue_script if you want to use the cachebuster for the given src. If the
     * src is not found in the cachebuster (inc/base/others/cachebuster.php) it falls back to _VERSION.
     *
     * You can also use something like this to determine SCRIPT_DEBUG files:
     *
     * ```php
     * $this->enqueueLibraryScript(
     *     Constants::ASSETS_HANDLE_REACT_DOM,
     *     [[$useNonMinifiedSources, 'react-dom/umd/react-dom.development.js'], 'react-dom/umd/react-dom.production.min.js'],
     *     Constants::ASSETS_HANDLE_REACT
     * );
     * ```
     *
     * @param string $handle Name of the script. Should be unique.
     * @param mixed $src The src relative to public/dist or public/dev folder (when $isLib is false)
     * @param string[] $deps An array of registered script handles this script depends on.
     * @param boolean $in_footer Whether to enqueue the script before </body> instead of in the <head>.
     * @param boolean $isLib If true the public/lib/ folder is used.
     * @return string|boolean The used handle
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/ For parameters
     */
    public function enqueueScript($handle, $src, $deps = [], $in_footer = \true, $isLib = \false)
    {
        return $this->enqueue($handle, $src, $deps, $isLib, 'script', $in_footer);
    }
    /**
     * Wrapper for Assets#enqueueScript() method with $isLib = true.
     *
     * @param string $handle
     * @param mixed $src
     * @param string[] $deps
     * @param boolean $in_footer
     * @return string|boolean The used handle
     * @see self::enqueueScript()
     */
    public function enqueueLibraryScript($handle, $src, $deps = [], $in_footer = \false)
    {
        return $this->enqueueScript($handle, $src, $deps, $in_footer, \true);
    }
    /**
     * Enqueue a CSS stylesheet. Use this wrapper method instead of wp_enqueue_style if you want
     * to use the cachebuster for the given src. If the src is not found in the cachebuster (inc/base/others/cachebuster.php)
     * it falls back to _VERSION.
     *
     * It also allows $src to be like in enqueueScript()
     *
     * @param string $handle Name of the style. Should be unique.
     * @param mixed $src The src relative to public/dist or public/dev folder (when $isLib is false)
     * @param string[] $deps An array of registered style handles this style depends on.
     * @param string $media The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @param boolean $isLib If true the public/lib/ folder is used.
     * @return string|boolean The used handle
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_style/ For parameters
     */
    public function enqueueStyle($handle, $src, $deps = [], $media = 'all', $isLib = \false)
    {
        return $this->enqueue($handle, $src, $deps, $isLib, 'style', null, $media);
    }
    /**
     * Wrapper for Assets#enqueueStyle() method with $isLib = true.
     *
     * @param string $handle
     * @param mixed $src
     * @param string[] $deps
     * @param string $media
     * @return string|boolean The used handle
     * @see enqueueStyle()
     */
    public function enqueueLibraryStyle($handle, $src, $deps = [], $media = 'all')
    {
        return $this->enqueueStyle($handle, $src, $deps, $media, \true);
    }
    /**
     * Checks if a `vendor-` file is created for a given script in a composer package and enqueue it.
     *
     * @param string $handle
     * @param string $src
     * @param string[] $deps
     * @param boolean $in_footer
     * @param string $media
     */
    protected function probablyEnqueueComposerChunk($handle, $src, &$deps, $in_footer, $media)
    {
        $rootSlug = $this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG);
        $scriptSuffix = $src === 'index.js' || $src === 'index.css' ? '' : '-' . \pathinfo($src, \PATHINFO_FILENAME);
        $handle = $this->enqueueComposer($handle, 'vendor-' . $src, $deps, 'script', $in_footer, $media, 'vendor-' . $rootSlug . '-' . $handle . $scriptSuffix);
        if ($handle !== \false) {
            \array_push($deps, $handle);
        }
    }
    /**
     * Enqueue helper for monorepo packages. See dependents for more documentation.
     *
     * @param string $handle
     * @param string $src
     * @param string[] $deps
     * @param string $type Can be 'script' or 'style'
     * @param boolean $in_footer
     * @param string $media
     * @param string $vendorHandle
     * @return string|boolean The used handle
     */
    protected function enqueueComposer($handle, $src = 'index.js', $deps = [], $type = 'script', $in_footer = \true, $media = 'all', $vendorHandle = null)
    {
        $rootSlug = $this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG);
        $pluginPath = \trailingslashit($this->getPluginConstant(Constants::PLUGIN_CONST_PATH));
        $scriptSuffix = $src === 'index.js' || $src === 'index.css' ? '' : '-' . \pathinfo($src, \PATHINFO_FILENAME);
        $useHandle = $vendorHandle !== null ? $vendorHandle : $rootSlug . '-' . $handle . $scriptSuffix;
        $useNonMinifiedSources = $this->useNonMinifiedSources();
        $packageDir = 'vendor/' . $rootSlug . '/' . $handle . '/';
        $devBundlesExists = \is_dir($pluginPath . $packageDir . 'dev');
        $packageSrc = $packageDir . ($useNonMinifiedSources && $devBundlesExists ? 'dev' : 'dist') . '/' . $src;
        $composerPath = $pluginPath . $packageSrc;
        $isLocalDevelopment = \defined('DEVOWL_WP_DEV') && \constant('DEVOWL_WP_DEV');
        if (\file_exists($composerPath)) {
            // The lerna package exists (we are in our local development environment!)
            $url = \plugins_url($packageSrc, $this->getPluginConstant(Constants::PLUGIN_CONST_FILE));
            $cachebuster = \filemtime($composerPath);
            $packageJson = $pluginPath . $packageDir . 'package.json';
            // Correct cachebuster version
            if (\file_exists($packageJson) && !$isLocalDevelopment) {
                $packageJson = \json_decode(\file_get_contents($packageJson), \true);
                $cachebuster = $packageJson['version'];
            }
            $this->probablyDeregisterWhenNewerVersion($useHandle, $cachebuster, $type);
            if ($type === 'script') {
                $this->probablyEnqueueComposerChunk($handle, $src, $deps, $in_footer, $media);
                \wp_enqueue_script($useHandle, $url, $deps, $cachebuster, $in_footer);
                $this->setLazyScriptTranslations($useHandle, $rootSlug . '-' . $handle, $pluginPath . $packageDir . 'languages/frontend/json');
            } else {
                \wp_enqueue_style($useHandle, $url, $deps, $cachebuster, $media);
            }
            return $useHandle;
        }
        return \false;
    }
    /**
     * Enqueue a composer package script from our multi-package repository.
     *
     * @param string $handle Name of the package.
     * @param string[] $deps An array of registered scripts handles this script depends on.
     * @param string $src The file to use in dist or dev folder.
     * @param boolean $in_footer Whether to enqueue the script before </body> instead of in the <head>.
     * @return string The used handle
     */
    public function enqueueComposerScript($handle, $deps = [], $src = 'index.js', $in_footer = \true)
    {
        return $this->enqueueComposer($handle, $src, $deps, 'script', $in_footer);
    }
    /**
     * Enqueue a composer package style from our multi-package repository.
     *
     * @param string $handle Name of the package.
     * @param string[] $deps An array of registered scripts handles this script depends on.
     * @param string $src The file to use in dist or dev folder.
     * @param string $media The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     * @return string The used handle
     */
    public function enqueueComposerStyle($handle, $deps = [], $src = 'index.css', $media = 'all')
    {
        return $this->enqueueComposer($handle, $src, $deps, 'style', null, $media);
    }
    /**
     * Enqueue scripts and styles for admin pages.
     *
     * @param string $hook_suffix The current admin page
     */
    public function admin_enqueue_scripts($hook_suffix)
    {
        $this->enqueue_scripts_and_styles(Constants::ASSETS_TYPE_ADMIN, $hook_suffix);
    }
    /**
     * Enqueue scripts and styles for frontend pages.
     */
    public function wp_enqueue_scripts()
    {
        $this->enqueue_scripts_and_styles(Constants::ASSETS_TYPE_FRONTEND);
    }
    /*
     * Enqueue blocker and banner in Login screen too, so reCaptcha forms or
     * similar scripts can be blocked.
     */
    public function login_enqueue_scripts()
    {
        $this->enqueue_scripts_and_styles(Constants::ASSETS_TYPE_LOGIN);
    }
    /**
     * Enqueue scripts in customize (not preview!)
     */
    public function customize_controls_print_scripts()
    {
        $this->enqueue_scripts_and_styles(Constants::ASSETS_TYPE_CUSTOMIZE);
    }
    /**
     * The function and mechanism of wp_set_script_translations() is great of course. Unfortunately
     * popular plugins like WP Rocket and Divi are not compatible with it (especially page builders
     * and caching plugins). Why? Shortly explained, the injected inline scripts relies on `wp.i18n`
     * which can be deferred or async loaded (the script itself) -> wp is not defined.
     *
     * In factory i18n.tsx the `window.wpi18nLazy` is automatically detected and the plugin gets localized.
     *
     * @param string $handle
     * @param string $domain
     * @param string $path
     * @see https://developer.wordpress.org/reference/classes/wp_scripts/print_translations/
     * @see https://developer.wordpress.org/reference/functions/wp_set_script_translations/
     * @see https://app.clickup.com/t/3mjh0e
     */
    public function setLazyScriptTranslations($handle, $domain, $path)
    {
        \add_filter('load_script_textdomain_relative_path', [$this, 'load_script_textdomain_relative_path']);
        \add_filter('load_script_translation_file', [$this, 'load_script_translation_file']);
        $json_translations = \load_script_textdomain($handle, $domain, PackageLocalization::getParentLanguageFolder($path));
        \remove_filter('load_script_textdomain_relative_path', [$this, 'load_script_textdomain_relative_path']);
        \remove_filter('load_script_translation_file', [$this, 'load_script_translation_file']);
        if (!empty($json_translations)) {
            $output = <<<JS
(function(domain, translations) {
    var localeData = translations.locale_data[domain] || translations.locale_data.messages;
    localeData[""].domain = domain;
    window.wpi18nLazy=window.wpi18nLazy || {};
    window.wpi18nLazy[domain] = window.wpi18nLazy[domain] || [];
    window.wpi18nLazy[domain].push(localeData);
})("{$domain}", {$json_translations});
JS;
            \wp_add_inline_script($handle, $output, 'before');
        }
    }
    /**
     * Fix correct filename for enqueued composer packages.
     *
     * @param string $file
     * @see https://regex101.com/r/vAM0hO/1
     */
    public function load_script_translation_file($file)
    {
        return \str_replace('/' . $this->getPluginConstant(Constants::PLUGIN_CONST_ROOT_SLUG) . '-', '/', $file);
    }
    /**
     * Force the basename for the md5 of a loaded JSON translation file.
     *
     * @param string $src
     * @see https://github.com/wp-cli/i18n-command/issues/177#issuecomment-523759266
     */
    public function load_script_textdomain_relative_path($src)
    {
        return \basename($src);
    }
    /**
     * Get the cachebuster entry for a given file. If the $src begins with public/lib/ it
     * will use the inc/base/others/cachebuster-lib.php cachebuster instead of inc/base/others/cachebuster.php.
     *
     * @param string $src The src relative to public/ folder
     * @param boolean $isLib If true the cachebuster-lib.php cachebuster is used
     * @param string $default
     * @return string _VERSION or cachebuster timestamp
     */
    public function getCachebusterVersion($src, $isLib = \false, $default = null)
    {
        $default = $default ?? $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION);
        $path = $this->getPluginConstant(Constants::PLUGIN_CONST_INC) . '/base/others/';
        $path_lib = $path . 'cachebuster-lib.php';
        $path = $path . 'cachebuster.php';
        if ($isLib) {
            // Library cachebuster
            if (\file_exists($path_lib)) {
                static $cachebuster_lib = null;
                if ($cachebuster_lib === null) {
                    $cachebuster_lib = (include $path_lib);
                }
                // Parse module
                \preg_match('/^public\\/lib\\/([^\\/]+)/', $src, $matches);
                if (\is_array($matches) && isset($matches[1]) && \is_array($cachebuster_lib) && \array_key_exists($matches[1], $cachebuster_lib)) {
                    // Valid cachebuster
                    return $cachebuster_lib[$matches[1]];
                }
            }
        } else {
            // Main cachebuster
            if (\file_exists($path)) {
                // Store cachebuster once
                static $cachebuster = null;
                if ($cachebuster === null) {
                    $cachebuster = (include $path);
                }
                // Prepend src/ because the cachebuster task prefixes it
                $src = 'src/' . $src;
                if (\is_array($cachebuster) && \array_key_exists($src, $cachebuster)) {
                    // Valid cachebuster
                    return $cachebuster[$src];
                }
            }
        }
        return $default;
    }
    /**
     * Make a localized array anonymous. Some plugins like WP Rocket tries to lazy load also localized scripts
     * and this should be avoided in some scenarios like Real Cookie Banners' banner script.
     * Use this instead of `wp_localize_script`.
     *
     * Settings:
     *
     * ```
     * string[]     makeBase64Encoded       List of keys of the array object which should be converted to base64 at output time (e.g. to avoid ModSecurity issues)
     * boolean      useCore                 Use `wp_localize_script` internally instead of custom localize script
     * string[]     lazyParse               A list of pathes of the array which should be lazy parsed. This could be useful to improve performance and parse as needed (e.g. huge arrays).
     * ```
     *
     * @param string $handle Name of the script to attach data to.
     * @param string $object_name Name of the variable that will contain the data.
     * @param array  $l10n Array of data to localize.
     * @param array  $settings
     * @see https://docs.wp-rocket.me/article/1349-delay-javascript-execution#technical
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     */
    public function anonymous_localize_script($handle, $object_name, $l10n, $settings = [])
    {
        $settings = \wp_parse_args($settings, ['makeBase64Encoded' => [], 'useCore' => \false, 'lazyParse' => []]);
        if ($settings['useCore']) {
            return \wp_localize_script($handle, $object_name, $l10n);
        }
        $makeBase64Encoded = $settings['makeBase64Encoded'];
        $lazyParse = $settings['lazyParse'];
        // Mark the script tag with some identifier, so our helper script (added below) can read
        // the JSON content. See also about: https://stackoverflow.com/q/12090883/5506547
        // Do not use a randomized string as it can lead to issues with cached web pages when the
        // inline scripts gets somehow into a combined file and the HTML is served not statically.
        $uuid = \wp_generate_uuid4();
        $uuid = \md5(\sprintf('%s:%s:%s', $handle, $object_name, $this->getPluginConstant(Constants::PLUGIN_CONST_VERSION)));
        $base64Marker = 'base64-encoded:';
        \add_filter('script_loader_tag', function ($tag, $scriptHandle) use($handle, $uuid, $l10n, $object_name, $makeBase64Encoded, $base64Marker, $lazyParse) {
            if ($scriptHandle === $handle) {
                if (\count($makeBase64Encoded) > 0) {
                    \array_walk_recursive($l10n, function (&$val, $key) use($makeBase64Encoded, $base64Marker) {
                        if (\in_array($key, $makeBase64Encoded, \true) && \is_string($val) && !empty($val)) {
                            $val = \sprintf('%s%s', $base64Marker, \base64_encode($val));
                        }
                    });
                }
                $foundLazyParse = [];
                // Only work with pathes which got converted successfully
                foreach ($lazyParse as $arrayPath) {
                    Utils::arrayModifyByKeyPath($l10n, $arrayPath, function ($value) use($arrayPath, &$foundLazyParse) {
                        $foundLazyParse[] = $arrayPath;
                        return \json_encode($value);
                    });
                }
                /*
                                    (() => {
                   var receiver = %5$s;
                   var createLazyParseProxy = (obj, key) => new Proxy(obj, {
                       get: (target, property) => {
                           let value = Reflect.get(target, property);
                           if (property === key && typeof value === "string") {
                               value = JSON.parse(value, receiver);
                               Reflect.set(target, property, value);
                           }
                           return value;
                       }
                   });
                   var o = /* document.write * / JSON.parse(document.getElementById("a%1$s1-js-extra").innerHTML, receiver);
                   %6$s
                   window.%3$s = o;
                                    })();
                */
                $tag = \sprintf('<script type="application/json" %4$s id="a%1$s1-js-extra">%2$s</script>
<script %4$s id="a%1$s2-js-extra">
(()=>{var x=%5$s,t=(e,t)=>new Proxy(e,{get:(e,n)=>{let r=Reflect.get(e,n);return n===t&&"string"==typeof r&&(r=JSON.parse(r,x),Reflect.set(e,n,r)),r}}),n=JSON.parse(document.getElementById("a%1$s1-js-extra").innerHTML,x);%6$s;window.%3$s=n})();
</script>', $uuid, \wp_json_encode($l10n), $object_name, \join(' ', [
                    // TODO: shouldn't this be part of @devowl-wp/cache-invalidate?
                    // Compatibility with most caching plugins which lazy load JavaScript
                    'data-skip-lazy-load="js-extra"',
                    // Compatibility with WP Fastest Cache and "Eliminate render blocking script"
                    // as WPFC is moving all scripts (even with `type="text/plain"`).
                    'data-skip-moving="true"',
                    // Compatibility with LiteSpeed Cache and do not delay this inline script
                    // See https://github.com/litespeedtech/lscache_wp/blob/6c95240003b89ef1d4ce190f5a96eba83528cd89/src/optimize.cls.php#L903
                    'data-no-defer',
                    // Compatibility with NitroPack
                    'nitro-exclude',
                    // Compatibility with WP Rocket as the filter `rocket_defer_inline_exclusions` does only check on `<script>` inner content
                    // so we cannot exclude by e.g. handle name
                    // See https://github.com/wp-media/wp-rocket/blob/ffac5d90e5a83bcb5a29b8e43f059a31adfae629/inc/Engine/Optimization/DeferJS/DeferJS.php#L128-L139
                    'data-alt-type="application/ld+json"',
                    // Compatibility with Swift Performance
                    'data-dont-merge',
                    // Compatibility with WP Meteor
                    'data-wpmeteor-nooptimize="true"',
                    // Compatibility with Cloudflare Rocket Loader
                    'data-cfasync="false"',
                ]), \sprintf(
                    /*
                    function(k, v) {
                        if (["%1$s"].indexOf(k) > -1 && typeof v === "string" && v.startsWith(%3$d)) {
                            return window.atob(v.substr("%2$s".length));
                        } else {
                            return v;
                        }
                    }
                    */
                    'function (a,b){return-1<["%1$s"].indexOf(a)&&"string"==typeof b&&b.startsWith("%2$s")?window.atob(b.substr(%3$d)):b}',
                    \join('","', $makeBase64Encoded),
                    $base64Marker,
                    \strlen($base64Marker)
                ), \join('', \array_map(function ($arrayKeyPath) {
                    $split = \explode('.', $arrayKeyPath);
                    $key = \array_pop($split);
                    $arrayKeyPath = \join('.', $split);
                    /*
                    if (window.Proxy) {
                        o.%1$s = createLazyParseProxy(o.%1$s, "%2$s");
                    }else{
                        o.%1$s.%2$s = JSON.parse(o.%1$s.%2$s, receiver);
                    }
                    */
                    return \sprintf('window.Proxy?n.%1$s=t(n.%1$s,"%2$s"):n.%1$s.%2$s=JSON.parse(n.%1$s.%2$s,x);', $arrayKeyPath, $key);
                }, $foundLazyParse))) . $tag;
            }
            return $tag;
        }, 10, 3);
    }
    /**
     * Wrapper for plugins_url. It respects the public folder depending on the SCRIPTS_DEBUG constant.
     *
     * @param string $asset The file name relative to the public folder path (dist or dev)
     * @param boolean $isLib If true the public/lib/ folder is used.
     * @return string
     * @see getPublicFolder()
     */
    public function getPluginsUrl($asset, $isLib = \false)
    {
        return \plugins_url($this->getPublicFolder($isLib) . $asset, $this->getPluginConstant(Constants::PLUGIN_CONST_FILE));
    }
    /**
     * Gets a public folder depending on the debug mode relative to the plugins folder with trailing slash.
     *
     * @param boolean $isLib If true the public/lib/ folder is returned.
     * @return string
     */
    public function getPublicFolder($isLib = \false)
    {
        if ($isLib) {
            return 'public/lib/';
        } elseif ($this->useNonMinifiedSources()) {
            // Check if dev folder exists because it can be removed in some builds
            $path = $this->getPluginConstant(Constants::PLUGIN_CONST_PATH);
            if (@\is_dir($path . '/public/dev') || @\is_dir($path . '/src/public/dev')) {
                return 'public/dev/';
            }
        }
        return 'public/dist/';
    }
    /**
     * Convert a complete URL to IDN url. This is necessery if you use a URIBuilder like
     * lil-url in your frontend.
     *
     * @see https://www.php.net/manual/en/function.idn-to-ascii.php
     * @param string $url The url
     * @return string
     * @codeCoverageIgnore Completely relies on external library!
     */
    public function getAsciiUrl($url)
    {
        if (\class_exists(Iri::class)) {
            /*
             * WordPress 5.9 introduced in first instance of their beta Requests 2.0, afterwards they
             * reverted the changes. The migration to Requests 2.0 got moved to WP 6.0. References:
             *
             * - https://git.io/JDL3e
             * - https://core.trac.wordpress.org/ticket/54504#comment:22
             *
             * So, with this condition we make sure it supports Requests 2.0 in all cases. Requests 2.0
             * supports autoloader, so we can safely check if the class exists.
             */
            $iri = new Iri($url);
            $iri->host = IdnaEncoder::encode($iri->host);
            return $iri->uri;
        } else {
            if (!\class_exists('Requests_IRI')) {
                require_once ABSPATH . WPINC . '/Requests/IRI.php';
            }
            if (!\class_exists('Requests_IDNAEncoder')) {
                require_once ABSPATH . WPINC . '/Requests/IDNAEncoder.php';
            }
            $iri = new Requests_IRI($url);
            $iri->host = Requests_IDNAEncoder::encode($iri->host);
            return $iri->uri;
        }
    }
    /**
     * Check if SCRIPT_DEBUG is set to true.
     *
     * @return boolean
     */
    public function useNonMinifiedSources()
    {
        return \defined('SCRIPT_DEBUG') && \constant('SCRIPT_DEBUG') === \true;
    }
    /**
     * Probably deregister a given handle when our requested version is higher than the current
     * enqueued version script / style.
     *
     * @param string $handle
     * @param string $newVersion
     * @param string $type
     */
    public function probablyDeregisterWhenNewerVersion($handle, $newVersion, $type = 'script')
    {
        $wp_dependencies = $type === 'script' ? \wp_scripts() : \wp_styles();
        $queried = $wp_dependencies->query($handle);
        if ($queried !== \false) {
            $currentVersion = $queried->ver;
            // When the currently active version is a representation of `filemtime` (e.g. `1671711519`), automatically
            // convert it to a semantic versioned string so `1.12.17 > 1671711519` and the real version is considered
            // as truth.
            if (\is_numeric($currentVersion) && $currentVersion > 1577865600) {
                $currentVersion = \sprintf('0.0.%d', $currentVersion);
            }
            if (\version_compare($newVersion, $currentVersion, '>')) {
                if ($type === 'script') {
                    \wp_deregister_script($handle);
                } else {
                    \wp_deregister_style($handle);
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Checks if a specific screen is active.
     *
     * @param string $base The base
     * @return boolean
     */
    public function isScreenBase($base)
    {
        if (\function_exists('get_current_screen')) {
            $screen = \get_current_screen();
        } else {
            return \false;
        }
        if (isset($screen->base)) {
            return $screen->base === $base;
        } else {
            return \false;
        }
    }
}
