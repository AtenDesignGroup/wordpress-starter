<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\client;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealProductManagerWpClient\AbstractInitiator;
use WP_Error;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle Real Product Manager API calls (Utilities).
 * @internal
 */
class ClientUtils
{
    const ERROR_CODE_REMOTE = 'rpm_wpc_remote';
    const ERROR_CODE_CLIENT_PARSE = 'rpm_wpc_client_parse';
    /**
     * Parse response as JSON or return `WP_Error` on failures.
     *
     * @param WP_Error|array $response
     * @return WP_Error|array
     */
    public static function parseResponse($response)
    {
        if (\is_wp_error($response)) {
            return $response;
        }
        $body = \wp_remote_retrieve_body($response);
        $code = \wp_remote_retrieve_response_code($response);
        $codeIsOk = $code >= 200 && $code < 300;
        if (!empty($body)) {
            // Successfully received a body content, parse it
            $json = \json_decode($body, ARRAY_A);
            if ($codeIsOk) {
                return $json;
            } elseif (\is_array($json)) {
                $errors = [];
                $codes = [];
                foreach ($json as $error) {
                    if (\is_array($error)) {
                        $errors[] = $error['message'];
                        $codes[] = $error['code'];
                    }
                }
                if (\count($errors) > 0) {
                    $wpError = new WP_Error(self::ERROR_CODE_REMOTE, '', ['body' => $json]);
                    foreach ($errors as $index => $error) {
                        $wpError->add($codes[$index], $error);
                    }
                    return $wpError;
                }
            }
        } elseif ($codeIsOk) {
            // Is the body empty and success code? E.g. `DELETE` requests
            return [];
        } else {
            return new WP_Error(self::ERROR_CODE_CLIENT_PARSE, \__('Something went wrong while parsing an error message from the remote server.'), ['response' => $response]);
        }
    }
    /**
     * `POST` (or `PUT`, `GET`, ...) data to the remote host.
     *
     * @param AbstractInitiator $initiator
     * @param string $endpoint
     * @param array $body
     * @param string $method
     */
    public static function request($initiator, $endpoint, $body = null, $method = 'GET')
    {
        $isGet = \strtolower($method) === 'get';
        $url = $initiator->getHost() . $endpoint;
        $response = \wp_remote_post($isGet ? \add_query_arg($body, $url) : $url, ['headers' => ['Content-Type' => $isGet ? null : 'application/json; charset=utf-8', 'Accept-Language' => \get_locale()], 'body' => $isGet ? null : \json_encode($body), 'method' => $method, 'data_format' => $isGet ? null : 'body']);
        return self::parseResponse($response);
    }
}
