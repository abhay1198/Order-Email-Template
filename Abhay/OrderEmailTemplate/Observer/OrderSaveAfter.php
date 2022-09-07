<?php

/**
 * @package     Abhay/OrderEmailTemplate 
 * @version     1.0.0
 * @author      Abhay
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
 
namespace Abhay\OrderEmailTemplate\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Abhay\OrderEmailTemplate\Helper\Email;

class OrderSaveAfter implements ObserverInterface
{
    protected $logger;
 
    public function __construct(
        LoggerInterface $logger,
        Email $email
    ){
        $this->logger = $logger;
        $this->email = $email;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if($order->getStatus() == 'holded')
            { 
                $this->sendEmail(
                    $order->getCustomerFirstname(),
                    $order->getCustomerEmail(),
                    $order->getStoreId(),
                    $order->getIncrementId(),
                );
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send Notification Email
     *
     * @param [string] $customerName
     * @param [string] $customerEmail
     * @param [int] $storeId
     * @param [int] $orderId
     * @return void
     */
    public function sendEmail($customerName, $customerEmail, $storeId, $orderId)
    {
        $senderInfo = [
            'name' =>$this->email->getConfig('trans_email/ident_general/name'),
            'email' => $this->email->getConfig('trans_email/ident_general/email'),
        ];
        $receiverInfo = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];

        $emailTemplateVariables = [];
        $emailTemplateVariables['myvar1'] = $orderId;
        $emailTemplateVariables['myvar2'] = $customerName;
        $emailTemplateVariables['myvar3'] = $senderInfo['name'];

        $this->email->sendOrderStatusChangeMail(
            $emailTemplateVariables,
            $senderInfo,
            $receiverInfo,
            $storeId
        );
    }
}