<?php

/**
 * This interface exists because there might be a time where we could use a direct database access for this.
 */
interface Oxygen_Drupal_StateInterface
{
    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);

    /**
     * @param string $name
     */
    public function delete($name);
}
