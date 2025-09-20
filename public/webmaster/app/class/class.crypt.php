<?php
class Crypt {
	private $hashAlgorithm = 'sha256';

	private $cryptAlgorithm = 'AES-256-CBC';

	private $security_key;


	public function __construct($key) {
	    $this->security_key = $key;
	}
	
	public function encode($data) {
	    $iv_len = openssl_cipher_iv_length($this->cryptAlgorithm);
	    $iv = openssl_random_pseudo_bytes($iv_len);
	    $ciphertext_raw = openssl_encrypt($data, $this->cryptAlgorithm, $this->security_key, OPENSSL_RAW_DATA, $iv);
	    $hmac = hash_hmac($this->hashAlgorithm, $ciphertext_raw, $this->security_key, true);
	    return base64_encode($iv.$hmac.$ciphertext_raw);
	}
	
	public function decode($data) {
        $sha2len = 32;
        $c = base64_decode($data);
        if(false === $c) {
            return false;
        }
        $iv_len = openssl_cipher_iv_length($this->cryptAlgorithm);
        $iv = substr($c, 0, $iv_len);
        if(false === $iv) {
            return false;
        }
        $hmac = substr($c, $iv_len, $sha2len);
        if(false === $hmac) {
            return false;
        }
        $ciphertext_raw = substr($c, $iv_len + $sha2len);
        if(false === $ciphertext_raw) {
            return false;
        }

        $original_plaintext = openssl_decrypt($ciphertext_raw, $this->cryptAlgorithm, $this->security_key, OPENSSL_RAW_DATA, $iv);
        if(false === $original_plaintext) {
            return false;
        }
        $calc_mac = hash_hmac($this->hashAlgorithm, $ciphertext_raw, $this->security_key, true);
        if(false === $calc_mac) {
            return false;
        }
        return hash_equals($hmac, $calc_mac) ? $original_plaintext : false;
	}
}