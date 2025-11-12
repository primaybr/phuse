<?php

declare(strict_types=1);

namespace Core\Text;

use DateTime;

// Use final class to prevent inheritance
final class Str
{
    public const SEED = 'bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ123456789';

    public function randomString(int $length = 6): string
    {
        $seed = str_split(self::SEED.date('YmdHis'));
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $length) as $k) {
            $rand .= $seed[$k];
        }

        return $rand;
    }

    public function cutString(string $string, int $length = 50): string
    {
        return strlen($string ?? '') > $length ? mb_strcut($string, 0, $length)."..." : $string;
    }

    public function timeElapsedString(string $datetime, bool $full = false): string
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $weeks = intdiv($diff->d, 7);
        $diff->d -= $weeks * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'days',
            'h' => 'hours',
            'i' => 'minutes',
            's' => 'seconds',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'now';
    }

    public function convertTimeFormat(string $datetime): string
    {
        $day = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $month = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // date contain space
        if ($datetime == trim($datetime) && str_contains($datetime, ' ')) {
            [$date, $time] = explode(' ', $datetime);
        } else {
            $date = $datetime;
        }

        [$y, $m, $d] = explode('-', $date);

        $day = $day[date('N', strtotime($date))];
        $month = $month[$m];

        return "$day, $d $month $y";
    }

    public function isBase64(string $string): bool
    {
        // check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }
        // decode the string in strict mode and check the results
        $decoded = base64_decode($string, true);

        if (false === $decoded) {
            return false;
        }

        // encode the string again
        if (base64_encode($decoded) === $string) {
            return false;
        }

        return true;
    }

    public function generateMetaKeywords(string $text): string
    {
        $text = strtolower($text);
        $text = strip_tags($text);
        $text = preg_replace('/[^A-Za-z0-9\s]/', '', $text);
        $keywords = explode(' ', $text);

        $finalText = [];
        if ($keywords) {
            foreach ($keywords as $keyword) {
                // check if keyword is greater than 3 chars long
                if (strlen($keyword) > 3) {
                    $finalText[] = $keyword;
                }
            }
        }

        return implode(', ', $finalText);
    }

    public function generateUUID(int $version = 4): string
    {
        if ($version === 4) {
            // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
            $data = random_bytes(16);
            assert(strlen($data) === 16);

            // Set version to 0100
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // Set bits 6-7 to 10
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            // Output the 36 character UUID.
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        // For other versions, return a default UUID (this method only supports v4)
        return '00000000-0000-0000-0000-000000000000';
    }

    public function studly(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    public function snake(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    public function plural(string $string): string
    {
        // Simple pluralization rules
        if (substr($string, -1) === 'y') {
            return substr($string, 0, -1) . 'ies';
        } elseif (substr($string, -1) === 's' || substr($string, -2) === 'es') {
            return $string;
        } else {
            return $string . 's';
        }
    }
}
