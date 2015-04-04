<?php

namespace Ulrichsg\Getopt;

interface HelpTextFormatter
{
    public function getHelpText(array $options, $padding);

    public function getBanner();
    public function setBanner($banner);
    public function setScriptName($scriptName);
}
