<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail\Parser;

use Swift_Message;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message\Part\MessagePart;

class MimeParser extends AbstractParser
{
    /** @var string  */
    protected $rawMessage;

    public function __construct(string $rawMessage)
    {
        $this->rawMessage = $rawMessage;
    }

    public function getSwiftMessage() : Swift_Message
    {
        $mailParser = new MailMimeParser();

        $message = $mailParser->parse($this->rawMessage);

        $swiftMessage = new Swift_Message();

        $headers = [];
        foreach ($message->getRawHeaders() as $header) {
            list($name, $value) = $header;
            $headers[$name] = $value;
        }

        $this->fillFromHeaders($swiftMessage, $headers);

        if ($part = $message->getTextPart()) {
            $swiftMessage->setBody($part->getContent(), $part->getContentType(), $part->getCharset());
        }

        if ($part = $message->getHtmlPart()) {
            $swiftMessage->setBody($part->getContent(), $part->getContentType('text/html'), $part->getCharset());
        }

        /** @var MessagePart $part */
        foreach ($message->getAllAttachmentParts() as $part) {
            $swiftMessage->addPart($part->getContent(), $part->getContentType(), $part->getCharset());
        }

        return $swiftMessage;
    }
}
