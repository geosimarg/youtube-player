<?php

namespace GGdS\YtPlayer\Youtube\Responses {
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    class PlayerApiResponse extends HttpResponse {
        protected function query($key) {
            return Utils::arrayGet($this->getJson(), $key);
        }

        public function getAllFormats() {
            $formats = $this->query('streamingData.formats');

            // video only or audio only streams
            $adaptiveFormats = $this->query('streamingData.adaptiveFormats');

            return array_merge((array)$formats, (array)$adaptiveFormats);
        }
    }
}