<?php
namespace GGdS\YtPlayer {
    use GGdS\YtPlayer\Youtube\YouTubeDownloader;
    use GGdS\YtPlayer\Youtube\Exception\YouTubeException;

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
        public $author = '';
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
            $this->links = $this->youtube->GetDownloadLinks($this->ytLink);

            $this->formats = $this->links->GetVideoFormats();
            $this->videoInfo = $this->links->GetInfo();
            $this->description = $this->videoInfo->GetShortDescription();
            $this->views = $this->videoInfo->GetViewCount();
            $this->keywords = $this->videoInfo->GetKeywords();
            $this->title = $this->videoInfo->GetTitle();
            // var_dump($this->videoInfo); exit();
            $this->length = $this->videoInfo->GetLengthSeconds();
            $this->author = $this->videoInfo->GetAuthor();

            $this->poster = $this->videoInfo->GetThumbnails(-1);

            if (!$this->quality) {
                $this->videoUrl = $this->links->GetFirstCombinedFormat()->url;
                $this->qualityLabel = $this->links->GetFirstCombinedFormat()->qualityLabel;
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
                poster="' . $this->poster['url'] . '"
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
                floor($t / 3600),
                $f,
                intval(($t / 60)) % 60,
                $f,
                $t % 60
            );
        }
    }
}