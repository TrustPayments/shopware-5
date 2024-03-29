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

namespace TrustPaymentsPayment\Components\Provider;

abstract class AbstractProvider
{
    /**
     *
     * @var \TrustPayments\Sdk\ApiClient
     */
    protected $apiClient;
    
    /**
     *
     * @var \Zend_Cache_Core
     */
    private $cache;

    private $cacheKey;
    
    private $data = null;

    public function __construct(\TrustPayments\Sdk\ApiClient $apiClient, \Zend_Cache_Core $cache, $cacheKey)
    {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Fetch the data from the remote server.
     *
     * @return array
     */
    abstract protected function fetchData();

    /**
     * Returns the id of the given entry.
     *
     * @param mixed $entry
     * @return string
     */
    abstract protected function getId($entry);

    /**
     * Returns a single entry by id.
     *
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        if ($this->data == null) {
            $this->loadData();
        }

        if (isset($this->data[$id])) {
            return $this->data[$id];
        } else {
            return false;
        }
    }

    /**
     * Returns all entries.
     *
     * @return array
     */
    public function getAll()
    {
        if ($this->data == null) {
            $this->loadData();
        }

        return $this->data;
    }

    private function loadData()
    {
        $cachedData = $this->cache->load($this->cacheKey);
        if ($cachedData) {
            $this->data = $cachedData;
        } else {
            $fetchedData = $this->callApi(function () {
                return $this->fetchData();
            });
            $this->data = array();
            foreach ($fetchedData as $entry) {
                $this->data[$this->getId($entry)] = $entry;
            }

            $this->cache->save($this->data, $this->cacheKey);
        }
    }
    
    private function callApi($callback)
    {
        $lastException = null;
        $this->apiClient->setConnectionTimeout(5);
        for ($i = 0; $i < 5; $i++) {
            try {
                return $callback();
            } catch (\TrustPayments\Sdk\VersioningException $e) {
                $lastException = $e;
            } catch (\TrustPayments\Sdk\Http\ConnectionException $e) {
                $lastException = $e;
            } finally {
                $this->apiClient->setConnectionTimeout(20);
            }
        }
        throw $lastException;
    }
}
