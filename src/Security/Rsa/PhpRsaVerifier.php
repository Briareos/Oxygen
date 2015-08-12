<?php

class Oxygen_Security_Rsa_PhpRsaVerifier implements Oxygen_Security_Rsa_RsaVerifierInterface {
  /**
   * @param string $public_key
   *   Public key in base64-encoded format (with -----BEGIN PUBLIC KEY-----
   *   header and footer).
   * @param string $data
   *   Data to verify.
   * @param string $signature
   *   Signature in base64-encoded format.
   *
   * @return boolean
   */
  public function verify($public_key, $data, $signature) {
    $key = $this->extract_key($public_key);
    list($modulus, $exponent) = $this->get_key_modulus_and_exponent($key);

    return $this->dsa_match($modulus, $exponent, $data, $signature);
  }

  /**
   * @param string $key Key in binary format.
   *
   * @return Oxygen_Math_BigInteger[]
   *   Two elements, fist is modulus, second is exponent.
   *
   * @throws Exception
   */
  private function get_key_modulus_and_exponent($key) {
    if (ord($this->str_shift($key)) !== 48) {
      // verify that the first byte is ord(CRYPT_RSA_ASN1_SEQUENCE) === 48
      throw new Exception('Not a valid key');
    }
    if ($this->get_key_length($key) !== strlen($key)) {
      throw new Exception('Not a valid key length');
    }

    if (ord($this->str_shift($key)) !== 48) {
      throw new Exception('Not a valid key (got ASN1 integer)');
    }

    $temp = $this->str_shift($key, $this->get_key_length($key));
    if (ord($this->str_shift($temp)) !== 6) {
      throw new Exception('Expected CRYPT_RSA_ASN1_OBJECT');
    }

    $length = $this->get_key_length($temp);

    if ($this->str_shift($temp, $length) !== "\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01") {
      throw new Exception('Did not get RSA encryption.');
    }

    $tag = ord($this->str_shift($key)); // skip over the BIT STRING / OCTET STRING tag
    $this->get_key_length($key); // skip over the BIT STRING / OCTET STRING length
    if ($tag === 3) {
      // Got CRYPT_RSA_ASN1_BITSTRING
      $this->str_shift($key);
    }
    if (ord($this->str_shift($key)) !== 48) {
      throw new Exception('Expected CRYPT_RSA_ASN1_SEQUENCE');
    }

    if ($this->get_key_length($key) !== strlen($key)) {
      throw new Exception('Key length is not right');
    }
    if (ord($this->str_shift($key)) !== 2) {
      throw new Exception('Did not get CRYPT_RSA_ASN1_INTEGER');
    }

    $newTemp = $this->str_shift($key, $this->get_key_length($key));

    if (strlen($newTemp) === 1 && ord($newTemp) <= 2) {
      throw new Exception('Should not get here');
    }
    $modulus = new Oxygen_Math_BigInteger($newTemp, 256);
    $this->str_shift($key);
    $exponent_length = $this->get_key_length($key);
    $exponent        = new Oxygen_Math_BigInteger($this->str_shift($key, $exponent_length), 256);

    return array($modulus, $exponent);
  }

  private function dsa_match(Oxygen_Math_BigInteger $modulus, Oxygen_Math_BigInteger $exponent, $data, $raw_signature) {
    $modulus_length = strlen($modulus->toBytes());
    if ($modulus_length !== strlen($raw_signature)) {
      throw new Exception('Signature size is not good');
    }

    $signature = new Oxygen_Math_BigInteger($raw_signature, 256);
    $m2        = $this->rsavp1($signature, $exponent, $modulus);
    if (strlen($m2->toBytes()) > $modulus_length) {
      throw new Exception('m2 is too large');
    }
    $em  = str_pad($m2->toBytes(), $modulus_length, chr(0), STR_PAD_LEFT);
    $em2 = $this->emsaPkcs1v15Encode($data, $modulus_length);

    return $em === $em2;
  }

  /**
   * @link http://tools.ietf.org/html/rfc3447#section-9.2
   *
   * @param $m
   * @param $em_len
   *
   * @return bool
   * @throws \Exception
   */
  private function emsaPkcs1v15Encode($m, $em_len) {
    $h = sha1($m, TRUE);
    $t = pack('H*', '3021300906052b0e03021a05000414');
    $t .= $h;
    $tLen = strlen($t);

    if ($em_len < $tLen + 11) {
      throw new Exception('Intended encoded message length too short');
    }

    $ps = str_repeat(chr(255), $em_len - $tLen - 3);

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
   * @throws Exception
   */
  private function rsavp1(Oxygen_Math_BigInteger $signature, Oxygen_Math_BigInteger $exponent, Oxygen_Math_BigInteger $modulus) {
    $zero = new Oxygen_Math_BigInteger(0);
    if ($signature->compare($zero) < 0 || $signature->compare($modulus) > 0) {
      throw new Exception('Signature representative out of range');
    }

    return $signature->modPow($exponent, $modulus);
  }

  private function extract_key($key) {
    // The key may be prefixed with
    // Bag Attributes
    //     localKeyID: 00 00 00 00
    // Remove that header, -----BEGIN CERTIFICATE----- and -----END CERTIFICATE-----.
    $key = preg_replace('#.*?^-+[^-]+-+#ms', '', $key, 1);
    $key = preg_replace('#-+[^-]+-+#', '', $key);
    // Remove new lines.
    $key = str_replace(array("\r", "\n", ' '), '', $key);
    if (!preg_match('#^[a-zA-Z\d/+]*={0,2}$#', $key)) {
      throw new Exception('The key format is not valid.');
    }

    return base64_decode($key);
  }

  private function str_shift(&$string, $length = 1) {
    $substr = substr($string, 0, $length);
    $string = substr($string, $length);

    return $substr;
  }

  private function get_key_length(&$key) {
    $length = ord($this->str_shift($key));
    if ($length & 0x80) { // definite length, long form
      $length &= 0x7F;
      $temp = $this->str_shift($key, $length);
      list(, $length) = unpack('N', substr(str_pad($temp, 4, chr(0), STR_PAD_LEFT), -4));
    }

    return $length;
  }
}
