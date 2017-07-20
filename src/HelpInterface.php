<?php

namespace GetOpt;

/**
 * Interface HelpInterface
 *
 * @package GetOpt
 * @author  Thomas Flori <thflori@gmail.com>
 */
interface HelpInterface
{
    /**
     * Render the help text for $getopt
     *
     * @param Getopt $getopt
     * @param array  $data
     * @return string
     */
    public function render(Getopt $getopt, array $data = []);
}
