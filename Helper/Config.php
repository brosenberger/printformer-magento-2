<?php
namespace Rissc\Printformer\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    const XML_PATH_V2_API_KEY                       = 'printformer/version2group/v2apiKey';
    const XML_PATH_V2_IDENTIFIER                    = 'printformer/version2group/v2identifier';

    const XML_PATH_CONFIG_ENABLED                   = 'printformer/general/enabled';
    const XML_PATH_CONFIG_HOST                      = 'printformer/general/remote_host';
    const XML_PATH_CONFIG_LICENSE                   = 'printformer/general/license_key';
    const XML_PATH_CONFIG_SECRET                    = 'printformer/general/secret_word';
    const XML_PATH_CONFIG_LOCALE                    = 'printformer/general/locale';
    const XML_PATH_CONFIG_STATUS                    = 'printformer/general/order_status';
    const XML_PATH_CONFIG_DRAFT_UPDATE              = 'printformer/general/draft_update';
    const XML_PATH_CONFIG_DRAFT_UPDATE_ORDER_ID     = 'printformer/general/draft_update_order_id';
    const XML_PATH_CONFIG_DISPLAY_MODE              = 'printformer/general/display_mode';
    const XML_PATH_CONFIG_FRAME_FULLSCREEN          = 'printformer/general/frame_fullscreen';

    const XML_PATH_CONFIG_REDIRECT_ON_CANCEL        = 'printformer/general/redirect_on_cancel';
    const XML_PATH_CONFIG_REDIRECT                  = 'printformer/general/redirect_after_config';
    const XML_PATH_CONFIG_REDIRECT_URL              = 'printformer/general/redirect_alt_url';
    const XML_PATH_CONFIG_SKIP_CONFIG               = 'printformer/general/allow_skip_config';
    const XML_PATH_CONFIG_WISHLIST_HINT             = 'printformer/general/guest_wishlist_hint';
    const XML_PATH_CONFIG_EXPIRE_DATE               = 'printformer/general/expire_date';
    const XML_PATH_CONFIG_EDIT_TEXT                 = 'printformer/general/cart_edit_text';
    const XML_PATH_CONFIG_IMAGE_PREVIEW             = 'printformer/general/product_image_preview';
    const XML_PATH_CONFIG_IMAGE_PREVIEW_WIDTH       = 'printformer/general/product_image_preview_width';
    const XML_PATH_CONFIG_IMAGE_PREVIEW_HEIGHT      = 'printformer/general/product_image_preview_height';

    const XML_PATH_CONFIG_IMAGE_THUMB_WIDTH         = 'printformer/general/product_image_thumbnail_width';
    const XML_PATH_CONFIG_IMAGE_THUMB_HEIGHT        = 'printformer/general/product_image_thumbnail_height';
    const XML_PATH_CONFIG_BUTTON_TEXT               = 'printformer/general/config_button_text';
    const XML_PATH_CONFIG_BUTTON_CSS                = 'printformer/general/config_button_css';
    const XML_PATH_CONFIG_SHOW_DELETE_BUTTON        = 'printformer/general/delete_draft_button';
    const XML_PATH_CONFIG_DELETE_CONFIRM_TEXT       = 'printformer/general/delete_confirm_text';

    const XML_PATH_CONFIG_FORMAT_CHANGE_NOTICE      = 'printformer/format/change_notice';
    const XML_PATH_CONFIG_FORMAT_NOTICE_TEXT        = 'printformer/format/notice_text';
    const XML_PATH_CONFIG_CLOSE_NOTICE_TEXT         = 'printformer/general/close_text';
    const XML_PATH_CONFIG_FORMAT_QUERY_PARAMETER    = 'printformer/format/query_parameter';
    const XML_PATH_CONFIG_FORMAT_ATTRIBUTE_ENABLED  = 'printformer/format/attribute_enabled';
    const XML_PATH_CONFIG_FORMAT_ATTRIBUTE_NAME     = 'printformer/format/attribute_name';
    const XML_PATH_CONFIG_FORMAT_ATTRIBUTE_VALUES   = 'printformer/format/attribute_values';
    const XML_PATH_CONFIG_FORMAT_OPTION_ENABLED     = 'printformer/format/option_enabled';
    const XML_PATH_CONFIG_FORMAT_OPTION_NAME        = 'printformer/format/option_name';
    const XML_PATH_CONFIG_FORMAT_OPTION_VALUES      = 'printformer/format/option_values';

    const XML_PATH_CONFIG_COLOR_QUERY_PARAMETER     = 'printformer/color/query_parameter';
    const XML_PATH_CONFIG_COLOR_ATTRIBUTE_ENABLED   = 'printformer/color/attribute_enabled';
    const XML_PATH_CONFIG_COLOR_ATTRIBUTE_NAME      = 'printformer/color/attribute_name';
    const XML_PATH_CONFIG_COLOR_ATTRIBUTE_VALUES    = 'printformer/color/attribute_values';
    const XML_PATH_CONFIG_COLOR_OPTION_ENABLED      = 'printformer/color/option_enabled';
    const XML_PATH_CONFIG_COLOR_OPTION_NAME         = 'printformer/color/option_name';
    const XML_PATH_CONFIG_COLOR_OPTION_VALUES       = 'printformer/color/option_values';

    const REGISTRY_KEY_WISHLIST_NEW_ITEM_ID         = 'printformer_new_wishlist_item_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Config constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param EncryptorInterface $encryptor
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->_customerSession = $customerSession;
        $this->encryptor = $encryptor;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->storeId) {
            $this->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        }
        return $this->storeId;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return int
     */
    public function getDisplayMode()
    {
        return intval($this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ));
    }

    /**
     * @return bool
     */
    public function isEditorFullscreenEnabled()
    {
        return $this->getDisplayMode() == 1;
    }

    /**
     * @return bool
     */
    public function isFrameEnabled()
    {
        return $this->getDisplayMode() == 2;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isFullscreenButtonEnabled()
    {
        return $this->isFrameEnabled() && $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_FRAME_FULLSCREEN,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_HOST,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_LICENSE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_SECRET,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_LOCALE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getOrderStatus()
    {
        return explode(',', $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_STATUS,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ));
    }

    /**
     * @return int
     */
    public function getOrderDraftUpdate()
    {
        return intval($this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_DRAFT_UPDATE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ));
    }

    /**
     * @return mixed
     */
    public function getOrderDraftUpdateOrderId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_DRAFT_UPDATE_ORDER_ID,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function getRedirectProductOnCancel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_REDIRECT_ON_CANCEL,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ) == '1';
    }

    /**
     * @return string
     */
    public function getConfigRedirect()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_REDIRECT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getRedirectAlt()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_REDIRECT_URL,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function isAllowSkipConfig()
    {
        return intval($this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_SKIP_CONFIG,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ));
    }

    /**
     * @return string
     */
    public function getGuestWishlistHint()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_WISHLIST_HINT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function isUseImagePreview()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_IMAGE_PREVIEW,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return int
     */
    public function getImagePreviewWidth()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_IMAGE_PREVIEW_WIDTH,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return int
     */
    public function getImagePreviewHeight()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_IMAGE_PREVIEW_HEIGHT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return int
     */
    public function getImageThumbnailWidth()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_IMAGE_THUMB_WIDTH,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return int
     */
    public function getImageThumbnailHeight()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_IMAGE_THUMB_HEIGHT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getEditText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_EDIT_TEXT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getExpireDate()
    {
        $days = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_EXPIRE_DATE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );

        return (new \DateTime())->add(\DateInterval::createFromDateString('+'.$days.' days'))->getTimestamp();
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_BUTTON_TEXT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getButtonCss()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_BUTTON_CSS,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function isFormatChangeNotice()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_FORMAT_CHANGE_NOTICE,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getCloseNoticeText()
    {
        $text = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_CLOSE_NOTICE_TEXT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );

        if($text == "") {
            $text = 'Are you sure?';
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getFormatNoticeText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_NOTICE_TEXT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getFormatQueryParameter()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_QUERY_PARAMETER,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function isFormatAttributeEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_FORMAT_ATTRIBUTE_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getFormatAttributeName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_ATTRIBUTE_NAME,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getFormatAttributeValues()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_ATTRIBUTE_VALUES,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
        return unserialize($value);
    }

    /**
     * @return string
     */
    public function isFormatOptionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_FORMAT_OPTION_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getFormatOptionName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_OPTION_NAME,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getFormatOptionValues()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FORMAT_OPTION_VALUES,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
        return unserialize($value);
    }

    /**
     * @return string
     */
    public function getColorQueryParameter()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_COLOR_QUERY_PARAMETER,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function isColorAttributeEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_COLOR_ATTRIBUTE_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getColorAttributeName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_COLOR_ATTRIBUTE_NAME,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getColorAttributeValues()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_COLOR_ATTRIBUTE_VALUES,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
        return unserialize($value);
    }

    /**
     * @return string
     */
    public function isColorOptionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_COLOR_OPTION_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getColorOptionName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_COLOR_OPTION_NAME,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return array
     */
    public function getColorOptionValues()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_COLOR_OPTION_VALUES,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
        return unserialize($value);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getClientApiKey($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }

        $encryptedKey = $this->scopeConfig->getValue(
            self::XML_PATH_V2_API_KEY,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );

        if (!empty($encryptedKey)){
            $decryptedKey = $this->encryptor->decrypt($encryptedKey);
        }

        return $decryptedKey;
    }

    /**
     * @return string
     */
    public function getClientIdentifier($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }


        return $this->scopeConfig->getValue(
            self::XML_PATH_V2_IDENTIFIER,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isDeleteButtonEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CONFIG_SHOW_DELETE_BUTTON,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getDeleteConfirmText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_DELETE_CONFIRM_TEXT,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
    }
}
