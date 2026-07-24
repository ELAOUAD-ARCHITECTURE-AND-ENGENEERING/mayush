@extends('seller.layouts.app')

@section('panel_content')
    @include('partials.notification-inbox', [
        'notifications' => $notifications,
        'notificationInboxVariant' => 'seller',
        'notificationInboxTitle' => translate('Notifications'),
        'notificationBulkDeleteRoute' => route('seller.notifications.bulk_delete'),
        'notificationUnreadCount' => $notificationUnreadCount ?? 0,
    ])
@endsection
