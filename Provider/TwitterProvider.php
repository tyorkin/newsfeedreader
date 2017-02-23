<?php

namespace NewsFeedReader\Provider;

class TwitterProvider extends BaseNewsFeedReaderProvider
{
    const PROVIDER_NAME = 'tw';
    const API_VERSION = '1.1';
    const NEWS_FEED_URL = 'https://api.twitter.com/'.self::API_VERSION.'/statuses/user_timeline.json';

    /** @var array  */
    private $params = [];


    /**
     * @param array $params
     *
     * @return mixed
     */
    public function requestNewsFeed(array $params = [])
    {
        $this->params = $params;
        $oAuthHeader = $this->buildOAuthHeader();
        $header = array("Authorization: Oauth {$oAuthHeader}", 'Expect:');
        $news = $this->client->request(self::NEWS_FEED_URL, 'get', $header, $params);
        return $news;
    }

    /**
     * @return string
     */
    private function buildOAuthSignature()
    {
        $oauthHash = '';
        if (!empty($this->params['count'])) {
            $oauthHash .= 'count=' . $this->params['count'] . '&';
        }
        $oauthHash .= 'oauth_consumer_key=' . $this->credentials['consumer_key'] . '&';
        $oauthHash .= 'oauth_nonce=' . time() . '&';
        $oauthHash .= 'oauth_signature_method=HMAC-SHA1&';
        $oauthHash .= 'oauth_timestamp=' . time() . '&';
        $oauthHash .= 'oauth_token=' . $this->credentials['access_token'] . '&';
        $oauthHash .= 'oauth_version=1.0';
        if (!empty($this->params['screen_name'])) {
            $oauthHash .=  '&screen_name=' . $this->params['screen_name'];
        }
        $base = '';
        $base .= 'GET';
        $base .= '&';
        $base .= rawurlencode(self::NEWS_FEED_URL);
        $base .= '&';
        $base .= rawurlencode($oauthHash);
        $key = '';
        $key .= rawurlencode($this->credentials['consumer_secret']);
        $key .= '&';
        $key .= rawurlencode($this->credentials['access_token_secret']);

        $signature = base64_encode(hash_hmac('sha1', $base, $key, true));
        $rawSignature = rawurlencode($signature);

        return $rawSignature;
    }

    /**
     * @return string
     */
    private function buildOAuthHeader()
    {
        $signature = $this->buildOAuthSignature();
        $oauthHeader = '';
        if (!empty($this->params['count'])) {
            $oauthHeader .= 'count="' . $this->params['count'] . '", ';
        }
        $oauthHeader .= 'oauth_consumer_key="' . $this->credentials['consumer_key'] . '", ';
        $oauthHeader .= 'oauth_nonce="' . time() . '", ';
        $oauthHeader .= 'oauth_signature="' . $signature . '", ';
        $oauthHeader .= 'oauth_signature_method="HMAC-SHA1", ';
        $oauthHeader .= 'oauth_timestamp="' . time() . '", ';
        $oauthHeader .= 'oauth_token="' . $this->credentials['access_token'] . '", ';
        $oauthHeader .= 'oauth_version="1.0", ';
        if (!empty($this->params['screen_name'])) {
            $oauthHeader .= 'screen_name="' . $this->params['screen_name'] . '", ';
        }

        return $oauthHeader;
    }
}