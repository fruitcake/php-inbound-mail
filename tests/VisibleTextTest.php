<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

class VisibleTextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test visible text
     */
    public function testReply()
    {
        $message = (new \Swift_Message())->setBody(file_get_contents(__DIR__ .'/fixtures/email_3.txt'));

        $inboundMail = new InboundMail($message);

        $replyText = $inboundMail->getVisibleText();

        $this->assertEquals('Oh thanks.

Having the function would be great.', $replyText);
    }
}
