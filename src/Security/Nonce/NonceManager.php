<?php

class Oxygen_Security_Nonce_NonceManager implements Oxygen_Security_Nonce_NonceManagerInterface
{
    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * @param DatabaseConnection $connection
     */
    public function __construct(DatabaseConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function useNonce($nonce, $expiresAt)
    {
        if ($expiresAt < time()) {
            throw new Oxygen_Exception(Oxygen_Exception::NONCE_EXPIRED);
        }

        $cleanNonce = strtr($nonce, ['-' => '']);
        $nonceUsed = (bool)$this->connection->query('SELECT 1 FROM {oxygen_nonce} WHERE nonce = :nonce', [':nonce' => $cleanNonce])->fetchField();

        if ($nonceUsed) {
            throw new Oxygen_Exception(Oxygen_Exception::NONCE_ALREADY_USED);
        }

        $this->connection->query('INSERT INTO {oxygen_nonce} (nonce, expires) VALUES (:nonce, :expires)', [':nonce' => $cleanNonce, ':expires' => $expiresAt]);
    }
}
