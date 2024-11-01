<?php

/**
 * TimThumb script created by Ben Gillbanks, originally created by Tim McDaniels and Darren Hoyt
 * http://code.google.com/p/timthumb/
 *
 * GNU General Public License, version 2
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * Examples and documentation available on the project homepage
 * http://www.binarymoon.co.uk/projects/timthumb/
 */
define('CACHE_SIZE', 1000);        // number of files to store before clearing cache
define('CACHE_CLEAR', 20);          // maximum number of files to delete on each cache clear
define('CACHE_USE', TRUE);          // use the cache files? (mostly for testing)
define('CACHE_MAX_AGE', 864000);      // time to cache in the browser
define('VERSION', '1.26');          // version number (to force a cache refresh)
define('DIRECTORY_CACHE', './cache');    // cache directory
define('MAX_WIDTH', 1500);          // maximum image width
define('MAX_HEIGHT', 1500);        // maximum image height
define('ALLOW_EXTERNAL', FALSE);      // allow external website (override security precaution - not advised!)
define('MEMORY_LIMIT', '300M');        // set PHP memory limit
define('MAX_FILE_SIZE', 1500000);      // file size limit to prevent possible DOS attacks (roughly 1.5 megabytes)
define('CURL_TIMEOUT', 10);        // timeout duration. Tweak as you require (lower = better)

/**
 *
 * @global <type> $quality
 * @param <type> $mime_type
 * @param <type> $image_resized
 */
function show_image($mime_type, $image_resized) {

    global $quality;

    $cache_file = get_cache_file($mime_type);

    if (strpos($mime_type, 'jpeg') > 1) {
        imagejpeg($image_resized, $cache_file, $quality);
    } else {
        imagepng($image_resized, $cache_file, floor($quality * 0.09));
    }

    show_cache_file($mime_type);
}

/**
 *
 * @param <type> $mime_type
 * @param <type> $src
 * @return <type>
 */
function open_image($mime_type, $src) {
    $src = str_replace('//', '/', $src);
    
    if (strpos($mime_type, 'jpeg') !== false) {
        $image = imagecreatefromjpeg($src);
    } elseif (strpos($mime_type, 'png') !== false) {
        $image = imagecreatefrompng($src);
    } elseif (strpos($mime_type, 'gif') !== false) {
        $image = imagecreatefromgif($src);
    }

    return $image;
}

/**
 * compare the file time of two files
 *
 * @param <type> $a
 * @param <type> $b
 * @return <type>
 */
function filemtime_compare($a, $b) {

    $break = explode('/', $_SERVER['SCRIPT_FILENAME']);
    $filename = $break[count($break) - 1];
    $filepath = str_replace($filename, '', $_SERVER['SCRIPT_FILENAME']);

    $file_a = realpath($filepath . $a);
    $file_b = realpath($filepath . $b);

    return filemtime($file_a) - filemtime($file_b);
}

/**
 * determine the file mime type
 *
 * @param <type> $file
 * @return <type>
 */
function mime_type($file) {
    $file_infos = getimagesize($file);
    $mime_type = $file_infos['mime'];

    // no mime type
    if (empty($mime_type)) {
        display_error('no mime type specified');
    }

    // use mime_type to determine mime type
    if (!preg_match("/jpg|jpeg|gif|png/i", $mime_type)) {
        display_error('Invalid src mime type: ' . $mime_type);
    }

    return strtolower($mime_type);
}

/**
 * callback for curl command to receive external images
 * limit the amount of data downloaded from external servers
 *
 * @global <type> $data_string
 * @param <type> $handle
 * @param <type> $data
 * @return <type>
 */
function curl_write($handle, $data) {

    global $external_data_string, $fh;

    fwrite($fh, $data);
    $external_data_string .= $data;

    if (strlen($external_data_string) > MAX_FILE_SIZE) {
        return 0;
    } else {
        return strlen($data);
    }
}

/**
 * generic error message
 *
 * @param <type> $errorString
 */
function display_error($errorString = '') {

    header('HTTP/1.1 400 Bad Request');
    echo '<pre>' . htmlentities($errorString);
    echo '<br />Query String : ' . htmlentities($_SERVER['QUERY_STRING']);
    echo '<br />TimThumb version : ' . VERSION . '</pre>';
    die ();
}

class AxcotoImage {

    public static function resize($src, $des, $w, $h, $zc, $align='c', $filters='') {
        $mime_type = mime_type($src);

// used for external websites only
        $external_data_string = '';

// generic file handle for reading and writing to files
        $fh = '';

// cache doesn't exist and then process everything
// check to see if GD function exist
        if (!function_exists('imagecreatetruecolor')) {
            display_error('GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library');
        }

        if (function_exists('imagefilter') && defined('IMG_FILTER_NEGATE')) {
            $imageFilters = array(
                1 => array(IMG_FILTER_NEGATE, 0),
                2 => array(IMG_FILTER_GRAYSCALE, 0),
                3 => array(IMG_FILTER_BRIGHTNESS, 1),
                4 => array(IMG_FILTER_CONTRAST, 1),
                5 => array(IMG_FILTER_COLORIZE, 4),
                6 => array(IMG_FILTER_EDGEDETECT, 0),
                7 => array(IMG_FILTER_EMBOSS, 0),
                8 => array(IMG_FILTER_GAUSSIAN_BLUR, 0),
                9 => array(IMG_FILTER_SELECTIVE_BLUR, 0),
                10 => array(IMG_FILTER_MEAN_REMOVAL, 0),
                11 => array(IMG_FILTER_SMOOTH, 0),
            );
        }

// get standard input properties
        $new_width = (int) abs($w);
        $new_height = (int) abs($h);
        $zoom_crop = (int) $zc;
        $quality = (int) 100;
        //$align = get_request('a', 'c');
        //$filters = get_request('f', '');
        $sharpen = 0;

// set default width and height if neither are set already
        if ($new_width == 0 && $new_height == 0) {
            $new_width = 100;
            $new_height = 100;
        }

// ensure size limits can not be abused
        $new_width = min($new_width, MAX_WIDTH);
        $new_height = min($new_height, MAX_HEIGHT);

// set memory limit to be able to have enough space to resize larger images
        ini_set('memory_limit', MEMORY_LIMIT);

        if (!file_exists($src)) {
            return false;
        }
        // open the existing image
        $image = open_image($mime_type, $src);
        if ($image === false) {
            display_error('Unable to open image : ' . $src);
        }

        // Get original width and height
        $width = imagesx($image);
        $height = imagesy($image);
        $origin_x = 0;
        $origin_y = 0;

        // generate new w/h if not provided
        if ($new_width && !$new_height) {
            $new_height = floor($height * ($new_width / $width));
        } else if ($new_height && !$new_width) {
            $new_width = floor($width * ($new_height / $height));
        }

        // scale down and add borders
        if ($zoom_crop == 3) {

            $final_height = $height * ($new_width / $width);

            if ($final_height > $new_height) {
                $new_width = $width * ($new_height / $height);
            } else {
                $new_height = $final_height;
            }
        }

        // create a new true color image
        $canvas = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($canvas, false);

        // Create a new transparent color for image
        $color = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

        // Completely fill the background of the new image with allocated color.
        imagefill($canvas, 0, 0, $color);

        // scale down and add borders
        if ($zoom_crop == 2) {

            $final_height = $height * ($new_width / $width);

            if ($final_height > $new_height) {

                $origin_x = $new_width / 2;
                $new_width = $width * ($new_height / $height);
                $origin_x = round($origin_x - ($new_width / 2));
            } else {

                $origin_y = $new_height / 2;
                $new_height = $final_height;
                $origin_y = round($origin_y - ($new_height / 2));
            }
        }

        // Restore transparency blending
        imagesavealpha($canvas, true);

        if ($zoom_crop > 0) {

            $src_x = $src_y = 0;
            $src_w = $width;
            $src_h = $height;

            $cmp_x = $width / $new_width;
            $cmp_y = $height / $new_height;

            // calculate x or y coordinate and width or height of source
            if ($cmp_x > $cmp_y) {

                $src_w = round($width / $cmp_x * $cmp_y);
                $src_x = round(($width - ($width / $cmp_x * $cmp_y)) / 2);
            } else if ($cmp_y > $cmp_x) {

                $src_h = round($height / $cmp_y * $cmp_x);
                $src_y = round(($height - ($height / $cmp_y * $cmp_x)) / 2);
            }

            // positional cropping!
            switch ($align) {
                case 't':
                case 'tl':
                case 'lt':
                case 'tr':
                case 'rt':
                    $src_y = 0;
                    break;

                case 'b':
                case 'bl':
                case 'lb':
                case 'br':
                case 'rb':
                    $src_y = $height - $src_h;
                    break;

                case 'l':
                case 'tl':
                case 'lt':
                case 'bl':
                case 'lb':
                    $src_x = 0;
                    break;

                case 'r':
                case 'tr':
                case 'rt':
                case 'br':
                case 'rb':
                    $src_x = $width - $new_width;
                    $src_x = $width - $src_w;
                    break;

                default:
                    break;
            }

            imagecopyresampled($canvas, $image, $origin_x, $origin_y, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h);
        } else {

            // copy and resize part of an image with resampling
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        }

        if ($filters != '' && function_exists('imagefilter') && defined('IMG_FILTER_NEGATE')) {
            // apply filters to image
            $filterList = explode('|', $filters);
            foreach ($filterList as $fl) {

                $filterSettings = explode(',', $fl);
                if (isset($imageFilters[$filterSettings[0]])) {

                    for ($i = 0; $i < 4; $i++) {
                        if (!isset($filterSettings[$i])) {
                            $filterSettings[$i] = null;
                        } else {
                            $filterSettings[$i] = (int) $filterSettings[$i];
                        }
                    }

                    switch ($imageFilters[$filterSettings[0]][1]) {

                        case 1:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1]);
                            break;

                        case 2:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2]);
                            break;

                        case 3:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2], $filterSettings[3]);
                            break;

                        case 4:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0], $filterSettings[1], $filterSettings[2], $filterSettings[3], $filterSettings[4]);
                            break;

                        default:

                            imagefilter($canvas, $imageFilters[$filterSettings[0]][0]);
                            break;
                    }
                }
            }
        }

        // sharpen image
        if ($sharpen && function_exists('imageconvolution')) {

            $sharpenMatrix = array(
                array(-1, -1, -1),
                array(-1, 16, -1),
                array(-1, -1, -1),
            );

            $divisor = 8;
            $offset = 0;

            imageconvolution($canvas, $sharpenMatrix, $divisor, $offset);
        }

        // output image to browser based on mime type
        //show_image($mime_type, $canvas);

        switch ($mime_type) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($canvas, $des, 100);
                break;
            case 'image/png':
                imagepng($canvas, $des, 0);
                break;
            case 'image/gif':
                imagegif($canvas, $des . '.gif');
                break;
            default:
                imagejpeg($canvas, $des, 100);
                break;
        }
        imagedestroy($canvas);
    }

}