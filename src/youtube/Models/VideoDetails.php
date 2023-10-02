<?php

namespace GGdS\YtPlayer\Youtube\Models {
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    class VideoDetails {
        protected $videoDetails = array();

        private function __construct($videoDetails) {
            $this->videoDetails = $videoDetails;
        }

        /**
         * From `videoDetails` array that appears inside JSON on /watch or /get_video_info pages
         * @param $array
         * @return static
         */
        public static function FromPlayerResponseArray($array) {
            return new static(Utils::ArrayGet($array, 'videoDetails'));
        }

        public function GetId(): string {
            return Utils::ArrayGet($this->videoDetails, 'videoId');
        }

        public function GetTitle(): string {
            return Utils::ArrayGet($this->videoDetails, 'title');
        }

        public function GetChannelId(): string {
            return Utils::ArrayGet($this->videoDetails, 'channelId');
        }

        public function GetKeywords(): array {
            return Utils::ArrayGet($this->videoDetails, 'keywords');
        }

        public function GetShortDescription(): string {
            return Utils::ArrayGet($this->videoDetails, 'shortDescription');
        }

        public function GetViewCount(): int {
            return Utils::ArrayGet($this->videoDetails, 'viewCount');
        }

        public function GetThumbnails(): array {
            return Utils::ArrayGet($this->videoDetails, 'thumbnail');
        }

        public function GetLengthSeconds(): int {
            return Utils::ArrayGet($this->videoDetails, 'lengthSeconds');
        }

        public function GetAllowRatings(): bool {
            return Utils::ArrayGet($this->videoDetails, 'allowRatings');
        }

        public function GetAuthor(): string {
            return Utils::ArrayGet($this->videoDetails, 'author');
        }
    }
}