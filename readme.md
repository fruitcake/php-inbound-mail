# PHP Inbound Mail 

## Parsers and utilities to process inbound mail

## Providers
 - [x] IMAP / RAW emails
 - [x] Gmail (Google API)
 - [ ] Postmark Inbound json
 - [ ] Sendgrid Inbound Parse json
 
> Postmark/Sendgrid/Gmail also support RAW MIME parts that can be used
 
 
## Install

```sh
composer require fruitcake/inbound-mail:^0.1@dev
```

## Usage

```php

$inboundMail = \Fruitcake\InboundMail\InboundMail::parse($message);

$html =  $inboundEmail->getHtml();
$text = $inboundEmail->getText();
$visibleText =  $inboundEmail->getVisibleText();

$reply = $inboundEmail->createReply();
```