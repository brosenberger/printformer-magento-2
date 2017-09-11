<?php
namespace Rissc\Printformer\Helper\Quote;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Rissc\Printformer\Helper\Url as UrlHelper;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class View
    extends AbstractHelper
{
    /** @var UrlHelper */
    protected $_urlHelper;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var BackendUrlInterface */
    protected $_backendUrl;

    /** @var State */
    protected $_appState;

    /** @var RequestInterface */
    protected $_request;

    /** @var UrlInterface */
    protected $_url;

    public function __construct(
        Context $context,
        UrlHelper $urlHelper,
        StoreManagerInterface $storeManager,
        BackendUrlInterface $backendUrl,
        State $appState,
        RequestInterface $request,
        UrlInterface $url
    )
    {
        $this->_urlHelper = $urlHelper;
        $this->_storeManager = $storeManager;
        $this->_backendUrl = $backendUrl;
        $this->_appState = $appState;
        $this->_request = $request;
        $this->_url = $url;

        parent::__construct($context);
    }

    /**
     * @param Item          $quoteItem
     * @param Product       $product
     * @param AbstractBlock $block
     * @param int           $userId
     *
     * @return string
     */
    public function getEditorView(Item $quoteItem, Product $product, $block, $userId = null)
    {
        $draftHtml = '<br />' . "\n";
        $draftHtml .= '<div id="printformer_draftid_' . $quoteItem->getId() . '">' . "\n";
        $draftHtml .= '    <span>' . __('Open Editor') . ':&nbsp;</span><br />' . "\n";
        $draftHtml .= '    <a href="' . $this->_getEditorUrl($quoteItem, $product, $userId) . '" class="pf-link">' . "\n";
        $draftHtml .= '     ' . $quoteItem->getPrintformerDraftid() . "\n";
        $draftHtml .= '    </a>' . "\n";
        $draftHtml .= '</div>' . "\n";

        /** @var \Rissc\Printformer\Block\Catalog\Product\View\Printformer $printFormerBlock */
        $printFormerBlock = $block->getLayout()->createBlock('Rissc\Printformer\Block\Catalog\Product\View\Printformer');

        $draftScript = '<div class="printformer-editor-main" id="printformer-editor-main_' . $quoteItem->getId() . '" title="' .
            $product->getName() . '"></div>' . "\n";
        $draftScript .= '<div id="printformer-editor-close_' . $quoteItem->getId() . '">' . "\n";
        $draftScript .= '    <p>' . __($printFormerBlock->getCloseNoticeText()) . '</p>' . "\n";
        $draftScript .= '</div>' . "\n";
        if ($printFormerBlock->isFormatChangeNotice()) {
            $draftScript .= '<div id="printformer-editor-notice_' . $quoteItem->getId() . '">' . "\n";
            $draftScript .= '    <p>' . __($printFormerBlock->getFormatNoticeText()) . '</p>' . "\n";
            $draftScript .= '</div>' . "\n";
        }
        $draftScript .= '<script type="text/x-magento-init">' . "\n";
        $draftScript .= json_encode([
            '#printformer_draftid_' . $quoteItem->getId() => [
                'Rissc_Printformer/js/custom-printformer' => [
                    'editBtnSelector'       => '#printformer_draftid_' . $quoteItem->getId() . ' .pf-link',
                    'editorMainSelector'    => '#printformer-editor-main_' . $quoteItem->getId(),
                    'editorCloseSelector'   => '#printformer-editor-close_' . $quoteItem->getId(),
                    'editorNoticeSelector'  => '#printformer-editor-notice_' . $quoteItem->getId(),
                    'productTitle' => $product->getName()
                ]
            ]
        ]);
        $draftScript .= '</script>' . "\n";

        return $draftHtml . $draftScript;
    }

    /**
     * @param Item    $quoteItem
     * @param Product $product
     * @param null    $userId
     *
     * @return string
     */
    public function _getEditorUrl(Item $quoteItem, Product $product, $userId = null)
    {
        $buyRequest = $quoteItem->getBuyRequest();

        $editorUrl = $this->_urlHelper
            ->setStoreId($quoteItem->getPrintformerStoreid())
            ->getEditorUrl(
                $product->getId(),
                $product->getPrintformerProduct(),
                $buyRequest->getPrintformerIntent(),
                $userId,
                [
                    'draft_id' => $buyRequest->getData('printformer_draftid'),
                    'session_id' => $buyRequest->getData('printformer_unique_session_id')
                ]
            );
        $request = $this->_request;
        $route = $request->getModuleName() . '/' . $request->getControllerName() . '/' . $request->getActionName();

        if($this->_appState->getAreaCode() == Area::AREA_ADMINHTML) {
            $referrerUrl = $this->_backendUrl->getUrl($route, ['quote_id' => $request->getParam('quote_id')]);
        } else {
            $referrerUrl = $this->_url->getUrl($route, ['quote_id' => $request->getParam('quote_id')]);
        }
        return $editorUrl .
            (strpos($editorUrl, '?') ? '&amp;' : '?') .
            'custom_referrer=' . urlencode($referrerUrl);
    }
}