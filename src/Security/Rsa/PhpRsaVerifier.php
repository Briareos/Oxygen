<?php

class Oxygen_Security_Rsa_PhpRsaVerifier implements Oxygen_Security_Rsa_RsaVerifierInterface
{
    /**
     * @inheritdoc
     */
    public function verify($publicKey, $data, $signature)
    {
        try {
            $key = $this->extractKey($publicKey);
            list($modulus, $exponent) = $this->getKeyModulusAndExponent($key);

            return $this->rsaMatch($modulus, $exponent, $data, base64_decode($signature));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $key Key in binary format.
     *
     * @return Oxygen_Math_BigInteger[]
     *   Two elements, fist is modulus, second is exponent.
     *
     * @throws Exception
     */
    private function getKeyModulusAndExponent($key)
    {
        if (ord($this->strShift($key)) !== 48) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_SEQUENCE);
        }
        if ($this->extractNextKeySegmentLength($key) !== strlen($key)) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_INVALID_LENGTH);
        }

        if (ord($this->strShift($key)) !== 48) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_SEQUENCE);
        }

        $header = $this->strShift($key, $this->extractNextKeySegmentLength($key));
        if (ord($this->strShift($header)) !== 6) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_OBJECT);
        }

        $headerLength = $this->extractNextKeySegmentLength($header);

        if ($this->strShift($header, $headerLength) !== "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01") {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_UNSUPPORTED_ENCRYPTION);
        }

        // Skip over the BIT STRING / OCTET STRING tag.
        $tag = ord($this->strShift($key));
        // Skip over the BIT STRING / OCTET STRING length.
        $this->extractNextKeySegmentLength($key);
        if ($tag !== 3) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_BITSTRING);
        }
        // This part should be run only of $tag === 3; but let's try to be consistent with our keys.
        // If the RSA_KEY_MISSING_ASN1_BITSTRING ever gets to end user, it should be removed and
        // handled appropriately.
        $this->strShift($key);
        if (ord($this->strShift($key)) !== 48) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_SEQUENCE);
        }

        if ($this->extractNextKeySegmentLength($key) !== strlen($key)) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_INVALID_LENGTH);
        }
        if (ord($this->strShift($key)) !== 2) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MISSING_ASN1_INTEGER);
        }

        $rawModulus = $this->strShift($key, $this->extractNextKeySegmentLength($key));

        if (strlen($rawModulus) === 1 && ord($rawModulus) <= 2) {
            throw new Exception('Should not get here');
        }
        $modulus = new Oxygen_Math_BigInteger($rawModulus, 256);
        $this->strShift($key);
        $exponentLength = $this->extractNextKeySegmentLength($key);
        $exponent = new Oxygen_Math_BigInteger($this->strShift($key, $exponentLength), 256);

        return array($modulus, $exponent);
    }

    /**
     * @param Oxygen_Math_BigInteger $modulus
     * @param Oxygen_Math_BigInteger $exponent
     * @param string                 $data
     * @param string                 $rawSignature
     *
     * @return bool
     * @throws Oxygen_Exception
     */
    private function rsaMatch(Oxygen_Math_BigInteger $modulus, Oxygen_Math_BigInteger $exponent, $data, $rawSignature)
    {
        $modulusLength = strlen($modulus->toBytes());
        if ($modulusLength !== strlen($rawSignature)) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_SIGNATURE_SIZE_INVALID);
        }

        $signature = new Oxygen_Math_BigInteger($rawSignature, 256);
        $m2 = $this->rsavp1($signature, $exponent, $modulus);
        if (strlen($m2->toBytes()) > $modulusLength) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_MODULUS_SIZE_INVALID);
        }
        $em = str_pad($m2->toBytes(), $modulusLength, chr(0), STR_PAD_LEFT);
        $em2 = $this->emsaPkcs1v15Encode($data, $modulusLength);

        return Oxygen_Util::hashEquals($em, $em2);
    }

    /**
     * @link http://tools.ietf.org/html/rfc3447#section-9.2
     *
     * @param string $m
     * @param int    $emLength
     *
     * @return bool
     * @throws \Exception
     */
    private function emsaPkcs1v15Encode($m, $emLength)
    {
        $h = sha1($m, true);
        $t = pack('H*', '3021300906052b0e03021a05000414');
        $t .= $h;
        $tLen = strlen($t);

        if ($emLength < $tLen + 11) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_ENCODED_SIZE_INVALID);
        }

        $ps = str_repeat(chr(255), $emLength - $tLen - 3);

        $em = "\0\1$ps\0$t";

        return $em;
    }

    /**
     * @link http://tools.ietf.org/html/rfc3447#section-5.2.2
     *
     * @param Oxygen_Math_BigInteger $signature
     * @param Oxygen_Math_BigInteger $exponent
     * @param Oxygen_Math_BigInteger $modulus
     *
     * @return Oxygen_Math_BigInteger
     * @throws Oxygen_Exception
     */
    private function rsavp1(Oxygen_Math_BigInteger $signature, Oxygen_Math_BigInteger $exponent, Oxygen_Math_BigInteger $modulus)
    {
        $zero = new Oxygen_Math_BigInteger(0);
        if ($signature->compare($zero) < 0 || $signature->compare($modulus) > 0) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_SIGNATURE_REPRESENTATIVE_OUT_OF_RANGE);
        }

        return $signature->modPow($exponent, $modulus);
    }

    /**
     * @param string $key Key in base64-encoded format.
     *
     * @return string Key in binary format.
     * @throws Oxygen_Exception
     */
    private function extractKey($key)
    {
        // Remove header and footer; -----BEGIN CERTIFICATE----- and -----END CERTIFICATE-----.
        $key = preg_replace('{^-.*$}m', '', $key);
        // Remove new lines.
        $key = str_replace(array("\r", "\n", ' '), '', $key);
        if (!preg_match('{^[a-zA-Z\d/+]+={0,2}$}', $key)) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_INVALID_FORMAT);
        }

        return base64_decode($key);
    }

    /**
     * Same as array_shift(), modifies the string by reference.
     *
     * @param string $string
     * @param int    $length How many characters to shift.
     *
     * @return string The substring that was shifted.
     * @throws Oxygen_Exception
     */
    private function strShift(&$string, $length = 1)
    {
        if (strlen($string) < $length || !$length) {
            throw new Oxygen_Exception(Oxygen_Exception::RSA_KEY_INVALID_LENGTH);
        }

        $subString = substr($string, 0, $length);
        $string = substr($string, $length);

        return $subString;
    }

    /**
     * @param string $key Key in binary format. The key is modified afterwards.
     *
     * @return int
     */
    private function extractNextKeySegmentLength(&$key)
    {
        $length = ord($this->strShift($key));
        if ($length & 0x80) {
            // Definite length, long form.
            $length &= 0x7F;
            $temp = $this->strShift($key, $length);
            list(, $length) = unpack('N', substr(str_pad($temp, 4, chr(0), STR_PAD_LEFT), -4));
        }

        return $length;
    }
}
