<?php

class Oxygen_Drupal_Context
{
    /**
     * @var array
     */
    private $context;

    /**
     * @var array|null
     */
    private $constants;

    /**
     * @param array $globals   The context to work with. Defaults to $GLOBALS, using the same global variables as Drupal.
     * @param array $constants The list of constants to use. Defaults to global constants.
     */
    public function __construct(array &$globals = null, array $constants = null)
    {
        if ($globals !== null) {
            $this->context = $globals;
        } else {
            $this->context = &$GLOBALS;
        }

        if ($constants !== null) {
            $this->constants = $constants;
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function setGlobal($name, $value)
    {
        $this->context[$name] = $value;
    }

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getGlobal($name, $default = null)
    {
        return array_key_exists($name, $this->context) ? $this->context[$name] : $default;
    }

    /**
     * @param string $constant
     *
     * @return bool
     */
    public function hasConstant($constant)
    {
        if (is_array($this->constants)) {
            return array_key_exists($constant, $this->constants);
        }

        return defined($constant);
    }

    /**
     * @param string                     $constant
     * @param int|string|float|bool|null $default
     *
     * @return int|string|float|bool|null
     */
    public function getConstant($constant, $default = null)
    {
        if (!$this->hasConstant($constant)) {
            return $default;
        }

        if (is_array($this->constants)) {
            return $this->constants[$constant];
        }

        return constant($constant);
    }

    /**
     * @param string                     $name
     * @param int|string|float|bool|null $value
     * @param bool                       $throw
     *
     * @return void
     *
     * @throws RuntimeException If the constant already exists and $throw === true.
     */
    public function setConstant($name, $value, $throw = true)
    {
        if ($this->hasConstant($name)) {
            if ($throw) {
                throw new RuntimeException(sprintf('The constant "%s" is already defined.', $name));
            }

            return;
        }

        if (is_array($this->constants)) {
            $this->constants[$name] = $value;

            return;
        }

        define($name, $value);
    }
}
