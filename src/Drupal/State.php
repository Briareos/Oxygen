<?php

class Oxygen_Drupal_State implements Oxygen_Drupal_StateInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return variable_get($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        variable_set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        variable_del($name);
    }
}
