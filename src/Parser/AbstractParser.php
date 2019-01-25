<?php

declare(strict_types=1);

namespace Fruitcake\InboundMail\Parser;

use Swift_Message;

abstract class AbstractParser
{
    abstract public function getSwiftMessage() : Swift_Message;

    protected function fillFromHeaders(Swift_Message $swiftMessage, array $headers) : Swift_Message
    {
        // Make them all Lower Case
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['message-id'])) {
            $swiftMessage->setId($this->parseIdHeader($headers['message-id']));
        }

        if (isset($headers['subject'])) {
            $swiftMessage->setSubject($headers['subject']);
        }

        if (isset($headers['date'])) {
            $swiftMessage->setDate(new \DateTime($headers['date']));
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

        $swiftHeaders = $swiftMessage->getHeaders();

        if (isset($headers['delivered-to'])) {
            $swiftHeaders->addMailboxHeader('Delivered-To', $this->parseMailboxHeader($headers['delivered-to']));
        }

        if (isset($headers['references'])) {
            $swiftHeaders->addIdHeader('References', $this->parseIdHeader($headers['references']));
        }

        if (isset($headers['in-reply-to'])) {
            $swiftHeaders->addIdHeader('In-Reply-To', $this->parseIdHeader($headers['in-reply-to']));
        }

        return $swiftMessage;
    }


    /**
     * Parse a mailbox to an array with username/email
     *
     * Examples:
     * 'Barry <barry@fruitcake.nl>' -> ['barry@fruitcake.nl' => 'Barry']
     * '<barry@fruitcake.nl' -> ['barry@fruitcake.nl' => null]
     * 'barry@fruitcake.nl -> ['barry@fruitcake.nl' => null]
     */
    protected function parseMailboxHeader(string $header) : array
    {
        $value = trim($header);
        $address = null;
        $name = null;

        if (strpos($value, '<') === false) {
            $address = $header;
        } else {
            $matches = array();
            preg_match('/(.*)(<.*>)/', $value, $matches);
            $name = trim($matches[1], "\" \t\r\n\0\x0B");
            $address = trim($matches[2], "<> \t\r\n\0\x0B");
        }

        return [$address => $name];
    }

    /**
     * Parse one or multiple IDs to an array of IDS, without brackets
     *
     * Examples:
     * 'foo@bar' -> ['foo@bar']
     * '<foo@bar>' -> ['foo@bar']
     * '<foo@bar> <bar@foo>' -> ['foo@bar', 'bar@foo']
     */
    protected function parseIdHeader(string $header) : array
    {
        // Split on whitespace
        $parts = preg_split('/\s+/', $header);

        $ids = [];
        foreach ($parts as $id) {
            // If it start with <, remove the start/closing brackets
            if (strpos($id, '<') !== false) {
                $id = substr($id, 1, -1);
            }

            $ids[] = $id;
        }

        return $ids;
    }
}
