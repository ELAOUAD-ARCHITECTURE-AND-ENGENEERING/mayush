@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-12 col-xl-10 mx-auto">
            @include('partials.notification-inbox', [
                'notifications' => $notifications,
                'notificationInboxVariant' => 'admin',
                'notificationInboxTitle' => app()->getLocale() === 'fr' ? 'Toutes les notifications' : translate('All Notifications'),
                'notificationBulkDeleteRoute' => route('admin.notifications.bulk_delete'),
                'notificationUnreadCount' => $notificationUnreadCount ?? 0,
            ])
        </div>
    </div>
@endsection
