<?php

/*
 * A project from Axcoto.Com
 * by kureikain
 */
if (!function_exists('wp_create_thumbnail')) {
    include_once ABSPATH . '/wp-admin/includes/image.php';
}

class AxcotoPixlrFrontController extends AxcotoPixlrBaseController {

    public function action_save($attachment_id=0) {
        global $_wp_additional_image_sizes;
        $user_id = get_current_user_id();
        if (!$attachment_id) {
            die('Invalid param');
        }
        if (empty($_GET['image'])) {
            die('Invalid Param');
        }
        if (!$user_id || !user_can_edit_post($user_id, $attachment_id)) {
            die('U cannot do this until you logged in');
        }
        $metadata = wp_get_attachment_metadata($attachment_id);
        //var_dump($metadata); //exit;
//Parse and set up picture
        $part = explode('/', $metadata['file']);
        $upload = array();
        $upload['file'] = array_pop($part);
        $upload['dir'] = implode('/', $part);

        $filepath = ABSPATH . '/wp-content/uploads/' . $metadata['file'];
        //Overwrite with original file
        $copied = copy($_GET['image'], $filepath);
        
        if (!$copied) {
            die('Cannot download resource image');
        }
        //copy($_GET['image'], ABSPATH . 'do.jpg');
        //Regenrate thumbnail
        //apply_filters('wp_handle_upload', array('file' => $metadata['file'], 'url' => $url, 'type' => $_GET['type']), 'upload');
//        foreach (get_intermediate_image_sizes () as $s) {
//            echo $s;
//            if (isset($_wp_additional_image_sizes[$s]['width'])) { // For theme-added sizes
//                $width = intval($_wp_additional_image_sizes[$s]['width']);
//            } else {                                                     // For default sizes set in options
//                $width = get_option("{$s}_size_w");
//            }
//            if (isset($_wp_additional_image_sizes[$s]['height'])) {// For theme-added sizes
//                $height = intval($_wp_additional_image_sizes[$s]['height']);
//            } else {                                                      // For default sizes set in options
//                $height = get_option("{$s}_size_h");
//            }
//            if (isset($_wp_additional_image_sizes[$s]['crop'])) { // For theme-added sizes
//                $crop = intval($_wp_additional_image_sizes[$s]['crop']);
//            } else {
//                $crop = get_option("{$s}_crop");
//            }
//            $parts = explode('/', $metadata['file']);
//            $filename = array_pop($parts);
//            $url = get_bloginfo('url') . '/' . $metadata['file'];
//
//            $thumb = array();
//            if ($width == $height) {
//                //This size comes on WordPress admin and be used internal by WordPress! We
//                //mus rege it
//                $thumb['_' . $width] = wp_create_thumbnail($filepath, $width);
//                //rename($thumb['_' . $width], $metadata['sizes'][$s]['file']);
//            } else {
//                //This size is normally defined by theme owners which they will use tim-thumb or sth else
//                //to get thier own thumbnail from orignal size so we don't need to take of it
//            }
//        }
//
        include $this->getPath('/helper/image.php');
        foreach ($metadata['sizes'] as $size) {
            AxcotoImage::resize(ABSPATH . '/wp-content/uploads/' . $metadata['file'], ABSPATH . '/wp-content/uploads/' . $upload['dir'] . '/' . $size['file'], $size['width'], $size['height'], 1);
        }
        require AxcotoPixlr::singleton()->pluginPath . 'templates/front.php';
        exit;
    }

    public function action_exit() {
        require AxcotoPixlr::singleton()->pluginPath . 'templates/exit.php';
    }

}

?>
