<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 10/4/18
 * Time: 4:39 PM
 */

namespace Apps\Core_MobileApi\Api;


interface ApiRequestInterface
{
    /**
     * Check method is POST
     * @return bool
     */
    public function isPost();

    /**
     * Check method is GET
     * @return bool
     */
    public function isGet();

    /**
     * Check Method is PATCH
     * @return bool
     */
    public function isPatch();

    /**
     * Check method is PUSH
     * @return bool
     */
    public function isPut();

    /**
     * Check method is
     *
     * @param $name string
     *
     * @return bool
     */
    public function isMethod($name);

    public function isOptions();

    public function isDelete();

    public function isHead();

    public function get($name, $default = null);

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return mixed Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri();

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod();

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name);

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name);

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders();

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion();

    /**
     * Get all request parameters
     * @return mixed
     */
    public function getRequests();

    /**
     * @return mixed
     */
    public function getFiles();

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getFile($key);

}