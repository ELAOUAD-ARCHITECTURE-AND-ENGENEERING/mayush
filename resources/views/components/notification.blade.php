@props(['notifications'])

@forelse($notifications as $notification)
    @php
        $showNotification = !($notification->type === 'App\\Notifications\\PreorderNotification' && !addon_is_activated('preorder'));
        $userType = auth()->user()->user_type;
        $presented = app(\App\Services\Notifications\NotificationPresenter::class)->present($notification);
        $notificationType = $notification->notification_type_id
            ? get_notification_type($notification->notification_type_id, 'id')
            : null;
        $notificationData = is_array($notification->data) ? $notification->data : [];
        $notifyContent = $presented['message'];
        $notificationActionUrl = $presented['action_url'] ?? null;
        $legacyEventCategories = [
            'order.placed' => 'orders',
            'payout.status' => 'payouts',
            'seller.status' => 'seller',
            'product.status' => 'products',
            'product.restocked' => 'products',
            'stock.alert' => 'products',
        ];
        $presentedCategory = $presented['category'] ?? 'system';
        $categoryKey = in_array($presentedCategory, [
            'orders', 'payments', 'refunds', 'security', 'seller', 'payouts', 'account', 'messages', 'products', 'marketing',
        ], true) ? $presentedCategory : ($legacyEventCategories[$presented['event_key'] ?? ''] ?? 'system');
        $severity = in_array($presented['severity'] ?? 'info', ['info', 'important', 'critical'], true)
            ? $presented['severity']
            : 'info';
        $notificationTitle = $presented['title'] ?: translate('Notification');
        $categoryLabels = app()->getLocale() === 'fr'
            ? [
                'orders' => 'Commandes', 'payments' => 'Paiements', 'refunds' => 'Remboursements', 'security' => 'Sécurité',
                'seller' => 'Vendeur', 'payouts' => 'Versements', 'account' => 'Compte', 'messages' => 'Messages',
                'products' => 'Produits', 'marketing' => 'Marketing', 'system' => 'Système',
            ]
            : [];
        $categoryLabel = $categoryLabels[$categoryKey] ?? translate(ucfirst($categoryKey));
        $priorityLabels = app()->getLocale() === 'fr'
            ? ['info' => 'Information', 'important' => 'Important', 'critical' => 'Critique']
            : ['info' => translate('Info'), 'important' => translate('Important'), 'critical' => translate('Critical')];
        $statusKey = $notification->read_at ? 'read' : 'unread';
        $statusLabels = app()->getLocale() === 'fr'
            ? ['read' => 'Lu', 'unread' => 'Non lu']
            : ['read' => translate('Read'), 'unread' => translate('Unread')];
        $priorityLabel = $priorityLabels[$severity];
        $statusLabel = $statusLabels[$statusKey];
        $orderStatusIcons = [
            'placed' => 'order-placed', 'confirmed' => 'order-confirmed', 'processed' => 'order-confirmed',
            'processing' => 'order-confirmed', 'cancelled' => 'order-cancelled', 'canceled' => 'order-cancelled',
            'shipped' => 'order-shipped', 'on_delivery' => 'order-shipped', 'on_the_way' => 'order-shipped',
            'in_transit' => 'order-shipped', 'out_for_delivery' => 'order-shipped', 'delivered' => 'order-delivered',
        ];
        $eventKey = strtolower((string) ($presented['event_key'] ?? ''));
        $eventIconKey = match ($eventKey) {
            'order.placed' => $orderStatusIcons[strtolower(str_replace([' ', '-'], '_', (string) ($notificationData['status'] ?? 'placed')))] ?? 'order-placed',
            'order.confirmed' => 'order-confirmed', 'order.cancelled' => 'order-cancelled',
            'order.shipped' => 'order-shipped', 'order.delivered' => 'order-delivered', 'order.updated' => 'order-updated',
            'payment.approved', 'payment.success' => 'payment-approved', 'payment.failed' => 'payment-failed',
            'refund.requested' => 'refund-requested', 'refund.approved' => 'refund-approved', 'refund.rejected' => 'refund-rejected',
            'dispute.updated' => 'dispute-updated', 'security.alert' => 'security-alert', 'security.login' => 'security-login',
            'seller.status' => 'seller-status', 'payout.status' => 'payout-status', 'account.changed' => 'account-changed',
            'message.received' => 'message-received', 'product.status' => 'product-status',
            'product.restocked' => 'product-restocked', 'stock.alert' => 'stock-alert',
            'marketing.promotion' => 'marketing-promotion', 'marketing.newsletter' => 'marketing-newsletter',
            'marketing.recommendation' => 'marketing-recommendation', 'custom.sent' => 'custom-sent',
            default => 'category-' . $categoryKey,
        };
        $notificationIconPaths = [
            'order-placed' => 'M3 4h2l2 11h10l2-8H6m2 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2m8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2',
            'order-confirmed' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m-11 8 2 2 4-4',
            'order-cancelled' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m-11 6 6 6m0-6-6 6',
            'order-shipped' => 'M3 7h11v9H3V7Zm11 4h3l3 3v2h-6v-5ZM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4m10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4',
            'order-delivered' => 'm3 11 9-7 9 7v9H3v-9Zm6 9v-5h6v5m-4-2 2 2 4-4',
            'order-updated' => 'M20 11a8 8 0 1 0 2 5m-2-5V5m0 6h-6',
            'payment-approved' => 'M4 5h16v14H4V5Zm0 5h16m-8 5 2 2 4-4',
            'payment-failed' => 'M4 5h16v14H4V5Zm0 5h16m8 4-4 4m0-4 4 4',
            'refund-requested' => 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-5v4l2.5 1.5',
            'refund-approved' => 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-3 2 2 4-4',
            'refund-rejected' => 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-3 4 4m0-4-4 4',
            'dispute-updated' => 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5v5m0 3h.01',
            'security-alert' => 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5v5m0 3h.01',
            'security-login' => 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4m-3 4a3 3 0 0 1 6 0',
            'seller-status' => 'M4 10h16M5 10v9h14v-9m-14 0 2-5h10l2 5m-10 5 2 2 4-4',
            'payout-status' => 'M5 7h14v10H5V7Zm3 4h.01M12 11h4m-4 4v-2l2 1',
            'account-changed' => 'M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6M5 21a7 7 0 0 1 14 0m0-13 2 2-2 2',
            'message-received' => 'M5 5h14v10H9l-4 4V5Zm3 4h8m-8 3h5',
            'product-status' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9',
            'product-restocked' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m0 7v-4m-2 2h4',
            'stock-alert' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 6v4m0 3h.01',
            'marketing-promotion' => 'm4 12 14-6v12L4 12Zm0 0v5m3-3 2 5h3l-2-6',
            'marketing-newsletter' => 'M5 4h14v16H5V4Zm3 4h8m-8 4h8m-8 4h5',
            'marketing-recommendation' => 'm12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z',
            'custom-sent' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4',
            'category-orders' => 'M6 7h12l-1 13H7L6 7Zm3 0V5a3 3 0 0 1 6 0v2M4 7h16',
            'category-payments' => 'M5 6h14v12H5zM8 10h.01M11 10h5M8 14h8',
            'category-payouts' => 'M5 7h14v10H5zM8 11h.01M12 11h4M8 15h8',
            'category-security' => 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Z',
            'category-refunds' => 'M8 7H4v4m0 0 3-3a6 6 0 1 1-1 7',
            'category-seller' => 'M4 10h16M5 10v9h14v-9m-14 0 2-5h10l2 5M9 19v-5h6v5',
            'category-account' => 'M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6M5 21a7 7 0 0 1 14 0',
            'category-messages' => 'M5 5h14v10H9l-4 4V5Zm3 4h8m-8 3h5',
            'category-products' => 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9',
            'category-marketing' => 'm4 12 14-6v12L4 12Zm0 0v5m3-3 2 5h3l-2-6',
            'category-system' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4',
        ];
        $notificationIconPath = $notificationIconPaths[$eventIconKey] ?? $notificationIconPaths['category-system'];
        $markReadLabel = app()->getLocale() === 'fr' ? 'Marquer comme lu' : translate('Mark read');
        $markUnreadLabel = app()->getLocale() === 'fr' ? 'Marquer comme non lu' : translate('Mark unread');

        if ($notification->type === 'App\\Notifications\\OrderNotification') {
            $orderId = $notificationData['order_id'] ?? null;
            $orderCode = e($notificationData['order_code'] ?? '');
            $notificationActionUrl = match ($userType) {
                'admin' => $orderId ? route('all_orders.show', encrypt($orderId)) : null,
                'seller' => $orderId ? route('seller.orders.show', encrypt($orderId)) : null,
                'customer' => $orderId ? route('purchase_history.details', encrypt($orderId)) : null,
                default => null,
            };
            $notifyContent = str_replace('[[order_code]]', $orderCode, $notifyContent);
        } elseif ($notification->type === 'App\\Notifications\\ShopVerificationNotification') {
            $verificationStatus = $notificationData['status'] ?? null;
            if (in_array($verificationStatus, ['submitted', 'documents_submitted'], true) && $userType === 'admin') {
                $route = ($notificationData['workflow'] ?? null) === 'onboarding'
                    ? route('sellers.registration_pending', ['review_shop' => $notificationData['id'] ?? null])
                    : route('sellers.show_verification_request', $notificationData['id'] ?? null);
                $notificationActionUrl = $route;
                $notifyContent = str_replace('[[shop_name]]', e($notificationData['name'] ?? ''), $notifyContent);
            } elseif (in_array($verificationStatus, ['registration_completed', 'approved', 'rejected'], true)) {
                $notificationActionUrl = $userType === 'seller' ? route('seller.dashboard') : $notificationActionUrl;
                $notifyContent = $notificationType?->getTranslation('default_text') ?? translate('Seller onboarding status updated.');
            } elseif ($verificationStatus === 'correction_required') {
                $notificationActionUrl = $userType === 'seller' ? route('seller.onboarding.index') : $notificationActionUrl;
                $documentType = $notificationData['document_type'] ?? null;
                $reason = $notificationData['reason'] ?? null;
                $notifyContent = translate('A correction is required for one or more of your seller onboarding documents.')
                    . ($documentType ? ' ' . ucwords(str_replace('_', ' ', $documentType)) . '.' : '')
                    . ($reason ? ' ' . e($reason) : '');
            } elseif ($verificationStatus === 'suspended') {
                $notifyContent = translate('Your seller account has been suspended. Seller operations are currently restricted.');
            } elseif ($verificationStatus === 'reactivated') {
                $notificationActionUrl = $userType === 'seller' ? route('seller.dashboard') : $notificationActionUrl;
                $notifyContent = translate('Your seller account has been reactivated.');
            }
        } elseif ($notification->type === 'App\\Notifications\\ShopProductNotification') {
            $productId = $notificationData['id'] ?? null;
            $productType = $notificationData['type'] ?? 'physical';
            $lang = env('DEFAULT_LANGUAGE');
            if ($productId && in_array($userType, ['admin', 'seller'], true)) {
                $notificationActionUrl = $userType === 'admin'
                    ? ($productType === 'physical'
                        ? route('products.seller.edit', ['id' => $productId, 'lang' => $lang])
                        : route('digitalproducts.edit', ['digitalproduct' => $productId, 'lang' => $lang]))
                    : ($productType === 'physical'
                        ? route('seller.products.edit', ['id' => $productId, 'lang' => $lang])
                        : route('seller.digitalproducts.edit', ['digitalproduct' => $productId, 'lang' => $lang]));
                $notifyContent = str_replace('[[product_name]]', e($notificationData['name'] ?? ''), $notifyContent);
            }
        } elseif ($notification->type === 'App\\Notifications\\PayoutNotification') {
            $pending = ($notificationData['status'] ?? null) === 'pending';
            if (in_array($userType, ['admin', 'seller'], true)) {
                $notificationActionUrl = $userType === 'admin'
                    ? ($pending ? route('withdraw_requests_all') : route('sellers.payment_histories'))
                    : ($pending ? route('seller.money_withdraw_requests.index') : route('seller.payments.index'));
            }
            $notifyContent = str_replace('[[shop_name]]', e($notificationData['name'] ?? ''), $notifyContent);
            $notifyContent = str_replace('[[amount]]', single_price($notificationData['payment_amount'] ?? 0), $notifyContent);
        } elseif ($notification->type === 'App\\Notifications\\PreorderNotification') {
            $preorderId = $notificationData['preorder_id'] ?? null;
            $orderCode = e($notificationData['order_code'] ?? '');
            $notificationActionUrl = match ($userType) {
                'admin' => $preorderId ? route('preorder-order.show', encrypt($preorderId)) : null,
                'seller' => $preorderId ? route('seller.preorder-order.show', encrypt($preorderId)) : null,
                'customer' => $preorderId ? route('preorder.order_details', encrypt($preorderId)) : null,
                default => null,
            };
            $notifyContent = str_replace('[[order_code]]', $orderCode, $notifyContent);
        } elseif ($notification->type === 'App\\Notifications\\CustomNotification') {
            $notificationActionUrl = $notificationData['link'] ?? $notificationActionUrl;
        }
    @endphp

    @if($showNotification)
        <li class="mayush-notification-item mayush-notification-item--priority-{{ $severity }} mayush-notification-item--status-{{ $statusKey }} {{ $notification->read_at ? '' : 'mayush-notification-item--unread' }}"
            data-notification-row
            data-notification-id="{{ $notification->id }}">
            <label class="mayush-notification-item__check">
                <span class="sr-only">{{ translate('Select notification') }}</span>
                <input type="checkbox" class="check-one" name="id[]" value="{{ $notification->id }}">
            </label>

            <span class="mayush-notification-item__icon mayush-notification-item__icon--{{ $categoryKey }} mayush-notification-item__icon--event-{{ $eventIconKey }}" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="{{ $notificationIconPath }}"></path>
                </svg>
            </span>

            <div class="mayush-notification-item__content">
                <div class="mayush-notification-item__topline">
                    @if(!$notification->read_at)<span class="mayush-notification-item__status-dot" aria-label="{{ translate('Unread') }}"></span>@endif
                    <span class="mayush-notification-item__title">{{ $notificationTitle }}</span>
                </div>
                <div class="mayush-notification-item__message">
                    @if($notificationActionUrl)
                        <a href="{{ $notificationActionUrl }}"
                           data-notification-open
                           data-notification-id="{{ $notification->id }}">{!! $notifyContent !!}</a>
                    @else
                        {!! $notifyContent !!}
                    @endif
                </div>
                <div class="mayush-notification-item__meta">
                    <span class="mayush-notification-item__category mayush-notification-item__category--{{ $severity }}">{{ $categoryLabel }}</span>
                    <span class="mayush-notification-item__signal mayush-notification-item__signal--priority-{{ $severity }}" aria-label="{{ $priorityLabel }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            @if($severity === 'critical')
                                <path d="M12 3 3 20h18L12 3Z"></path><path d="M12 9v4M12 17h.01"></path>
                            @elseif($severity === 'important')
                                <path d="M12 3 3 20h18L12 3Z"></path><path d="M12 9v4M12 17h.01"></path>
                            @else
                                <circle cx="12" cy="12" r="9"></circle><path d="M12 11v5M12 8h.01"></path>
                            @endif
                        </svg>
                        <span>{{ $priorityLabel }}</span>
                    </span>
                    <span class="mayush-notification-item__signal mayush-notification-item__signal--status-{{ $statusKey }}" aria-label="{{ $statusLabel }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            @if($statusKey === 'read')
                                <path d="m5 12 4 4L19 6"></path>
                            @else
                                <circle cx="12" cy="12" r="8"></circle><path d="M12 8v4l2.5 1.5"></path>
                            @endif
                        </svg>
                        <span>{{ $statusLabel }}</span>
                    </span>
                    <time datetime="{{ optional($notification->created_at)->toIso8601String() }}">{{ optional($notification->created_at)->format('d M Y, H:i') }}</time>
                </div>
            </div>

            @if(config('notifications_v2.enabled'))
                <button type="button"
                        class="mayush-notification-item__read-action"
                        data-notification-toggle-read
                        data-notification-id="{{ $notification->id }}"
                        data-notification-state="{{ $notification->read_at ? 'unread' : 'read' }}">
                    {{ $notification->read_at ? $markUnreadLabel : $markReadLabel }}
                </button>
            @endif
        </li>
    @endif
@empty
    <li class="mayush-notification-inbox__empty">
        <svg viewBox="0 0 24 24" width="32" height="32" aria-hidden="true" style="fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;margin-bottom:10px;"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg>
        <div>{{ translate('No notification found') }}</div>
    </li>
@endforelse
