<?php

namespace Aci\Payment\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\AddressConverter;

/**
 * Email Management - Send notification mail to merchant and customer
 */
class EmailManagement
{
    public const FAILED_MAIL_SUBJECT        =   'Subscription Failed';
    public const FAILED_MAIL_CONTENT        =   'Your Recurring Order with ID "%1" is failed.'
                                                    .'Please contact customer care for more details. Reason: %2';

    /**
     * @var array<mixed>
     */
    protected static array $mailTypes       = [
        'fail' => [
            'subject' => self::FAILED_MAIL_SUBJECT,
            'content' => self::FAILED_MAIL_CONTENT
        ]
    ];

    /**
     * @var TransportInterfaceFactory
     */
    protected TransportInterfaceFactory $transportInterfaceFactory;

    /**
     * @var EmailMessageInterfaceFactory
     */
    protected EmailMessageInterfaceFactory $emailMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    protected MimePartInterfaceFactory $mimePartInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    protected MimeMessageInterfaceFactory $mimeMessageInterfaceFactory;

    /**
     * @var AddressConverter
     */
    protected AddressConverter $addressConverter;

    /**
     * @param TransportInterfaceFactory $transportInterfaceFactory
     * @param EmailMessageInterfaceFactory $emailMessageInterfaceFactory
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param AddressConverter $addressConverter
     */
    public function __construct(
        TransportInterfaceFactory $transportInterfaceFactory,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        AddressConverter $addressConverter
    ) {
        $this->transportInterfaceFactory = $transportInterfaceFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Send Recurring order - notification
     *
     * @param string $subscriptionId
     * @param string $from
     * @param string $fromName
     * @param string $to
     * @param string $toName
     * @param string $mailType
     * @param string $reason
     * @return void
     * @throws MailException
     */
    public function sendMessage(
        string $subscriptionId,
        string $from,
        string $fromName,
        string $to,
        string $toName,
        string $mailType,
        string $reason
    ): void {
        $mailData = self::$mailTypes[$mailType];
        $partType = MimeInterface::TYPE_TEXT;
        $subject = $mailData['subject'];
        $content = __($mailData['content'], $subscriptionId, $reason);

        $mimePart = $this->mimePartInterfaceFactory->create(
            [
                'content' => (string)$content,
                'type' => $partType
            ]
        );

        $messageData['encoding'] = $mimePart->getCharset();
        $messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $messageData['subject'] = $subject;
        $messageData['from'][] = $this->addressConverter->convert($from, $fromName);
        $messageData['to'][] = $this->addressConverter->convert($to, $toName);

        $message = $this->emailMessageInterfaceFactory->create($messageData);

        $transport = $this->transportInterfaceFactory->create(
            ['message' => $message]
        );
        $transport->sendMessage();
    }
}
