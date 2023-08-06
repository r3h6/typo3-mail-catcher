<?php

declare(strict_types=1);

namespace R3H6\MailCatcher\EventListener;

use R3H6\MailCatcher\Domain\Model\Message;
use R3H6\MailCatcher\Domain\Repository\MessageRepository;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Event\BeforeMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\Mailer;

final class MailCatcher
{
    public function __construct(
        private readonly MessageRepository $messageRepository
    ) {
    }

    public function beforeSend(BeforeMailerSentMessageEvent $event): void
    {
        assert($event->getMailer() instanceof Mailer);
        if (!($event->getMailer()->getTransport() instanceof NullTransport)) {
            throw new \RuntimeException('Mail catching requires null transport', 1689538382101);
        }
    }

    public function afterSend(AfterMailerSentMessageEvent $event): void
    {
        assert($event->getMailer() instanceof Mailer);
        $sentMessage = $event->getMailer()->getSentMessage();
        assert($sentMessage->getOriginalMessage() instanceof Email);
        $newMessage = new Message();
        $newMessage->setMessageId($sentMessage->getMessageId());
        $newMessage->setSubject($sentMessage->getOriginalMessage()->getSubject());
        $newMessage->setFrom(quoted_printable_decode($sentMessage->getOriginalMessage()->getHeaders()->get('from')->getBodyAsString()));
        $newMessage->setTo(quoted_printable_decode($sentMessage->getOriginalMessage()->getHeaders()->get('to')->getBodyAsString()));
        $newMessage->setCrdate(new \DateTime());
        $newMessage->setSerialized(serialize($sentMessage->getOriginalMessage()));
        $newMessage->setSource(str_replace("=\r\n", '', $sentMessage->getMessage()->toString()));
        $this->messageRepository->add($newMessage);
    }
}
