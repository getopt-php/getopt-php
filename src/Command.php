<?php

namespace GetOpt;

/**
 * Class Command
 *
 * @package GetOpt
 * @author  Thomas Flori <thflori@gmail.com>
 */
class Command
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $shortDescription;
    /** @var string */
    protected $longDescription;

    /** @var Option[] */
    protected $options = [];

    /** @var mixed */
    protected $handler;

    /**
     * Command constructor.
     *
     * @param string $name
     * @param string $shortDescription
     * @param mixed  $handler
     * @param array  $options
     * @param string $longDescription
     */
    public function __construct(
        $name,
        $shortDescription,
        $handler,
        array $options = [],
        $longDescription = ''
    ) {
        $this->setName($name);
        $this->shortDescription = $shortDescription;
        $this->handler          = $handler;
        $this->options          = $options;
        $this->longDescription  = $longDescription ?: $shortDescription;
    }

    /**
     * @param string $name
     */
    protected function setName($name)
    {
        if (empty($name) || $name[0] === '-' || strpos($name, ' ') !== false) {
            throw new \InvalidArgumentException(sprintf(
                'Command name has to be an alphanumeric string not starting with dash, found \'%s\'',
                $name
            ));
        }
        $this->name = $name;
    }

    /**
     * Add options to this command.
     *
     * @param Option[] $options
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get description
     *
     * @param bool $short
     * @return string
     */
    public function getDescription($short = false)
    {
        return $short ? $this->shortDescription : $this->longDescription;
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
