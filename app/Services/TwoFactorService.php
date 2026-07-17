<?php

namespace App\Services;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class TwoFactorService
{
    private const SECRET_LENGTH = 20;

    private const TOTP_PERIOD = 30;

    private const TOTP_DIGITS = 6;

    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < self::SECRET_LENGTH; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    public function getQrCodeUrl(string $secret, string $email, ?string $issuer = null): string
    {
        $issuer = $issuer ?? \App\Models\Setting::get('company_name', config('app.name', 'DevlioPay'));
        $otpauthUrl = "otpauth://totp/{$issuer}:{$email}?".http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::TOTP_DIGITS,
            'period' => self::TOTP_PERIOD,
        ]);

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => 'M',
            'addQuietzone' => true,
            'drawLightModules' => false,
            'moduleValues' => [
                QRMatrix::M_DATA_DARK => '#ffffff',
                QRMatrix::M_FINDER_DARK => '#ffffff',
                QRMatrix::M_ALIGNMENT_DARK => '#ffffff',
                QRMatrix::M_TIMING_DARK => '#ffffff',
                QRMatrix::M_SEPARATOR_DARK => '#ffffff',
                QRMatrix::M_FORMAT_DARK => '#ffffff',
                QRMatrix::M_VERSION_DARK => '#ffffff',
                QRMatrix::M_QUIETZONE_DARK => '#ffffff',
                QRMatrix::M_DARKMODULE => '#ffffff',
                QRMatrix::M_FINDER_DOT => '#ffffff',
            ],
        ]);

        $qr = new QRCode($options);

        return $qr->render($otpauthUrl);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $timestamp = floor(time() / self::TOTP_PERIOD);

        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = $this->generateCode($secret, $timestamp + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    private function generateCode(string $secret, int $timestamp): string
    {
        $time = pack('N*', 0).pack('N*', $timestamp);
        $hmac = hash_hmac('sha1', $time, $this->base32Decode($secret), true);

        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
        $code = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        ) % pow(10, self::TOTP_DIGITS);

        return str_pad((string) $code, self::TOTP_DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $input): string
    {
        $map = [
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5,
            'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11,
            'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17,
            'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29,
            '6' => 30, '7' => 31,
        ];

        $input = strtoupper(rtrim($input, '='));
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $val = $map[$input[$i]] ?? 0;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
