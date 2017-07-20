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

    /** @var Operand[] */
    protected $operands = [];

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
     * @return self
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
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add options to this command.
     *
     * @param Option[] $options
     * @return self
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * @param Option $option
     * @return self
     */
    public function addOption(Option $option)
    {
        $this->options[] = $option;
        return $this;
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add operands to this command.
     *
     * @param array $operands
     * @return self
     */
    public function addOperands(array $operands)
    {
        foreach ($operands as $operand) {
            $this->addOperand($operand);
        }
        return $this;
    }

    /**
     * @param Operand $operand
     * @return self
     */
    public function addOperand(Operand $operand)
    {
        $this->operands[] = $operand;
        return $this;
    }

    /**
     * @return Operand[]
     */
    public function getOperands()
    {
        return $this->operands;
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
}
