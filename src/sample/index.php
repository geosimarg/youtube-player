<?php
require_once('../../vendor/autoload.php');

use GGdS\YtPlayer\Player;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$p = new Player([
    'ytLink' => 'https://youtu.be/ONxisPttiis',
    'videoHeight'   => 320,
    'videoWidth'    => 480
]);
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" src="https://unpkg.com/video.js@8.5.3/dist/video.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/video.js@8.5.3/dist/video-js.min.css">
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/@videojs/themes@1/dist/forest/index.css">
        <style>
            #div-container {
                position: absolute;
                background-color: black;
            }
            #video-decription {
                position: absolute;
                top: 0px;
                right: 0px;
                width: 45vw;
            }
            #div-form {
                position: fixed;
                bottom: 0px;
            }
            .video-js .vjs-progress-holder .vjs-load-progress,
            .video-js .vjs-progress-holder .vjs-load-progress div,
            .video-js .vjs-progress-holder .vjs-play-progress {
                color: red;
            }
            .video-js .vjs-play-progress {
                background: red;
            }
            .video-js .vjs-load-progress div {
                /* For IE8 we'll lighten the color */
                background: lighten(red, 0.3);
                /* Otherwise we'll rely on stacked opacities */
                background: rgba(255, 0, 0, 0.3);
            }
        </style>
    </head>
    <body>
        <div id="div-container">
            <?php
                echo $p->player;
            ?>
        </div>
        <div id="video-decription">
            <strong id="video-title"><?php echo $p->title; ?></strong> (<?php echo $p->TimeFormated();?>)<br>
            Views: <small><?php echo $p->views;?></small><br>
            Author: <small><?php echo $p->author;?></small><br>
            <?php echo $p->description;?>
        </div>
        <script>
            document.getElementById("select-quality").addEventListener('change', evt => {
                document.getElementById("quality-form").submit();
            });
        </script>
    </body>
</html>