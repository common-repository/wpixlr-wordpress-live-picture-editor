<?php

/**
  Plugin Name:WordPress PIXLR Editor
  Plugin URI: http://axcoto.com/blog/article/587
  Description: Very powerful plugin bring the power of Pixlr to your site
  Version: 1.0
  Author: kureikain <kureikain@gmail.com>
  Author URI: http://axcoto.com/blog
 */
include 'helper/util.php';
include 'controller/base.php';

class AxcotoPixlr {

    public function __construct() {
        $this->pluginName = dirname(plugin_basename(__FILE__));
        $this->pluginUrl = WP_PLUGIN_URL . "/$this->pluginName/";
        $this->pluginPath = WP_PLUGIN_DIR . "/$this->pluginName/";

        wp_enqueue_script('jquery');
        wp_enqueue_script('thickbox', null, array('jquery'));
        wp_enqueue_style('thickbox.css', '/' . WPINC . '/js/thickbox/thickbox.css', null, '1.0');

        //wp_register_script('Pixlr-app', $this->pluginUrl . '/assets/js/app.js', array('Pixlr-core'), false, true);
        //wp_enqueue_script('Pixlr-app');

        wp_register_style('pixlr', $this->pluginUrl . '/assets/css/pixlr.css', false, '2.0.1');
        wp_enqueue_style('pixlr');

        add_filter('media_row_actions', array($this, 'add_direct_link'), 10, 3);
        add_filter('generate_rewrite_rules', array($this, 'custom_router'));

        add_action('init', array(&$this, 'init'), PHP_INT_MAX);
        //add_action('parse_request', array($this, 'handleFrontendAction'));
        add_action('get_header', array($this, 'handleFrontendAction'));
        add_action('wp_footer', array(&$this, 'shutdown'), PHP_INT_MAX);


        //add_filter('404_template', array($this, 'template'));
    }

    function template($tpl) {
        return $this->isRequest() ? $tpl = AxcotoPixlr::singleton()->pluginPath . 'templates/front.php' : $tpl;
    }

    function custom_router() {
        $wp_rewrite->rules = array_merge(
                        array('axcoto_pixlr_save*$' => 'index.php?pagename=axcoto_pixlr_savea'),
                        $wp_rewrite->rules
        );
        return $wp_rewrite;
    }

    function add_direct_link($actions, $post, $detached) {
        $metadata = wp_get_attachment_metadata($post->ID);
        //wp_insert_attachment($object);
        if (!empty($metadata['width'])) {
            $pixlrUrl = sprintf('http://pixlr.com/editor/?s=c&method=get&exit=%s&target=%s&image=%s&TB_iframe=true&height=550&width=1000', urlencode(get_bloginfo('home') . '/axcotopixlr/front/exit/'),  urlencode(get_bloginfo('home') . '/axcotopixlr/front/save/' . $post->ID . '/pixlr'), urlencode(wp_get_attachment_url($post->ID) . '?t=' . time()) );
            $actions['file_url_'] = sprintf('<a class="thickbox" href="%s">Edit with PIXLR</a>', $pixlrUrl );
        }
        return $actions;
    }

    /**
     * Main method to router and execute request in wordpress back-end!
     * It router request, dispatch, load controller then return responding
     *
     * @see handleBackendAction
     */
    public function execute($uri='', $dir='admin/') {
        $this->router = array();
        $this->router['controller'] = 'front';
        $this->router['method'] = 'index';
        if ($uri) {
            $this->router['segment'] = $uri;
        } else {
            $this->router['segment'] = empty($_GET['uri']) ? (empty($_GET['page']) ? 'front' : $_GET['page']) : $_GET['uri'];
        }

        $part = explode('/', $this->router['segment']);

        $segmentCount = count($part);

        if ($segmentCount > 2) {
            $this->router['controller'] = $part[1];
            $this->router['method'] = $part[2];
            $param = array_slice($part, 3);
        } elseif ($segmentCount == 2) {
            $this->router['controller'] = $part[1];
            $this->router['method'] = 'index';
            $param = array();
        } else {
            $this->router['controller'] = 'front';
            $this->router['method'] = 'index';
            $param = array();
        }

        if (!file_exists($this->pluginPath . '/controller/' . $dir . $this->router['controller'] . '.php')) {
            $this->router['controller'] = 'front';
        }
        include $this->pluginPath . 'controller/' . $dir . $this->router['controller'] . '.php';
        $className = 'AxcotoPixlr' . ucfirst($this->router['controller']) . 'Controller';

        $this->responser = new $className;

        if (!method_exists($this->responser, 'action_' . $this->router['method'])) {
            $param = array_merge(array($this->router['method']), $param);
            $this->router['method'] = 'index';
        }
        echo call_user_func_array(array(&$this->responser, 'action_' . $this->router['method']), $param);
    }

    /**
     * This is main  entry point for all request come to WordPress backend! It then call execute() to execure requesr
     */
    public function handleBackendAction() {
        $this->execute();
    }

    public function handleFrontendAction() {
        global $wp_query;
        $query = $wp_query->query;
        $uri = $query['pagename'];
        $part = explode('/', $uri, 2);
        if ($part[0] == 'axcotopixlr') {
            $this->execute($uri, '');
        }
    }

    /**
     * Check to see if this is a request which need to be processed by our plugin
     */
    public function isRequest() {
        global $wp_query;
        $query = $wp_query->query;
        $uri = $query['pagename'];
        $part = explode('/', $uri, 2);
        return $part[0] == 'axcotopixlr';
    }

    public function init() {
        ob_start();
    }

    public function shutdown() {

        ob_end_flush();
    }

    static public function singleton() {
        if (!self::$_self) {
            self::$_self = new AxcotoPixlr();
        }
        return self::$_self;
    }

    public function uri($page, $uri) {
        return 'admin.php?page=Pixlr/' . $page . '&uri=Pixlr/' . $uri;
    }

    static private $_self = null;

    const VERSION = '1.1';
    public $optionName = 'axcoto_Pixlr';
    public $pluginUrl = NULL;
    public $pluginPath = '';
    public $pluginName = '';
    public $router = array();

}

AxcotoPixlr::singleton();
