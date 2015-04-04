<?php

namespace Ulrichsg\Getopt;

/**
 * Legacy class. Use this if you want to keep using features of v2 that were removed from the main Getopt class in v3.
 */
class Getoptv2 extends Getopt implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /** @var Result */
    private $result = null;

    public function parse($arguments = null)
    {
        $this->result = parent::parse($arguments);
        return $this->result;
    }

    /**
     * Returns the value of the given option. Must be invoked after parse().
     *
     * The return value can be any of the following:
     * <ul>
     *   <li><b>null</b> if the option is not given and does not have a default value</li>
     *   <li><b>the default value</b> if it has been defined and the option is not given</li>
     *   <li><b>an integer</b> if the option is given without argument. The
     *       returned value is the number of occurrences of the option.</li>
     *   <li><b>a string</b> if the option is given with an argument. The returned value is that argument.</li>
     * </ul>
     *
     * @param string $name The (short or long) option name.
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->result) ? $this->result->getOption($name) : null;
    }

    /**
     * Returns the list of options. Must be invoked after parse() (otherwise it returns an empty array).
     *
     * @return array
     */
    public function getOptions()
    {
        return isset($this->result) ? $this->result->getOptions() : array();
    }

    /**
     * Returns the list of operands. Must be invoked after parse().
     *
     * @return array
     */
    public function getOperands()
    {
        return isset($this->result) ? $this->result->getOperands() : array();
    }

    /**
     * Returns the i-th operand (starting with 0), or null if it does not exist. Must be invoked after parse().
     *
     * @param int $i
     * @return string
     */
    public function getOperand($i)
    {
        return isset($this->result) ? $this->result->getOperand($i) : null;
    }


    /*
     * Interface support functions
     */

    public function count()
    {
        return isset($this->result) ? count($this->result->getOptions()) : 0;
    }

    public function offsetExists($offset)
    {
        $options = $this->getOptions();
        return isset($options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getOption($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Getopt is read-only');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Getopt is read-only');
    }

    public function getIterator()
    {
        // For options that have both short and long names, $this->options has two entries.
        // We don't want this when iterating, so we have to filter the duplicates out.
        $filteredOptions = array();
        foreach ($this->getOptions() as $name => $value) {
            $keep = true;
            foreach ($this->optionList as $option) {
                if ($option->long() == $name && !is_null($option->short())) {
                    $keep = false;
                }
            }
            if ($keep) {
                $filteredOptions[$name] = $value;
            }
        }
        return new \ArrayIterator($filteredOptions);
    }

    /**
     * Returns the banner string
     *
     * @return string
     */
    public function getBanner()
    {
        return $this->helpTextFormatter->getBanner();
    }

    /**
     * Set the banner string
     *
     * @param string $banner    The banner string; will be passed to sprintf(), can include %s for current scripts name.
     *                          Be sure to include a trailing line feed.
     * @return Getopt
     */
    public function setBanner($banner)
    {
        $this->helpTextFormatter->setBanner($banner);
        return $this;
    }
}
