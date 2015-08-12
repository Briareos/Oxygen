<?php

interface Oxygen_Security_Rsa_RsaVerifierInterface {
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
  public function verify($public_key, $data, $signature);
}
