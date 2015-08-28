<?php

class Oxygen_Drupal_SessionManager
{
    /**
     * @var Oxygen_Drupal_Context
     */
    private $context;

    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * @param Oxygen_Drupal_Context $context
     * @param DatabaseConnection    $connection
     */
    public function __construct(Oxygen_Drupal_Context $context, DatabaseConnection $connection)
    {
        $this->context    = $context;
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @param string $userUid
     * @param string $sessionId
     */
    public function registerSession($userUid, $sessionId)
    {
        $this->connection->query('INSERT INTO {oxygen_session} SET sid = :sid, uid = :uid', array(
            'sid' => $sessionId,
            'uid' => $userUid,
        ));
    }

    /**
     * @param string $userUid
     *
     * @return int Number of destroyed sessions.
     */
    public function destroySessions($userUid)
    {
        // Run two separate queries so we know exactly how many sessions are deleted.
        $destroyed = $this->connection->query('DELETE s FROM {sessions} s INNER JOIN {oxygen_session} os ON os.sid = s.sid  WHERE os.uid = :uid', array(
            'uid' => $userUid,
        ), array(
            'return' => Database::RETURN_AFFECTED,
        ));

        $this->connection->query('DELETE FROM {oxygen_session} WHERE uid = :uid', array(
            'uid' => $userUid,
        ));

        return $destroyed;
    }

    /**
     * @param stdClass $user
     */
    public function userLogin(stdClass $user)
    {
        $this->context->setGlobal('user', $user);

        $login = array('name' => $user->name);
        user_login_finalize($login);
    }
}
