<?php

namespace GGdS\YtPlayer\Youtube {
    use GGdS\YtPlayer\Curl\BrowserClient;
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    class Browser extends BrowserClient {
        public function setUserAgent($agent) {
            $this->headers['User-Agent'] = $agent;
        }

        public function getUserAgent() {
            return Utils::arrayGet($this->headers, 'User-Agent');
        }

        public function followRedirects($enabled) {
            $this->options[CURLOPT_FOLLOWLOCATION] = $enabled ? 1 : 0;
            return $this;
        }

        public function cachedGet($url) {
            $cache_path = sprintf('%s/%s', static::getStorageDirectory(), $this->getCacheKey($url));

            if (file_exists($cache_path)) {
                $str = file_get_contents($cache_path);
                /*$str_clear = str_replace(['O:13:"Curl\\Response":4:', ';O:13:"Curl\CurlInfo":1:', 's:6:', 's:3:', ''], '', file_get_contents($cache_path));
                // unserialize could fail on empty file
                $str_coma = str_replace([';s:4:', ';s:5:', ';s:12:', ';s:9:', ';s:11:', ';s:12:', ';s:16:', ';s:13:', ';s:14:'], ',', $str_clear);
                $str_tDot = str_replace([';s:0:', ';s:73:', ';s:15:', ';a:37:'], [':'], $str_coma);
                $str = str_replace([';i:200'], [200], $str_tDot);
                $json = json_encode($str);

                var_dump(json_decode($json));
                exit();*/
                return unserialize($str);
            }

            $response = $this->get($url);

            // cache only if successful
            if (empty($response->error)) {
                file_put_contents($cache_path, serialize($response));
            }

            return $response;
        }

        protected function getCacheKey($url) {
            return md5($url) . '_v3';
        }

        public function consentCookies() {
            $response = $this->get('https://www.youtube.com/');
            $current_url = $response->info->url;

            // must be missing that special cookie
            if (strpos($current_url, 'consent.youtube.com') !== false) {

                $field_names = ['gl', 'm', 'pc', 'continue', 'ca', 'x', 'v', 't', 'hl', 'src', 'uxe'];

                $form_data = [];

                foreach ($field_names as $name) {
                    $value = Utils::getInputValueByName($response->body, $name);
                    $form_data[$name] = htmlspecialchars_decode($value);
                }

                // this will set that cookie that we need to never see that message again
                $this->post('https://consent.youtube.com/s', $form_data);
            }
        }
    }
}