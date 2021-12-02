<?php

namespace Rissc\Printformer\Block\Adminhtml\History\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Url extends AbstractRenderer
{

    /**
     * Class to try format array from string
     *
     * @param string $data
     * @return string
     */
    public function prepareJsonData(string $data)
    {
        $result = '';

        if (!empty($data)){
            $result = json_encode(json_decode($data, true), JSON_PRETTY_PRINT);
            if (empty($result) || $result === 'null'){
                $result = $data;
            }
        }

        return $result;
    }

    /**
     * @param array $parsed_url
     * @return string
     */
    protected function unparseUrl(array $parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        /** @var \Rissc\Printformer\Model\History\Log $row */

        $url = $row->getApiUrl();
        $parsedUrl = parse_url($url);

        if(!empty($parsedUrl['query'])) {
            unset($parsedUrl['query']);
        }

        $dataHtml = '<div class="api_data" style="display: none;">
            <div>
                <label for="api_request_data_' . $row->getId() . '"><strong>' . __('Request Data') . ':</strong></label>
                <div class="control">
                    <pre style="background-color:#ECECEC; padding: 10px;" class="api_request_data" data-row-id="' . $row->getId() . '" id="api_request_data_' . $row->getId() . '">' . $this->prepareJsonData($row->getData('request_data')) . '</pre>
                </div>
            </div><br />
            <div>
                <label for="api_response_data_' . $row->getId() . '"><strong>' . __('Response Data') . ':</strong></label>
                <div class="control">
                    <pre style="background-color:#ECECEC; padding: 10px;" class="api_response_data" data-row-id="' . $row->getId() . '" id="api_response_data_' . $row->getId() . '">' . $this->prepareJsonData($row->getData('response_data')) . '</pre>
                </div>
            </div>
        </div>';

        return $this->unparseUrl($parsedUrl) . $dataHtml;
    }
}