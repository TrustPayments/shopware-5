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

use TrustPaymentsPayment\Models\OrderTransactionMapping;
use TrustPaymentsPayment\Models\TransactionInfo;
use Shopware\Components\CSRFWhitelistAware;
use TrustPaymentsPayment\Components\Controller\Backend;

class Shopware_Controllers_Backend_TrustPaymentsPaymentRefund extends Backend implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return [
            'downloadRefund'
        ];
    }

    public function markAsFailedAction()
    {
        $spaceId = $this->Request()->getParam('spaceId', null);
        if (empty($spaceId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_space_id_passed', 'No valid space id passed.')
            ));
            return;
        }

        $refundId = $this->Request()->getParam('refundId', null);
        if (empty($refundId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_refund_id_passed', 'No valid refund id passed.')
            ));
            return;
        }

        try {
            $refundService = new \TrustPayments\Sdk\Service\RefundService($this->get('trustpayments_payment.api_client')->getInstance());
            $refundService->fail($spaceId, $refundId);
            $this->View()->assign(array(
                'success' => true
            ));
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/refund_failure', 'There has been an error while sending the refund to the gateway.')
            ));
        }
    }

    public function markAsSuccessfulAction()
    {
        $spaceId = $this->Request()->getParam('spaceId', null);
        if (empty($spaceId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_space_id_passed', 'No valid space id passed.')
            ));
            return;
        }

        $refundId = $this->Request()->getParam('refundId', null);
        if (empty($refundId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_refund_id_passed', 'No valid refund id passed.')
            ));
            return;
        }

        try {
            $refundService = new \TrustPayments\Sdk\Service\RefundService($this->get('trustpayments_payment.api_client')->getInstance());
            $refundService->succeed($spaceId, $refundId);
            $this->View()->assign(array(
                'success' => true
            ));
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/refund_failure', 'There has been an error while sending the refund to the gateway.')
            ));
        }
    }

    public function createRefundAction()
    {
        /* @var \TrustPaymentsPayment\Components\Refund $refundService */
        $refundService = $this->get('trustpayments_payment.refund');

        $spaceId = $this->Request()->getParam('spaceId', null);
        if (empty($spaceId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_space_id_passed', 'No valid space id passed.')
            ));
            return;
        }

        $transactionId = $this->Request()->getParam('transactionId', null);
        if (empty($transactionId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/no_transaction_id_passed', 'No valid transaction id passed.')
            ));
            return;
        }

        try {
            /* @var \TrustPayments\Sdk\Model\Transaction $transaction */
            $transaction = $this->get('trustpayments_payment.transaction')->getTransaction($spaceId, $transactionId);
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/cannot_load_transaction', 'The transaction cannot be loaded.')
            ));
            return;
        }

        try {
            /* @var \TrustPayments\Sdk\Model\TransactionInvoice $invoice */
            $invoice = $this->get('trustpayments_payment.invoice')->getInvoice($spaceId, $transactionId);
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/cannot_load_transaction_invoice', 'The transaction invoice cannot be loaded.')
            ));
            return;
        }

        try {
            /* @var \TrustPayments\Sdk\Model\Refund[] $refunds */
            $refunds = $refundService->getRefunds($spaceId, $transactionId);
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/cannot_load_refunds', 'The refunds cannot be loaded.')
            ));
            return;
        }

        /* @var OrderTransactionMapping $orderTransactionMapping */
        $orderTransactionMapping = $this->getModelManager()
            ->getRepository(OrderTransactionMapping::class)
            ->findOneBy([
            'spaceId' => $spaceId,
            'transactionId' => $transactionId
        ]);
        if (! ($orderTransactionMapping instanceof OrderTransactionMapping) || $orderTransactionMapping->getOrder()->getNumber() != $transaction->getMerchantReference()) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/cannot_find_transaction_order', 'No order linked to the transaction could be found.')
            ));
            return;
        }

        $reductions = [];
        foreach ($this->Request()->getParam('reductions') as $uniqueId => $reductionData) {
            $reduction = new \TrustPayments\Sdk\Model\LineItemReductionCreate();
            $reduction->setLineItemUniqueId($uniqueId);
            $reduction->setQuantityReduction($reductionData['quantity']);
            $reduction->setUnitPriceReduction($reductionData['unitPrice']);
            $reductions[] = $reduction;
        }

        try {
            $refundRequest = $refundService->createRefund($orderTransactionMapping->getOrder(), $transaction, $reductions);
            $refund = $refundService->refund($spaceId, $refundRequest);
        } catch (\Exception $e) {
            $this->View()->assign(array(
                'success' => true,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/refund_failure', 'There has been an error while sending the refund to the gateway.'),
            ));
            return;
        }

        if ($refund->getState() == \TrustPayments\Sdk\Model\RefundState::FAILED) {
            $this->View()->assign(array(
                'success' => true,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->translate($refund->getFailureReason()
                    ->getDescription()),
                'refundId' => $refund->getId()
            ));
            return;
        } elseif ($refund->getState() == \TrustPayments\Sdk\Model\RefundState::PENDING) {
            $this->View()->assign(array(
                'success' => true,
                'data' => $this->Request()
                    ->getParams(),
                'message' => $this->get('snippets')->getNamespace('backend/trustpayments_payment/main')->get('error/refund_pending', 'The refund was requested successfully, but is still pending on the gateway.'),
                'refundId' => $refund->getId()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'refundId' => $refund->getId()
        ));
    }

    public function downloadRefundAction()
    {
        $id = $this->Request()->getParam('id');
        /* @var TransactionInfo $transactionInfo */
        $transactionInfo = $this->getModelManager()
            ->getRepository(TransactionInfo::class)
            ->find($id);

        $refundId = $this->Request()->getParam('refundId');

        $service = new \TrustPayments\Sdk\Service\RefundService($this->get('trustpayments_payment.api_client')->getInstance());
        $document = $service->getRefundDocument($transactionInfo->getSpaceId(), $refundId);
        $this->download($document);
    }
}
