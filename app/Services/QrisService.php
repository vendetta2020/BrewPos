<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrisService
{
    /**
     * Format a TLV (Tag-Length-Value) element according to EMVCo QRIS specification.
     */
    public function formatTlv(string $tag, string $value): string
    {
        $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Calculate CRC16-CCITT (polynomial 0x1021, init 0xFFFF) for EMVCo QR string.
     */
    public function calculateCrc16(string $data): string
    {
        $crc = 0xFFFF;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Generate an authentic EMVCo dynamic QRIS string for BrewPOS.
     */
    public function generateQrisString(float $amount, string $referenceNo, string $merchantName = 'BrewPOS Coffee'): string
    {
        // 00: Format Indicator
        $payload = $this->formatTlv('00', '01');

        // 01: Initiation Method (12 = Dynamic QR with fixed amount)
        $payload .= $this->formatTlv('01', '12');

        // 26: Merchant Account Information (Standard National QRIS)
        $maiSub = $this->formatTlv('00', 'ID.CO.QRIS.WWW');
        $maiSub .= $this->formatTlv('01', 'ID1020021234567'); // National Merchant ID / Acquirer
        $maiSub .= $this->formatTlv('02', '081234567890');
        $payload .= $this->formatTlv('26', $maiSub);

        // 52: Merchant Category Code (5812: Food & Beverage / Restaurants)
        $payload .= $this->formatTlv('52', '5812');

        // 53: Transaction Currency (360 = IDR per ISO 4217)
        $payload .= $this->formatTlv('53', '360');

        // 54: Transaction Amount
        $amountFormatted = number_format($amount, 0, '', '');
        $payload .= $this->formatTlv('54', $amountFormatted);

        // 58: Country Code
        $payload .= $this->formatTlv('58', 'ID');

        // 59: Merchant Name
        $payload .= $this->formatTlv('59', substr($merchantName, 0, 25));

        // 60: Merchant City
        $payload .= $this->formatTlv('60', 'Jakarta');

        // 61: Postal Code
        $payload .= $this->formatTlv('61', '12340');

        // 62: Additional Data Field (Reference Number)
        $addSub = $this->formatTlv('01', substr($referenceNo, 0, 25));
        $payload .= $this->formatTlv('62', $addSub);

        // 63: Checksum tag & length before computing CRC
        $payloadWithoutCrc = $payload . '6304';
        $crc = $this->calculateCrc16($payloadWithoutCrc);

        return $payloadWithoutCrc . $crc;
    }

    /**
     * Generate SVG data URI of the QR Code using chillerlan/php-qrcode.
     */
    public function renderQrCodeImage(string $qrContent): string
    {
        $options = new \chillerlan\QRCode\QROptions();
        $options->scale = 6;
        $options->outputBase64 = true;
        $options->drawLightModules = true;
        $options->svgViewBox = true;

        $qrcode = new QRCode($options);
        return $qrcode->render($qrContent);
    }

    /**
     * Create and register a pending QRIS transaction in cache.
     */
    public function createTransaction(float $amount, ?string $orderNumber = null, ?int $orderId = null, ?string $customReference = null): array
    {
        $referenceNo = $customReference ?: ('QRIS-' . date('YmdHis') . '-' . strtoupper(Str::random(4)));
        $qrString = $this->generateQrisString($amount, $referenceNo);
        $qrImage = $this->renderQrCodeImage($qrString);
        $expiresAt = now()->addMinutes(15);

        $data = [
            'reference_no'  => $referenceNo,
            'order_id'      => $orderId,
            'order_number'  => $orderNumber,
            'amount'        => (float) $amount,
            'qr_string'     => $qrString,
            'qr_image'      => $qrImage,
            'status'        => 'pending',
            'created_at'    => now()->toIso8601String(),
            'expires_at'    => $expiresAt->toIso8601String(),
        ];

        Cache::put("qris_trx_{$referenceNo}", $data, $expiresAt);

        if ($orderNumber) {
            Cache::put("qris_order_{$orderNumber}", $referenceNo, $expiresAt);
        }

        return $data;
    }

    /**
     * Get transaction data by reference or order number.
     */
    public function getTransaction(string $identifier): ?array
    {
        $data = Cache::get("qris_trx_{$identifier}");

        if (! $data) {
            $refFromOrder = Cache::get("qris_order_{$identifier}");
            if ($refFromOrder) {
                $data = Cache::get("qris_trx_{$refFromOrder}");
            }
        }

        return $data;
    }

    /**
     * Mark a QRIS transaction as paid (via callback or simulation).
     */
    public function markAsPaid(string $referenceNo, array $metadata = []): ?array
    {
        $data = $this->getTransaction($referenceNo);
        if (! $data) {
            // If transaction not found in cache, still create a record for this reference
            $data = [
                'reference_no'       => $referenceNo,
                'order_id'           => null,
                'order_number'       => null,
                'amount'             => (float) ($metadata['amount'] ?? 0),
                'status'             => 'paid',
                'created_at'         => now()->toIso8601String(),
                'expires_at'         => now()->addHours(2)->toIso8601String(),
            ];
        }

        $data['status'] = 'paid';
        $data['paid_at'] = now()->toIso8601String();
        $data['callback_metadata'] = $metadata;

        Cache::put("qris_trx_{$data['reference_no']}", $data, now()->addHours(2));

        if (! empty($data['order_number'])) {
            Cache::put("qris_order_{$data['order_number']}", $data['reference_no'], now()->addHours(2));
        }

        return $data;
    }
}
