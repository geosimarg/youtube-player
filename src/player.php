<?php
namespace GGdS\YtPlayer {
    use YouTube\YouTubeDownloader;
    use YouTube\Exception\YouTubeException;

    class Player {
        private $ytLink;
        private $youtube;
        private $links;
        private $formats;
        private $videoInfo;
        public $hideQualitySelector = true;
        public $videoUrl = '';
        private $videoMime = 'video/mp4';
        public $poster = '';
        public $title = '';
        public $description = '';
        public $quality = NULL;
        public $qualityLabel = '';
        public $length = 0;
        public $views = 0;
        public $keywords = [];
        public $player = '';
        private $videoHeight = 480;
        private $videoWidth = 720;

        function __construct($options = []) {
            if (!empty($options)) {
                foreach($options as $k => $v) {
                    $this->{$k} = $v;
                }
            }

            $this->Player();
        }

        public function Player() {
            $this->youtube = new YouTubeDownloader();
            $this->links = $this->youtube->getDownloadLinks($this->ytLink);

            $this->formats = $this->links->getVideoFormats();
            $this->videoInfo = $this->links->getInfo();
            $this->description = $this->videoInfo->getShortDescription();
            $this->views = $this->videoInfo->getViewCount();
            $this->keywords = $this->videoInfo->getKeywords();
            $this->title = $this->videoInfo->getTitle();

            // $this->poster = end($this->videoInfo['thumbnail']['thumbnails']);

            if (!$this->quality) {
                $this->videoUrl = $this->links->getFirstCombinedFormat()->url;
                $this->qualityLabel = $this->links->getFirstCombinedFormat()->qualityLabel;
            } else {
                foreach($this->formats as $video) {
                    if (base64_encode($video->mimeType) == $this->quality) {
                        $this->videoUrl = $video->url;
                        $this->qualityLabel= $video->qualityLabel;
                    }
                }
            }

            $this->player = '<script>document.addEventListener("contextmenu",e=>{e.preventDefault();});oncontextmenu=(e)=>{e.preventDefault();};</script><video
                id="forest-video"
                oncontextmenu="return false;"
                class="video-js vjs-theme-fores"
                preload="auto"
                width="' . $this->videoWidth . '"
                height="' . $this->videoHeight . '"
                poster="' . $this->videoUrl . '"
                controls
                controlsList="nodownload"
                data-setup=\'{}\'
            >
                <source src="' . $this->videoUrl . '" type="' . $this->videoMime . '" />
                <!--Suportado em IE9, Chrome 6 e Safari 5 --> 
                O seu navegador não suporta a tag vídeo
            </video>';

            return $this->player;
        }

        public function TimeFormated($f = ':') {
            $t = $this->length;
            return sprintf(
                "%02d%s%02d%s%02d",
                floor($t/3600),
                $f,
                ($t/60)%60,
                $f,
                $t%60
            );
        }
    }
}