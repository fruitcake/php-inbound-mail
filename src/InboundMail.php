<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

use Fruitcake\InboundMail\Parser\GmailParser;
use Fruitcake\InboundMail\Parser\MimeParser;
use Swift_Message;

class InboundMail
{
    /** @var Swift_Message */
    protected $message;

    public function __construct(Swift_Message $message)
    {
        $this->message = $message;
    }

    public static function parse($message) : self
    {
        if ($message instanceof Swift_Message) {
            return new static($message);
        }

        if ($message instanceof \Google_Service_Gmail_Message) {
            $parser = new GmailParser($message);
        } else {
            $parser = new MimeParser((string) $message);
        }

        return new static($parser->getSwiftMessage());
    }

    public function getMessage() : Swift_Message
    {
        return $this->message;
    }

    public function getText() : ?string
    {
        $message = $this->getMessage();

        if ($message->getBody() && $message->getBodyContentType() === 'text/plain') {
            return $message->getBody();
        } else {
            foreach ($message->getChildren() as $child) {
                if ($child->getBodyContentType() === 'text/plain') {
                    return $child->getBody();
                }
            }
        }
    }

    public function getHtml() : ?string
    {
        $message = $this->getMessage();

        if ($message->getBody() && $message->getBodyContentType() === 'text/html') {
            return $message->getBody();
        } else {
            foreach ($message->getChildren() as $child) {
                if ($child->getBodyContentType() === 'text/html') {
                    return $child->getBody();
                }
            }
        }
    }

    public function getVisibleText() : string
    {
        $text = $this->getText();

        if (empty($text)) {
            throw new \RuntimeException('No text body is found');
        }

        return \EmailReplyParser\EmailReplyParser::parseReply($text);
    }


    public function createReply($includeCc = true) : Swift_Message
    {
        $originalMessage = $this->getMessage();

        // Prepend the subject with 'Re: ' if not already present
        $subject = $originalMessage->getSubject();
        if (substr($subject, 0, 3) !== 'Re:') {
            $subject = 'Re: ' . $subject;
        }

        // Reply to the original recipient
        $reply = (new Swift_Message($subject))->setTo($originalMessage->getFrom());

        // If required, include the original CC addresses
        if ($includeCc) {
            $reply->setCc($originalMessage->getCc());
        }

        // Set the In-Reply-To header to the original message
        $reply->getHeaders()->addIdHeader('In-Reply-To', $originalMessage->getId());

        // When present, update the References header with the original id. If not, create it
        if ($references = $originalMessage->getHeaders()->get('References')) {
            $references = $references->getFieldBodyModel();
            $references[] = $originalMessage->getId();

            $reply->getHeaders()->addIdHeader('References', $references);
        } else {
            $reply->getHeaders()->addIdHeader('References', [$originalMessage->getId()]);
        }

        return $reply;
    }
}
