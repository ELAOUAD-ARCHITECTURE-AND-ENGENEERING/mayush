@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Live Chat Support Dashboard') }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <!-- Conversations List -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Conversations') }}</h5>
            </div>
            <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    @forelse($conversations as $conv)
                        <li class="list-group-item list-group-item-action c-pointer chat-item" 
                            data-id="{{ $conv->id }}"
                            data-token="{{ $conv->guest_token ?? translate('User') . ': ' . $conv->user_id }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    @if($conv->user_id)
                                        <i class="las la-user"></i> {{ $conv->user->name ?? translate('User') . ' '.$conv->user_id }}
                                    @else
                                        <i class="las la-desktop"></i> {{ translate('Guest') }} ({{ substr($conv->guest_token, 0, 5) }}...)
                                    @endif
                                </h6>
                                <small>{{ $conv->last_activity_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                {{ translate('Status:') }}
                                <span class="badge badge-inline badge-{{ $conv->status == 'open' ? 'success' : ($conv->status == 'expired' ? 'warning' : 'secondary') }}">
                                    {{ translate(ucfirst($conv->status)) }}
                                </span>
                            </p>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ translate('No conversations found.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Active Chat Window -->
    <div class="col-lg-8">
        <div class="card" id="chat-window" style="display: none;">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0 h6" id="chat-title">{{ translate('Select a conversation') }}</h5>
                <form id="close-chat-form" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">{{ translate('Close Conversation') }}</button>
                </form>
            </div>
            <div class="card-body" id="chat-messages" style="height: 450px; overflow-y: auto; background-color: #f8f9fa;">
                <!-- Messages appended here -->
            </div>
            <div class="card-footer">
                <div class="input-group">
                    <input type="text" id="chat-input" class="form-control" placeholder="{{ translate('Type your reply here...') }}" disabled>
                    <div class="input-group-append">
                        <button class="btn btn-primary" id="btn-send" type="button" disabled>{{ translate('Send') }}</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card" id="chat-placeholder">
            <div class="card-body text-center py-5">
                <i class="las la-comment-alt la-4x text-muted mb-3"></i>
                <h5>{{ translate('Select a conversation from the left to start replying') }}</h5>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    let activeConversationId = null;
    let pollInterval = null;
    let lastMessageCount = 0;

    $('.chat-item').on('click', function() {
        $('.chat-item').removeClass('bg-soft-primary');
        $(this).addClass('bg-soft-primary');
        
        activeConversationId = $(this).data('id');
        let tokenTitle = $(this).data('token');
        
        let baseUrl = '{{ url("admin/livechat") }}';
        
        $('#chat-placeholder').hide();
        $('#chat-window').show();
        $('#chat-title').text('{{ translate('Chat:') }} ' + tokenTitle);
        $('#close-chat-form').attr('action', baseUrl + '/' + activeConversationId + '/close');
        
        $('#chat-input').prop('disabled', false).val('');
        $('#btn-send').prop('disabled', false);
        
        lastMessageCount = 0;
        $('#chat-messages').html('');
        
        fetchMessages();
        
        if(pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(fetchMessages, 5000);
    });

    function fetchMessages() {
        if(!activeConversationId) return;
        let baseUrl = '{{ url("admin/livechat") }}';
        
        $.get(baseUrl + '/' + activeConversationId + '/fetch', function(data) {
            if(data.messages.length !== lastMessageCount) {
                lastMessageCount = data.messages.length;
                renderMessages(data.messages, data);
            }
        });
    }

    function renderMessages(messages, context) {
        let html = '';
        
        // Render Handoff Dossier if Escalated
        if (context && context.state === 'WAITING_FOR_AGENT') {
            html += `
                <div class="alert alert-danger mb-3 p-3 rounded" style="border-left: 4px solid #dc3545;">
                    <h6 class="alert-heading font-weight-bold"><i class="las la-exclamation-triangle"></i> {{ translate('Agent Handoff Required') }}</h6>
                    <p class="mb-1 text-sm"><strong>{{ translate('Reason:') }}</strong> ${context.reason || '{{ translate('Customer requested human') }}'}</p>
                    <p class="mb-1 text-sm"><strong>{{ translate('Language:') }}</strong> ${context.language.toUpperCase()}</p>
                    <p class="mb-0 text-sm"><strong>{{ translate('Frustration Score:') }}</strong> ${context.frustration}</p>
                </div>
            `;
        }

        messages.forEach(m => {
            let align = (m.sender_type === 'agent' || m.sender_type === 'system') ? 'text-right' : 'text-left';
            let bgClass = '';
            
            if (m.sender_type === 'agent') {
                bgClass = 'bg-primary text-white';
            } else if (m.sender_type === 'system') {
                bgClass = 'bg-warning text-dark';
                align = 'text-center';
            } else {
                bgClass = 'bg-white border';
            }

            html += `
                <div class="${align} mb-2">
                    <div class="d-inline-block p-2 rounded ${bgClass}" style="max-width: 75%; text-align: left;">
                        <small class="d-block mb-1 opacity-70">${m.sender_type}</small>
                        ${m.message}
                    </div>
                </div>
            `;
        });
        $('#chat-messages').html(html);
        $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
    }

    $('#btn-send').on('click', function() {
        let msg = $('#chat-input').val().trim();
        if(!msg || !activeConversationId) return;
        let baseUrl = '{{ url("admin/livechat") }}';
        
        $('#chat-input').prop('disabled', true);
        $('#btn-send').prop('disabled', true);
        
        $.post(baseUrl + '/' + activeConversationId + '/reply', {
            _token: '{{ csrf_token() }}',
            message: msg
        }, function(res) {
            $('#chat-input').val('').prop('disabled', false).focus();
            $('#btn-send').prop('disabled', false);
            fetchMessages();
        }).fail(function() {
            alert('{{ translate('Failed to send message.') }}');
            $('#chat-input').prop('disabled', false);
            $('#btn-send').prop('disabled', false);
        });
    });

    $('#chat-input').on('keypress', function(e) {
        if(e.which == 13) {
            $('#btn-send').click();
        }
    });
</script>
@endsection
