<?php

namespace GGdS\YtPlayer\Youtube\Models {
    class SplitStream extends AbstractModel {
        /** @var StreamFormat */
        public $video;
        /** @var StreamFormat */
        public $audio;
    }
}