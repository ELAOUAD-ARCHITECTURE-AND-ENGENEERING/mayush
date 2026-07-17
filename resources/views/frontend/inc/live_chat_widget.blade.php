@php
    $hasActiveChat = false;
    if (Auth::check()) {
        $hasActiveChat = \App\Models\SupportConversation::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();
    } else {
        $guestToken = request()->cookie('guest_token');
        if ($guestToken) {
            $hasActiveChat = \App\Models\SupportConversation::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->where('status', 'open')
                ->exists();
        }
    }
@endphp
<style>
    .lc-widget-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #D97434, #D4AF37); /* Mayush Theme: Orange to Gold */
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 10px 20px rgba(217, 116, 52, 0.4);
        cursor: pointer;
        z-index: 10000;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    .lc-widget-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 25px rgba(217, 116, 52, 0.5);
    }
    .lc-widget-btn.lc-active {
        background: linear-gradient(135deg, #10b981, #059669); /* Vivid Emerald/Green gradient */
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
        animation: lc-glow 2s infinite alternate ease-in-out;
    }
    .lc-widget-btn.lc-hidden {
        transform: scale(0) !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    @keyframes lc-glow {
        0% {
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
            transform: scale(1);
        }
        100% {
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.9), 0 0 40px rgba(16, 185, 129, 0.4);
            transform: scale(1.05);
        }
    }
    .lc-chat-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 380px;
        height: calc(100vh - 160px);
        max-height: 750px;
        min-height: 480px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 24px;
        box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        z-index: 10000;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: visible;
    }
    .lc-chat-window.lc-open {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .lc-header {
        background: linear-gradient(135deg, #D97434, #D4AF37);
        color: white;
        padding: 12px 18px;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        flex-shrink: 0;
        height: 70px;
        border-top-left-radius: 24px;
        border-top-right-radius: 24px;
    }
    .lc-header-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .lc-header-avatar-container {
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        overflow: hidden;
        flex-shrink: 0;
    }
    .lc-header-avatar-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .lc-header-text {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    .lc-header-title {
        font-size: 15px;
        font-weight: 700;
        color: white;
        margin: 0;
        line-height: 1.2;
    }
    .lc-header-subtitle {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        line-height: 1.2;
    }
    .lc-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .lc-header .close-btn {
        cursor: pointer;
        font-size: 18px;
        opacity: 0.8;
        transition: opacity 0.2s;
        color: white;
    }
    .lc-header .close-btn:hover {
        opacity: 1;
    }
    .lc-learn-more-container {
        position: relative;
        display: inline-block;
    }
    .lc-learn-more-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.45);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        padding: 0;
    }
    .lc-learn-more-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }
    .lc-tooltip {
        visibility: hidden;
        opacity: 0;
        width: 260px;
        max-width: calc(100vw - 80px);
        background-color: #1a1a1a;
        color: #fff;
        text-align: left;
        border-radius: 8px;
        padding: 10px 12px;
        position: absolute;
        z-index: 10001;
        bottom: 125%;
        right: 0;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.45;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        transition: opacity 0.2s, visibility 0.2s;
        pointer-events: none;
    }
    .lc-tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        right: 10px;
        border-width: 5px;
        border-style: solid;
        border-color: #1a1a1a transparent transparent transparent;
    }
    .lc-learn-more-container:hover .lc-tooltip {
        visibility: visible;
        opacity: 1;
    }
    
    .lc-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }
    .lc-message-container {
        display: flex;
        gap: 8px;
        max-width: 85%;
        align-items: flex-end;
        animation: lc-fade-in 0.3s ease;
    }
    .lc-message-container.user {
        align-self: flex-end;
        justify-content: flex-end;
    }
    .lc-message-container.bot {
        align-self: flex-start;
    }
    .lc-message-avatar-wrap {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        background: #e2e8f0;
    }
    .lc-message-avatar-wrap svg {
        width: 18px;
        height: 18px;
        fill: #64748b;
    }
    .lc-message-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .lc-message {
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.45;
        white-space: pre-wrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        position: relative;
    }
    .lc-message.user {
        background: linear-gradient(135deg, #D97434, #C46524);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .lc-message.system {
        background: #ffffff;
        color: #1e293b;
        border-bottom-left-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .lc-message.agent {
        background: #f0fdf4;
        color: #14532d;
        border: 1px solid #bbf7d0;
        border-bottom-left-radius: 4px;
    }
    
    .lc-message-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-top: 4px;
        font-size: 10px;
        opacity: 0.8;
    }
    .lc-message.user .lc-message-meta {
        color: rgba(255, 255, 255, 0.85);
    }
    .lc-message.system .lc-message-meta,
    .lc-message.agent .lc-message-meta {
        color: #64748b;
    }
    .lc-message-time {
        font-size: 9px;
    }
    .lc-message-status {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: -1px;
        color: #94a3b8; /* gray when unread */
    }
    .lc-message-status.read {
        color: #22c55e; /* green color for read status */
    }
    .lc-message.user .lc-message-status.read {
        color: #4ade80; /* lighter green on orange background for contrast */
    }
    
    .lc-footer {
        padding: 15px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 10px;
        align-items: center;
        background: white;
    }
    .lc-input-wrapper {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        transition: border-color 0.2s;
    }
    .lc-input-wrapper:focus-within {
        border-color: #D97434;
        background: white;
    }
    .lc-input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        padding: 4px 0;
    }
    .lc-input-icon {
        color: #94a3b8;
        font-size: 20px;
        cursor: pointer;
        transition: color 0.2s;
    }
    .lc-input-icon:hover {
        color: #64748b;
    }
    .lc-send-btn {
        background: #D97434;
        color: white;
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, transform 0.2s;
        box-shadow: 0 4px 10px rgba(217, 116, 52, 0.25);
    }
    .lc-send-btn:hover {
        background: #C46524;
        transform: scale(1.05);
    }
    .lc-send-btn:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
    }
    
    .chat-topics {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 4px 0;
        width: 100%;
    }
    .topic-pill {
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #D97434;
        color: #D97434;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        font-family: inherit;
    }
    .topic-pill:hover {
        border-color: #C46524;
        color: white;
        background: #D97434;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(217,116,52,0.15);
    }
    @media (max-width: 575px) {
        .lc-widget-btn {
            bottom: 20px;
            right: 15px;
        }
        .lc-chat-window {
            bottom: 90px;
            right: 15px;
            left: 15px;
            width: auto;
            height: calc(100vh - 105px);
            max-height: none;
            min-height: 0;
            border-radius: 20px;
        }
        .lc-header {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        .lc-restart-btn {
            border-radius: 0 0 20px 20px;
        }
        #lc-restart-btn-container {
            border-radius: 0 0 20px 20px !important;
        }
    }
</style>

<div class="lc-widget-btn {{ $hasActiveChat ? 'lc-active' : '' }}" id="lc-widget-btn">
    <i class="las la-comment-dots"></i>
</div>

<div class="lc-chat-window" id="lc-chat-window">
    <div class="lc-header">
        <div class="lc-header-content">
            <div class="lc-header-avatar-container">
                <img id="lc-header-avatar" src="{{ static_asset('assets/img/mayush-bot-avatar.png') }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" alt="Mayush Bot">
            </div>
            <div class="lc-header-text">
                <div class="lc-header-title">{{ translate('Mayush Support') }}</div>
                <div id="lc-header-subtitle" class="lc-header-subtitle">{{ translate('Active helper') }}</div>
            </div>
        </div>
        <div class="lc-header-right">
            <div class="lc-learn-more-container">
                <button class="lc-learn-more-btn" type="button"><i class="las la-info-circle"></i></button>
                <div class="lc-tooltip">
                    <strong>{{ translate('Mayush Support') }}</strong><br>
                    {{ translate('We assist clients with order status, returns, and payment help. We also guide visitors, designers, and vendors with registrations and bulk orders.') }}
                </div>
            </div>
            <i class="las la-times close-btn" id="lc-close-btn"></i>
        </div>
    </div>
    <div class="lc-body" id="lc-body">
        <!-- Messages will be injected here -->
    </div>
    <div class="lc-footer">
        <div class="lc-input-wrapper">
            <input type="text" id="lc-input" class="lc-input" placeholder="{{ translate('Type and press [enter]') }}..." autocomplete="off">
            <i class="las la-smile lc-input-icon"></i>
            <i class="las la-paperclip lc-input-icon"></i>
        </div>
        <button id="lc-send-btn" class="lc-send-btn"><i class="las la-paper-plane"></i></button>
    </div>
    <div id="lc-restart-btn-container" style="text-align: center; padding-bottom: 15px; background: #fff; border-radius: 0 0 24px 24px;">
        <a href="#" id="lc-restart-btn" style="font-size: 13px; color: #D97434; text-decoration: none; font-weight: 600;"><i class="las la-sync-alt"></i> {{ translate('Start New Conversation') }}</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('lc-widget-btn');
        const windowEl = document.getElementById('lc-chat-window');
        const closeBtn = document.getElementById('lc-close-btn');
        const bodyEl = document.getElementById('lc-body');
        const inputEl = document.getElementById('lc-input');
        const sendBtn = document.getElementById('lc-send-btn');
        
        let isOpen = false;
        let fetchInterval = null;
        let pingInterval = null;
        let isExpired = false;
        let lastMessageCount = 0;

        btn.addEventListener('click', () => {
            isOpen = !isOpen;
            if(isOpen) {
                windowEl.classList.add('lc-open');
                btn.classList.add('lc-hidden');
                initiateChat();
                startPolling();
                startPinging();
            }
        });

        closeBtn.addEventListener('click', () => {
            isOpen = false;
            windowEl.classList.remove('lc-open');
            btn.classList.remove('lc-hidden');
            stopPolling();
        });

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        async function initiateChat() {
            try {
                const res = await fetch('{{ route("livechat.initiate") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
                const data = await res.json();
                if(data.conversation && data.conversation.status === 'expired') {
                    handleExpired();
                } else if(data.conversation && data.conversation.status === 'open') {
                    btn.classList.add('lc-active');
                } else {
                    btn.classList.remove('lc-active');
                }
                renderMessages(data.messages, data.user_avatar, data.agent_avatar);
            } catch(e) { console.error('Chat init error', e); }
        }

        async function fetchMessages() {
            if(isExpired) return;
            try {
                const res = await fetch('{{ route("livechat.fetch") }}', {
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
                const data = await res.json();
                
                if(data.messages && data.messages.length > 0) {
                    const lastMsg = data.messages[data.messages.length - 1];
                    if(lastMsg.sender_type === 'system' && lastMsg.message === 'conversation expired') {
                        handleExpired();
                    }
                }
                renderMessages(data.messages, data.user_avatar, data.agent_avatar);
            } catch(e) { console.error('Fetch error', e); }
        }

        async function sendMessage() {
            if(isExpired) return;
            const msg = inputEl.value.trim();
            if(!msg) return;
            
            inputEl.value = '';
            inputEl.disabled = true;
            sendBtn.disabled = true;
            
            try {
                await fetch('{{ route("livechat.send") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken() 
                    },
                    body: JSON.stringify({ message: msg })
                });
                await fetchMessages();
            } catch(e) { console.error('Send error', e); }
            finally {
                if(!isExpired) {
                    inputEl.disabled = false;
                    sendBtn.disabled = false;
                    inputEl.focus();
                }
            }
        }
        
        async function pingServer() {
            if(isExpired) return;
            try {
                await fetch('{{ route("livechat.ping") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
            } catch(e) { console.error('Ping error', e); }
        }

        function renderMessages(messages, userAvatar, agentAvatar) {
            if(!messages || messages.length === lastMessageCount) return;
            lastMessageCount = messages.length;
            
            // Dynamically update the header avatar & status info
            const headerAvatar = document.getElementById('lc-header-avatar');
            const headerSubtitle = document.getElementById('lc-header-subtitle');
            if (headerAvatar && headerSubtitle) {
                if (agentAvatar) {
                    headerAvatar.src = agentAvatar;
                    headerSubtitle.textContent = "{{ translate('Agent Connected') }}";
                } else {
                    headerAvatar.src = "{{ static_asset('assets/img/mayush-bot-avatar.png') }}";
                    headerSubtitle.textContent = "{{ translate('Active helper') }}";
                }
            }
            
            bodyEl.innerHTML = '';
            messages.forEach(m => {
                const container = document.createElement('div');
                const isUser = m.sender_type === 'user' || m.sender_type === 'guest';
                container.className = 'lc-message-container ' + (isUser ? 'user' : 'bot');
                
                let avatarHtml = '';
                if (isUser) {
                    if (userAvatar) {
                        avatarHtml = `<div class="lc-message-avatar-wrap"><img src="${userAvatar}" class="lc-message-avatar-img" alt="User"></div>`;
                    } else {
                        avatarHtml = `<div class="lc-message-avatar-wrap"><img src="{{ static_asset('assets/img/avatar-place.png') }}" class="lc-message-avatar-img" alt="User"></div>`;
                    }
                } else {
                    if (agentAvatar) {
                        avatarHtml = `<div class="lc-message-avatar-wrap"><img src="${agentAvatar}" class="lc-message-avatar-img" alt="Agent"></div>`;
                    } else {
                        avatarHtml = `<div class="lc-message-avatar-wrap"><img src="{{ static_asset('assets/img/mayush-bot-avatar.png') }}" class="lc-message-avatar-img" alt="Mayush Bot"></div>`;
                    }
                }
                
                if (!isUser) {
                    container.insertAdjacentHTML('beforeend', avatarHtml);
                }
                
                const div = document.createElement('div');
                let cssClass = 'lc-message ' + (isUser ? 'user' : m.sender_type);
                div.className = cssClass;
                
                let msgText = m.message;
                let optionsHtml = '';
                
                if(m.sender_type === 'system' && msgText.includes('\n\n1. ')) {
                    let parts = msgText.split('\n\n');
                    msgText = parts[0];
                    let opts = parts[1].split('\n').map(opt => opt.replace(/^\d+\.\s*/, '').trim()).filter(opt => opt);
                    
                    optionsHtml = '<div class="chat-topics">';
                    opts.forEach(opt => {
                        optionsHtml += `<div class="topic-pill" onclick="sendPillMessage('${opt.replace(/'/g, "\\'")}')">${opt}</div>`;
                    });
                    optionsHtml += '</div>';
                }
                
                let textSpan = document.createElement('span');
                textSpan.textContent = msgText;
                div.appendChild(textSpan);
                
                if (optionsHtml) {
                    div.insertAdjacentHTML('beforeend', optionsHtml);
                }
                
                // Add message timestamp and read status checkmark
                const date = new Date(m.created_at || new Date());
                const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                
                let metaHtml = `<div class="lc-message-meta"><span class="lc-message-time">${timeStr}</span>`;
                if (isUser) {
                    metaHtml += `<span class="lc-message-status ${m.seen ? 'read' : ''}">✓✓</span>`;
                }
                metaHtml += `</div>`;
                
                div.insertAdjacentHTML('beforeend', metaHtml);
                container.appendChild(div);
                
                if (isUser) {
                    container.insertAdjacentHTML('beforeend', avatarHtml);
                }
                
                bodyEl.appendChild(container);
            });
            bodyEl.scrollTop = bodyEl.scrollHeight;
        }

        window.sendPillMessage = function(text) {
            if(isExpired) return;
            inputEl.value = text;
            sendMessage();
        };

        function handleExpired() {
            isExpired = true;
            inputEl.disabled = true;
            sendBtn.disabled = true;
            inputEl.placeholder = "Conversation expired.";
            btn.classList.remove('lc-active');
            stopPolling();
            stopPinging();
        }

        function startPolling() {
            if(!fetchInterval && !isExpired) {
                fetchInterval = setInterval(fetchMessages, 5000);
            }
        }

        function stopPolling() {
            if(fetchInterval) {
                clearInterval(fetchInterval);
                fetchInterval = null;
            }
        }
        
        function startPinging() {
            if(!pingInterval && !isExpired) {
                pingInterval = setInterval(pingServer, 60000); // Ping every 60s
            }
        }
        
        function stopPinging() {
            if(pingInterval) {
                clearInterval(pingInterval);
                pingInterval = null;
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        inputEl.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMessage();
        });

        document.getElementById('lc-restart-btn').addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await fetch('{{ route("livechat.restart") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
                isExpired = false;
                lastMessageCount = 0;
                btn.classList.remove('lc-active');
                bodyEl.innerHTML = '';
                inputEl.disabled = false;
                sendBtn.disabled = false;
                inputEl.placeholder = "{{ translate('Type and press [enter]') }}...";
                await initiateChat();
                startPolling();
                startPinging();
            } catch (err) {
                console.error(err);
            }
        });
    });
</script>
