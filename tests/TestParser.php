<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail;

use Fruitcake\InboundMail\Parser\AbstractParser;
use Swift_Message;

class TestParser extends AbstractParser
{
    protected $subject;
    protected $body;
    protected $from;
    protected $to;
    protected $headers;

    protected $idHeaders = ['In-Reply-To', 'References'];

    public function __construct(
        string $subject,
        string $body = null,
        string $from = null,
        string $to = null,
        array $headers = []
    ) {
        $this->subject = $subject;
        $this->body = $body;
        $this->from = $from;
        $this->to = $to;
        $this->headers = $headers;
    }


    public function getSwiftMessage() : Swift_Message
    {
        $message = new Swift_Message($this->subject, $this->body);

        if ($this->from) {
            $message->setFrom($this->parseMailboxHeader($this->from));
        }

        if ($this->to) {
            $message->setTo($this->parseMailboxHeader($this->to));
        }

        foreach ($this->headers as $name => $value) {
            if ($name === 'Message-ID') {
                $message->setId($value);
            } elseif (in_array($name, $this->idHeaders)) {
                $message->getHeaders()->addIdHeader($name, $this->parseIdHeader($value));
            } else {
                $message->getHeaders()->addTextHeader($name, $value);
            }
        }

        return $message;
    }
}
