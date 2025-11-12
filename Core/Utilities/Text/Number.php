<?php

declare(strict_types=1);

namespace Core\Utilities\Text;

class Number
{
    /**
     * Format a number into a short readable format (e.g., 1.5K, 2.3M)
     *
     * @param int|float $n The number to format
     * @return string The formatted short number
     */
    public static function shortNumber(int|float $n): string
    {
        if ($n < 0) {
            return '-' . self::shortNumber(abs($n));
        }

        [$nFormat, $suffix] = match (true) {
            $n < 1_000 => [floor($n), ''],
            $n < 1_000_000 => [floor($n / 1_000), 'K'],
            $n < 1_000_000_000 => [floor($n / 1_000_000), 'M'],
            $n < 1_000_000_000_000 => [floor($n / 1_000_000_000), 'B'],
            default => [floor($n / 1_000_000_000_000), 'T'],
        };

        return $nFormat . $suffix;
    }

    /**
     * Format a value as currency
     *
     * @param int|float|string $value The value to format
     * @param string $format Currency symbol or prefix
     * @param int $decimals Number of decimal places
     * @param string $decimalSeparator Decimal separator
     * @param string $thousandsSeparator Thousands separator
     * @return string The formatted currency string
     */
    public static function formatCurrency(int|float|string $value, string $format = '', int $decimals = 0, string $decimalSeparator = ',', string $thousandsSeparator = '.'): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $numericValue = (float) $value;
        return $format . number_format($numericValue, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Format a phone number with country code
     *
     * @param string|int $phoneNumber The phone number to format
     * @param string $country The country code ('id', 'us', 'uk', 'au', etc.)
     * @return string The formatted phone number
     * @throws \InvalidArgumentException If country is not supported
     */
    public static function formatPhoneNumber(string|int $phoneNumber, string $country = 'id'): string
    {
        $phoneNumber = (string) $phoneNumber;

        // Remove non-numeric characters
        $cleanedNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (empty($cleanedNumber)) {
            return $phoneNumber;
        }

        $countryCodes = [
            'id' => '62',
            'us' => '1',
            'uk' => '44',
            'au' => '61',
            'ca' => '1',
            'de' => '49',
            'fr' => '33',
            'jp' => '81',
            'kr' => '82',
            'sg' => '65',
        ];

        if (!isset($countryCodes[$country])) {
            throw new \InvalidArgumentException("Unsupported country: {$country}");
        }

        $countryCode = $countryCodes[$country];

        // Check if the cleaned number already starts with the country code
        if (str_starts_with($cleanedNumber, $countryCode)) {
            return $cleanedNumber;
        }

        // Remove leading zeroes and prepend the country code
        $cleanedNumber = ltrim($cleanedNumber, '0');
        return $countryCode . $cleanedNumber;
    }
}
