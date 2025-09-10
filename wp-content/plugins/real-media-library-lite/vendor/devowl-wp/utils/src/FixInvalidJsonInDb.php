<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use MatthiasWeb\RealMediaLibrary\Vendor\ColinODell\Json5\Json5Decoder;
use MatthiasWeb\RealMediaLibrary\Vendor\ColinODell\Json5\SyntaxError;
use JsonException;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Migration tools can corrupt JSON in database by search & replace domains. For example, the data
 * `["example.com"]` can be transformed to `['example.com']` with invalid single quotes.
 *
 * @see https://app.clickup.com/t/863g4efkw
 * @internal
 */
class FixInvalidJsonInDb
{
    private $json5Supported = \true;
    private $hookedIntoMetadata = [];
    private $metadataSingleMetaKey = [];
    /**
     * C'tor.
     */
    public function __construct()
    {
        $this->json5Supported = \extension_loaded('mbstring') && \class_exists(JsonException::class);
    }
    /**
     * Register a fixer for posts, terms, users, ... metadata.
     *
     * @param string $expectMetaKey
     * @param string $expectMetaType
     */
    public function fixMetadataBySingleMetaKey($expectMetaKey, $expectMetaType = 'post')
    {
        $this->metadataSingleMetaKey[$expectMetaType] = $this->metadataSingleMetaKey[$expectMetaType] ?? [];
        $this->metadataSingleMetaKey[$expectMetaType][] = $expectMetaKey;
        return $this->hookMetadata($expectMetaType);
    }
    /**
     * Hook into retriving the metadata for posts, terms, users, ...
     *
     * @param string $meta_type
     */
    protected function hookMetadata($meta_type = 'post')
    {
        global $wp_version;
        if (\version_compare($wp_version, '5.7.0', '<')) {
            // WP < 5.7 compatibility, do nothing.
            // We need at least this fix: https://github.com/WordPress/WordPress/commit/f0b5757e35ce5217deee077241f9b869a4e81465
            return \false;
        }
        if (!\in_array($meta_type, $this->hookedIntoMetadata, \true)) {
            \add_filter("get_{$meta_type}_metadata", [$this, 'get_metadata'], 10, 5);
            $this->hookedIntoMetadata[] = $meta_type;
        }
        return \true;
    }
    /**
     * Only used for internal usage.
     *
     * @param mixed $value The value to return, either a single metadata value or an array
     *                     of values depending on the value of `$single`. Default null.
     * @param int $object_id ID of the object metadata is for.
     * @param string $meta_key Metadata key.
     * @param bool $single Whether to return only the first value of the specified `$meta_key`
     * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
     *                          or any other object type with an associated meta table.
     * @see https://developer.wordpress.org/reference/hooks/get_meta_type_metadata/
     */
    public function get_metadata($value, $object_id, $meta_key, $single, $meta_type)
    {
        $modifyMetaKeys = $this->metadataSingleMetaKey[$meta_type] ?? [];
        $loadAllMetaKeys = empty($meta_key);
        $loadFixableMetaKey = \in_array($meta_key, $modifyMetaKeys, \true);
        if (\count($modifyMetaKeys) > 0 && ($loadAllMetaKeys || $loadFixableMetaKey)) {
            // Check if another plugin already modified this behavior and use that value instead of recalling
            $changed = \false;
            $check = $value !== null ? $value : $this->get_metadata_raw($meta_type, $object_id, $meta_key, $single);
            if ($loadAllMetaKeys) {
                // Meta key is empty so all meta keys are loaded from cache
                if (\is_array($check)) {
                    foreach ($check as $nestedKey => &$nestedValue) {
                        if (\in_array($nestedKey, $modifyMetaKeys, \true) && \is_array($nestedValue)) {
                            foreach ($nestedValue as $nnKey => &$nnValue) {
                                $newValue = $this->ensureValidJsonString($nnValue);
                                if ($newValue !== \false) {
                                    $nnValue = $newValue;
                                    $changed = \true;
                                }
                            }
                        }
                    }
                }
            } elseif ($loadFixableMetaKey) {
                if ($single) {
                    $newValue = $this->ensureValidJsonString($check);
                    if ($newValue !== \false) {
                        $check = $newValue;
                        $changed = \true;
                    }
                } elseif (\is_array($check)) {
                    foreach ($check as &$nestedValue) {
                        $newValue = $this->ensureValidJsonString($nestedValue);
                        if ($newValue !== \false) {
                            $nestedValue = $newValue;
                            $changed = \true;
                        }
                    }
                }
            }
            if ($changed) {
                return $check;
            }
        }
        return $value;
    }
    /**
     * Ensure a passed string value is a valid JSON string.
     *
     * @param string $val
     */
    public function ensureValidJsonString($val)
    {
        if (!\is_string($val) || !$this->json5Supported) {
            return $val;
        }
        try {
            $decoded = Json5Decoder::decode($val, \true);
            $newVal = \json_encode($decoded);
            return $val !== $newVal ? $newVal : \false;
        } catch (SyntaxError $e) {
            return \false;
        }
    }
    /**
     * Proxy to `get_metadata_raw` with our filter deactivated.
     *
     * @param mixed $value
     * @param int $object_id
     * @param string $meta_key
     * @param bool $single
     * @param string $meta_type
     */
    protected function get_metadata_raw($meta_type, $object_id, $meta_key, $single)
    {
        \remove_filter("get_{$meta_type}_metadata", [$this, 'get_metadata'], 10);
        $value = \get_metadata_raw($meta_type, $object_id, $meta_key, $single);
        \add_filter("get_{$meta_type}_metadata", [$this, 'get_metadata'], 10, 5);
        return $value;
    }
}
