<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\view;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\announcement\Announcement;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\announcement\AnnouncementPool;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Show announcement notices.
 * @internal
 */
class AnnouncementView
{
    use UtilsProvider;
    /**
     * Announcement pool.
     *
     * @var AnnouncementPool
     */
    private $pool;
    /**
     * C'tor.
     *
     * @param AnnouncementPool $pool
     * @codeCoverageIgnore
     */
    private function __construct($pool)
    {
        $this->pool = $pool;
    }
    /**
     * Output parsed announcement.
     */
    public function admin_notices()
    {
        $pool = $this->getPool();
        if (!$pool->isActive()) {
            return;
        }
        foreach ($pool->getItems() as $a) {
            // Determine if visible
            if (!$a->isVisible() || $a->isDismissed()) {
                continue;
            }
            // Check if only-banner view
            $useBanner = empty($a->getTitle()) && empty($a->getText()) && !empty($a->getGraphicUrl());
            // Get dismiss link
            $pool->syncViewStatus($a);
            if ($useBanner) {
                echo \sprintf('<div class="notice" style="background: none; border: none; box-shadow: none; margin: 5px 0; padding: 0;">%s<p>%s</p></div>', $this->img($a), $a->isDismissible() ? \sprintf('<a href="#" onClick="%s">%s</a>', \esc_js($this->dismissOnClickHandler($a)), \__('Dismiss', RPM_WP_CLIENT_TD)) : '');
            } else {
                echo \sprintf(
                    // Use custom style to overlap the notice over the logo
                    '<div class="notice notice-%s" style="padding-right:38px;position:relative;">%s%s%s%s</div>',
                    $a->getSeverity(),
                    $this->img($a),
                    empty($a->getTitle()) ? '' : \sprintf('<p><strong>%s</strong></p>', $a->getTitle()),
                    empty($a->getText()) ? '' : \sprintf('<p>%s</p>', $a->getText()),
                    // We do not use `is-dismissible` class as we can not add any JS event
                    $a->isDismissible() ? \sprintf('<button type="button" class="notice-dismiss" onClick="%s"></button>', \esc_js($this->dismissOnClickHandler($a))) : ''
                );
            }
        }
    }
    /**
     * Return `<img ` for a given announcement. Can be empty if no graphic is set.
     *
     * @param Announcement $a
     */
    protected function img($a)
    {
        if (empty($a->getGraphicUrl())) {
            return '';
        }
        $img = \sprintf('<img %s style="max-width: 100%%; height: auto; display: block; margin: 10px 0;" />', $this->srcset($a));
        $link = $a->getGraphicFileLink();
        return empty($link) ? $img : \sprintf('<a href="%s" target="_blank">%s</a>', $link, $img);
    }
    /**
     * Return `src` and `srcset` attributes as string for a given announcement.
     *
     * @param Announcement $a
     */
    protected function srcset($a)
    {
        if (empty($a->getGraphicUrlRetina())) {
            return \sprintf('src="%s"', $a->getGraphicUrl());
        }
        return \sprintf('src="%1$s" srcset="%1$s 1x, %2$s 2x"', $a->getGraphicUrl(), $a->getGraphicUrlRetina());
    }
    /**
     * Dismiss-functionality is handled through a inline-onclick handler because we
     * do not need to enqueue an extra script on each page.
     *
     * @param Announcement $announcement
     */
    protected function dismissOnClickHandler($announcement)
    {
        return \join('', ['jQuery(this).parents(".notice").remove();', \sprintf('window.fetch("%s");', \add_query_arg(['_method' => 'DELETE', '_wpnonce' => \wp_create_nonce('wp_rest')], \sprintf('%sannouncement/%s/%d/view', Service::getUrl(Core::getInstance()), $announcement->getPool()->getPluginUpdate()->getInitiator()->getPluginSlug(), $announcement->getId())))]);
    }
    /**
     * Get pool instance.
     *
     * @codeCoverageIgnore
     */
    public function getPool()
    {
        return $this->pool;
    }
    /**
     * New instance.
     *
     * @param AnnouncementPool $pool
     * @codeCoverageIgnore
     */
    public static function instance($pool)
    {
        return new AnnouncementView($pool);
    }
}
