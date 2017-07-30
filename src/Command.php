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
    use WithOptions;

    /** @var string */
    protected $name;

    /** @var string */
    protected $shortDescription;

    /** @var string */
    protected $longDescription;

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
     * @param array|string  $options
     * @param string $longDescription
     */
    public function __construct(
        $name,
        $shortDescription,
        $handler,
        $options = null,
        $longDescription = ''
    ) {
        $this->setName($name);
        $this->shortDescription = $shortDescription;
        $this->handler          = $handler;
        $this->longDescription  = $longDescription ?: $shortDescription;

        if ($options !== null) {
            $this->addOptions($options);
        }
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
