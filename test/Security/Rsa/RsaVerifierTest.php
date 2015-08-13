<?php

namespace Oxygen\Tests\Security\Rsa;

class RsaVerifierTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE = 'This is a unverified message that must be signed.';

    static $privateKey;

    static $publicKey;

    public static function setUpBeforeClass()
    {
        $keyResource = openssl_pkey_new();
        openssl_pkey_export($keyResource, $privateKey);

        self::$publicKey  = openssl_pkey_get_details($keyResource)['key'];
        self::$privateKey = $privateKey;
    }

    public function signerProvider()
    {
        return array(
            array(new \Oxygen_Security_Rsa_OpensslRsaVerifier()),
            array(new \Oxygen_Security_Rsa_PhpRsaVerifier()),
        );
    }

    /**
     * @dataProvider signerProvider
     */
    public function testVerifierVerifiesSignature(\Oxygen_Security_Rsa_RsaVerifierInterface $verifier)
    {
        $data = self::MESSAGE;

        $keyResource = openssl_pkey_new();
        openssl_pkey_export($keyResource, $otherPrivateKey);

        openssl_sign($data, $signature1, self::$privateKey);
        openssl_sign($data.$data, $signature2, self::$privateKey);
        openssl_sign($data, $signature3, $otherPrivateKey);
        openssl_sign($data.$data, $signature4, $otherPrivateKey);

        $this->assertGreaterThan(0, strlen($signature1));
        $this->assertGreaterThan(0, strlen($signature2));
        $this->assertGreaterThan(0, strlen($signature3));
        $this->assertGreaterThan(0, strlen($signature4));

        $this->assertTrue($verifier->verify(self::$publicKey, $data, $signature1));
        $this->assertFalse($verifier->verify(self::$publicKey, $data, $signature2));
        $this->assertFalse($verifier->verify(self::$publicKey, $data, $signature3));
        $this->assertFalse($verifier->verify(self::$publicKey, $data, $signature4));
        $this->assertFalse($verifier->verify(self::$publicKey, $data, 'foobar'));
    }
}
