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
    public function useNonce($nonce)
    {
        list($nonceValue, $expiresAt) = explode('_', $nonce);

        $expiresAt = (int)$expiresAt;

        if ($expiresAt < time()) {
            throw new Oxygen_Exception(Oxygen_Exception::NONCE_EXPIRED);
        }

        $nonceUsed = (bool)$this->connection->query('SELECT 1 FROM {oxygen_nonce} WHERE nonce = :nonce', array(':nonce' => $nonceValue))->fetchField();

        if ($nonceUsed) {
            throw new Oxygen_Exception(Oxygen_Exception::NONCE_ALREADY_USED);
        }

        $this->connection->query('INSERT INTO {oxygen_nonce} SET nonce = :nonce, expires = :expires', array(':nonce' => $nonceValue, ':expires' => $expiresAt));
    }
}
