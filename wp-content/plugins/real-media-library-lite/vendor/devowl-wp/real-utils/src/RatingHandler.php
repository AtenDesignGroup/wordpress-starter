<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Allow to handle rating popups depending on usage time.
 * @internal
 */
class RatingHandler
{
    use UtilsProvider;
    const NEVER_SHOW_RATING_AGAIN = -1;
    /**
     * C'tor.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        // Silence is golden.
    }
    /**
     * Dismiss a rating popup for a configured time or completely.
     *
     * @param string $slug
     * @param boolean $force
     * @return boolean
     */
    public function dismiss($slug, $force)
    {
        // Check if initiator exists
        $initiator = Core::getInstance()->getInitiator($slug);
        if ($initiator === null) {
            return \false;
        }
        $ts = $force ? self::NEVER_SHOW_RATING_AGAIN : $initiator->getNextRatingPopup(\false);
        if (TransientHandler::set($initiator, TransientHandler::TRANSIENT_NEXT_RATING, $ts)) {
            return $ts;
        }
        return \false;
    }
    /**
     * Get an array of slugs which can be rated now.
     *
     * @return string[]
     */
    public function getCanBeRated()
    {
        // Only installer should rate, because they downloaded / bought the product
        if (!\current_user_can('activate_plugins')) {
            return [];
        }
        // Cache it
        // @codeCoverageIgnoreStart
        if (!\defined('PHPUNIT_FILE')) {
            static $cache = null;
            if ($cache !== null) {
                return $cache;
            }
        }
        // @codeCoverageIgnoreEnd
        $cache = [];
        foreach (Core::getInstance()->getInitiators() as $initiator) {
            if ($initiator->getRateLink() === null) {
                continue;
            }
            $ts = TransientHandler::get($initiator, TransientHandler::TRANSIENT_NEXT_RATING);
            // Initialize default
            if ($ts === null) {
                $ts = $initiator->getNextRatingPopup(\true);
                TransientHandler::set($initiator, TransientHandler::TRANSIENT_NEXT_RATING, $ts);
            }
            if ($ts !== self::NEVER_SHOW_RATING_AGAIN && \time() >= $ts) {
                $cache[] = $initiator->getPluginSlug();
            }
        }
        return $cache;
    }
    /**
     * Get an array of links of each slug.
     *
     * @return array
     */
    public function getLinks()
    {
        // Only installer should rate, because they downloaded / bought the product
        if (!\current_user_can('activate_plugins')) {
            return [];
        }
        $result = [];
        $can = $this->getCanBeRated();
        foreach (Core::getInstance()->getInitiators() as $initiator) {
            if (\in_array($initiator->getPluginSlug(), $can, \true)) {
                $link = $initiator->getRateLink();
                if ($link !== null) {
                    $result[$initiator->getPluginSlug()] = $link;
                }
            }
        }
        return $result;
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance()
    {
        return new RatingHandler();
    }
}
