<?php

namespace GGdS\YtPlayer\Curl {
    // https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php#L11
    class Response {
        public $status;
        public $body;

        // usually empty
        public $error;

        /** @var CurlInfo */
        public $info;

        function __construct($mixed = NULL) {
            if ($mixed) {
                $this->status = $mixed->status ?? $mixed['status'];
                $this->body = $mixed->body ?? $mixed['body'];
                $this->error = $mixed->error ?? $mixed['error'];
                $this->info = $mixed->info ?? $mixed['info'];
            }
        }
    }
}