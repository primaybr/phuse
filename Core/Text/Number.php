<?php

declare(strict_types=1);

namespace Core\Text;

class Number
{
    public function shortNumber(int|float $n): string
    {
        [$nFormat, $suffix] = match (true) {
            $n >= 0 && $n < 10**3 => [floor($n), ''],
            $n >= 10**3 && $n < 10**6 => [floor($n / 10**3), 'K+'],
            $n >= 10**6 && $n < 10**9 => [floor($n / 10**6), 'M+'],
            $n >= 10**9 && $n < 10**12 => [floor($n / 10**9), 'B+'],
            $n >= 10**12 => [floor($n / 10**12), 'T+'],
            default => [0, ''],
        };

        return ($nFormat . $suffix) ?? '0';
    }

    public function formatCurrency(mixed $value, string $format = '', int $decimals = 0, string $decimalPoints = ',', string $separator = '.'): string
    {
        return (is_string($value) && is_numeric($value)) || is_numeric($value)
            ? $format . number_format(num: $value, decimals: $decimals, decimal_separator: $decimalPoints, thousands_separator: $separator )
            : (string) $value;
    }

    public function formatPhoneNumber(string|int $phoneNumber, string $prefix = '', string $country = 'id'): string
	{
		$phoneNumber = (string) $phoneNumber;
		
		// Remove non-numeric characters
		$cleanedNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

		// Check if the cleaned number already starts with the country code
		$countryCode = match ($country) {
			'id' => $prefix . '62',
			'us' => $prefix . '1',
			default => $prefix,
		};

		if (strpos($cleanedNumber, $countryCode) === 0) {
			return $cleanedNumber;
		}

		// Remove leading zeroes and prepend the country code
		$cleanedNumber = ltrim($cleanedNumber, '0');
		return $countryCode . $cleanedNumber;
	}
}
