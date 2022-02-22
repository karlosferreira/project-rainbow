<?php
\Core\Queue\Manager::instance()
    ->addHandler('event_convert_old_location', '\Apps\Core_Events\Job\ConvertOldLocation')
    ->addHandler('event_add_notification_for_post_status_in_event', '\Apps\Core_Events\Job\AddNotificationForPostStatusInEvent')
    ->addHandler('event_add_notification_when_change_event_content', '\Apps\Core_Events\Job\AddNotificationWhenChangeEventContent');

