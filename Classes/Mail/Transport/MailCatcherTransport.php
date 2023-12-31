<?php

declare(strict_types=1);

namespace R3H6\MailCatcher\Mail\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\SentMessage;
use R3H6\MailCatcher\Domain\Model\Message;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use R3H6\MailCatcher\Domain\Repository\MessageRepository;
use Symfony\Component\Mailer\Transport\AbstractTransport;

final class MailCatcherTransport extends AbstractTransport
{
    /** @phpstan-ignore-next-line */
    public function __construct(
        array $mailSettings,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);
    }

    protected function doSend(SentMessage $message): void
    {
        assert($message->getOriginalMessage() instanceof Email);
        $newMessage = new Message();
        $newMessage->setMessageId($message->getMessageId());
        $newMessage->setSubject(quoted_printable_decode($message->getOriginalMessage()->getSubject()));
        $newMessage->setFrom($this->decode($message->getOriginalMessage()->getHeaders()->get('from')->getBodyAsString()));
        $newMessage->setTo($this->decode($message->getOriginalMessage()->getHeaders()->get('to')->getBodyAsString()));
        $newMessage->setCrdate(new \DateTime());
        $newMessage->setSerialized(serialize($message->getOriginalMessage()));
        $newMessage->setSource($message->getMessage()->toString());
        GeneralUtility::makeInstance(MessageRepository::class)->add($newMessage);
    }

    private function decode(string $string): string
    {
        return mb_decode_mimeheader(quoted_printable_decode($string));
    }

    public function __toString()
    {
        return 'mailcatcher://';
    }
}
