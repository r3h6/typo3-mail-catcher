<?php

declare(strict_types=1);

namespace R3H6\MailCatcher\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Message extends AbstractEntity
{
    protected string $messageId = '';
    protected string $subject = '';
    protected string $to = '';
    protected string $from = '';
    protected string $serialized = '';
    protected string $source = '';
    protected \DateTime $crdate;

    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setSerialized(string $serialized): void
    {
        $this->serialized = $serialized;
    }

    public function getSerialized(): string
    {
        return $this->serialized;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setCrdate(\DateTime $crdate): void
    {
        $this->crdate = $crdate;
    }

    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }
}
