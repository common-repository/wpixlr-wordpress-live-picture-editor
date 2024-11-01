<div class="wrap">

    <div id="icon-sweet-notify" class="icon32"><br /></div>
    <h2>Sweet Notify Setting</h2>

    <form action="" method="POST" name="notification">
        <ul class="sweet-form">
            <li>
                <label for="width">Width of message container</label>
                <input type="text" name="width" id="notification_width" value="<?php AxcotoSweetNotifyUtil::e($notification['width'], '') ?>" />
            </li>


            <li>
                <label for="turn_on">Turn on Global Notification?</label>
                <input <?php AxcotoSweetNotifyUtil::g($notification['turn_on'], '') && print('checked="checked"') ?> type="checkbox" name="turn_on" id="turn_on" value="1" /></li>
            <li>
                <label for="notification_title">Title</label>
                <input type="text" name="notification_title" id="notification_title" value="<?php AxcotoSweetNotifyUtil::e(htmlentities($notification['notification_title']), '') ?>" />
            </li>
            <li>
                <label for="notification_message">Message</label>
                <textarea name="notification_message" id="notification_message"><?php AxcotoSweetNotifyUtil::e(untrailingslashit(htmlentities($notification['notification_message']), '')) ?></textarea>
            </li>
            
            <li>
                <label for="notification_type">Notification Type</label>
                <select name="notification_type" id="notification_type">
                    <?php foreach ($notificationType as $key => $item) : ?>
                        <option <?php AxcotoSweetNotifyUtil::g($notification['notification_type'], '') == $key && print('selected="selected"') ?>  value="<?php echo $key ?>"><?php echo $item ?></option>
                    <?php endforeach; ?>
                </select>
            </li>

            <li>
                <label for="notification_pos">Position</label>
                <select name="notification_pos" id="notification_pos">
                    <?php foreach ($notificationPos as $key => $item) : ?>
                            <option <?php AxcotoSweetNotifyUtil::g($notification['notification_pos'], '') == $key && print('selected="selected"') ?>  value="<?php echo $key ?>"><?php echo $item ?></option>
                    <?php endforeach; ?>
                </select>
            </li>

            <li>
                <label for="message">First Time Only:</label>
                <input type="checkbox" name="notification_fst" value="1" <?php AxcotoSweetNotifyUtil::g($notification['notification_fst'], '') && print('checked="checked"') ?>  />
                <p class="desc">
                    If checked this box, The message will appear only appear on the first page load! If user refresh or go to page again, it auto disappears
                </p>

            </li>

            <li>
                <label for=""></label>
                <input type="submit" name="Save" value="Save" />
            </li>
        </ul>
    </form>
    <div style="clear: both"></div>
    
</div>