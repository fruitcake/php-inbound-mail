<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail\Parser;

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

        $headers = [];
        /** @var \Google_Service_Gmail_MessagePartHeader $header */
        foreach ($payload->getHeaders() as $header) {
            $headers[$header->getName()] = $header->getValue();
        }

        $swiftMessage = $this->fillFromHeaders($swiftMessage, $headers);

        if ($this->gmailMessage->getThreadId()) {
            $swiftMessage->getHeaders()
                ->addTextHeader('X-Gmail-Thread-Id', $this->gmailMessage->getThreadId());
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
