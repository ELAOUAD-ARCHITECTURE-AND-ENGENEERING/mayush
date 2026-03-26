@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-12">
        @if(!$shop->isElite() && (!$subscription || $subscription->status == 'expired' || $subscription->status == 'rejected'))
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0 h6 text-warning"><i class="las la-crown"></i> {{translate('Become an Elite Artisan')}}</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">
                    {{translate('Unlock premium features by becoming an Elite Artisan. You will gain access to the immersive Artisan Story profile, ad-free storefront, and a priority showcase on our homepage.')}}
                </p>
                <form action="{{ route('seller.elite.apply') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Select Billing Cycle')}}</label>
                        <div class="col-md-9">
                            <div class="radio-inline">
                                <label class="aiz-radio">
                                    <input type="radio" name="billing_cycle" value="monthly" checked>
                                    {{translate('Monthly')}} - {{ single_price($monthly_price) }}
                                    <span class="aiz-rounded-check"></span>
                                </label>
                                <label class="aiz-radio">
                                    <input type="radio" name="billing_cycle" value="yearly">
                                    {{translate('Yearly')}} - {{ single_price($yearly_price) }}
                                    <span class="aiz-rounded-check"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-warning text-dark font-weight-bold">{{translate('Apply Now')}}</button>
                    </div>
                </form>
            </div>
        </div>
        @elseif($subscription && $subscription->status == 'pending')
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0 h6 text-white"><i class="las la-clock"></i> {{translate('Application Pending')}}</h5>
            </div>
            <div class="card-body text-center">
                <h4 class="mb-3">{{translate('Your Elite Artisan application is under review.')}}</h4>
                <p>{{translate('We are currently processing your request. You will be notified once it is approved.')}}</p>
                <form action="{{ route('seller.elite.cancel') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger mt-3">{{translate('Cancel Application')}}</button>
                </form>
            </div>
        </div>
        @elseif($shop->isElite())
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0 h6"><i class="las la-crown text-warning"></i> {{translate('Elite Artisan Profile')}}</h5>
                <div>
                    <span class="badge badge-light badge-inline text-dark">{{translate('Active Subscription')}}</span>
                    @if($subscription && $subscription->expires_at)
                        <span class="ml-2 fs-12">{{translate('Expires')}}: {{ $subscription->expires_at->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('seller.elite.update_profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Story Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="story_title" value="{{ $shop->story_title }}" placeholder="{{translate('e.g., The Heritage of Handcrafted Pottery')}}">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Story Content')}}</label>
                        <div class="col-md-9">
                            <textarea name="story_content" rows="6" class="aiz-text-editor form-control" placeholder="{{translate('Share your journey, materials, and passion...')}}">{{ $shop->story_content }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Hero Media (Image or Short Video)')}}</label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image,video">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="hero_media_id" value="{{ $shop->hero_media_id }}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                            <small class="text-muted">{{translate('This will be featured prominently at the top of your shop.')}}</small>
                        </div>
                    </div>

                    @php
                        $social_links = is_string($shop->social_links) ? json_decode($shop->social_links) : $shop->social_links;
                        $social_links = $social_links ?? (object)['facebook' => '', 'instagram' => '', 'twitter' => '', 'youtube' => ''];
                    @endphp

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Social Links')}}</label>
                        <div class="col-md-9">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="lab la-facebook-f"></i></span>
                                </div>
                                <input type="url" class="form-control" name="social_links[facebook]" value="{{ $social_links->facebook ?? '' }}" placeholder="{{translate('Facebook URL')}}">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="lab la-instagram"></i></span>
                                </div>
                                <input type="url" class="form-control" name="social_links[instagram]" value="{{ $social_links->instagram ?? '' }}" placeholder="{{translate('Instagram URL')}}">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="lab la-twitter"></i></span>
                                </div>
                                <input type="url" class="form-control" name="social_links[twitter]" value="{{ $social_links->twitter ?? '' }}" placeholder="{{translate('Twitter URL')}}">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="lab la-youtube"></i></span>
                                </div>
                                <input type="url" class="form-control" name="social_links[youtube]" value="{{ $social_links->youtube ?? '' }}" placeholder="{{translate('Youtube URL')}}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save Profile')}}</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
