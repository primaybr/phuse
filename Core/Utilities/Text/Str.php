<?php

declare(strict_types=1);

namespace Core\Utilities\Text;

use DateTime;
use DateTimeZone;

// Use final class to prevent inheritance
final class Str
{
    public const SEED = 'bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ123456789';

    /**
     * Generate a cryptographically secure random string
     *
     * @param int $length The length of the random string
     * @return string The random string
     * @throws \InvalidArgumentException If length is not positive
     */
    public static function randomString(int $length = 6): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Length must be a positive integer');
        }

        $characters = self::SEED;
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Truncate a string to a specified length with ellipsis
     *
     * @param string $string The string to truncate
     * @param int $length The maximum length
     * @param string $suffix The suffix to append when truncated
     * @return string The truncated string
     */
    public static function cutString(string $string, int $length = 50, string $suffix = '...'): string
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }

        return mb_strcut($string, 0, $length) . $suffix;
    }

    /**
     * Generate a human-readable time elapsed string
     *
     * @param string $datetime The datetime string to compare against
     * @param bool $full Whether to show full elapsed time or just the most significant unit
     * @return string The time elapsed string
     * @throws \Exception If datetime is invalid
     */
    public static function timeElapsedString(string $datetime, bool $full = false): string
    {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $units = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        $parts = [];
        foreach ($units as $unit => $name) {
            if ($diff->$unit > 0) {
                $parts[] = $diff->$unit . ' ' . $name . ($diff->$unit > 1 ? 's' : '');
            }
        }

        if (empty($parts)) {
            return 'just now';
        }

        if (!$full) {
            return $parts[0] . ' ago';
        }

        $last = array_pop($parts);
        return (empty($parts) ? '' : implode(', ', $parts) . ' and ') . $last . ' ago';
    }

    /**
     * Convert a date string to a formatted Indonesian date
     *
     * @param string $datetime The datetime string (Y-m-d format or Y-m-d H:i:s)
     * @param string $timezone The timezone for formatting
     * @return string The formatted date string
     * @throws \Exception If datetime is invalid
     */
    public static function convertTimeFormat(string $datetime, string $timezone = 'Asia/Jakarta'): string
    {
        $date = new DateTime($datetime, new DateTimeZone($timezone));

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $dayName = $days[$date->format('w')];
        $day = $date->format('d');
        $monthName = $months[$date->format('n') - 1];
        $year = $date->format('Y');

        return "{$dayName}, {$day} {$monthName} {$year}";
    }

    /**
     * Check if a string is valid base64 encoded
     *
     * @param string $string The string to check
     * @return bool True if the string is valid base64
     */
    public static function isBase64(string $string): bool
    {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($string, true);

        if ($decoded === false) {
            return false;
        }

        // Encode the string again and check if it matches
        return base64_encode($decoded) === $string;
    }

    /**
     * Generate meta keywords from text content
     *
     * @param string $text The text to extract keywords from
     * @param int $minLength Minimum keyword length
     * @param int $maxKeywords Maximum number of keywords to return
     * @return string Comma-separated keywords
     */
    public static function generateMetaKeywords(string $text, int $minLength = 3, int $maxKeywords = 10): string
    {
        $text = strtolower(strip_tags($text));
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $words = array_filter(explode(' ', $text));

        $keywords = [];
        $wordCount = array_count_values($words);

        // Sort by frequency, then alphabetically
        arsort($wordCount);

        foreach ($wordCount as $word => $count) {
            if (strlen($word) >= $minLength && count($keywords) < $maxKeywords) {
                $keywords[] = $word;
            }
        }

        return implode(', ', $keywords);
    }

    /**
     * Generate a UUID with enhanced uniqueness guarantees
     *
     * Supports multiple UUID versions with different uniqueness strategies:
     * - v4: Random (highest entropy, best for general use)
     * - v1: Time-based (includes timestamp and MAC address)
     * - v3: Name-based MD5 (deterministic, based on namespace and name)
     * - v5: Name-based SHA1 (deterministic, based on namespace and name)
     *
     * @param int $version The UUID version (1, 3, 4, 5) - defaults to 4
     * @param string|null $namespace UUID namespace for v3/v5 (RFC 4122 namespaces)
     * @param string|null $name Name for v3/v5 generation
     * @return string The generated UUID
     * @throws \InvalidArgumentException If version is not supported or parameters are invalid
     * @throws \RuntimeException If random data generation fails
     */
    public static function generateUUID(int $version = 4, ?string $namespace = null, ?string $name = null): string
    {
        return match ($version) {
            1 => self::generateUUIDv1(),
            3 => self::generateUUIDv3($namespace ?? '', $name ?? ''),
            4 => self::generateUUIDv4(),
            5 => self::generateUUIDv5($namespace ?? '', $name ?? ''),
            default => throw new \InvalidArgumentException("UUID version {$version} is not supported. Supported versions: 1, 3, 4, 5")
        };
    }

    /**
     * Generate a UUID v4 (random) with maximum entropy
     *
     * Uses cryptographically secure random bytes and includes additional
     * entropy sources for maximum uniqueness guarantee.
     *
     * @return string UUID v4 string
     * @throws \RuntimeException If random data generation fails
     */
    private static function generateUUIDv4(): string
    {
        try {
            // Generate 16 bytes of cryptographically secure random data
            $data = random_bytes(16);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate cryptographically secure random data: ' . $e->getMessage());
        }

        // Add additional entropy by mixing with microtime and process ID
        $entropy = microtime(true) . getmypid() . random_int(0, PHP_INT_MAX);
        $hash = hash('sha256', $entropy, true);

        // XOR the random data with additional entropy (first 16 bytes)
        for ($i = 0; $i < 16; $i++) {
            $data[$i] = chr(ord($data[$i]) ^ ord($hash[$i]));
        }

        // Set version to 0100 (bits 4-7 of byte 6)
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

        // Set bits 6-7 to 10 (RFC 4122 variant)
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        // Format as UUID string
        return sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    /**
     * Generate a UUID v1 (time-based)
     *
     * Includes timestamp, clock sequence, and node identifier for uniqueness.
     * Useful when you need UUIDs that can be sorted chronologically.
     *
     * @return string UUID v1 string
     */
    private static function generateUUIDv1(): string
    {
        // Get current timestamp (100-nanosecond intervals since UUID epoch)
        $timestamp = self::getUUIDTimestamp();

        // Generate clock sequence (random for this implementation)
        $clockSeq = random_int(0, 0x3FFF);

        // Use MAC address or random node identifier
        $node = self::getNodeIdentifier();

        // Construct UUID v1
        $timeLow = $timestamp & 0xFFFFFFFF;
        $timeMid = ($timestamp >> 32) & 0xFFFF;
        $timeHi = ($timestamp >> 48) & 0x0FFF;

        // Set version (1) in time_hi
        $timeHi |= 0x1000;

        // Set variant in clock_seq_hi
        $clockSeq |= 0x8000;

        return sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            $timeLow,
            $timeMid,
            $timeHi,
            $clockSeq,
            $node
        );
    }

    /**
     * Generate a UUID v3 (name-based with MD5)
     *
     * Deterministically generates UUID based on namespace and name.
     * Same input will always produce the same UUID.
     *
     * @param string $namespace UUID namespace
     * @param string $name Name to hash
     * @return string UUID v3 string
     * @throws \InvalidArgumentException If namespace is invalid
     */
    private static function generateUUIDv3(string $namespace, string $name): string
    {
        if (!self::isValidUUID($namespace)) {
            throw new \InvalidArgumentException('Invalid namespace UUID provided');
        }

        // Convert namespace UUID to binary
        $namespaceBytes = self::uuidToBytes($namespace);

        // Hash namespace + name with MD5
        $hash = md5($namespaceBytes . $name, true);

        // Set version (3) and variant
        $hash[6] = chr((ord($hash[6]) & 0x0F) | 0x30);
        $hash[8] = chr((ord($hash[8]) & 0x3F) | 0x80);

        return self::bytesToUUID($hash);
    }

    /**
     * Generate a UUID v5 (name-based with SHA1)
     *
     * Deterministically generates UUID based on namespace and name using SHA1.
     * Same input will always produce the same UUID. Preferred over v3.
     *
     * @param string $namespace UUID namespace
     * @param string $name Name to hash
     * @return string UUID v5 string
     * @throws \InvalidArgumentException If namespace is invalid
     */
    private static function generateUUIDv5(string $namespace, string $name): string
    {
        if (!self::isValidUUID($namespace)) {
            throw new \InvalidArgumentException('Invalid namespace UUID provided');
        }

        // Convert namespace UUID to binary
        $namespaceBytes = self::uuidToBytes($namespace);

        // Hash namespace + name with SHA1
        $hash = sha1($namespaceBytes . $name, true);

        // Set version (5) and variant
        $hash[6] = chr((ord($hash[6]) & 0x0F) | 0x50);
        $hash[8] = chr((ord($hash[8]) & 0x3F) | 0x80);

        return self::bytesToUUID($hash);
    }

    /**
     * Get RFC 4122 namespace UUIDs
     *
     * @return array<string, string> Predefined namespace UUIDs
     */
    public static function getNamespaces(): array
    {
        return [
            'dns' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            'url' => '6ba7b811-9dad-11d1-80b4-00c04fd430c8',
            'oid' => '6ba7b812-9dad-11d1-80b4-00c04fd430c8',
            'x500' => '6ba7b814-9dad-11d1-80b4-00c04fd430c8',
        ];
    }

    /**
     * Validate UUID format
     *
     * @param string $uuid UUID string to validate
     * @return bool True if valid UUID format
     */
    public static function isValidUUID(string $uuid): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        ) === 1;
    }

    /**
     * Get timestamp for UUID v1 (100-nanosecond intervals since UUID epoch)
     *
     * @return int Timestamp value
     */
    private static function getUUIDTimestamp(): int
    {
        // UUID epoch: October 15, 1582
        $uuidEpoch = new \DateTime('1582-10-15 00:00:00', new \DateTimeZone('UTC'));
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $interval = $now->diff($uuidEpoch);
        $seconds = ($interval->days * 86400) + ($interval->h + ($interval->i * 60) + $interval->s);

        // Convert to 100-nanosecond intervals
        return ($seconds * 10000000) + ($now->format('u') * 10);
    }

    /**
     * Get node identifier (MAC address or random)
     *
     * @return int 48-bit node identifier
     */
    private static function getNodeIdentifier(): int
    {
        // Try to get MAC address (simplified - in production you'd use proper MAC detection)
        // For now, generate a random 48-bit identifier
        return random_int(0, 0xFFFFFFFFFFFF);
    }

    /**
     * Convert UUID string to binary bytes
     *
     * @param string $uuid UUID string
     * @return string Binary representation
     */
    private static function uuidToBytes(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
    }

    /**
     * Convert binary bytes to UUID string
     *
     * @param string $bytes 16 bytes of binary data
     * @return string UUID string
     */
    private static function bytesToUUID(string $bytes): string
    {
        return sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6))
        );
    }

    /**
     * Convert a string to StudlyCase (PascalCase)
     *
     * @param string $string The string to convert
     * @return string The converted string
     */
    public static function studly(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string)));
    }

    /**
     * Convert a string to snake_case
     *
     * @param string $string The string to convert
     * @return string The converted string
     */
    public static function snake(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * Convert a word to its plural form (basic implementation)
     *
     * @param string $string The word to pluralize
     * @return string The pluralized word
     */
    public static function plural(string $string): string
    {
        $string = trim($string);

        if (empty($string)) {
            return $string;
        }

        // Handle common irregular plurals
        $irregulars = [
            'child' => 'children',
            'foot' => 'feet',
            'goose' => 'geese',
            'man' => 'men',
            'mouse' => 'mice',
            'person' => 'people',
            'tooth' => 'teeth',
            'woman' => 'women',
        ];

        $lower = strtolower($string);
        if (isset($irregulars[$lower])) {
            return $irregulars[$lower];
        }

        // Handle words ending in 'is' -> 'es'
        if (substr($lower, -2) === 'is') {
            return substr($string, 0, -2) . 'es';
        }

        // Handle words ending in 'us' -> 'i' (but only for Latin words)
        if (substr($lower, -2) === 'us' && in_array($lower, ['focus', 'radius', 'virus'])) {
            return substr($string, 0, -2) . 'i';
        }

        // Handle words ending in 'y' preceded by consonant
        if (substr($lower, -1) === 'y' && !in_array(substr($lower, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            return substr($string, 0, -1) . 'ies';
        }

        // Handle words ending in 'f' or 'fe'
        if (substr($lower, -1) === 'f') {
            return substr($string, 0, -1) . 'ves';
        }
        if (substr($lower, -2) === 'fe') {
            return substr($string, 0, -2) . 'ves';
        }

        // Handle words ending in 's', 'sh', 'ch', 'x', 'z'
        if (in_array(substr($lower, -1), ['s', 'sh', 'ch', 'x', 'z'])) {
            return $string . 'es';
        }

        // Default: add 's'
        return $string . 's';
    }
}
