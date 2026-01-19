<?php

namespace Rapidmail\Tests\Shopware\Unit\Services;

use PHPUnit\Framework\TestCase;
use Rapidmail\Shopware\Services\Encrypter;

class EncrypterTest extends TestCase
{
    public function testEncrypter(): void
    {
        $encrypter = new Encrypter('foo', '1234123412341234');

        $this->assertIsString(
            $encrypter->encrypt(['foo' => 'lol', 'bar' => true]),
            'Ecrypt result should be a string.'
        );
    }
}
