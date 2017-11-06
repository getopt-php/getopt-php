<?php

namespace GetOpt\Test;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class MagicGettersTest extends TestCase
{
    /** @dataProvider provideGetOptAttributes
     * @test */
    public function getOptUsesMagicGetters($getOpt, $attribute, $expected)
    {
        $result = $getOpt->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideGetOptAttributes()
    {
        $getOpt = new GetOpt();
        $getOpt->addOption(Option::create('a', 'alpha'));
        $getOpt->addCommand($command = Command::create('test', 'var_dump'));
        $getOpt->setHelp($help = new Help());
        $getOpt->process('test --alpha omega');

        return [
            [ $getOpt, 'options', ['a' => 1, 'alpha' => 1] ],
            [ $getOpt, 'operands', ['omega'] ],
            [ $getOpt, 'command', $command ],
            [ $getOpt, 'commands', [ 'test' => $command ] ],
            [ $getOpt, 'help', $help ],
            [
                $getOpt,
                'helpText',
                'Usage: ' . $getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' test [options] [operands]' . PHP_EOL .
                '' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
                'Options:' . PHP_EOL .
                '  -a, --alpha  ' . PHP_EOL
            ],
            [ $getOpt, 'anything', null ],
        ];
    }

    /** @dataProvider provideCommandAttributes
     * @test */
    public function commandUsesMagicGetters($command, $attribute, $expected)
    {
        $result = $command->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideCommandAttributes()
    {
        $command = new Command('test', 'Foo@Bar');
        $command->setDescription('This is the long description');
        $command->setShortDescription('This is the short description');
        $command->addOption($option = Option::create('a', 'alpha'));

        return [
            [ $command, 'name', 'test' ],
            [ $command, 'handler', 'Foo@Bar' ],
            [ $command, 'description', 'This is the long description' ],
            [ $command, 'shortDescription', 'This is the short description' ],
            [ $command, 'options', [ $option ] ],
        ];
    }
}
