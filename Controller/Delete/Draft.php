<?php
namespace Rissc\Printformer\Controller\Delete;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ObjectManager;
use Rissc\Printformer\Helper\Session;
use Magento\Framework\Message\Manager as MessageManager;

class Draft
    extends Action
{
    /** @var ProductFactory */
    protected $_productFactory;

    /** @var ProductResource */
    protected $_productResource;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        ProductResource $productResource
    ) {
        $this->_productFactory = $productFactory;
        $this->_productResource = $productResource;

        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        /** @var Product $product */
        $product = $this->_productFactory->create();
        $this->_productResource->load($product, intval($params['product_id']));

        if ($product && $product->getId()) {
            $connection = $this->_productResource->getConnection();

            $objm = ObjectManager::getInstance();
            /** @var Session $sessionHelper */
            $sessionHelper = $objm->get(Session::class);

            $uniqueId = explode(':', $sessionHelper->getCustomerSession()->getSessionUniqueID() ?? '')[0];

            $sqlQuery = "
                SELECT * FROM `" . $connection->getTableName('printformer_draft') . "`
                WHERE
                    `intent` = '" . $params['intent'] . "' AND
                    `product_id` = " . $product->getId() . " AND
                    `printformer_product_id` = " . $params['printformer_product'] . " AND
                    `session_unique_id` = '" . $uniqueId . ':' . $product->getId() . "'
                ORDER BY `created_at` DESC
            ";

            $rawDraft = $connection->fetchRow($sqlQuery);

            if (!empty($rawDraft['id']) && isset($rawDraft['store_id']) && is_numeric($rawDraft['store_id'])) {
                $connection->query("
                    DELETE FROM " . $connection->getTableName('printformer_draft') . "
                    WHERE `id` = " . $rawDraft['id'] . ";
                ");

                $sessionHelper->unsetCurrentIntent();

                $printformerSession = $sessionHelper->getCatalogSession()->getData(Session::SESSION_KEY_PRINTFORMER_DRAFTID);
                if (isset($printformerSession[$rawDraft['store_id']][$product->getId()])) {
                    unset($printformerSession[$rawDraft['store_id']][$product->getId()]);
                    $sessionHelper->getCatalogSession()->setData(Session::SESSION_KEY_PRINTFORMER_DRAFTID,
                        $printformerSession);
                }

                $this->messageManager->addSuccessMessage(__('Draft has been successfully deleted.'));
            }
        }

        header('Location: ' . $product->getProductUrl());
        exit();
    }
}