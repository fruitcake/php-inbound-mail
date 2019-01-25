<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

use Fruitcake\InboundMail\Parser\MimeParser;

class MimeParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test parsing a MIME message (created from a Swift Message cast to string)
     */
    public function testParser()
    {
        $original = (new \Swift_Message)
            ->setSubject('Test')
            ->setBody('This is a test')
            ->setFrom('barry@fruitcake.nl', 'Barry')
            ->setTo('info@fruitcake.nl')
            ->setId('3@foo')
        ;

        $original->getHeaders()->addIdHeader('In-Reply-To', ['2@foo']);
        $original->getHeaders()->addIdHeader('References', ['1@foo', '2@foo']);

        $parser = new MimeParser($original->toString());
        $message = $parser->getSwiftMessage();

        $this->assertInstanceOf(\Swift_Message::class, $message);

        $this->assertEquals('Test', $message->getSubject());
        $this->assertEquals('This is a test', $message->getBody());
        $this->assertEquals(['barry@fruitcake.nl' => 'Barry'], $message->getFrom());
        $this->assertEquals(['info@fruitcake.nl' => null], $message->getTo());
        $this->assertEquals('3@foo', $message->getId());
        $this->assertEquals(['2@foo'], $message->getHeaders()->get('In-Reply-To')->getFieldBodyModel());
        $this->assertEquals(['1@foo', '2@foo'], $message->getHeaders()->get('References')->getFieldBodyModel());

        $this->assertEquals(strtolower($original->toString()), strtolower($message->toString()));
    }
}
