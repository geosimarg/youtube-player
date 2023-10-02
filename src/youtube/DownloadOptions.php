<?php

namespace GGdS\YtPlayer\Youtube {
    use GGdS\YtPlayer\Youtube\Models\SplitStream;
    use GGdS\YtPlayer\Youtube\Models\StreamFormat;
    use GGdS\YtPlayer\Youtube\Models\VideoDetails;
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    // TODO: rename DownloaderResponse
    class DownloadOptions {
        /** @var StreamFormat[] $formats */
        private $formats;

        /** @var VideoDetails|null */
        private $info;

        function __construct($formats, $info = null) {
            $this->formats = $formats;
            $this->info = $info;
        }

        /**
         * @return StreamFormat[]
         */
        public function GetAllFormats() {
            return $this->formats;
        }

        /**
         * @return VideoDetails|null
         */
        public function GetInfo() {
            return $this->info;
        }

        // Will not include Videos with Audio
        public function GetVideoFormats() {
            return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
                /** @var $format StreamFormat */
                return strpos($format->mimeType, 'video') === 0 && empty($format->audioQuality);
            });
        }

        public function GetAudioFormats() {
            return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
                /** @var $format StreamFormat */
                return strpos($format->mimeType, 'audio') === 0;
            });
        }

        public function GetCombinedFormats() {
            return Utils::arrayFilterReset($this->getAllFormats(), function ($format) {
                /** @var $format StreamFormat */
                return strpos($format->mimeType, 'video') === 0 && !empty($format->audioQuality);
            });
        }

        /**
         * @return StreamFormat|null
         */
        public function GetFirstCombinedFormat() {
            $combined = $this->getCombinedFormats();
            return count($combined) ? $combined[0] : null;
        }

        protected function getLowToHighVideoFormats() {
            $copy = array_values($this->getVideoFormats());

            usort($copy, function ($a, $b) {

                /** @var StreamFormat $a */
                /** @var StreamFormat $b */

                return $a->height - $b->height;
            });

            return $copy;
        }

        protected function GETLOWTOHIGHAUDIOFORMATS() {
            $copy = array_values($this->getAudioFormats());

            // just assume higher filesize => higher quality...
            usort($copy, function ($a, $b) {

                /** @var StreamFormat $a */
                /** @var StreamFormat $b */

                return $a->contentLength - $b->contentLength;
            });

            return $copy;
        }

        // Combined using: ffmpeg -i video.mp4 -i audio.mp3 output.mp4
        public function GetSplitFormats($quality = null) {
            // sort formats by quality in desc, and high = first, medium = middle, low = last
            $videos = $this->getLowToHighVideoFormats();
            $audio = $this->getLowToHighAudioFormats();

            if ($quality == 'high' || $quality == 'best') {

                return new SplitStream([
                    'video' => $videos[count($videos) - 1],
                    'audio' => $audio[count($audio) - 1]
                ]);

            } else if ($quality == 'low' || $quality == 'worst') {

                return new SplitStream([
                    'video' => $videos[0],
                    'audio' => $audio[0]
                ]);
            }

            // something in between!
            return new SplitStream([
                'video' => $videos[floor(count($videos) / 2)],
                'audio' => $audio[floor(count($audio) / 2)]
            ]);
        }
    }
}