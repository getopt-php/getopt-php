<?php

namespace GetOpt;

class Getopt implements \Countable, \ArrayAccess, \IteratorAggregate
{
    const NO_ARGUMENT = 0;
    const REQUIRED_ARGUMENT = 1;
    const OPTIONAL_ARGUMENT = 2;
    const MULTIPLE_ARGUMENT = 3;

    const SETTING_DEFAULT_MODE = 'defaultMode';

    /** @var OptionParser */
    protected $optionParser;

    /** @var array */
    protected $settings = [
        self::SETTING_DEFAULT_MODE => self::NO_ARGUMENT
    ];

    /**@var Option[] */
    protected $options = [];

    /**
     * Creates a new Getopt object.
     *
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array.
     *
     * @param array $settings
     * @link https://www.gnu.org/s/hello/manual/libc/Getopt.html GNU Getopt manual
     */
    public function __construct(array $settings = [])
    {
        foreach ($settings as $setting => $value) {
            $this->set($settings, $value);
        }
    }

    public function set($setting, $value)
    {
        switch ($setting) {
            default:
                $this->settings[$setting] = $value;
        }
        return $this;
    }

    public function addOptions($options)
    {
        if (is_string($options)) {
            $options = $this->getOptionParser()->parseString($options);
        }

        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    public function addOption($option)
    {
        if (!$option instanceof Option) {
            if (is_string($option)) {
                $options = $this->getOptionParser()->parseString($option);
                if (count($options) === 0) {
                    throw new \InvalidArgumentException(sprintf(
                        'Could not create options from string \'%s\'',
                        $option
                    ));
                }
                // this is addOption - so we use only the first one
                $option = $options[0];
            } elseif (is_array($option)) {
                $option = $this->getOptionParser()->parseArray($option);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    '$option has to be a string, an array or an Option. %s given',
                    gettype($option)
                ));
            }
        }

        $this->options[] = $option;

        return $this;
    }

    /**
     * Create or get the OptionParser
     *
     * @return OptionParser
     */
    protected function getOptionParser()
    {
        if ($this->optionParser === null) {
            $this->optionParser = new OptionParser($this->settings[self::SETTING_DEFAULT_MODE]);
        }

        return $this->optionParser;
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Read only array access');
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Read only array access');
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        // TODO: Implement count() method.
    }
}
