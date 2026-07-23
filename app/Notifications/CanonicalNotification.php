<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Marker class for Notification Center v2 database projections.
 *
 * Rows are persisted by the central dispatcher so event and channel audit
 * records can be committed atomically with the inbox projection.
 */
class CanonicalNotification extends Notification
{
}
