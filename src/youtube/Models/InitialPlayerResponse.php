<?php

namespace GGdS\YtPlayer\Youtube\Models {
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    /**
     * Class InitialPlayerResponse
     * JSON data that appears inside /watch?v= page [ytInitialPlayerResponse=]
     * @package YouTube\Models
     */
    class InitialPlayerResponse {
        private $ytInitialPlayerResponse;

        public function __construct($ytInitialPlayerResponse) {
            $this->ytInitialPlayerResponse = $ytInitialPlayerResponse;
        }

        public function all() {
            return $this->ytInitialPlayerResponse;
        }

        protected function query($key) {
            return Utils::arrayGet($this->ytInitialPlayerResponse, $key);
        }

        public function isPlayabilityStatusOkay() {
            return $this->query('playabilityStatus.status') == 'OK';
        }

        public function getVideoDetails() {
            return $this->query('videoDetails');
        }
    }
}