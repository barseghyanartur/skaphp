<?php

declare(strict_types=1);

namespace barseghyanartur\ska\Tests;

use barseghyanartur\ska\Placeholder;
use PHPUnit\Framework\TestCase;

final class PlaceholderTest extends TestCase
{
    private Placeholder $placeholder;

    protected function setUp(): void
    {
        $this->placeholder = new Placeholder('Artur Barseghyan says: ');
    }

    public function testItEchoesAValue(): void
    {
        self::assertSame('Artur Barseghyan says: Hello', $this->placeholder->echo('Hello'));
    }
}
