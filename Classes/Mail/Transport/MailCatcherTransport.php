<?php

declare(strict_types=1);

namespace R3H6\MailCatcher\Mail\Transport;

use R3H6\MailCatcher\Domain\Model\Message;
use R3H6\MailCatcher\Domain\Repository\MessageRepository;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MailCatcherTransport extends AbstractTransport
{
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
        $newMessage->setSubject($message->getOriginalMessage()->getSubject());
        $newMessage->setFrom(quoted_printable_decode($message->getOriginalMessage()->getHeaders()->get('from')->getBodyAsString()));
        $newMessage->setTo(quoted_printable_decode($message->getOriginalMessage()->getHeaders()->get('to')->getBodyAsString()));
        $newMessage->setCrdate(new \DateTime());
        $newMessage->setSerialized(serialize($message->getOriginalMessage()));
        $newMessage->setSource($message->getMessage()->toString());
        GeneralUtility::makeInstance(MessageRepository::class)->add($newMessage);
    }

    public function __toString()
    {
        return 'mailcatcher://';
    }
}
