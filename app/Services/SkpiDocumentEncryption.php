<?php

namespace App\Services;

use RuntimeException;

/**
 * Service enkripsi/dekripsi file dokumen SKPI.
 *
 * Menggunakan AES-256-CBC via OpenSSL.
 * Kunci diambil dari APP_KEY Laravel (32 byte setelah di-decode dari base64).
 * Output enkripsi berupa string base64 aman disimpan di kolom TEXT/LONGTEXT.
 *
 * Format hasil enkripsi: base64( iv[16 bytes] + ciphertext )
 */
class SkpiDocumentEncryption
{
    private const CIPHER = 'AES-256-CBC';
    private const IV_LEN = 16;

    /**
     * Ambil 32-byte key dari APP_KEY.
     */
    private static function getKey(): string
    {
        $appKey = config('app.key');

        // Laravel menyimpan APP_KEY dengan prefix "base64:"
        if (str_starts_with($appKey, 'base64:')) {
            $key = base64_decode(substr($appKey, 7));
        } else {
            $key = $appKey;
        }

        // Pastikan key tepat 32 byte untuk AES-256
        $key = substr($key, 0, 32);
        if (strlen($key) < 32) {
            $key = str_pad($key, 32, "\0");
        }

        return $key;
    }

    /**
     * Enkripsi binary content (isi file .docx).
     *
     * @param  string  $binaryContent  Isi file mentah (binary string)
     * @return string  String base64 yang aman disimpan di database
     */
    public static function encrypt(string $binaryContent): string
    {
        $key = self::getKey();
        $iv  = random_bytes(self::IV_LEN);

        $encrypted = openssl_encrypt(
            $binaryContent,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new RuntimeException('Enkripsi file SKPI gagal: ' . openssl_error_string());
        }

        // Gabungkan IV + ciphertext lalu encode ke base64
        return base64_encode($iv . $encrypted);
    }

    /**
     * Dekripsi string dari database menjadi binary content (isi file .docx).
     *
     * @param  string  $encryptedData  String base64 dari database
     * @return string  Binary content asli (isi file .docx)
     */
    public static function decrypt(string $encryptedData): string
    {
        $key     = self::getKey();
        $decoded = base64_decode($encryptedData, true);

        if ($decoded === false || strlen($decoded) <= self::IV_LEN) {
            throw new RuntimeException('Data enkripsi SKPI tidak valid atau rusak.');
        }

        $iv         = substr($decoded, 0, self::IV_LEN);
        $ciphertext = substr($decoded, self::IV_LEN);

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new RuntimeException('Dekripsi file SKPI gagal: ' . openssl_error_string());
        }

        return $decrypted;
    }
}
