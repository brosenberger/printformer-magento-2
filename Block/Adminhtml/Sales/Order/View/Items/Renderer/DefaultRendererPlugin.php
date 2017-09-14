<?php

namespace Rissc\Printformer\Block\Adminhtml\Sales\Order\View\Items\Renderer;

use Magento\Framework\DataObject;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Rissc\Printformer\Helper\Url;

class DefaultRendererPlugin extends DefaultRenderer
{
    /**
     * @var Url
     */
    protected $_urlHelper;

    /**
     * @param Url $urlHelper
     */
    public function __construct(
        Url $urlHelper
    ) {
        $this->_urlHelper = $urlHelper;
    }

    /**
     * @param DefaultRenderer $renderer
     * @param \Closure $proceed
     * @param DataObject $item
     * @param $column
     * @param null $field
     * @return string
     */
    public function aroundGetColumnHtml(
        DefaultRenderer $renderer,
        \Closure $proceed,
        DataObject $item,
        $column,
        $field = null
    ) {
        /** @var Item $item */
        $html = $proceed($item, $column, $field);
        if ($column == 'product' && $item->getPrintformerDraftid()) {
            $product = $item->getProduct();
            $product->getResource()->load($product, $product->getId());

            $html .= $this->_viewHelper->getEditorView($item, $product, $renderer);

            if ($item->getPrintformerOrdered()) {
                $html .= '<div style="margin-top: 5px;"><a class="action-default scalable action-save action-secondary" href="' . $this->getPdfUrl($item) . '" target="_blank">';
                $html .= __('Show print file');
                $html .= '</a></div>';
            }
        }

        return $html;
    }

    /**
     * @param DataObject $item
     * @return string
     */
    public function getPdfUrl(\Magento\Framework\DataObject $item)
    {
        return $this->_urlHelper->setStoreId($item->getPrintformerStoreid())
            ->getAdminPdfUrl($item->getPrintformerDraftid(), $item->getOrder()->getQuoteId());
    }

    /**
     * @param DataObject $item
     * @return string
     */
    public function getThumbImgUrl(\Magento\Framework\DataObject $item)
    {
        return $this->_urlHelper->setStoreId($item->getPrintformerStoreid())
            ->getThumbImgUrl($item->getPrintformerDraftid());
    }
}
