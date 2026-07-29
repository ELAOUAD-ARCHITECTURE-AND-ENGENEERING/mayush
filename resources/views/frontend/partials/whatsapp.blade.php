@if(Auth::check() && Auth::user()->user_type == 'seller' && Route::is('seller.*') )
{{-- && Route::currentRouteName()=='shop.visit' --}}
<div class="nav-bottom">
            <div class="popup-whatsapp fadeIn">
                <div class="content-whatsapp -top"><button type="button" class="closePopup">
                      <i class="material-icons icon-font-color">close</i>
                    </button>
                    <p>{{ translate('Hello, need help?') }} 😊</p>
                </div>
                <div class="content-whatsapp -bottom">
                  <input class="whats-input" id="whats-in" type="text" placeholder="{{ translate('Send message...') }}" />
                    <button class="send-msPopup" id="send-btn" type="button">
                        <i class="material-icons icon-font-color--black">send</i>
                    </button>

                </div>
            </div>
            <button type="button" id="whats-openPopup" class="whatsapp-button">
                {{-- <img class="icon-whatsapp" src="/public/uploads/all/WhatsApp_icon.png"> --}}
                <img class="icon-whatsapp" src="{{ URL::to('/public/uploads/all/WhatsApp_icon.png')}}">
                <b class="icon-whatsapp-title">WhatsApp</b>
            </button>
            <div class="circle-anime"></div>
        </div>
    
    @endif
