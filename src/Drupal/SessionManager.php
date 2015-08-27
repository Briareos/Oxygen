<?php

class Oxygen_Drupal_SessionManager
{
    /**
     * @var Oxygen_Drupal_Context
     */
    private $context;

    /**
     * @param Oxygen_Drupal_Context $context
     */
    public function __construct(Oxygen_Drupal_Context $context)
    {
        $this->context = $context;
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
