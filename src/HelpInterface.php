<?php

namespace GetOpt;

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
