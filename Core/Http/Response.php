<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * HTTP Response Class
 *
 * Represents an HTTP response with status code and status text. This class provides
 * a comprehensive collection of HTTP status codes and their corresponding messages.
 * It uses modern PHP features like readonly properties for immutability.
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
class Response
{
    /**
     * Comprehensive collection of HTTP status codes and their corresponding messages.
     *
     * This constant array includes all standard HTTP status codes organized by category:
     * - 1×× Informational responses
     * - 2×× Successful responses
     * - 3×× Redirection messages
     * - 4×× Client error responses
     * - 5×× Server error responses
     */
    public const STATUS_CODES = [
        // 1×× Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 2×× Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // 3×× Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4×× Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // 5×× Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error'
    ];

    /**
     * The HTTP status code for this response.
     */
    public readonly int $statusCode;

    /**
     * The human-readable status message for this response.
     */
    public readonly string $statusName;

    /**
     * The wrapper/protocol used for this response (http, https, etc.).
     */
    public readonly string $wrapper;

    /**
     * Creates a new HTTP Response instance.
     *
     * @param int $status The HTTP status code (default: 200 OK).
     * @param string $wrapper The protocol wrapper (default: 'http').
     */
    public function __construct(int $status = 200, string $wrapper = 'http')
    {
        $this->statusCode = $status;
        $this->statusName = self::STATUS_CODES[$status] ?? 'Unknown Status Code';
        $this->wrapper = $wrapper;
    }
}
