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
     * @param string $name
     *
     * @return stdClass|null
     */
    public function findUserByName($name)
    {
        $user = user_load_by_name($name);
        return $user ? $user : null;
    }
}
