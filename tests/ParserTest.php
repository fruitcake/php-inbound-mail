<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testParser()
    {
        $parser = new TestParser(
            'Test',
            'This is a test',
            'barry@fruitcake.nl',
            'info@fruitcake.nl',
            [
                'Message-ID' => '3@foo',
                'In-Reply-To' => '<2@foo.nl>',
                'References' => '<1@foo> <2@foo>',
            ]
        );

        $message = $parser->getSwiftMessage();

        $this->assertInstanceOf(\Swift_Message::class, $message);
    }
}
