<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail\Parser;

use DateTime;
use Google_Service_Gmail_Message;
use Swift_Message;

class GmailParser extends AbstractParser
{
    /** @var Google_Service_Gmail_Message  */
    protected $gmailMessage;

    public function __construct(Google_Service_Gmail_Message $gmailMessage)
    {
        $this->gmailMessage = $gmailMessage;
    }

    public function getSwiftMessage() : Swift_Message
    {
        $payload = $this->gmailMessage->getPayload();

        $swiftMessage = new Swift_Message();
        $swiftHeaders = $swiftMessage->getHeaders();

        $headers = [];
        /** @var \Google_Service_Gmail_MessagePartHeader $header */
        foreach ($payload->getHeaders() as $header) {
            $headers[strtolower($header->getName())] = $header->getValue();
        }

        if (isset($headers['message-id'])) {
            $swiftMessage->setId($this->parseIdHeader($headers['message-id']));
        }

        if (isset($headers['subject'])) {
            $swiftMessage->setSubject($headers['subject']);
        }

        if (isset($headers['date'])) {
            $swiftMessage->setDate(new DateTime($headers['date']));
        }

        if (isset($headers['from'])) {
            $swiftMessage->setFrom($this->parseMailboxHeader($headers['from']));
        }

        if (isset($headers['to'])) {
            $swiftMessage->setTo($this->parseMailboxHeader($headers['to']));
        }

        if (isset($headers['cc'])) {
            $swiftMessage->setTo($this->parseMailboxHeader($headers['cc']));
        }

        if (isset($headers['reply-to'])) {
            $swiftMessage->setReplyTo($this->parseMailboxHeader($headers['reply-to']));
        }

        if (isset($headers['delivered-to'])) {
            $swiftHeaders->addMailboxHeader('Delivered-To', $this->parseMailboxHeader($headers['delivered-to']));
        }

        if (isset($headers['references'])) {
            $swiftHeaders->addIdHeader('References', $this->parseIdHeader($headers['references']));
        }

        if (isset($headers['in-reply-to'])) {
            $swiftHeaders->addIdHeader('In-Reply-To', $this->parseIdHeader($headers['in-reply-to']));
        }

        if ($this->gmailMessage->getThreadId()) {
            $swiftHeaders->addTextHeader('X-Gmail-Thread-Id', $this->gmailMessage->getThreadId());
        }

        /** @var \Google_Service_Gmail_MessagePart $part */
        foreach ($payload->getParts() as $part) {
            $body = base64_decode($part->getBody()->getData());
            if ($part->getMimeType() === 'text/plain') {
                $swiftMessage->setBody($body, 'text/plain');
            } else {
                $swiftMessage->addPart($body, $part->getMimeType());
            }
        }

        return $swiftMessage;
    }
}
