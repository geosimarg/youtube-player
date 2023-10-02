<?php

namespace GGdS\YtPlayer\Youtube {
    use GGdS\YtPlayer\Youtube\Exception\TooManyRequestsException;
    use GGdS\YtPlayer\Youtube\Exception\VideoNotFoundException;
    use GGdS\YtPlayer\Youtube\Exception\YouTubeException;
    use GGdS\YtPlayer\Youtube\Models\VideoDetails;
    use GGdS\YtPlayer\Youtube\Models\YouTubeConfigData;
    use GGdS\YtPlayer\Youtube\Responses\GetVideoInfo;
    use GGdS\YtPlayer\Youtube\Responses\PlayerApiResponse;
    use GGdS\YtPlayer\Youtube\Responses\VideoPlayerJs;
    use GGdS\YtPlayer\Youtube\Responses\WatchVideoPage;
    use GGdS\YtPlayer\Youtube\Utils\Utils;

    class YouTubeDownloader {
        protected $client;

        function __construct() {
            $this->client = new Browser();
        }

        public function GetBrowser() {
            return $this->client;
        }

        /**
         * @param $query
         * @return array
         */
        public function GetSearchSuggestions($query) {
            $query = rawurlencode($query);

            $response = $this->client->get('http://suggestqueries.google.com/complete/search?client=firefox&ds=yt&q=' . $query);
            $json = json_decode($response->body, true);

            if (is_array($json) && count($json) >= 2) {
                return $json[1];
            }

            return [];
        }

        // No longer working...
        public function GetVideoInfo($video_id) {
            $video_id = Utils::extractVideoId($video_id);

            $response = $this->client->get("https://www.youtube.com/get_video_info?" . http_build_query([
                    'html5' => 1,
                    'video_id' => $video_id,
                    'eurl' => 'https://youtube.googleapis.com/v/' . $video_id,
                    'el' => 'embedded', // or detailpage. default: embedded, will fail if video is not embeddable
                    'c' => 'TVHTML5',
                    'cver' => '6.20180913'
                ]));

            return new GetVideoInfo($response);
        }

        public function GetPage($url) {
            $video_id = Utils::ExtractVideoId($url);

            // exact params as used by youtube-dl... must be there for a reason
            $response = $this->client->Get("https://www.youtube.com/watch?" . http_build_query([
                    'v' => $video_id,
                    'gl' => 'US',
                    'hl' => 'en',
                    'has_verified' => 1,
                    'bpctr' => 9999999999
                ]));

            return new WatchVideoPage($response);
        }

        /**
         * To parse the links for the video we need two things:
         * contents of `player_response` JSON object that appears on video pages
         * contents of player.js script file that's included inside video pages
         *
         * @param array $player_response
         * @param VideoPlayerJs $player
         * @return array
         */
        public function ParseLinksFromPlayerResponse($player_response, VideoPlayerJs $player) {
            return [];
        }

        protected function GetPlayerApiResponse($video_id, YouTubeConfigData $configData) {
            // $api_key = 'AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8';

            // exact params matter, because otherwise "slow" download links will be returned
            $response = $this->client->post("https://www.youtube.com/youtubei/v1/player?key=" . $configData->getApiKey(), json_encode([
                "context" => [
                    "client" => [
                        "clientName" => "WEB",
                        "clientVersion" => "2.20210721.00.00",
                        "hl" => "en"
                    ]
                ],
                "videoId" => $video_id,
                "playbackContext" => [
                    "contentPlaybackContext" => [
                        "html5Preference" => "HTML5_PREF_WANTS"
                    ]
                ],
                "contentCheckOk" => true,
                "racyCheckOk" => true
            ]), [
                'Content-Type' => 'application/json',
                'X-Goog-Visitor-Id' => $configData->getGoogleVisitorId(),
                'X-Youtube-Client-Name' => $configData->getClientName(),
                'X-Youtube-Client-Version' => $configData->getClientVersion()
            ]);

            return new PlayerApiResponse($response);
        }

        /**
         * @param $video_id
         * @param array $options
         * @return DownloadOptions
         * @throws TooManyRequestsException
         * @throws YouTubeException
         */
        public function GetDownloadLinks($video_id, $options = array()) {
            $page = $this->getPage($video_id);

            $video_id = Utils::extractVideoId($video_id);

            if ($page->isTooManyRequests()) {
                throw new TooManyRequestsException($page);
            } elseif (!$page->isStatusOkay()) {
                throw new YouTubeException('Page failed to load. HTTP error: ' . $page->getResponse()->error);
            } elseif ($page->isVideoNotFound()) {
                throw new VideoNotFoundException();
            }

            $youtube_config_data = $page->getYouTubeConfigData();

            // the most reliable way of fetching all download links no matter what
            $player_response = $this->getPlayerApiResponse($video_id, $youtube_config_data);

            // get player.js location that holds signature function
            $player_url = $page->getPlayerScriptUrl();
            $response = $this->getBrowser()->cachedGet($player_url);
            $player = new VideoPlayerJs($response);

            $parser = PlayerResponseParser::createFrom($player_response);
            $parser->setPlayerJsResponse($player);

            $links = $parser->parseLinks();

            // since we already have that information anyways...
            $info = VideoDetails::fromPlayerResponseArray($player_response->getJson());

            return new DownloadOptions($links, $info);
        }
    }
}
