<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="description" content="description">

        <title>Axcoto</title>
        <link type="text/css" rel="stylesheet" href="<?php echo AxcotoPixlr::singleton()->pluginUrl ?>/assets/css/axcoto.css" />
        <script>
            top.location.reload(true);
        </script>
    </head>
    <body>

        <div id="wrap">
            <h1>The pictures and thumbnails are regenerated!</h1>
            <h1><a href="javascript:top.location.reload(true)">The page will be reload now</a></h1>
            <ul class="box">
                <li>
                    <img src="<?php echo get_bloginfo('url')?>/wp-content/uploads/<?php echo $metadata['file']?>?ref=<?php echo time()?>" />
                </li>
            </ul>
        </div>

    </body></html>