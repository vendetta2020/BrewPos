<?php

namespace Tests\Unit;

use App\Services\QrisService;
use Tests\TestCase;

class QrisServiceTest extends TestCase
{
    public function test_qris_service_generates_emvco_string_and_valid_crc16(): void
    {
        $service = new QrisService();
        $qrisString = $service->generateQrisString(50000, 'QRIS-TEST-1234');

        $this->assertStringStartsWith('000201010212', $qrisString);
        $this->assertStringContainsString('5303360', $qrisString); // Currency IDR
        $this->assertStringContainsString('540550000', $qrisString); // Amount 50000
        $this->assertStringContainsString('5802ID', $qrisString); // Country ID
        $this->assertStringContainsString('5914BrewPOS Coffee', $qrisString); // Merchant Name
        $this->assertStringContainsString('6304', $qrisString); // CRC Tag

        // Checksum is 4 hex chars at the end
        $crc = substr($qrisString, -4);
        $payloadWithoutCrc = substr($qrisString, 0, -4);
        $calculatedCrc = $service->calculateCrc16($payloadWithoutCrc);
        $this->assertEquals($calculatedCrc, $crc);
    }

    public function test_qris_service_creates_transaction_and_svg_image(): void
    {
        $service = new QrisService();
        $trx = $service->createTransaction(35000, 'TRX-ORDER-001');

        $this->assertNotEmpty($trx['reference_no']);
        $this->assertStringStartsWith('QRIS-', $trx['reference_no']);
        $this->assertEquals('TRX-ORDER-001', $trx['order_number']);
        $this->assertEquals(35000, $trx['amount']);
        $this->assertEquals('pending', $trx['status']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $trx['qr_image']);

        // Check cache retrieval
        $fetched = $service->getTransaction($trx['reference_no']);
        $this->assertNotNull($fetched);
        $this->assertEquals($trx['reference_no'], $fetched['reference_no']);

        // Test mark as paid
        $paid = $service->markAsPaid($trx['reference_no'], ['channel' => 'simulator']);
        $this->assertEquals('paid', $paid['status']);
        $this->assertNotEmpty($paid['paid_at']);
    }
}
