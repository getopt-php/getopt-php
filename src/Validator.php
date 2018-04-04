<?php

namespace GetOpt;

/**
 * Class Validator
 *
 * @package GetOpt
 * @author Olivier Cecillon <arcesilas@neutre.email>
 */
class Validator
{
    /** @var callable */
    protected $callable;

    /** @var string */
    protected $message;

    /** @var bool */
    protected $isClosure = false;

    /**
     * Creates a new Validator object
     *
     * @param callable $callable
     * @param string   $message
     */
    public function __construct(callable $callable, $message = '')
    {
        $this->callable = $callable;
        $this->message = $message;
        $this->isClosure = $callable instanceof \Closure;
    }

    /**
     * Check whether the value passed in argument satisfies the validation
     *
     * @param  mixed $arg
     * @return bool
     */
    public function validates($arg)
    {
        $arguments = $this->isClosure ? [$arg, $this] : [$arg];

        return (bool) call_user_func_array($this->callable, $arguments);
    }

    /**
     * Defines a custom message to be displayed if validation fails
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get the custom message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
