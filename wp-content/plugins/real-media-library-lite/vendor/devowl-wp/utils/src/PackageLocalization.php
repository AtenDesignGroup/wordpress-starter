<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Base i18n management for backend and frontend for a package.
 * For non-utils packages you need to extend from this class and
 * properly fill the constructor.
 * @internal
 */
class PackageLocalization
{
    use Localization;
    private $rootSlug;
    private $packageDir;
    private $packageInfo = null;
    /**
     * C'tor.
     *
     * @param string $rootSlug Your workspace scope name.
     * @param string $packageDir Absolute path to your package.
     * @codeCoverageIgnore
     */
    protected function __construct($rootSlug, $packageDir)
    {
        $this->rootSlug = $rootSlug;
        $this->packageDir = \trailingslashit($packageDir);
    }
    /**
     * Get the directory where the languages folder exists.
     *
     * @param string $type
     * @return string[]
     */
    protected function getPackageInfo($type)
    {
        if ($this->packageInfo === null) {
            $textdomain = $this->getRootSlug() . '-' . $this->getPackage();
            if ($type === Constants::LOCALIZATION_BACKEND) {
                $this->packageInfo = [$this->getPackageDir() . 'languages/backend', $textdomain, $this->getPackage()];
            } else {
                $this->packageInfo = [$this->getPackageDir() . 'languages/frontend/json', $textdomain, $this->getPackage()];
            }
        }
        return $this->packageInfo;
    }
    /**
     * Getter.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRootSlug()
    {
        return $this->rootSlug;
    }
    /**
     * Get package name.
     *
     * @return string
     */
    public function getPackage()
    {
        return \basename($this->getPackageDir());
    }
    /**
     * Getter.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPackageDir()
    {
        return $this->packageDir;
    }
    /**
     * New instance.
     *
     * @param string $rootSlug
     * @param string $packageDir
     * @return PackageLocalization
     * @codeCoverageIgnore Instance getter
     */
    public static function instance($rootSlug, $packageDir)
    {
        return new PackageLocalization($rootSlug, $packageDir);
    }
    /**
     * Get the `/languages` folder which is directly located under the plugins path.
     *
     * @param string $path A path to a folder or file within the plugins folder
     * @param boolean $appendFile If `true`, it automatically appends the basename of the `$path` to the resulting path
     * @param string $format The result format, can be `filesystem` or `url`
     */
    public static function getParentLanguageFolder($path, $appendFile = \false, $format = 'filesystem')
    {
        $slug = \explode('/', \plugin_basename($path))[0];
        $pluginFilePath = \constant('WP_PLUGIN_DIR') . '/' . $slug;
        $pluginFile = $pluginFilePath . '/index.php';
        $pluginLanguagesFolder = $pluginFilePath . '/languages/';
        $appendFile = $appendFile ? \basename($path) : '';
        $pathRelative = \substr($path, \strlen($pluginFilePath) + 1);
        $defaultReturn = $format === 'url' ? \plugins_url($pathRelative, $pluginFile) : \untrailingslashit($path);
        $pluginFilePathIsDir = @\is_dir($pluginLanguagesFolder);
        $result = \untrailingslashit($pluginFilePathIsDir ? $pluginLanguagesFolder . $appendFile : $path);
        $isRemoteMeta = $pluginFilePathIsDir && \is_file($pluginLanguagesFolder . 'meta.json');
        if ($isRemoteMeta) {
            // It is placed in `wp-content/languages/` or `wp-includes/languages/...`
            $cacheDir = self::getMoCacheDir($slug);
            if (!$cacheDir) {
                // Offloaded languages were not yet downloaded
                return $defaultReturn;
            }
            $result = \trailingslashit($cacheDir) . $appendFile;
            if ($format === 'url') {
                $wpContentDir = \constant('WP_CONTENT_DIR');
                $wpIncludesDir = \constant('ABSPATH') . \constant('WPINC');
                if (\strpos($result, $wpContentDir) === 0) {
                    return \content_url(\substr($result, \strlen($wpContentDir)));
                } elseif (\strpos($result, $wpIncludesDir) === 0) {
                    return \includes_url(\substr($result, \strlen($wpIncludesDir)));
                }
            } else {
                return $result;
            }
        }
        return $defaultReturn;
    }
}
