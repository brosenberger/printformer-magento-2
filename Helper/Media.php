<?php

namespace Rissc\Printformer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Rissc\Printformer\Helper\Api\Url;

/**
 * Class Media
 * @package Rissc\Printformer\Helper
 */
class Media extends AbstractHelper
{
    const IMAGE_PATH = 'printformer/{type}/%s_%d.png';
    const IMAGE_PARENT_PATH = 'printformer/{type}';

    /** @var Filesystem */
    protected $filesystem;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var Api */
    protected $_apiHelper;

    /** @var Url */
    protected $_urlHelper;

    /** @var Config */
    protected $_config;

    /**
     * Media constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Api $apiHelper
     * @param Config $config
     * @param Url $urlHelper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Api $apiHelper,
        Config $config,
        Url $urlHelper
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->_apiHelper = $apiHelper;
        $this->_config = $config;
        $this->_urlHelper = $urlHelper;

        parent::__construct($context);
    }

    /**
     * @param string $draftId
     * @param int $page
     * @param bool $isThumbnail
     *
     * @return string
     *
     * @throws FileSystemException
     */
    public function getImageFilePath($draftId, $page = 1, $isThumbnail = false)
    {
        $imagePathDefaultString = $this->getImagePath($isThumbnail);
        $imagePath = sprintf($imagePathDefaultString, $draftId, $page);

        $imageParentFolderPath = $this->getImageParentFolderPath($isThumbnail);
        $mediaDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $mediaDir->create($imageParentFolderPath);
        return $mediaDir->getAbsolutePath(sprintf($imagePath, $draftId, $page));
    }

    /**
     * @param string $draftId
     * @param int $page
     *
     * @return bool
     *
     * @throws FileSystemException
     */
    public function deleteImage($draftId, $page = 1, $isThumbnail = false)
    {
        $imagePath = $this->getImagePath($isThumbnail);

        $mediaDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $draftImagePath = sprintf($imagePath, $draftId, $page);
        if($mediaDir->isExist($draftImagePath)) {
            $mediaDir->delete($draftImagePath);
            return true;
        }

        return false;
    }

    /**
     * Delete all draft images
     *
     * @param string $draftId
     *
     * @throws FileSystemException
     */
    public function deleteAllImages($draftId, $isThumbnail = false)
    {
        $run = true;
        $page = 1;
        while($run) {
            $run = $this->deleteImage($draftId, $page, $isThumbnail);
            $page++;
        }
    }

    /**
     * @param $draftHash
     * @param int $uniqueGetParam
     * @return string
     */
    public function getThumbnail($draftHash, $uniqueGetParam = 0)
    {
        $thumbnailUrl = $this->getThumbnail($draftHash);
        if ($uniqueGetParam) {
            $thumbnailUrl = $this->_urlHelper->appendUniqueGetParam($thumbnailUrl);
        }
        return $thumbnailUrl;
    }

    /**
     * @param $draftId
     * @param int $page
     * @param bool $isThumbnail
     * @param int $uniqueGetParam
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl($draftId, $page = 1, $isThumbnail = false, $uniqueGetParam = 1)
    {
        $imagePath = $this->getImagePath($isThumbnail);
        
        $thumbnailUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . sprintf($imagePath, $draftId, $page);
        
        if ($uniqueGetParam) {
            $thumbnailUrl = $this->_urlHelper->appendUniqueGetParam($thumbnailUrl);
        }
        
        return $thumbnailUrl;
    }

    /**
     * @param bool $isThumbnail
     *
     * @return string
     */
    public function getImagePath($isThumbnail = false)
    {
        return str_replace('{type}', ($isThumbnail ? 'thumbs' : 'preview'), self::IMAGE_PATH);
    }

    /**
     * @param bool $isThumbnail
     *
     * @return string
     */
    public function getImageParentFolderPath($isThumbnail = false)
    {
        return str_replace('{type}', ($isThumbnail ? 'thumbs' : 'preview'), self::IMAGE_PARENT_PATH);
    }

    /**
     * @param string $draftId
     * @param int $page
     *
     * @throws FileSystemException
     * @throws AlreadyExistsException
     */
    public function createThumbnail(string $draftId, $page = 1)
    {
        $jpgImg = $this->_apiHelper->getThumbnail(
            $draftId,
            $this->_apiHelper->getUserIdentifier(),
            $this->_config->getImageThumbnailWidth(),
            $this->_config->getImageThumbnailHeight(),
            $page
        );

        $printformerImage = $jpgImg['content'];

        $imageFilePath = $this->getImageFilePath($draftId, $page, true);

        $image = imagecreatefromstring($printformerImage);

        $width = imagesx($image);
        $height = imagesy($image);

        $out = imagecreatetruecolor($width, $height);
        imagealphablending($out,false);
        $transparentindex = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $transparentindex);
        imagesavealpha($out, true);

        imagecopyresampled($out, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        imagepng($out, $imageFilePath, 7);

        imagedestroy($image);
    }

    /**
     * @param string $draftId
     * @param int $page
     *
     * @throws FileSystemException
     * @throws AlreadyExistsException
     */
    public function createPreview(string $draftId, $page = 1)
    {
        $jpgImg = $this->_apiHelper->getThumbnail(
            $draftId,
            $this->_apiHelper->getUserIdentifier(),
            $this->_config->getImagePreviewWidth(),
            $this->_config->getImagePreviewHeight(),
            $page
        );

        $printformerImage = $jpgImg['content'];

        $imageFilePath = $this->getImageFilePath($draftId, $page);

        $image = imagecreatefromstring($printformerImage);

        $width = imagesx($image);
        $height = imagesy($image);

        $out = imagecreatetruecolor($width, $height);
        imagealphablending($out,false);
        $transparentindex = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $transparentindex);
        imagesavealpha($out, true);

        imagecopyresampled($out, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        imagepng($out, $imageFilePath, 7);

        imagedestroy($image);
    }
}