<?php

/**
 * Trust Payments Shopware 5
 *
 * This Shopware 5 extension enables to process payments with Trust Payments (https://www.trustpayments.com//).
 *
 * @package TrustPayments_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

namespace TrustPaymentsPayment\Subscriber;

use Enlight\Event\SubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Theme implements SubscriberInterface
{
    
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascriptFiles'
        ];
    }
    
    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onCollectJavascriptFiles()
    {
        $frontendViewDirectory = $this->container->getParameter('trust_payments_payment.plugin_dir') . '/Resources/views/frontend/';
        
        return new ArrayCollection([
            $frontendViewDirectory . 'checkout/trust_payments_payment/_resources/checkout.js'
        ]);
    }
}
