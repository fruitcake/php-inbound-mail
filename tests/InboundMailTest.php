<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

use Fruitcake\InboundMail\InboundMail;

class InboundMailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test our example Parser, to check the Abstract Parser methods
     */
    public function testReply()
    {
        $message = (new \Swift_Message('Testmail'))
            ->setFrom('barry@fruitcake.nl')
            ->setCc('info@fruitcake.nl');

        $inboundMail = new InboundMail();

        $reply = $inboundMail->createSwiftReply($message);

        $this->assertInstanceOf(\Swift_Message::class, $reply);
        $this->assertEquals('Re: Testmail', $reply->getSubject());
        $this->assertEquals($message->getFrom(), $reply->getTo());
        $this->assertEquals($message->getCc(), $reply->getCc());
        $this->assertEquals([$message->getId()], $reply->getHeaders()->get('In-Reply-To')->getFieldBodyModel());
        $this->assertEquals([$message->getId()], $reply->getHeaders()->get('References')->getFieldBodyModel());
    }

    /**
     * Test our example Parser, to check the Abstract Parser methods
     */
    public function testReplyWithReferences()
    {
        $message = (new \Swift_Message('Testmail'));
        $message->setId('2@foo');
        $message->getHeaders()->addIdHeader('References', '1@foo');

        $inboundMail = new InboundMail();

        $reply = $inboundMail->createSwiftReply($message);

        $this->assertEquals(['1@foo', '2@foo'], $reply->getHeaders()->get('References')->getFieldBodyModel());
    }


    /**
     * Test our example Parser, to check the Abstract Parser methods
     */
    public function testReplyWithRe()
    {
        $message = (new \Swift_Message('Re: Testmail'));

        $inboundMail = new InboundMail();

        $reply = $inboundMail->createSwiftReply($message);

        $this->assertEquals('Re: Testmail', $reply->getSubject());
    }
}
