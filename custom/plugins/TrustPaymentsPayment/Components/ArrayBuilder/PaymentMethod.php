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

namespace TrustPaymentsPayment\Components\ArrayBuilder;

use TrustPayments\Sdk\Model\PaymentMethod as PaymentMethodModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentMethod extends AbstractArrayBuilder
{
    /**
     *
     * @var PaymentMethodModel
     */
    private $paymentMethod;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param PaymentMethodModel $paymentMethod
     */
    public function __construct(ContainerInterface $container, PaymentMethodModel $paymentMethod)
    {
        parent::__construct($container);
        $this->paymentMethod = $paymentMethod;
    }

    public function build()
    {
        return [
            'id' => $this->paymentMethod->getId(),
            'name' => $this->translate($this->paymentMethod->getName()),
            'description' => $this->translate($this->paymentMethod->getDescription())
        ];
    }
}
