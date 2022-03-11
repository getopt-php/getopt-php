<?php

namespace GetOpt\Test\Translator;

use GetOpt\Translator;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /** @test */
    public function throwsWhenLanguageNotAvailable(): void
    {
        self::expectException(\InvalidArgumentException::class);

        new Translator('unknown');
    }

    /** @test */
    public function usesTranslationFile(): void
    {
        $translator = new Translator(__DIR__ . '/incomplete-translation.php');

        $result = $translator->translate('usage-title');

        self::assertSame('Verwendung: ', $result);
    }

    /** @test */
    public function usesFallBackTranslation(): void
    {
        $translator = new Translator(__DIR__ . '/incomplete-translation.php');

        $result = $translator->translate('commands-title');

        self::assertSame("Commands:\n", $result);
    }
}
