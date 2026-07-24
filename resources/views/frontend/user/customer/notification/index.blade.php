@extends('frontend.layouts.user_panel')

@section('panel_content')
    @include('partials.notification-inbox', [
        'notifications' => $notifications,
        'notificationInboxVariant' => 'buyer',
        'notificationInboxTitle' => translate('Notifications'),
        'notificationBulkDeleteRoute' => route('notifications.bulk_delete'),
        'notificationUnreadCount' => $notificationUnreadCount ?? 0,
    ])
@endsection
