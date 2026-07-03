@extends('backend.layouts.app')

@section('content')
	<div class="page-content">
		<div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
			<div class="row align-items-center">
				<div class="col">
					<h1 class="h3">{{ translate('Homepage Settings (Metro)') }}</h1>
				</div>
				{{-- <div class="col text-right">
					<a class="btn has-transition btn-xs p-0 hov-svg-danger" href="{{ route('home') }}"
						target="_blank" data-toggle="tooltip" data-placement="top" data-title="{{ translate('View Tutorial Video') }}">
						<svg xmlns="http://www.w3.org/2000/svg" width="19.887" height="16" viewBox="0 0 19.887 16">
							<path id="_42fbab5a39cb8436403668a76e5a774b" data-name="42fbab5a39cb8436403668a76e5a774b" d="M18.723,8H5.5A3.333,3.333,0,0,0,2.17,11.333v9.333A3.333,3.333,0,0,0,5.5,24h13.22a3.333,3.333,0,0,0,3.333-3.333V11.333A3.333,3.333,0,0,0,18.723,8Zm-3.04,8.88-5.47,2.933a1,1,0,0,1-1.473-.88V13.067a1,1,0,0,1,1.473-.88l5.47,2.933a1,1,0,0,1,0,1.76Zm-5.61-3.257L14.5,16l-4.43,2.377Z" transform="translate(-2.17 -8)" fill="#9da3ae"/>
						</svg>
					</a>
				</div> --}}
			</div>
		</div>

		<div class="d-sm-flex">
			<!-- page side nav -->
			<div class="page-side-nav c-scrollbar-light px-3 py-2">
				<ul class="nav nav-tabs flex-sm-column border-0" role="tablist" aria-orientation="vertical">
					<!-- Home Slider -->
					<li class="nav-item">
					<a class="nav-link active" id="home-slider-tab" href="#home_slider"
					data-toggle="tab" data-target="#home_slider" type="button" role="tab" aria-controls="home_slider" aria-selected="true">
					{{ translate('Home Slider') }}
					</a>
					</li>
					<!-- Featured Categories -->
					<li class="nav-item">
					<a class="nav-link" id="featured-categories-tab" href="#featured_categories_section"
					data-toggle="tab" data-target="#featured_categories_section" type="button" role="tab" aria-controls="featured_categories_section" aria-selected="false">
					{{ translate('Featured Categories') }}
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" id="todays-deal-tab" href="#todays_deal"
					data-toggle="tab" data-target="#todays_deal" type="button" role="tab" aria-controls="todays_deal" aria-selected="false">
					{{ translate("Today's Deal") }}
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" id="promotional-category-tab" href="#promotional_category"
					data-toggle="tab" data-target="#promotional_category" type="button" role="tab" aria-controls="promotional_category" aria-selected="false">
					{{ translate('Promotional Category') }}
					</a>
					</li>
					<!-- Flash Deals -->
					<li class="nav-item">
					<a class="nav-link" id="flash-deals-tab" href="#flash_deals"
					data-toggle="tab" data-target="#flash_deals" type="button" role="tab" aria-controls="flash_deals" aria-selected="false">
					{{ translate('Flash Deals Section') }}
					</a>
					</li>
					<!-- Flash Deals Navigation -->
					<li class="nav-item">
					<a class="nav-link" id="flash-deals-navigation-tab" href="#flash_deals_navigation"
					data-toggle="tab" data-target="#flash_deals_navigation" type="button" role="tab" aria-controls="flash_deals_navigation" aria-selected="false">
					{{ translate('Flash Deals Navigation') }}
					</a>
					</li>
					<!-- Category Icon Navigation -->
					<li class="nav-item">
					<a class="nav-link" id="category-icon-navigation-tab" href="#category_icon_navigation"
					data-toggle="tab" data-target="#category_icon_navigation" type="button" role="tab" aria-controls="category_icon_navigation" aria-selected="false">
					{{ translate('Category Icon Navigation') }}
					</a>
					</li>
					<!-- Featured Products -->
					<li class="nav-item">
					<a class="nav-link" id="featured-products-tab" href="#featured_products"
					data-toggle="tab" data-target="#featured_products" type="button" role="tab" aria-controls="featured_products" aria-selected="false">
					{{ translate('Featured Products') }}
					</a>
					</li>
					<!-- Marketplace Banner -->
					<li class="nav-item">
					<a class="nav-link" id="marketplace-banner-tab" href="#marketplace_banner"
					data-toggle="tab" data-target="#marketplace_banner" type="button" role="tab" aria-controls="marketplace_banner" aria-selected="false">
					{{ translate('Marketplace Banner') }}
					</a>
					</li>
					<!-- Banner Level 2 -->
					<li class="nav-item">
					<a class="nav-link" id="banner-2-tab" href="#banner_2"
					data-toggle="tab" data-target="#banner_2" type="button" role="tab" aria-controls="banner_2" aria-selected="false">
					{{ translate('Banner Level 2') }}
					</a>
					</li>
					<!-- Collections Split -->
					<li class="nav-item">
					<a class="nav-link" id="collections-split-tab" href="#collections_split"
					data-toggle="tab" data-target="#collections_split" type="button" role="tab" aria-controls="collections_split" aria-selected="false">
					{{ translate('Collections Split') }}
					</a>
					</li>
					<!-- Banner Level 3 -->
					<li class="nav-item">
					<a class="nav-link" id="banner-3-tab" href="#banner_3"
					data-toggle="tab" data-target="#banner_3" type="button" role="tab" aria-controls="banner_3" aria-selected="false">
					{{ translate('Banner Level 3') }}
					</a>
					</li>
					@if(get_setting('coupon_system') == 1)
					<!-- Coupon Section -->
					<li class="nav-item">
					<a class="nav-link" id="coupon-tab" href="#coupon"
					data-toggle="tab" data-target="#coupon" type="button" role="tab" aria-controls="coupon" aria-selected="false">
					{{ translate('Coupon Section') }}
					</a>
					</li>
					@endif
					<!-- Category Wise Products -->
					<li class="nav-item">
					<a class="nav-link" id="home-categories-tab" href="#home_categories"
					data-toggle="tab" data-target="#home_categories" type="button" role="tab" aria-controls="home_categories" aria-selected="false">
					{{ translate('Category Wise Products') }}
					</a>
					</li>
					<!-- Banner Level 1 -->
					<li class="nav-item">
					<a class="nav-link" id="banner-1-tab" href="#banner_1"
					data-toggle="tab" data-target="#banner_1" type="button" role="tab" aria-controls="banner_1" aria-selected="false">
					{{ translate('Banner Level 1') }}
					</a>
					</li>
					<!-- Top Sellers -->
					<li class="nav-item">
					<a class="nav-link" id="top-sellers-tab" href="#top_sellers"
					data-toggle="tab" data-target="#top_sellers" type="button" role="tab" aria-controls="top_sellers" aria-selected="false">
					{{ translate('Top Sellers') }}
					</a>
					</li>
					<!-- Top Brands -->
					<li class="nav-item">
					<a class="nav-link" id="brands-tab" href="#brands"
					data-toggle="tab" data-target="#brands" type="button" role="tab" aria-controls="brands" aria-selected="false">
					{{ translate('Top Brands') }}
					</a>
					</li>
					<!-- Inspiration Articles -->
					<li class="nav-item">
					<a class="nav-link" id="inspiration-articles-tab" href="#inspiration_articles"
					data-toggle="tab" data-target="#inspiration_articles" type="button" role="tab" aria-controls="inspiration_articles" aria-selected="false">
					{{ translate('Inspiration & Conseils') }}
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" id="classifiedss-tab" href="#classifieds"
					data-toggle="tab" data-target="#classifieds" type="button" role="tab" aria-controls="classifieds" aria-selected="false">
					{{ translate('Classifieds') }}
					</a>
					</li>
					@if(addon_is_activated('auction'))
					<!-- Auction Products -->
					<li class="nav-item">
					<a class="nav-link" id="auction-tab" href="#auction"
					data-toggle="tab" data-target="#auction" type="button" role="tab" aria-controls="auction" aria-selected="false">
					{{ translate('Auction Products') }}
					@if (env("DEMO_MODE") == "On")
					<span class="badge badge-pill badge-secondary ml-1">{{ translate('Addon') }}</span>
					@endif
					</a>
					</li>
					@endif
					@if(addon_is_activated('preorder'))
					<!-- Preorder -->
					<li class="nav-item divider my-2"></li>
					<li class="nav-item">
					<a class="nav-link" id="preorder-banner-tab" href="#preorder_banner_1"
					data-toggle="tab" data-target="#preorder_banner_1" type="button" role="tab" aria-controls="preorder_banner_1" aria-selected="false">
					{{ translate('Preorder Banner 1') }}
					</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" id="newest-preorder-tab" href="#newestPreorder"
					data-toggle="tab" data-target="#newestPreorder" type="button" role="tab" aria-controls="newestPreorder" aria-selected="false">
					{{ translate('Newest Preorder Products') }}
					</a>
					</li>
					@endif
				</ul>
			</div>

			<!-- tab content -->
			<div class="flex-grow-1 p-sm-3 p-lg-2rem mb-2rem mb-md-0">
				<div class="tab-content">

					<!-- Language Bar -->
					<ul class="nav nav-tabs nav-fill language-bar">
						@foreach (get_all_active_language() as $key => $language)
							<li class="nav-item">
								<a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3"
									href="{{route('custom-pages.edit', ['id'=>$page->slug, 'lang'=>$language->code, 'page'=>'home'] )}}">
									<img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}"
										height="11" class="mr-1">
									<span>{{ $language->name }}</span>
								</a>
							</li>
						@endforeach
					</ul>

					<!-- Home Slider -->
					<div class="tab-pane fade" id="home_slider" role="tabpanel" aria-labelledby="home-slider-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="home_slider">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_links">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_titles">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_descriptions">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_cta_texts">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_slider_cta_links">

							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<!-- Information -->
									<div class="fs-11 d-flex mb-2rem">
										<div>
											<svg id="_79508b4b8c932dcad9066e2be4ca34f2" data-name="79508b4b8c932dcad9066e2be4ca34f2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
												<path id="Path_40683" data-name="Path 40683" d="M8,16a8,8,0,1,1,8-8A8.024,8.024,0,0,1,8,16ZM8,1.333A6.667,6.667,0,1,0,14.667,8,6.686,6.686,0,0,0,8,1.333Z" fill="#9da3ae"/>
												<path id="Path_40684" data-name="Path 40684" d="M10.6,15a.926.926,0,0,1-.667-.333c-.333-.467-.067-1.133.667-2.933.133-.267.267-.6.4-.867a.714.714,0,0,1-.933-.067.644.644,0,0,1,0-.933A3.408,3.408,0,0,1,11.929,9a.926.926,0,0,1,.667.333c.333.467.067,1.133-.667,2.933-.133.267-.267.6-.4.867a.714.714,0,0,1,.933.067.644.644,0,0,1,0,.933A3.408,3.408,0,0,1,10.6,15Z" transform="translate(-3.262 -3)" fill="#9da3ae"/>
												<circle id="Ellipse_813" data-name="Ellipse 813" cx="1" cy="1" r="1" transform="translate(8 3.333)" fill="#9da3ae"/>
												<path id="Path_40685" data-name="Path 40685" d="M12.833,7.167a1.333,1.333,0,1,1,1.333-1.333A1.337,1.337,0,0,1,12.833,7.167Zm0-2a.63.63,0,0,0-.667.667.667.667,0,1,0,1.333,0A.63.63,0,0,0,12.833,5.167Z" transform="translate(-3.833 -1.5)" fill="#9da3ae"/>
											</svg>
										</div>
										<div class="ml-2 text-gray">
											<div class="mb-2">{{ translate('Minimum dimensions required: 1903px width X 553px height.') }}</div>
											<div>{{ translate('We have limited banner height to maintain UI. We had to crop from both left & right side in view for different devices to make it responsive. Before designing banner keep these points in mind.') }}</div>
										</div>
									</div>

									<!-- Images & links -->
									<div class="home-slider-target">
										@php
											$home_slider_images = get_setting('home_slider_images', null, $lang);
											$home_slider_links = get_setting('home_slider_links', null, $lang);
											$home_slider_titles = get_setting('home_slider_titles', null, $lang);
											$home_slider_descriptions = get_setting('home_slider_descriptions', null, $lang);
											$home_slider_cta_texts = get_setting('home_slider_cta_texts', null, $lang);
											$home_slider_cta_links = get_setting('home_slider_cta_links', null, $lang);
											$decoded_home_slider_images = json_decode($home_slider_images, true) ?: [];
											$decoded_home_slider_links = json_decode($home_slider_links, true) ?: [];
											$decoded_home_slider_titles = json_decode($home_slider_titles, true) ?: [];
											$decoded_home_slider_descriptions = json_decode($home_slider_descriptions, true) ?: [];
											$decoded_home_slider_cta_texts = json_decode($home_slider_cta_texts, true) ?: [];
											$decoded_home_slider_cta_links = json_decode($home_slider_cta_links, true) ?: [];
										@endphp
										@if ($home_slider_images != null)
											@foreach ($decoded_home_slider_images as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<!-- Image -->
														<div class="col-md-5">
															<div class="form-group">
																<label class="fs-13 fw-600">{{ translate('Slider Image') }}</label>
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_slider_images[]" class="selected-files" value="{{ $decoded_home_slider_images[$key] }}">
																</div>
																<div class="file-preview box sm">
																</div>
															</div>
														</div>
														<div class="col-md">
															<div class="row gutters-10">
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="fs-13 fw-600">{{ translate('Slide Link') }}</label>
																		<input type="text" class="form-control" placeholder="http://" name="home_slider_links[]" value="{{ $decoded_home_slider_links[$key] ?? '' }}">
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="fs-13 fw-600">{{ translate('Hero Title') }}</label>
																		<textarea class="aiz-text-editor hero-title-editor form-control" data-buttons='[["font", ["bold", "underline", "italic", "clear"]], ["color", ["color"]], ["view", ["undo", "redo"]]]' data-min-height="90" placeholder="{{ translate('Large headline shown over this image') }}" name="home_slider_titles[]">{{ $decoded_home_slider_titles[$key] ?? '' }}</textarea>
																	</div>
																</div>
																<div class="col-12">
																	<div class="form-group">
																		<label class="fs-13 fw-600">{{ translate('Hero Paragraph') }}</label>
																		<textarea class="form-control" rows="2" placeholder="{{ translate('Short supporting text shown below the title') }}" name="home_slider_descriptions[]">{{ $decoded_home_slider_descriptions[$key] ?? '' }}</textarea>
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group mb-md-0">
																		<label class="fs-13 fw-600">{{ translate('CTA Button Text') }}</label>
																		<input type="text" class="form-control" placeholder="{{ translate('Shop Now') }}" name="home_slider_cta_texts[]" value="{{ $decoded_home_slider_cta_texts[$key] ?? '' }}">
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group mb-md-0">
																		<label class="fs-13 fw-600">{{ translate('CTA Button Link') }}</label>
																		<input type="text" class="form-control" placeholder="http://" name="home_slider_cta_links[]" value="{{ $decoded_home_slider_cta_links[$key] ?? '' }}">
																	</div>
																</div>
															</div>
														</div>
														<!-- remove parent button -->
														<div class="col-md-auto">
															<div class="form-group mb-md-0">
																<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																	<i class="las la-times"></i>
																</button>
															</div>
														</div>
													</div>
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group">
															<label class="fs-13 fw-600">{{ translate('Slider Image') }}</label>
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																</div>
																<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																<input type="hidden" name="home_slider_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm">
															</div>
														</div>
													</div>
													<div class="col-md">
														<div class="row gutters-10">
															<div class="col-md-6">
																<div class="form-group">
																	<label class="fs-13 fw-600">{{ translate('Slide Link') }}</label>
																	<input type="text" class="form-control" placeholder="http://" name="home_slider_links[]" value="">
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label class="fs-13 fw-600">{{ translate('Hero Title') }}</label>
																	<textarea class="aiz-text-editor hero-title-editor form-control" data-buttons="[[&quot;font&quot;, [&quot;bold&quot;, &quot;underline&quot;, &quot;italic&quot;, &quot;clear&quot;]], [&quot;color&quot;, [&quot;color&quot;]], [&quot;view&quot;, [&quot;undo&quot;, &quot;redo&quot;]]]" data-min-height="90" placeholder="{{ translate('Large headline shown over this image') }}" name="home_slider_titles[]"></textarea>
																</div>
															</div>
															<div class="col-12">
																<div class="form-group">
																	<label class="fs-13 fw-600">{{ translate('Hero Paragraph') }}</label>
																	<textarea class="form-control" rows="2" placeholder="{{ translate('Short supporting text shown below the title') }}" name="home_slider_descriptions[]"></textarea>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group mb-md-0">
																	<label class="fs-13 fw-600">{{ translate('CTA Button Text') }}</label>
																	<input type="text" class="form-control" placeholder="{{ translate('Shop Now') }}" name="home_slider_cta_texts[]" value="">
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group mb-md-0">
																	<label class="fs-13 fw-600">{{ translate('CTA Button Link') }}</label>
																	<input type="text" class="form-control" placeholder="http://" name="home_slider_cta_links[]" value="">
																</div>
															</div>
														</div>
													</div>
													<!-- remove parent button -->
													<div class="col-md-auto">
														<div class="form-group mb-md-0">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
											</div>'
											data-target=".home-slider-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Flash Deals -->
					<div class="tab-pane fade" id="flash_deals" role="tabpanel" aria-labelledby="flash-deals-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="flash_deals">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row gutters-16">
									<!-- Flash Deal Settings -->
									<div class="col-lg-6">
										<div class="p-4 border h-250px h-lg-300px" style="background: #fcfcfc;">
											<p class="fs-14 fw-500 mb-3">{{ translate("Flash Deal Section Settings") }}</p>
											<!-- Background color -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Background color') }}</label>
												<div class="input-group mb-3">
													<input type="hidden" name="types[]" value="flash_deal_bg_color">
													<input type="text" class="form-control aiz-color-input" placeholder="#000000" name="flash_deal_bg_color" value="{{ get_setting('flash_deal_bg_color') }}">
													<div class="input-group-append">
														<span class="input-group-text p-0">
															<input class="aiz-color-picker border-0 size-40px" type="color" value="{{ get_setting('flash_deal_bg_color') ?: '#000000' }}">
														</span>
													</div>
												</div>
											</div>
											<!-- background color checkbox -->
											<div class="form-group d-inline-block">
												<label class="aiz-checkbox">
													<input type="hidden" name="types[]" value="flash_deal_bg_full_width">
													<input type="checkbox" class="check-one" name="flash_deal_bg_full_width" value="1" @if(get_setting('flash_deal_bg_full_width') == 1) checked @endif>
													<span class="fs-13 fw-400">{{ translate('Use background color as full width') }}</span>
													<span class="aiz-square-check"></span>
												</label>
											</div>

											<!-- Banner Text Color -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Flash Deal Banner Text Color') }}</label>
												<div class="input-group mb-3 d-flex">
													@php
														$flash_deal_banner_text_color = get_setting('flash_deal_banner_menu_text');
													@endphp
													<input type="hidden" name="types[]" value="flash_deal_banner_menu_text">
													<div class="radio mar-btm mr-3 d-flex align-items-center">
														<input id="flash_deal_banner_menu_text_light" class="magic-radio" type="radio" name="flash_deal_banner_menu_text" value="light" @if(( $flash_deal_banner_text_color == 'light') || ($flash_deal_banner_text_color == null)) checked @endif>
														<label for="flash_deal_banner_menu_text_light" class="mb-0 ml-2">{{translate('Light')}}</label>
													</div>
													<div class="radio mar-btm mr-3 d-flex align-items-center">
														<input id="flash_deal_banner_menu_text_dark" class="magic-radio" type="radio" name="flash_deal_banner_menu_text" value="dark" @if($flash_deal_banner_text_color == 'dark') checked @endif>
														<label for="flash_deal_banner_menu_text_dark" class="mb-0 ml-2">{{translate('Dark')}}</label>
													</div>
												</div>
											</div>

										</div>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

                    <!-- Flash Deals Navigation -->
					<div class="tab-pane fade" id="flash_deals_navigation" role="tabpanel" aria-labelledby="flash-deals-navigation-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="flash_deals_navigation">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row gutters-16">
									<div class="col-lg-6">
										<div class="p-4 border" style="background: #fcfcfc;">
											<p class="fs-14 fw-500 mb-3">{{ translate("Flash Deals Navigation Activation") }}</p>
											<!-- Activation -->
											<div class="form-group d-flex align-items-center">
												<label class="aiz-switch aiz-switch-success mb-0">
													<input type="hidden" name="types[]" value="flash_deals_navigation_activation">
													<input type="checkbox" name="flash_deals_navigation_activation" value="1" @if(get_setting('flash_deals_navigation_activation') == 1) checked @endif>
													<span></span>
												</label>
												<span class="ml-3 fs-13 fw-400">{{ translate('Activate Flash Deals Navigation') }}</span>
											</div>
										</div>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Today's Deal -->
					<div class="tab-pane fade" id="todays_deal" role="tabpanel" aria-labelledby="todays-deal-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="todays_deal">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row gutters-16">
									<!-- Todays Deal Section Settings -->
									<div class="col-lg-5 order-lg-1 mb-3">
										<div class="p-4 border h-250px h-lg-300px" style="background: #fcfcfc;">
											<p class="fs-14 fw-500">{{ translate("Today's Deal Section Settings") }}</p>
											<!-- background color checkbox -->
											<div class="form-group d-inline-block">
												<label class="aiz-checkbox">
													<input type="hidden" name="types[]" value="todays_deal_section_bg">
													<input type="checkbox" class="check-one" name="todays_deal_section_bg" value="1" @if(get_setting('todays_deal_section_bg') == 1) checked @endif>
													<span class="fs-13 fw-400">{{ translate('Use background color in this section') }}</span>
													<span class="aiz-square-check"></span>
												</label>
											</div>
											<!-- Select Color -->
											<div class="form-group ml-4">
												<label class="col-from-label">{{ translate('Select Color') }}</label>
												<div class="input-group mb-3">
													<input type="hidden" name="types[]" value="todays_deal_section_bg_color">
													<input type="text" class="form-control aiz-color-input" placeholder="#000000" name="todays_deal_section_bg_color" value="{{ get_setting('todays_deal_section_bg_color') }}">
													<div class="input-group-append">
														<span class="input-group-text p-0">
															<input class="aiz-color-picker border-0 size-40px" type="color" value="{{ get_setting('todays_deal_section_bg_color') ?: '#000000' }}">
														</span>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Todays Deal Settings -->
									<div class="col-lg-7">
										<div class="w-100">
											<!-- Title -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Title") }}</label>
												<div class="input-group">
													<input type="hidden" name="types[][{{ $lang }}]" value="todays_deal_title">
													<input type="text" class="form-control" name="todays_deal_title" value="{{ get_setting('todays_deal_title', null, $lang) }}" placeholder="{{ translate('Specially Made For U') }}">
												</div>
											</div>

											<!-- Subtitle / Badge -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Subtitle / Badge") }}</label>
												<div class="input-group">
													<input type="hidden" name="types[][{{ $lang }}]" value="todays_deal_subtitle">
													<input type="text" class="form-control" name="todays_deal_subtitle" value="{{ get_setting('todays_deal_subtitle', null, $lang) }}" placeholder="{{ translate('Today\'s Deal') }}">
												</div>
											</div>

											<!-- Description -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Description") }}</label>
												<div class="input-group">
													<input type="hidden" name="types[][{{ $lang }}]" value="todays_deal_description">
													<textarea class="form-control" name="todays_deal_description" rows="3" placeholder="{{ translate('Featured products selected for today only. The countdown resets every night at midnight.') }}">{{ get_setting('todays_deal_description', null, $lang) }}</textarea>
												</div>
											</div>

											<!-- Large Banner -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Large Banner") }} (<small>{{ translate('Will be shown in large device') }}</small>)</label>
												<div class="input-group " data-toggle="aizuploader" data-type="image">
													<div class="input-group-prepend">
														<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
													</div>
													<div class="form-control file-amount">{{ translate('Choose File') }}</div>
													<input type="hidden" name="types[][{{ $lang }}]" value="todays_deal_banner">
													<input type="hidden" name="todays_deal_banner" value="{{ get_setting('todays_deal_banner', null, $lang) }}" class="selected-files">
												</div>
												<div class="file-preview box"></div>
                                            <small class="text-muted">{{ translate("Minimum dimensions required: 1370px width X 242px height.") }}</small>
											</div>

											<!-- Small Banner -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Small Banner") }} (<small>{{ translate('Will be shown in small device') }}</small>)</label>
												<div class="input-group " data-toggle="aizuploader" data-type="image">
													<div class="input-group-prepend">
														<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
													</div>
													<div class="form-control file-amount">{{ translate('Choose File') }}</div>
													<input type="hidden" name="types[][{{ $lang }}]" value="todays_deal_banner_small">
													<input type="hidden" name="todays_deal_banner_small" value="{{ get_setting('todays_deal_banner_small', null, $lang) }}" class="selected-files">
												</div>
												<div class="file-preview box"></div>
                                            <small class="text-muted">{{ translate("Minimum dimensions required: 400px width X 200px height.") }}</small>
											</div>

											<!-- Products background color -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Products background color') }}</label>
												<div class="input-group">
													@php $todays_deal_bg_color =  get_setting('todays_deal_bg_color') @endphp
													<input type="hidden" name="types[]" value="todays_deal_bg_color">
													<input type="text" class="form-control aiz-color-input" placeholder="#000000" name="todays_deal_bg_color" value="{{ $todays_deal_bg_color }}">
													<div class="input-group-append">
														<span class="input-group-text p-0">
															<input class="aiz-color-picker border-0 size-40px" type="color" value="{{ $todays_deal_bg_color ?: '#000000' }}">
														</span>
													</div>
												</div>
											</div>

											<!-- Banner Text Color -->
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate("Today's Deal Banner Text Color") }}</label>
												<div class="input-group mb-3 d-flex">
													@php
														$todays_deal_banner_text_color =  get_setting('todays_deal_banner_text_color');
													@endphp
													<input type="hidden" name="types[]" value="todays_deal_banner_text_color">
													<div class="radio mar-btm mr-3 d-flex align-items-center">
														<input id="todays_deal_banner_text_light" class="magic-radio" type="radio" name="todays_deal_banner_text_color" value="light" @if(($todays_deal_banner_text_color == 'light') || ($todays_deal_banner_text_color == null)) checked @endif>
														<label for="todays_deal_banner_text_light" class="mb-0 ml-2">{{translate('Light')}}</label>
													</div>
													<div class="radio mar-btm mr-3 d-flex align-items-center">
														<input id="todays_deal_banner_text_dark" class="magic-radio" type="radio" name="todays_deal_banner_text_color" value="dark" @if($todays_deal_banner_text_color == 'dark') checked @endif>
														<label for="todays_deal_banner_text_dark" class="mb-0 ml-2">{{translate('Dark')}}</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Banner Level 1 -->
					<div class="tab-pane fade" id="banner_1" role="tabpanel" aria-labelledby="banner-1-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="banner_1">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_links">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_titles">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_descriptions">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_cta_texts">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner1_collection_ids">
							<input type="hidden" name="types[]" value="home_banner1_status">

							<div class="bg-white p-3 p-sm-2rem">
								<div class="form-group row align-items-center mb-4">
									<label class="col-md-3 col-from-label">{{ translate('Show Banner Level 1') }}</label>
									<div class="col-md-9">
										<label class="aiz-switch aiz-switch-success mb-0">
											<input type="checkbox" name="home_banner1_status" value="1" @if(get_setting('home_banner1_status', '1') == '1') checked @endif>
											<span></span>
										</label>
									</div>
								</div>
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-0">{{ translate('Banner & Links (Max 3)') }}</label>
                                    <div class="small text-muted mb-3">{{ translate("Minimum dimensions required: 436px width X 436px height.") }}</div>

									<!-- Images & links -->
									<div class="home-banner1-target">
										@php
											$home_banner1_images = get_setting('home_banner1_images', null, $lang);
											$home_banner1_links = get_setting('home_banner1_links', null, $lang);
											$home_banner1_titles = json_decode(get_setting('home_banner1_titles', null, $lang), true) ?: [];
											$home_banner1_descriptions = json_decode(get_setting('home_banner1_descriptions', null, $lang), true) ?: [];
											$home_banner1_cta_texts = json_decode(get_setting('home_banner1_cta_texts', null, $lang), true) ?: [];
											$home_banner1_collection_ids = json_decode(get_setting('home_banner1_collection_ids', null, $lang), true) ?: [];
										@endphp
										@if ($home_banner1_images != null)
											@foreach (json_decode($home_banner1_images, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<!-- Image -->
														<div class="col-md-5">
															<div class="form-group mb-md-0">
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_banner1_images[]" class="selected-files" value="{{ json_decode($home_banner1_images, true)[$key] }}">
																</div>
																<div class="file-preview box sm">
																</div>
															</div>
														</div>
														<!-- link -->
														<div class="col-md">
															<div class="form-group mb-md-0">
																<input type="text" class="form-control" placeholder="http://" name="home_banner1_links[]" value="{{ isset(json_decode($home_banner1_links, true)[$key]) ? json_decode($home_banner1_links, true)[$key] : '' }}">
															</div>
														</div>
														<!-- remove parent button -->
														<div class="col-md-auto">
															<div class="form-group mb-md-0">
																<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																	<i class="las la-times"></i>
																</button>
															</div>
														</div>
													</div>
													@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', [
														'bannerKey' => 'home_banner1',
														'bannerTitle' => $home_banner1_titles[$key] ?? '',
														'bannerDescription' => $home_banner1_descriptions[$key] ?? '',
														'bannerCta' => $home_banner1_cta_texts[$key] ?? '',
														'bannerCollectionId' => $home_banner1_collection_ids[$key] ?? '',
													])
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																</div>
																<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																<input type="hidden" name="home_banner1_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm">
															</div>
														</div>
													</div>
													<!-- link -->
													<div class="col-md">
														<div class="form-group mb-md-0 mb-0">
															<input type="text" class="form-control" placeholder="http://" name="home_banner1_links[]" value="">
														</div>
													</div>
													<!-- remove parent button -->
													<div class="col-md-auto">
														<div class="form-group mb-md-0">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
												@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', ['bannerKey' => 'home_banner1'])
											</div>'
											data-target=".home-banner1-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
									<div class="mt-3">
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner1_titles" data-lang="{{ $lang }}">{{ translate('Title Version History') }}</button>
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner1_descriptions" data-lang="{{ $lang }}">{{ translate('Description Version History') }}</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Preorder Banner 1 -->
					<div class="tab-pane fade" id="preorder_banner_1" role="tabpanel" aria-labelledby="preorder-banner-2-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="preorder_banner_1">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_preorder_banner_1_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_preorder_banner_1_links">

							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-0">{{ translate('Banner & Links (Max 3)') }}</label>
									<div class="small text-muted mb-3">{{ translate("Minimum dimensions required: 1370px width X 360px height (If use a single banner).") }}</div>

									<!-- Images & links -->
									<div class="home-preorder_banner_1-target">
										@php
											$home_preorder_banner_1_images = get_setting('home_preorder_banner_1_images', null, $lang);
											$home_preorder_banner_1_links = get_setting('home_preorder_banner_1_links', null, $lang);
										@endphp
										@if ($home_preorder_banner_1_images != null)
											@foreach (json_decode($home_preorder_banner_1_images, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<!-- Image -->
														<div class="col-md-5">
															<div class="form-group mb-md-0">
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_preorder_banner_1_images[]" class="selected-files" value="{{ json_decode($home_preorder_banner_1_images, true)[$key] }}">
																</div>
																<div class="file-preview box sm">
																</div>
															</div>
														</div>
														<!-- link -->
														<div class="col-md">
															<div class="form-group mb-md-0">
																<input type="text" class="form-control" placeholder="http://" name="home_preorder_banner_1_links[]" value="{{ isset(json_decode($home_preorder_banner_1_links, true)[$key]) ? json_decode($home_preorder_banner_1_links, true)[$key] : '' }}">
															</div>
														</div>
														<!-- remove parent button -->
														<div class="col-md-auto">
															<div class="form-group mb-md-0">
																<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																	<i class="las la-times"></i>
																</button>
															</div>
														</div>
													</div>
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																</div>
																<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																<input type="hidden" name="home_preorder_banner_1_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm">
															</div>
														</div>
													</div>
													<!-- link -->
													<div class="col-md">
														<div class="form-group mb-md-0 mb-0">
															<input type="text" class="form-control" placeholder="http://" name="home_preorder_banner_1_links[]" value="">
														</div>
													</div>
													<!-- remove parent button -->
													<div class="col-md-auto">
														<div class="form-group mb-md-0">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
											</div>'
											data-target=".home-preorder_banner_1-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>


					<!-- Banner Level 2 -->
					<div class="tab-pane fade" id="banner_2" role="tabpanel" aria-labelledby="banner-2-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="banner_2">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_links">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_titles">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_descriptions">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_cta_texts">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner2_collection_ids">
							<input type="hidden" name="types[]" value="home_banner2_status">

							<div class="bg-white p-3 p-sm-2rem">
								<div class="form-group row align-items-center mb-4">
									<label class="col-md-3 col-from-label">{{ translate('Show Banner Level 2') }}</label>
									<div class="col-md-9">
										<label class="aiz-switch aiz-switch-success mb-0">
											<input type="checkbox" name="home_banner2_status" value="1" @if(get_setting('home_banner2_status', '1') == '1') checked @endif>
											<span></span>
										</label>
									</div>
								</div>
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-0">{{ translate('Banner & Links (Max 3)') }}</label>
                                    <div class="small text-muted mb-3">{{ translate("Minimum dimensions required: 1370px width X 420px height (If use a single banner).") }}</div>

									<!-- Images & links -->
									<div class="home-banner2-target">
										@php
											$home_banner2_images = get_setting('home_banner2_images', null, $lang);
											$home_banner2_links = get_setting('home_banner2_links', null, $lang);
											$home_banner2_titles = json_decode(get_setting('home_banner2_titles', null, $lang), true) ?: [];
											$home_banner2_descriptions = json_decode(get_setting('home_banner2_descriptions', null, $lang), true) ?: [];
											$home_banner2_cta_texts = json_decode(get_setting('home_banner2_cta_texts', null, $lang), true) ?: [];
											$home_banner2_collection_ids = json_decode(get_setting('home_banner2_collection_ids', null, $lang), true) ?: [];
										@endphp
										@if ($home_banner2_images != null)
											@foreach (json_decode($home_banner2_images, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<!-- Image -->
														<div class="col-md-5">
															<div class="form-group mb-md-0">
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_banner2_images[]" class="selected-files" value="{{ json_decode($home_banner2_images, true)[$key] }}">
																</div>
																<div class="file-preview box sm">
																</div>
															</div>
														</div>
														<!-- link -->
														<div class="col-md">
															<div class="form-group mb-md-0">
																<input type="text" class="form-control" placeholder="http://" name="home_banner2_links[]" value="{{ isset(json_decode($home_banner2_links, true)[$key]) ? json_decode($home_banner2_links, true)[$key] : '' }}">
															</div>
														</div>
														<!-- remove parent button -->
														<div class="col-md-auto">
															<div class="form-group mb-md-0">
																<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																	<i class="las la-times"></i>
																</button>
															</div>
														</div>
													</div>
													@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', [
														'bannerKey' => 'home_banner2',
														'bannerTitle' => $home_banner2_titles[$key] ?? '',
														'bannerDescription' => $home_banner2_descriptions[$key] ?? '',
														'bannerCta' => $home_banner2_cta_texts[$key] ?? '',
														'bannerCollectionId' => $home_banner2_collection_ids[$key] ?? '',
													])
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																</div>
																<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																<input type="hidden" name="home_banner2_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm">
															</div>
														</div>
													</div>
													<!-- link -->
													<div class="col-md">
														<div class="form-group mb-md-0 mb-0">
															<input type="text" class="form-control" placeholder="http://" name="home_banner2_links[]" value="">
														</div>
													</div>
													<!-- remove parent button -->
													<div class="col-md-auto">
														<div class="form-group mb-md-0">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
												@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', ['bannerKey' => 'home_banner2'])
											</div>'
											data-target=".home-banner2-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
									<div class="mt-3">
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner2_titles" data-lang="{{ $lang }}">{{ translate('Title Version History') }}</button>
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner2_descriptions" data-lang="{{ $lang }}">{{ translate('Description Version History') }}</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Banner Level 3 -->
					<div class="tab-pane fade" id="banner_3" role="tabpanel" aria-labelledby="banner-3-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="banner_3">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_links">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_titles">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_descriptions">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_cta_texts">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner3_collection_ids">
							<input type="hidden" name="types[]" value="home_banner3_status">

							<div class="bg-white p-3 p-sm-2rem">
								<div class="form-group row align-items-center mb-4">
									<label class="col-md-3 col-from-label">{{ translate('Show Banner Level 3') }}</label>
									<div class="col-md-9">
										<label class="aiz-switch aiz-switch-success mb-0">
											<input type="checkbox" name="home_banner3_status" value="1" @if(get_setting('home_banner3_status', '1') == '1') checked @endif>
											<span></span>
										</label>
									</div>
								</div>
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-0">{{ translate('Banner & Links (Max 3)') }}</label>
                                    <div class="small text-muted mb-3">{{ translate("Minimum dimensions required: 436px width X 436px height.") }}</div>

									<!-- Images & links -->
									<div class="home-banner3-target">
										@php
											$home_banner3_images = get_setting('home_banner3_images', null, $lang);
											$home_banner3_links = get_setting('home_banner3_links', null, $lang);
											$home_banner3_titles = json_decode(get_setting('home_banner3_titles', null, $lang), true) ?: [];
											$home_banner3_descriptions = json_decode(get_setting('home_banner3_descriptions', null, $lang), true) ?: [];
											$home_banner3_cta_texts = json_decode(get_setting('home_banner3_cta_texts', null, $lang), true) ?: [];
											$home_banner3_collection_ids = json_decode(get_setting('home_banner3_collection_ids', null, $lang), true) ?: [];
										@endphp
										@if ($home_banner3_images != null)
											@foreach (json_decode($home_banner3_images, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<!-- Image -->
														<div class="col-md-5">
															<div class="form-group mb-md-0">
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_banner3_images[]" class="selected-files" value="{{ json_decode($home_banner3_images, true)[$key] }}">
																</div>
																<div class="file-preview box sm">
																</div>
															</div>
														</div>
														<!-- link -->
														<div class="col-md">
															<div class="form-group mb-md-0">
																<input type="text" class="form-control" placeholder="http://" name="home_banner3_links[]" value="{{ isset(json_decode($home_banner3_links, true)[$key]) ? json_decode($home_banner3_links, true)[$key] : '' }}">
															</div>
														</div>
														<!-- remove parent button -->
														<div class="col-md-auto">
															<div class="form-group mb-md-0">
																<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																	<i class="las la-times"></i>
																</button>
															</div>
														</div>
													</div>
													@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', [
														'bannerKey' => 'home_banner3',
														'bannerTitle' => $home_banner3_titles[$key] ?? '',
														'bannerDescription' => $home_banner3_descriptions[$key] ?? '',
														'bannerCta' => $home_banner3_cta_texts[$key] ?? '',
														'bannerCollectionId' => $home_banner3_collection_ids[$key] ?? '',
													])
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																</div>
																<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																<input type="hidden" name="home_banner3_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm">
															</div>
														</div>
													</div>
													<!-- link -->
													<div class="col-md">
														<div class="form-group mb-md-0 mb-0">
															<input type="text" class="form-control" placeholder="http://" name="home_banner3_links[]" value="">
														</div>
													</div>
													<!-- remove parent button -->
													<div class="col-md-auto">
														<div class="form-group mb-md-0">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
												@include('backend.website_settings.pages.metro.partials.promo_banner_text_fields', ['bannerKey' => 'home_banner3'])
											</div>'
											data-target=".home-banner3-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
									<div class="mt-3">
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner3_titles" data-lang="{{ $lang }}">{{ translate('Title Version History') }}</button>
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner3_descriptions" data-lang="{{ $lang }}">{{ translate('Description Version History') }}</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					@if(addon_is_activated('auction'))
					<!-- Auction Banner -->
					<div class="tab-pane fade" id="auction" role="tabpanel" aria-labelledby="auction-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="auction">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-3">{{ translate('Auction Banner') }}</label>
									<!-- Images -->
									<div class="form-group">
										<div class="input-group" data-toggle="aizuploader" data-type="image">
											<div class="input-group-prepend">
												<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
											</div>
											<div class="form-control file-amount">{{ translate('Choose File') }}</div>
											<input type="hidden" name="types[][{{ $lang }}]" value="auction_banner_image">
											<input type="hidden" name="auction_banner_image" class="selected-files" value="{{ get_setting('auction_banner_image', null, $lang) }}">
										</div>
										<div class="file-preview box sm">
										</div>
                                        <small class="text-muted">{{ translate("Minimum dimensions required: 435px width X 485px height.") }}</small>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>
					@endif

					@if(get_setting('coupon_system') == 1)
					<!-- Coupon system -->
					<div class="tab-pane fade" id="coupon" role="tabpanel" aria-labelledby="coupon-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="coupon">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<div class="row gutters-16">

										<!-- Background Image -->
										<div class="col-lg-6">
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Background Image') }}</label>
												<div class="input-group mb-3">
													<div class="input-group" data-toggle="aizuploader" data-type="image">
														<div class="input-group-prepend">
															<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
														</div>
														<div class="form-control file-amount">{{ translate('Choose File') }}</div>
														<input type="hidden" name="types[][{{ $lang }}]" value="coupon_background_image">
														<input type="hidden" name="coupon_background_image" class="selected-files" value="{{ get_setting('coupon_background_image', null, $lang) }}">
													</div>
													<div class="file-preview box sm">
													</div>
                                                    <small>{{ translate('Minimum dimensions required: 552px width X 322px height.') }}</small>
												</div>
											</div>
										</div>

										<!-- Background Color -->
										<div class="col-lg-6">
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Background color') }}</label>
												<div class="input-group mb-3">
													@php $coupon_background_color = get_setting('cupon_background_color') @endphp
													<input type="hidden" name="types[]" value="cupon_background_color">
													<input type="text" class="form-control aiz-color-input" placeholder="#000000" name="cupon_background_color" value="{{ $coupon_background_color }}">
													<div class="input-group-append">
														<span class="input-group-text p-0">
															<input class="aiz-color-picker border-0 size-40px" type="color" value="{{ $coupon_background_color ?: '#000000' }}">
														</span>
													</div>
												</div>
											</div>
										</div>
										<!-- Title -->
										<div class="col-lg-12">
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Title') }}</label>
												<input type="hidden" name="types[][{{ $lang }}]" value="cupon_title">
												<input type="text" class="form-control" placeholder="{{ translate('Title') }}" name="cupon_title" value="{{ get_setting('cupon_title', null, $lang) }}">
											</div>
										</div>
										<!-- Subtitle -->
										<div class="col-12">
											<div class="form-group">
												<label class="col-from-label fs-13 fw-500">{{ translate('Subtitle') }}</label>
												<input type="hidden" name="types[][{{ $lang }}]" value="cupon_subtitle">
												<input type="text" class="form-control" placeholder="{{ translate('Subtitle') }}" name="cupon_subtitle" value="{{ get_setting('cupon_subtitle', null, $lang) }}">
											</div>
										</div>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>
					@endif


					<!-- newestPreorder -->
					<div class="tab-pane fade" id="newestPreorder" role="tabpanel" aria-labelledby="newestPreorder-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="newestPreorder">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="form-group">
									<label class="col-from-label fs-13 fw-500">{{ translate("Banner") }}</label>
									<div class="input-group " data-toggle="aizuploader" data-type="image">
										<div class="input-group-prepend">
											<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
										</div>
										<div class="form-control file-amount">{{ translate('Choose File') }}</div>
										<input type="hidden" name="types[][{{ $lang }}]" value="newest_preorder_banner_image">
										<input type="hidden" name="newest_preorder_banner_image" value="{{ get_setting('newest_preorder_banner_image', null, $lang) }}" class="selected-files">
									</div>
									<div class="file-preview box"></div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Category Wise Products -->
					<div class="tab-pane fade" id="home_categories" role="tabpanel" aria-labelledby="home-categories-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="home_categories">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<input type="hidden" name="types[]" value="home_categories_section_status">
									<input type="hidden" name="home_categories_section_status" value="0">
									<div class="form-group row align-items-center mb-4">
										<label class="col-md-3 col-from-label">{{ translate('Show Category Wise Products') }}</label>
										<div class="col-md-8">
											<label class="aiz-switch aiz-switch-success mb-0">
												<input type="checkbox" name="home_categories_section_status" value="1" @if(get_setting('home_categories_section_status', '1') == '1') checked @endif>
												<span></span>
											</label>
										</div>
									</div>
									<label class="col-from-label fs-13 fw-500 mb-3">{{ translate('Categories') }}</label>
									<div class="home-categories-target">
										<input type="hidden" name="types[]" value="home_categories">
										@php $home_categories = get_setting('home_categories'); @endphp
										@if ($home_categories != null)
											@php $categories = \App\Models\Category::where('parent_id', 0)->with('childrenCategories')->get(); @endphp
											@foreach (json_decode($home_categories, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-5">
														<div class="col">
															<div class="form-group mb-0">
																<select class="form-control aiz-selectpicker" name="home_categories[]" data-live-search="true" data-selected={{ $value }} required>
																	@foreach ($categories as $category)
																		<option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
																		@foreach ($category->childrenCategories as $childCategory)
																			@include('categories.child_category', ['child_category' => $childCategory])
																		@endforeach
																	@endforeach
																</select>
															</div>
														</div>
														<div class="col-auto">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<div class="col">
														<div class="form-group mb-0">
															<select class="form-control aiz-selectpicker" name="home_categories[]" data-live-search="true" required>
																@foreach (\App\Models\Category::where('parent_id', 0)->with('childrenCategories')->get() as $category)
																	<option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
																	@foreach ($category->childrenCategories as $childCategory)
																		@include('categories.child_category', ['child_category' => $childCategory])
																	@endforeach
																@endforeach
															</select>
														</div>
													</div>
													<div class="col-auto">
														<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
															<i class="las la-times"></i>
														</button>
													</div>
												</div>
											</div>'
											data-target=".home-categories-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Classifieds -->
					<div class="tab-pane fade" id="classifieds" role="tabpanel" aria-labelledby="classifieds-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="classifieds">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row">
									<!-- Large Banner -->
									<div class="col-lg-6">
										<div class="form-group">
											<label class="col-from-label fs-13 fw-500">{{ translate("Large Banner") }} (<small>{{ translate('Will be shown in large device') }}</small>)</label>
											<div class="input-group " data-toggle="aizuploader" data-type="image">
												<div class="input-group-prepend">
													<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
												</div>
												<div class="form-control file-amount">{{ translate('Choose File') }}</div>
												<input type="hidden" name="types[][{{ $lang }}]" value="classified_banner_image">
												<input type="hidden" name="classified_banner_image" value="{{ get_setting('classified_banner_image', null, $lang) }}" class="selected-files">
											</div>
											<div class="file-preview box"></div>
										</div>
									</div>
									<!-- Small Banner -->
									<div class="col-lg-6">
										<div class="form-group">
											<label class="col-from-label fs-13 fw-500">{{ translate("Small Banner") }} (<small>{{ translate('Will be shown in small device') }}</small>)</label>
											<div class="input-group " data-toggle="aizuploader" data-type="image">
												<div class="input-group-prepend">
													<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
												</div>
												<div class="form-control file-amount">{{ translate('Choose File') }}</div>
												<input type="hidden" name="types[][{{ $lang }}]" value="classified_banner_image_small">
												<input type="hidden" name="classified_banner_image_small" value="{{ get_setting('classified_banner_image_small', null, $lang) }}" class="selected-files">
											</div>
											<div class="file-preview box"></div>
										</div>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Top Brands -->
					<div class="tab-pane fade" id="brands" role="tabpanel" aria-labelledby="brands-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="brands">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-3">{{ translate('Top Brands (Max 12)') }}</label>
									<!-- Brands -->
									<div class="form-group">
										<input type="hidden" name="types[]" value="top_brands">
										<select name="top_brands[]" class="form-control aiz-selectpicker" multiple data-max-options="12" data-live-search="true" data-selected="{{ get_setting('top_brands') }}">
											@foreach (\App\Models\Brand::all() as $key => $brand)
												<option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Inspiration Articles -->
					<div class="tab-pane fade" id="inspiration_articles" role="tabpanel" aria-labelledby="inspiration-articles-tab">
						<form action="{{ route('business_settings.update') }}" method="POST">
							@csrf
							<input type="hidden" name="tab" value="inspiration_articles">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="w-100">
									<input type="hidden" name="types[]" value="home_inspiration_section_status">
									<input type="hidden" name="home_inspiration_section_status" value="0">
									<div class="form-group row align-items-center mb-4">
										<label class="col-md-3 col-from-label">{{ translate('Show Inspiration & Conseils') }}</label>
										<div class="col-md-8">
											<label class="aiz-switch aiz-switch-success mb-0">
												<input type="checkbox" name="home_inspiration_section_status" value="1" @if(get_setting('home_inspiration_section_status', '1') == '1') checked @endif>
												<span></span>
											</label>
										</div>
									</div>

									<div class="form-group">
										<input type="hidden" name="types[]" value="home_inspiration_blog_ids">
										<input type="hidden" name="home_inspiration_blog_ids[]" value="">
										<label class="col-from-label fs-13 fw-500 mb-2">{{ translate('Articles to Show') }} ({{ translate('Max 6') }})</label>
										<select name="home_inspiration_blog_ids[]" class="form-control aiz-selectpicker" multiple data-max-options="6" data-live-search="true" data-selected-text-format="count" data-selected="{{ get_setting('home_inspiration_blog_ids') }}">
											@foreach (\App\Models\Blog::published()->with('translations')->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->limit(100)->get() as $blog)
												<option value="{{ $blog->id }}">{{ $blog->getTranslation('title') }}</option>
											@endforeach
										</select>
										<small class="text-muted d-block mt-2">{{ translate('Leave empty to automatically show the latest 6 published blog articles.') }}</small>
									</div>
								</div>
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Category Icon Navigation -->
					<div class="tab-pane fade" id="category_icon_navigation" role="tabpanel" aria-labelledby="category-icon-navigation-tab">
						<form action="{{ route('business_settings.update') }}" method="POST">
							@csrf
							<input type="hidden" name="tab" value="category_icon_navigation">
							<input type="hidden" name="types[]" value="category_icon_navigation_status">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row gutters-16">
									<div class="col-lg-6">
										<div class="p-4 border" style="background: #fcfcfc;">
											<div class="form-group row align-items-center">
												<label class="col-md-7 col-from-label">{{ translate('Show Category Icon Navigation') }}</label>
												<div class="col-md-5">
													<label class="aiz-switch aiz-switch-success mb-0">
														<input type="checkbox" name="category_icon_navigation_status" value="1" @if(get_setting('category_icon_navigation_status', '1') == '1') checked @endif>
														<span></span>
													</label>
												</div>
											</div>
											<p class="fs-14 fw-500 mb-0">{{ translate("To set Category Icon Navigation on the homepage, first enable 'Hot Category' from the Category Listing page. Only the enabled categories will appear in this section.") }}
												 <br>{{ translate("Set Hot categories") }}<a href="{{ route('categories.index') }}"> {{ translate('Here') }}</a> 
											</p>
										</div>
									</div>
								</div>
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Collections Split -->
					<div class="tab-pane fade" id="collections_split" role="tabpanel" aria-labelledby="collections-split-tab">
						@php
							$metroCollectionPanels = [
								'newest' => [
									'label' => translate('New Collections Panel'),
									'eyebrow' => translate('Newest products'),
									'title' => translate('Nouvelles collections'),
									'description' => translate('Découvrez une sélection exclusive de mobilier et décoration où design contemporain, confort et raffinement se rencontrent.'),
									'cta_text' => translate('View All'),
									'cta_link' => route('search', ['sort_by' => 'newest']),
								],
								'best_selling' => [
									'label' => translate('Best Selling Panel'),
									'eyebrow' => translate('Best sellers'),
									'title' => translate("L’art de vivre commence chez vous"),
									'description' => translate('Les meilleures ventes qui font la tendance cette saison.'),
									'cta_text' => translate('View All'),
									'cta_link' => route('search', ['sort_by' => 'popular']),
								],
							];
						@endphp
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="collections_split">
							<input type="hidden" name="types[]" value="metro_collections_section_status">
							<input type="hidden" name="metro_collections_section_status" value="0">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_newest_image">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_newest_title">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_newest_description">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_newest_cta_text">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_newest_cta_link">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_best_selling_image">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_best_selling_title">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_best_selling_description">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_best_selling_cta_text">
							<input type="hidden" name="types[][{{ $lang }}]" value="metro_collections_best_selling_cta_link">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="row gutters-16">
									<div class="col-lg-8">
										<div class="p-4 border" style="background: #fcfcfc;">
											<div class="form-group row align-items-center">
												<label class="col-md-7 col-from-label">{{ translate('Show Collections Split Section') }}</label>
												<div class="col-md-5">
													<label class="aiz-switch aiz-switch-success mb-0">
														<input type="checkbox" name="metro_collections_section_status" value="1" @if(get_setting('metro_collections_section_status', '1') == '1') checked @endif>
														<span></span>
													</label>
												</div>
											</div>
											<p class="fs-14 fw-500 mb-0">{{ translate('Each panel uses a large background image, editable title, description and CTA, with a compact autoplay product slider displayed at the bottom.') }}</p>
											<a href="{{ route('product-collections.index') }}" class="btn btn-soft-primary btn-sm mt-3">
												<i class="las la-layer-group mr-1"></i>{{ translate('Manage Product Collections') }}
											</a>
										</div>
									</div>
								</div>
								<div class="row gutters-16 mt-4">
									@foreach ($metroCollectionPanels as $panelKey => $panelDefaults)
										@php
											$panelImage = get_setting('metro_collections_' . $panelKey . '_image', null, $lang);
											$panelTitle = get_setting('metro_collections_' . $panelKey . '_title', null, $lang) ?: $panelDefaults['title'];
											$panelDescription = get_setting('metro_collections_' . $panelKey . '_description', null, $lang) ?: $panelDefaults['description'];
											$panelCtaText = get_setting('metro_collections_' . $panelKey . '_cta_text', null, $lang) ?: $panelDefaults['cta_text'];
											$panelCtaLink = get_setting('metro_collections_' . $panelKey . '_cta_link', null, $lang) ?: $panelDefaults['cta_link'];
										@endphp
										<div class="col-xl-6 mb-3">
											<div class="p-4 border h-100" style="background: #fcfcfc;">
												<div class="d-flex align-items-start justify-content-between mb-3">
													<div>
														<span class="badge badge-inline badge-soft-primary mb-2">{{ $panelDefaults['eyebrow'] }}</span>
														<h3 class="fs-16 fw-700 mb-1">{{ $panelDefaults['label'] }}</h3>
														<p class="fs-12 text-muted mb-0">{{ translate('This copy appears over the panel image on the homepage.') }}</p>
													</div>
												</div>
												<div class="form-group">
													<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Background Image') }}</label>
													<div class="input-group" data-toggle="aizuploader" data-type="image">
														<div class="input-group-prepend">
															<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
														</div>
														<div class="form-control file-amount">{{ translate('Choose File') }}</div>
														<input type="hidden" name="metro_collections_{{ $panelKey }}_image" class="selected-files" value="{{ $panelImage }}">
													</div>
													<div class="file-preview box sm"></div>
												</div>
												<div class="form-group">
													<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Title') }}</label>
													<input type="text" class="form-control" name="metro_collections_{{ $panelKey }}_title" value="{{ $panelTitle }}" placeholder="{{ $panelDefaults['title'] }}">
													<small class="form-text text-muted">{{ translate('Main heading displayed in the collection panel.') }}</small>
												</div>
												<div class="form-group">
													<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Description') }}</label>
													<textarea class="form-control" rows="3" name="metro_collections_{{ $panelKey }}_description" placeholder="{{ $panelDefaults['description'] }}">{{ $panelDescription }}</textarea>
													<small class="form-text text-muted">{{ translate('Short supporting text shown under the title.') }}</small>
												</div>
												<div class="row gutters-10">
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Button Text') }}</label>
															<input type="text" class="form-control" name="metro_collections_{{ $panelKey }}_cta_text" value="{{ $panelCtaText }}" placeholder="{{ $panelDefaults['cta_text'] }}">
														</div>
													</div>
													<div class="col-md-7">
														<div class="form-group mb-0">
															<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Link') }}</label>
															<input type="text" class="form-control" placeholder="{{ $panelDefaults['cta_link'] }}" name="metro_collections_{{ $panelKey }}_cta_link" value="{{ $panelCtaLink }}">
															<small class="form-text text-muted">{{ translate('Use a full URL or a storefront path such as /search?sort_by=newest.') }}</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									@endforeach
								</div>
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Marketplace Banner -->
					<div class="tab-pane fade" id="marketplace_banner" role="tabpanel" aria-labelledby="marketplace-banner-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="marketplace_banner">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_images">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_links">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_titles">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_descriptions">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_cta_texts">
							<input type="hidden" name="types[][{{ $lang }}]" value="home_banner4_collection_ids">
							<input type="hidden" name="types[]" value="home_banner4_status">

							<div class="bg-white p-3 p-sm-2rem">
								<!-- Toggle -->
								<div class="form-group row align-items-center mb-4">
									<label class="col-md-3 col-from-label">{{ translate('Show Marketplace Banner') }}</label>
									<div class="col-md-9">
										<label class="aiz-switch aiz-switch-success mb-0">
											<input type="checkbox" name="home_banner4_status" value="1" @if(get_setting('home_banner4_status', '1') == '1') checked @endif>
											<span></span>
										</label>
									</div>
								</div>

								<div class="w-100">
									<label class="col-from-label fs-13 fw-500 mb-0">{{ translate('Banner Items') }}</label>
									<div class="small text-muted mb-3">{{ translate("Each banner has an image with overlaid title, description and call-to-action button.") }}</div>

									<!-- Banner items -->
									<div class="home-banner4-target">
										@php
											$home_banner4_images = get_setting('home_banner4_images', null, $lang);
											$home_banner4_links = get_setting('home_banner4_links', null, $lang);
											$home_banner4_titles = get_setting('home_banner4_titles', null, $lang);
											$home_banner4_descriptions = get_setting('home_banner4_descriptions', null, $lang);
											$home_banner4_cta_texts = get_setting('home_banner4_cta_texts', null, $lang);
											$home_banner4_collection_ids = get_setting('home_banner4_collection_ids', null, $lang);
										@endphp
										@if ($home_banner4_images != null)
											@foreach (json_decode($home_banner4_images, true) as $key => $value)
												<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
													<div class="row gutters-10">
														<!-- Image -->
														<div class="col-md-6">
															<div class="form-group">
																<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Banner Image') }}</label>
																<div class="input-group" data-toggle="aizuploader" data-type="image">
																	<div class="input-group-prepend">
																		<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
																	</div>
																	<div class="form-control file-amount">{{ translate('Choose File') }}</div>
																	<input type="hidden" name="home_banner4_images[]" class="selected-files" value="{{ json_decode($home_banner4_images, true)[$key] }}">
																</div>
																<div class="file-preview box sm"></div>
															</div>
														</div>
														<!-- Remove button -->
														<div class="col-md-6 text-right">
															<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
																<i class="las la-times"></i>
															</button>
														</div>
													</div>
													<div class="row gutters-10">
														<!-- H2 Title -->
														<div class="col-md-6">
															<div class="form-group">
																<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('H2 Title') }}</label>
																<textarea class="form-control" rows="3" placeholder="{{ translate('Headline shown over this banner') }}" name="home_banner4_titles[]">{{ isset(json_decode($home_banner4_titles ?? '[]', true)[$key]) ? trim(strip_tags(app(\App\Services\BannerTextSanitizerService::class)->sanitize(json_decode($home_banner4_titles, true)[$key]))) : '' }}</textarea>
															</div>
														</div>
														<!-- Description -->
														<div class="col-md-6">
															<div class="form-group">
																<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Description') }}</label>
																<textarea class="form-control" rows="3" placeholder="{{ translate('Supporting text shown below the title') }}" name="home_banner4_descriptions[]">{{ isset(json_decode($home_banner4_descriptions ?? '[]', true)[$key]) ? trim(strip_tags(app(\App\Services\BannerTextSanitizerService::class)->sanitize(json_decode($home_banner4_descriptions, true)[$key]))) : '' }}</textarea>
															</div>
														</div>
													</div>
													<div class="row gutters-10">
														<!-- CTA Text -->
														<div class="col-md-4">
															<div class="form-group">
																<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Button Text') }}</label>
																<input type="text" class="form-control" placeholder="{{ translate('e.g. Acheter maintenant') }}" name="home_banner4_cta_texts[]" value="{{ isset(json_decode($home_banner4_cta_texts ?? '[]', true)[$key]) ? json_decode($home_banner4_cta_texts, true)[$key] : '' }}">
															</div>
														</div>
														<!-- CTA Link -->
														<div class="col-md-8">
															<div class="form-group">
																<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Link') }}</label>
																<input type="text" class="form-control" placeholder="http://" name="home_banner4_links[]" value="{{ isset(json_decode($home_banner4_links ?? '[]', true)[$key]) ? json_decode($home_banner4_links, true)[$key] : '' }}">
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Product Collection') }}</label>
														<select class="form-control aiz-selectpicker" name="home_banner4_collection_ids[]" data-live-search="true">
															<option value="">{{ translate('Use custom URL') }}</option>
															@foreach (\App\Models\ProductCollection::orderBy('name')->get() as $productCollection)
																<option value="{{ $productCollection->id }}" @selected((string) (json_decode($home_banner4_collection_ids ?? '[]', true)[$key] ?? '') === (string) $productCollection->id)>{{ $productCollection->name }}</option>
															@endforeach
														</select>
													</div>
													<div class="text-right">
														<button type="button" class="btn btn-soft-primary btn-sm js-banner-preview">
															<i class="las la-eye mr-1"></i>{{ translate('Preview Banner') }}
														</button>
													</div>
												</div>
											@endforeach
										@endif
									</div>

									<!-- Add button -->
									<div class="">
										<button
											type="button"
											class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center" style="background: #fcfcfc;"
											data-toggle="add-more"
											data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-10">
													<div class="col-md-6">
														<div class="form-group">
															<label class="fs-12 fw-600 text-uppercase text-muted">Banner Image</label>
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>
																</div>
																<div class="form-control file-amount">Choose File</div>
																<input type="hidden" name="home_banner4_images[]" class="selected-files" value="">
															</div>
															<div class="file-preview box sm"></div>
														</div>
													</div>
													<div class="col-md-6 text-right">
														<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".remove-parent">
															<i class="las la-times"></i>
														</button>
													</div>
												</div>
												<div class="row gutters-10">
													<div class="col-md-6">
														<div class="form-group">
															<label class="fs-12 fw-600 text-uppercase text-muted">H2 Title</label>
															<textarea class="form-control" rows="3" placeholder="Headline shown over this banner" name="home_banner4_titles[]"></textarea>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="fs-12 fw-600 text-uppercase text-muted">Description</label>
															<textarea class="form-control" rows="3" placeholder="Supporting text shown below the title" name="home_banner4_descriptions[]"></textarea>
														</div>
													</div>
												</div>
												<div class="row gutters-10">
													<div class="col-md-4">
														<div class="form-group">
															<label class="fs-12 fw-600 text-uppercase text-muted">CTA Button Text</label>
															<input type="text" class="form-control" placeholder="Acheter maintenant" name="home_banner4_cta_texts[]" value="">
														</div>
													</div>
														<div class="col-md-8">
														<div class="form-group">
															<label class="fs-12 fw-600 text-uppercase text-muted">CTA Link</label>
															<input type="text" class="form-control" placeholder="http://" name="home_banner4_links[]" value="">
														</div>
													</div>
													<div class="form-group">
														<label class="fs-12 fw-600 text-uppercase text-muted">CTA Product Collection</label>
														<select class="form-control aiz-selectpicker" name="home_banner4_collection_ids[]" data-live-search="true">
															<option value="">Use custom URL</option>
															@foreach (\App\Models\ProductCollection::orderBy('name')->get() as $productCollection)
																<option value="{{ $productCollection->id }}">{{ $productCollection->name }}</option>
															@endforeach
														</select>
													</div>
												</div>
												<div class="text-right">
													<button type="button" class="btn btn-soft-primary btn-sm js-banner-preview">
														<i class="las la-eye mr-1"></i>Preview Banner
													</button>
												</div>
											</div>'
											data-target=".home-banner4-target">
											<i class="las la-2x text-success la-plus-circle"></i>
											<span class="ml-2">{{ translate('Add New') }}</span>
										</button>
									</div>
									<div class="mt-3">
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner4_titles" data-lang="{{ $lang }}">{{ translate('Title Version History') }}</button>
										<button type="button" class="btn btn-soft-secondary btn-sm js-banner-history" data-setting-key="home_banner4_descriptions" data-lang="{{ $lang }}">{{ translate('Description Version History') }}</button>
									</div>
								</div>
								<!-- Save Button -->
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Top Sellers -->
					<div class="tab-pane fade" id="top_sellers" role="tabpanel" aria-labelledby="top-sellers-tab">
						<div class="bg-white p-3 p-sm-2rem text-center">
							<p class="fs-14 fw-500">{{ translate("Top sellers are displayed automatically based on vendor stats.") }}</p>
						</div>
					</div>


					<!-- Promotional Category -->
					<div class="tab-pane fade" id="promotional_category" role="tabpanel" aria-labelledby="promotional-category-tab">
						<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="tab" value="promotional_category">
							<div class="bg-white p-3 p-sm-2rem">
								<div class="card shadow-none bg-light">
									<div class="card-header border-bottom-0">
										<h6 class="mb-0">{{ translate('Promotional Category Settings') }}</h6>
									</div>
									<div class="card-body">
										<input type="hidden" name="types[]" value="promoted_category_status">
										<div class="form-group row">
											<label class="col-md-3 col-from-label">{{translate('Enable Promotional Section')}}</label>
											<div class="col-md-8">
												<label class="aiz-switch aiz-switch-success mb-0">
													<input type="checkbox" name="promoted_category_status" value="1" @if(get_setting('promoted_category_status') == '1') checked @endif>
													<span></span>
												</label>
											</div>
										</div>
										
										<input type="hidden" name="types[]" value="promoted_category_id">
										<div class="form-group row">
											<label class="col-md-3 col-from-label">{{translate('Select Category to Promote')}}</label>
											<div class="col-md-8">
												<select class="form-control aiz-selectpicker" name="promoted_category_id" id="promoted_category_id" data-live-search="true">
													<option value="">{{ translate('Select Category') }}</option>
													@foreach (\App\Models\Category::all() as $category)
														<option value="{{ $category->id }}" @if(get_setting('promoted_category_id') == $category->id) selected @endif>{{ $category->getTranslation('name') }}</option>
													@endforeach
												</select>
											</div>
										</div>

										<input type="hidden" name="types[][{{ $lang }}]" value="promoted_category_subtitle">
										<div class="form-group row">
											<label class="col-md-3 col-from-label">{{ translate('Category Subtitle') }}</label>
											<div class="col-md-8">
												<textarea class="form-control" name="promoted_category_subtitle" rows="3" placeholder="{{ translate('Des espaces inspirants pour plus d’efficacité Découvrez notre sélection exclusive de mobilier de bureau alliant design, confort et fonctionnalité.') }}">{{ get_setting('promoted_category_subtitle', null, $lang) }}</textarea>
												<small class="text-muted">{{ translate('This text appears as the H3 subtitle below the selected promotional category title.') }}</small>
											</div>
										</div>

										<div class="form-group row">
											<div class="col-md-12">
												<h6 class="mb-3">{{ translate('Set Per-Product Discounts for this Category') }}</h6>
												<div id="promotional-products-table" class="bg-white p-3 rounded border">
													<!-- AJAX Loaded Content -->
													<p class="text-muted text-center py-4">{{ translate('Select a category to load products.') }}</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="mt-4 text-right">
									<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
								</div>
							</div>
						</form>
					</div>

				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="bannerTextPreviewModal" tabindex="-1" role="dialog" aria-labelledby="bannerTextPreviewLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="bannerTextPreviewLabel">{{ translate('Banner Preview') }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="banner-editor-preview position-relative overflow-hidden bg-dark text-white">
						<img class="js-banner-preview-image w-100" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" alt="{{ translate('Banner preview') }}">
						<div class="position-absolute w-100 h-100 top-0 left-0 d-flex flex-column align-items-center justify-content-center text-center p-4" style="background: rgba(0, 0, 0, .2);">
							<h2 class="js-banner-preview-title fw-700 mb-3"></h2>
							<p class="js-banner-preview-description mb-3"></p>
							<div class="js-banner-preview-cta border-bottom border-white pb-1"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="bannerTextHistoryModal" tabindex="-1" role="dialog" aria-labelledby="bannerTextHistoryLabel" aria-hidden="true"
		data-history-url="{{ route('banner_versions.index', '__SETTING__') }}"
		data-restore-url="{{ route('banner_versions.restore', '__VERSION__') }}">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="bannerTextHistoryLabel">{{ translate('Version History') }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="js-banner-history-status text-muted"></div>
					<div class="list-group js-banner-history-list"></div>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('script')
	<script src="{{ static_asset('assets/js/banner-text-editor.js') }}"></script>
    <script type="text/javascript">
		$(document).ready(function(){
		    AIZ.plugins.bootstrapSelect('refresh');
		});
    </script>
	<script>
		$(document).ready(function(){
			var hash = document.location.hash;
			if (hash) {
				$('.nav-tabs a[href="'+hash+'"]').tab('show');
			}else{
				$('.nav-tabs a[href="#home_slider"]').tab('show');
			}

			// Change hash for page-reload
			$('.nav-tabs a').on('shown.bs.tab', function (e) {
				window.location.hash = e.target.hash;
			});

			$(document).on('click', '[data-target=".home-slider-target"]', function () {
				setTimeout(function () {
					initHeroTitleEditors($('.home-slider-target'));
				}, 80);
			});

			$(document).on('submit', 'form', function () {
				if ($(this).find('textarea.hero-title-editor').length) {
					syncHeroTitleEditors($(this));
				}
			});
		});

		function initHeroTitleEditors(context) {
			$(context).find('textarea.hero-title-editor').each(function () {
				var editor = $(this);
				if (editor.next('.note-editor').length) {
					return;
				}

				editor.summernote({
					toolbar: [
						['font', ['bold', 'underline', 'italic', 'clear']],
						['color', ['color']],
						['view', ['undo', 'redo']]
					],
					placeholder: editor.attr('placeholder') || '',
					disableDragAndDrop: true,
					height: editor.data('min-height') || 90,
					callbacks: {
						onChange: function (contents) {
							editor.val(contents);
						}
					}
				});
			});
		}

		function syncHeroTitleEditors(context) {
			$(context).find('textarea.hero-title-editor').each(function () {
				var editor = $(this);
				if (editor.data('summernote') || editor.next('.note-editor').length) {
					editor.val(editor.summernote('code'));
				}
			});
		}

	    function loadPromotionalProducts(categoryId) {
	        if(!categoryId) {
	            $('#promotional-products-table').html('<p class="text-muted text-center py-4">{{ translate('Select a category to load products.') }}</p>');
	            return;
	        }
	        $('#promotional-products-table').html('<div class="text-center py-3"><i class="las la-spinner la-spin la-3x"></i></div>');
	        
	        $.post('{{ route('promotional_category.products') }}', {
	            _token: '{{ csrf_token() }}',
	            category_id: categoryId
	        }, function(data){
	            $('#promotional-products-table').html(data);
	        });
	    }

	    $('#promoted_category_id').on('change', function(){
	         loadPromotionalProducts($(this).val());
	    });

	    // Load initially if set
	    if($('#promoted_category_id').val()) {
	        loadPromotionalProducts($('#promoted_category_id').val());
	    }
	    
	    // Handle inline update
	    $(document).on('click', '.btn-update-discount', function() {
	        var btn = $(this);
	        var tr = btn.closest('tr');
	        var productId = btn.data('id');
	        var discount = tr.find('.input-discount').val();
	        var discountType = tr.find('.select-discount-type').val();
	        
	        btn.html('<i class="las la-spinner la-spin"></i>').prop('disabled', true);
	        
	        $.post('{{ route('promotional_category.update_discounts') }}', {
	            _token: '{{ csrf_token() }}',
	            product_id: productId,
	            discount: discount,
	            discount_type: discountType
	        }, function(response){
	            btn.html('{{ translate('Updated') }}').removeClass('btn-primary').addClass('btn-success');
	            setTimeout(function(){
	                btn.html('{{ translate('Update') }}').removeClass('btn-success').addClass('btn-primary').prop('disabled', false);
	            }, 2000);
	        }).fail(function() {
	            alert('{{ translate('Failed to update discount') }}');
	            btn.html('{{ translate('Update') }}').prop('disabled', false);
	        });
	    });
	</script>
@endsection


