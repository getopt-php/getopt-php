<?php

namespace GetOpt;

interface HelpInterface
{
    /**
     * Render the help text for $getopt
     *
     * @param Getopt $getopt
     * @return string
     */
    public function render(Getopt $getopt);
}
