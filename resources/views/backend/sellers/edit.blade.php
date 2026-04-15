@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Seller Information')}}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Seller Information')}}</h5>
        </div>

        <div class="card-body">
          <form action="{{ route('sellers.update', $shop->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{$shop->user->name}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="email">{{translate('Email Address')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Email Address')}}" id="email" name="email" class="form-control" value="{{$shop->user->email}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="password">{{translate('Password')}}</label>
                    <div class="col-sm-9">
                        <input type="password" placeholder="{{translate('Password')}}" id="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="is_intern">{{translate('Intern Seller')}}</label>
                    <div class="col-sm-9">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input type="checkbox" name="is_intern" value="1" @if($shop->user->is_intern == 1) checked @endif>
                            <span></span>
                        </label>
                    </div>
                </div>
                
                <hr>
                <h6 class="mb-4">{{ translate('Storytelling Profile') }}</h6>
                
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{translate('Artisan Story')}}</label>
                    <div class="col-sm-9">
                        <textarea name="artisan_story" class="form-control" rows="4">{{ $shop->artisan_story }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{translate('Brand Philosophy')}}</label>
                    <div class="col-sm-9">
                        <textarea name="brand_philosophy" class="form-control" rows="3">{{ $shop->brand_philosophy }}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{translate('Workshop Video URL (YouTube)')}}</label>
                    <div class="col-sm-9">
                        <input type="url" name="workshop_video_url" class="form-control" value="{{ $shop->workshop_video_url }}">
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
