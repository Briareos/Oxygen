<?php

class Oxygen_Drupal_UserManager
{
    /**
     * @param int $id
     *
     * @return stdClass|null
     */
    public function findUserById($id)
    {
        $user = user_load($id);
        return $user ? $user : null;
    }

    /**
     * @param string $username
     *
     * @return stdClass|null
     */
    public function findUserByUsername($username)
    {
        $user = user_load_by_name($username);
        return $user ? $user : null;
    }
}
