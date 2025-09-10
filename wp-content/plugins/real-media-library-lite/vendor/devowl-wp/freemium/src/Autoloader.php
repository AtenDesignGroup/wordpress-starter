<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request.
// @codeCoverageIgnoreEnd
/**
 * Plugin autoloader for lite version.
 * @internal
 */
class Autoloader
{
    private $constantPrefix;
    private $overridesInc;
    private $nsPrefix;
    /**
     * C'tor.
     *
     * @param string $constantPrefix
     * @codeCoverageIgnore
     */
    public function __construct($constantPrefix)
    {
        $this->constantPrefix = $constantPrefix;
        $this->prepare();
    }
    /**
     * Register the autoloader and define needed constants.
     */
    protected function prepare()
    {
        $prefix = $this->getConstantPrefix();
        $inc = \constant($prefix . '_INC');
        $isPro = \is_dir($inc . 'overrides/pro') && !(\defined($prefix . '_LITE') && \constant($prefix . '_LITE'));
        $overridesInc = $inc . 'overrides/' . ($isPro ? 'pro' : 'lite') . '/';
        $this->nsPrefix = \constant($this->getConstantPrefix() . '_NS') . '\\';
        $this->overridesInc = $overridesInc;
        \define($prefix . '_IS_PRO', $isPro);
        \define($prefix . '_OVERRIDES_INC', $overridesInc);
        \spl_autoload_register([$this, 'autoload']);
    }
    /**
     * Autoloader for lite classes.
     *
     * @param string $className
     */
    public function autoload($className)
    {
        $nsPrefix = $this->getNsPrefix();
        if (0 === \strpos($className, $nsPrefix)) {
            $name = \substr($className, \strlen($nsPrefix));
            $basepath = \str_replace('\\', '/', $name);
            if (\substr($basepath, 0, 5) === 'lite/') {
                $basepath = \substr($basepath, 5);
                $filename = $this->getOverridesInc() . $basepath . '.php';
                if (\is_file($filename)) {
                    // @codeCoverageIgnoreStart
                    if (!\defined('PHPUNIT_FILE')) {
                        require_once $filename;
                    }
                    // @codeCoverageIgnoreEnd
                    return;
                }
            }
        }
    }
    /**
     * Get constant prefix.
     *
     * @codeCoverageIgnore
     */
    public function getConstantPrefix()
    {
        return $this->constantPrefix;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getOverridesInc()
    {
        return $this->overridesInc;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getNsPrefix()
    {
        return $this->nsPrefix;
    }
}
