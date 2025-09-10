<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\announcement;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Represent an announcement model.
 * @internal
 */
class Announcement
{
    use UtilsProvider;
    const VISIBILITY_GLOBAL = 'global';
    const VISIBILITY_LOCAL = 'local';
    /**
     * Announcement pool instance.
     *
     * @var AnnouncementPool
     */
    private $pool;
    /**
     * ID.
     *
     * @var int
     */
    private $id;
    /**
     * Full URL to graphic file, can be `null`.
     *
     * @var string
     */
    private $graphicUrl;
    /**
     * Full URL to retina graphic file, can be `null`.
     *
     * @var string
     */
    private $graphicUrlRetina;
    /**
     * Link for graphic file, can be `null`.
     *
     * @var string
     */
    private $graphicFileLink;
    /**
     * Title, can be `null`.
     *
     * @var string
     */
    private $title;
    /**
     * Text, can be `null`.
     *
     * @var string
     */
    private $text;
    /**
     * Can the notice be dismissed?
     *
     * @var boolean
     * @default `true`
     */
    private $dismissible;
    /**
     * Additional data, not yet in use.
     *
     * @var array
     */
    private $data;
    /**
     * Severity of the notice, can be `warning|info|success|error`.
     *
     * @var string
     * @default `info`
     */
    private $severity;
    /**
     * Visibility, can be `global|local`.
     *
     * @var string
     * @default `local`
     */
    private $visibility;
    /**
     * C'tor.
     *
     * @param AnnouncementPool $pool
     * @param string $id
     * @param string $graphicUrl
     * @param string $graphicUrlRetina
     * @param string $graphicFileLink
     * @param string $title
     * @param string $text
     * @param string $dismissible
     * @param string $data
     * @param string $severity
     * @param string $visibility
     * @codeCoverageIgnore
     */
    private function __construct($pool, $id, $graphicUrl = null, $graphicUrlRetina = null, $graphicFileLink = null, $title = null, $text = null, $dismissible = \true, $data = [], $severity = 'info', $visibility = 'local')
    {
        $this->pool = $pool;
        $this->id = $id;
        $this->graphicUrl = $graphicUrl;
        $this->graphicUrlRetina = $graphicUrlRetina;
        $this->graphicFileLink = $graphicFileLink;
        $this->title = $title;
        $this->text = $text;
        $this->dismissible = $dismissible;
        $this->data = $data;
        $this->severity = $severity;
        $this->visibility = $visibility;
    }
    /**
     * Determine if notice is visible on current page.
     */
    public function isVisible()
    {
        return $this->getVisibility() === Announcement::VISIBILITY_LOCAL ? $this->getPool()->getPluginUpdate()->getInitiator()->isLocalAnnouncementVisible() : \true;
    }
    /**
     * Check if this announcement is already dismissed and should no longer be visible.
     */
    public function isDismissed()
    {
        return \in_array($this->getId(), $this->getPool()->getDismissed(), \true);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getPool()
    {
        return $this->pool;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Self-explanatory.
     */
    public function getProduct()
    {
        return $this->getPool()->getPluginUpdate()->getInitiator()->getProductAndVariant()[0];
    }
    /**
     * Self-explanatory.
     */
    public function getProductVariant()
    {
        return $this->getPool()->getPluginUpdate()->getInitiator()->getProductAndVariant()[1];
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getGraphicUrl()
    {
        return \esc_url($this->graphicUrl);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getGraphicUrlRetina()
    {
        return \esc_url($this->graphicUrlRetina);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getGraphicFileLink()
    {
        return \esc_url($this->graphicFileLink);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getTitle()
    {
        return \wp_kses_post($this->title);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getText()
    {
        return \wp_kses_post($this->text);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function isDismissible()
    {
        return $this->dismissible;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getSeverity()
    {
        return \esc_attr($this->severity);
    }
    /**
     * Self-explanatory.
     *
     * @codeCoverageIgnore
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
    /**
     * New instance.
     *
     * @param AnnouncementPool $pool The associated pool to all announcements
     * @param array $response An item from the official REST API response
     * @codeCoverageIgnore
     */
    public static function fromResponse($pool, $response)
    {
        return new Announcement($pool, $response['id'], isset($response['graphicFile']) ? $response['graphicFile']['downloadUrl'] : null, isset($response['graphicFile2x']) ? $response['graphicFile2x']['downloadUrl'] : null, isset($response['graphicFileLink']) ? $response['graphicFileLink'] : null, isset($response['title']) ? $response['title'] : null, isset($response['text']) ? $response['text'] : null, isset($response['dismissible']) ? $response['dismissible'] : \true, isset($response['data']) ? $response['data'] : [], isset($response['severity']) ? $response['severity'] : 'info', isset($response['visibility']) ? $response['visibility'] : 'local');
    }
}
