<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test our example Parser, to check the Abstract Parser methods
     */
    public function testParser()
    {
        $parser = new TestParser(
            'Test',
            'This is a test',
            'Barry <barry@fruitcake.nl>',
            'info@fruitcake.nl',
            [
                'Message-ID' => '3@foo',
                'In-Reply-To' => '<2@foo>',
                'References' => '<1@foo> <2@foo>',
            ]
        );

        $message = $parser->getSwiftMessage();

        $this->assertInstanceOf(\Swift_Message::class, $message);

        $this->assertEquals('Test', $message->getSubject());
        $this->assertEquals('This is a test', $message->getBody());
        $this->assertEquals(['barry@fruitcake.nl' => 'Barry'], $message->getFrom());
        $this->assertEquals(['info@fruitcake.nl' => null], $message->getTo());
        $this->assertEquals('3@foo', $message->getId());
        $this->assertEquals(['2@foo'], $message->getHeaders()->get('In-Reply-To')->getFieldBodyModel());
        $this->assertEquals(['1@foo', '2@foo'], $message->getHeaders()->get('References')->getFieldBodyModel());
    }
}
