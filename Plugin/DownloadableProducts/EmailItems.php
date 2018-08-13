<?php

namespace Rissc\Printformer\Plugin\DownloadableProducts;

use Magento\Downloadable\Block\Sales\Order\Email\Items\Downloadable;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Rissc\Printformer\Helper\DownloadableProduct as DownloadableProductHelper;

class EmailItems
{
    /**
     * @var DownloadableProductHelper
     */
    protected $downloadableProductHelper;

    /**
     * EmailItems constructor.
     * @param DownloadableProductHelper $downloadableProductHelper
     */
    public function __construct(
        DownloadableProductHelper $downloadableProductHelper
    ) {
        $this->downloadableProductHelper = $downloadableProductHelper;
    }

    /**
     * Try to get Printformer download url
     * @param Downloadable $subject
     * @param \Closure $proceed
     * @param Item $item
     * @return string
     */
    public function aroundGetPurchasedLinkUrl(Downloadable $subject, \Closure $proceed, Item $item)
    {
        $url = $proceed($item);
        $downloadableProductUrl = null;
        if($this->downloadableProductHelper->tryGetPrintformerPdfUrl($item, $downloadableProductUrl)) {
            $url = $downloadableProductUrl;
        }
        return $url;
    }
}