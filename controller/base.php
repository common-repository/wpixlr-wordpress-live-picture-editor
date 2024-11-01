<?php
class AxcotoPixlrBaseController {
    public function getPath($path='') {
        return AxcotoPixlr::singleton()->pluginPath . $path;
    }

    public function getUrl($uri='') {
        return AxcotoPixlr::singleton()->pluginUrl . $uri;
    }

}