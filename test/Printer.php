<?php

namespace GetOpt\Test;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use PHPUnit\TextUI\DefaultResultPrinter;

class Printer extends DefaultResultPrinter
{
    /**
     * Replacement symbols for test statuses.
     *
     * @var array
     */
    protected static $symbols = [
        'E' => "\e[31m!\e[0m", // red !
        'F' => "\e[31m\xe2\x9c\x96\e[0m", // red X
        'W' => "\e[33mW\e[0m", // yellow W
        'I' => "\e[33mI\e[0m", // yellow I
        'R' => "\e[33mR\e[0m", // yellow R
        'S' => "\e[36mS\e[0m", // cyan S
        '.' => "\e[32m\xe2\x9c\x94\e[0m", // green checkmark
    ];
    /**
     * Structure of the outputted test row.
     *
     * @var string
     */
    protected $testRow = '';

    /** @var string */
    protected $previousClassName = '';

    /**
     * {@inheritdoc}
     */
    protected function writeProgress(string $progress): void
    {
        if ($this->hasReplacementSymbol($progress)) {
            $progress = static::$symbols[$progress];
        }
        $this->write("  {$progress} {$this->testRow}" . PHP_EOL);
        $this->column++;
        $this->numTestsRun++;
    }
    /**
     * {@inheritdoc}
     */
    public function addError(Test $test, \Throwable $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-red');
        parent::addError($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-red');
        parent::addFailure($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-yellow');
        parent::addWarning($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $test, \Throwable $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-yellow');
        parent::addIncompleteTest($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $test, \Throwable $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-yellow');
        parent::addRiskyTest($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function addSkippedTest(Test $test, \Throwable $e, float $time): void
    {
        $this->buildTestRow(get_class($test), $test->getName(), $time, 'fg-cyan');
        parent::addSkippedTest($test, $e, $time);
    }
    /**
     * {@inheritdoc}
     */
    public function endTest(Test $test, float $time): void
    {
        list($className, $methodName) = \PHPUnit\Util\Test::describe($test);
        $this->buildTestRow($className, $methodName, $time);
        parent::endTest($test, $time);
    }
    /**
     * {@inheritdoc}
     *
     * We'll handle the coloring ourselves.
     */
    protected function writeProgressWithColor(string $color, string $buffer): void
    {
        $this->writeProgress($buffer);
    }
    /**
     * Formats the results for a single test.
     *
     * @param $className
     * @param $methodName
     * @param $time
     * @param $color
     */
    protected function buildTestRow($className, $methodName, $time, $color = 'fg-white'): void
    {
        if ($className != $this->previousClassName) {
            $this->write(PHP_EOL . $this->colorizeTextBox('fg-magenta', $className) . PHP_EOL);
            $this->previousClassName = $className;
        }

        $this->testRow = sprintf(
            "(%s) %s",
            $this->formatTestDuration($time),
            $this->colorizeTextBox($color, "{$this->formatMethodName($methodName)}")
        );
    }
    /**
     * Makes the method name more readable.
     *
     * @param $method
     * @return mixed
     */
    protected function formatMethodName($method)
    {
        return ucfirst(
            $this->splitCamels(
                $this->splitSnakes($method)
            )
        );
    }
    /**
     * Replaces underscores in snake case with spaces.
     *
     * @param $name
     * @return string
     */
    protected function splitSnakes($name)
    {
        return str_replace('_', ' ', $name);
    }
    /**
     * Splits camel-cased names while handling caps sections properly.
     *
     * @param $name
     * @return string
     */
    protected function splitCamels($name)
    {
        return preg_replace('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', ' $1', $name);
    }
    /**
     * Colours the duration if the test took longer than 500ms.
     *
     * @param $time
     * @return string
     */
    protected function formatTestDuration($time)
    {
        $testDurationInMs = round($time * 1000);
        $duration = $testDurationInMs > 500
            ? $this->colorizeTextBox('fg-yellow', $testDurationInMs)
            : $testDurationInMs;
        return sprintf('%s ms', $duration);
    }
    /**
     * Verifies if we have a replacement symbol available.
     *
     * @param $progress
     * @return bool
     */
    protected function hasReplacementSymbol($progress)
    {
        return in_array($progress, array_keys(static::$symbols));
    }
    /**
     * Checks if the class name is in format Class::method.
     *
     * @param $testName
     * @return bool
     */
    protected function hasCompoundClassName($testName)
    {
        return !empty($testName) && strpos($testName, '::') > -1;
    }
}
