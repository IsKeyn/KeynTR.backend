<?php
namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class MessageCipher
{
    private string $key;
    private string $cipher = 'AES-128-CBC'; // Самый быстрый безопасный блочный шифр

    public function __construct()
    {
        // Ключ должен быть ровно 16 байт для AES-128
        $rawKey = config('app.message_encryption_key') ?? env('MESSAGE_ENCRYPTION_KEY');

        if (!$rawKey) {
            throw new \Exception('Message encryption key is not set.');
        }

        // Если ключ передан как base64, декодируем
        if (str_starts_with($rawKey, 'base64:')) {
            $rawKey = base64_decode(substr($rawKey, 7));
        }

        // Обрезаем или дополняем до 16 байт, чтобы openssl не ругался
        $this->key = substr($rawKey, 0, 16);
        if (strlen($this->key) < 16) {
            $this->key = str_pad($this->key, 16, '0');
        }
    }

    /**
     * Шифрует строку. Возвращает base64 строку (IV + ciphertext).
     */
    public function encrypt(string $value): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength); // Случайный вектор инициализации

        $encrypted = openssl_encrypt(
            $value,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA, // Важно: RAW_DATA быстрее и компактнее
            $iv
        );

        // Объединяем IV и зашифрованные данные, кодируем в base64 для хранения в TEXT/BLOB
        return base64_encode($iv . $encrypted);
    }

    /**
     * Дешифрует строку.
     */
    public function decrypt(string $payload): ?string
    {
        $data = base64_decode($payload);
        if ($data === false) {
            return null;
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);

        // Извлекаем IV (первые байты) и само сообщение
        $iv = substr($data, 0, $ivLength);
        $encryptedText = substr($data, $ivLength);

        $decrypted = openssl_decrypt(
            $encryptedText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted;
    }
}
