<?php

namespace Typoheads\Formhandler\Mailer;

use TYPO3\CMS\Core\Mail\MailMessage;
use Typoheads\Formhandler\Component\Manager;
use Typoheads\Formhandler\Controller\Configuration;
use Typoheads\Formhandler\Utility\GeneralUtility;
use Typoheads\Formhandler\Utility\Globals;

class SymfonyMailer extends AbstractMailer implements MailerInterface
{

    /**
     * The TYPO3 mail message object
     *
     * @var MailMessage
     */
    protected $mailer;

    /**
     * Initializes the email object and calls the parent constructor
     *
     * @param Manager $componentManager
     * @param Configuration $configuration
     * @param Globals $globals
     * @param GeneralUtility $utilityFuncs
     */
    public function __construct(
        Manager $componentManager,
        Configuration $configuration,
        Globals $globals,
        GeneralUtility $utilityFuncs
    ) {
        parent::__construct($componentManager, $configuration, $globals, $utilityFuncs);
        $this->mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailMessage::class);
    }

    /**
     * @param array $recipients
     */
    public function send($recipients): bool
    {
        if (!empty($recipients)) {
            $this->mailer->setTo($recipients);

            return $this->mailer->send();
        }

        return false;
    }

    /**
     * @param string $html
     */
    public function setHTML($html): void
    {
        if (!empty($html)) {
            $this->mailer->html($html);
        }
    }

    /**
     * @param string $plain
     */
    public function setPlain($plain): void
    {
        if (!empty($plain)) {
            $this->mailer->text($plain);
        }
    }

    /**
     * @param string $value
     */
    public function setSubject($value): void
    {
        $this->mailer->subject($value);
    }

    /**
     * Sets the name and email of the "From" header.
     *
     * The function name setSender is misleading since there is
     * also a "Sender" header which is not set by this method
     *
     * @param string $email
     * @param string $name
     */
    public function setSender($email, $name): void
    {
        if (!empty($email)) {
            $this->mailer->setFrom($email, $name);
        }
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function setReplyTo($email, $name): void
    {
        if (!empty($email)) {
            $this->mailer->setReplyTo($email, $name);
        }
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addCc($email, $name): void
    {
        $this->mailer->addCc($email, $name);
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addBcc($email, $name): void
    {
        $this->mailer->addBcc($email, $name);
    }

    /**
     * @param string $value
     */
    public function setReturnPath($value): void
    {
        $this->mailer->setReturnPath($value);
    }

    /**
     * @param string $value
     */
    public function addHeader($value): void
    {
        //@TODO: Find a good way to make headers configurable
    }

    /**
     * @param string $value
     */
    public function addAttachment($value): void
    {
        $this->mailer->attachFromPath($value);
    }

    public function getHTML(): string
    {
        if ($this->mailer->getHtmlBody()) {
            return $this->mailer->getHtmlBody();
        }
        return '';
    }

    public function getPlain(): string
    {
        if ($this->mailer->getTextBody()) {
            return $this->mailer->getTextBody();
        }
        return '';
    }

    public function getSubject(): string
    {
        $subject = $this->mailer->getSubject();

        return $subject ?? '';
    }

    public function getSender(): string
    {
        $address = '';
        $from = $this->mailer->getFrom();

        if (count($from) > 0) {
            $address = $from[0]->toString();
        }

        return $address;
    }

    public function getReplyTo(): string
    {
        $address = '';
        $replyTo = $this->mailer->getReplyTo();

        if (count($replyTo) > 0) {
            $address = $replyTo[0]->toString();
        }

        return $address;
    }

    public function getCc(): array
    {
        $rawCc = $this->mailer->getCc();
        $cc = [];

        if (is_array($rawCc) && count($rawCc) > 0) {
            foreach ($rawCc as $address) {
                $cc[] = $address->toString();
            }
        }
        return $cc;
    }

    public function getBcc(): array
    {
        $rawBcc = $this->mailer->getCc();
        $bcc = [];

        if (is_array($rawBcc) && count($rawBcc) > 0) {
            foreach ($rawBcc as $address) {
                $bcc[] = $address->toString();
            }
        }
        return $bcc;
    }

    public function getReturnPath(): string
    {
        $returnPath = $this->mailer->getReturnPath();

        return $returnPath !== null ? $returnPath->toString() : '';
    }

    /**
     * @param string $image
     */
    public function embed($image): void
    {
        $this->mailer->embedFromPath($image);
    }
}
