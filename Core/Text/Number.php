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

    public function formatCurrency(int|float $value, string $format = ''): string
    {
        return is_string($value) && is_numeric($value)
            ? $format . number_format(num: $value, decimals: 0, thousands_separator: '.')
            : (string) $value;
    }

    public function formatPhoneNumber(string|int $phoneNumber, string $prefix = '', string $country = 'id'): string
    {
        $countryCode = match ($country) {
            'id' => $prefix . '62',
            'us' => $prefix . '1',
            default => $prefix,
        };

        return preg_replace('/[^0-9+]/', '', str_replace('0', $countryCode, $phoneNumber));
    }
}
