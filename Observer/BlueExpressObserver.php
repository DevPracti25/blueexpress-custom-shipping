<?php

namespace BlueExpress\ShippingBX\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class BlueExpressObserver implements ObserverInterface{

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        Session $checkoutSession){

            $this->quoteRepository = $quoteRepository;
            $this->orderRepository = $orderRepository;
            $this->addressRepository = $addressRepository;
            $this->checkoutSession = $checkoutSession;
    }

    public function execute(EventObserver $observer){

        /** @var \Magento\Sales\Model\Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return $this;
        }

        $headers = array(
            "Authentication" => '8e91831ddf8d2b28254de19cc3f0121c',
        );

        $event = $observer->getEvent();
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        //$shippment = json_encode($order->getAllItems());
        $itemsQuantity = 0;
        foreach ($order->getAllItems() as $_item){

            if ($_item->getProductType() == 'configurable')
            continue;

            $itemsQuantity += 1;
        }


        $data = [
            'id' => $order->getId(),
            'store_id' => $order->getStoreId(),
            'currency' => 'CLP',
            'customer_id' => $order->getCustomerId(),
            'email' => $order->getCustomerEmail(),
            'shipping_method' => $order->getShippingMethod(),
            'shippimg_amount' => $order->getBaseShippingAmount(),
            'shipping_description' => $order->getShippingDescription(),
            'city' => $order->getShippingAddress()->getCity(),
            'shippimg_adress' => 'Street Test 123',   //$order->getShippingAddress()->getAddress(),
            'shippimg_telephone' => $order->getShippingAddress()->getTelephone(),
            'customerName' => $order->getCustomerName(),
            'items_weightUnit' => 'KG',
            'items_weight' => $order->getWeight(),
            'items_quantity' => $itemsQuantity,
            'token' => '8e91831ddf8d2b28254de19cc3f0121c'
        ];

        //API Set Order
        $price = curl_init('http://portal-bx.knownonline.com/api/magento/set-order');
        curl_setopt($price, CURLOPT_POSTFIELDS, $data);
        curl_setopt($price, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($price, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($price);
        //$response = json_decode($response);


        return $this;
    }

}
