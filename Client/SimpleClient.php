<?php

namespace NewsFeedReader\Client;

use NewsFeedReader\Exception\BadRequestException;

class SimpleClient implements ClientInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * @param string $url
     * @param string $method
     * @param string $headers
     * @param array $params
     *
     * @return mixed
     */
    public function request($url, $method, $headers = '', $params = [])
    {
        if (!function_exists('curl_version')) {
            throw new BadRequestException(
                'CURL not installed/enabled'
            );
        }
        $curlRequest = curl_init();
        if ($headers) {
            curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curlRequest, CURLOPT_HEADER, false);
        $requestUrl = $url;
        if (self::METHOD_GET == strtoupper($method)) {
            $requestUrl = $this->normalizeUrl($url, $params);
        } elseif (self::METHOD_POST == strtoupper($method)) {
            curl_setopt($curlRequest, CURLOPT_POST, 1);
            if (count($params)) {
                $postFields = http_build_query($params, '', '&');
                curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $postFields);
            }
        }
        curl_setopt($curlRequest, CURLOPT_URL, $requestUrl);
        curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlRequest, CURLOPT_SSL_VERIFYPEER, false);
        $json = curl_exec($curlRequest);
        $httpStatus = curl_getinfo($curlRequest, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curlRequest);
        $curlErrorNum = curl_errno($curlRequest);
        curl_close($curlRequest);
        if ($json === false) {
            throw new BadRequestException($curlError, $curlErrorNum);
        }
        $data = json_decode($json);
        if ($httpStatus !== 200) {
            $message = 'Error during receive response';
            $code = 0;
            if (json_last_error() == JSON_ERROR_NONE && isset($data->errors) && is_array($data->errors)) {
                $message = $data->errors[0]->message;
                $code = $data->errors[0]->code;
            }
            throw new BadRequestException(
                $message,
                $code
            );
        }


        return $data;
    }

    /**
     * @param string $url
     * @param array $parameters
     *
     * @return string
     */
    protected function normalizeUrl($url, array $parameters = array())
    {
        $normalizedUrl = $url;
        if (!empty($parameters)) {
            $normalizedUrl .= (false !== strpos($url, '?') ? '&' : '?') . http_build_query($parameters, '', '&');
        }

        return $normalizedUrl;
    }
}