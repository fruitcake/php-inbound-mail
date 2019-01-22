<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

use Swift_Message;

class InboundMail
{
    public function getVisibleText(Swift_Message $message) : string
    {
        $body = null;
        if ($message->getBody() && $message->getBodyContentType() === 'text/plain') {
            $body = $message->getBody();
        } else {
            foreach ($message->getChildren() as $child) {
                if ($child->getBodyContentType() === 'text/plain') {
                    $body = $child->getBody();
                }
            }
        }

        if (is_null($body)) {
            throw new \RuntimeException('No text body is found');
        }

        return \EmailReplyParser\EmailReplyParser::parseReply($body);
    }


    public function createSwiftReply(Swift_Message $originalMessage, $includeCc = true) : Swift_Message
    {
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
