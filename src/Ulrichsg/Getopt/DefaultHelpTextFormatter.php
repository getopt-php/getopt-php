<?php

namespace Ulrichsg\Getopt;

class DefaultHelpTextFormatter implements HelpTextFormatter
{
    /** @var string */
    private $banner =  "Usage: %s [options] [operands]\n";
    private $scriptName;

    /**
     * Returns an usage information text generated from the given options.
     * @param Option[] $options
     * @param int $padding Number of characters to pad output of options to
     * @return string
     */
    public function getHelpText(array $options, $padding = 25)
    {
        $helpText = sprintf($this->getBanner(), $this->scriptName);
        $helpText .= "Options:\n";
        foreach ($options as $option) {
            $mode = '';
            switch ($option->mode()) {
                case Getopt::NO_ARGUMENT:
                    $mode = '';
                    break;
                case Getopt::REQUIRED_ARGUMENT:
                    $mode = "<".$option->getArgument()->getName().">";
                    break;
                case Getopt::OPTIONAL_ARGUMENT:
                    $mode = "[<".$option->getArgument()->getName().">]";
                    break;
            }
            $short = ($option->short()) ? '-'.$option->short() : '';
            $long = ($option->long()) ? '--'.$option->long() : '';
            if ($short && $long) {
                $options = $short.', '.$long;
            } else {
                $options = $short ? : $long;
            }
            $padded = str_pad(sprintf("  %s %s", $options, $mode), $padding);
            $helpText .= sprintf("%s %s\n", $padded, $option->getDescription());
        }
        return $helpText;
    }

    public function getBanner()
    {
        return $this->banner;
    }

    public function setBanner($banner)
    {
        $this->banner = $banner;
    }

    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
    }
}
