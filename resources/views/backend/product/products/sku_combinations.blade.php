@php
	$dimensionChoiceId = collect(request('choice_no', []))->first(function ($choiceId) {
		$attribute = \App\Models\Attribute::find($choiceId);

		return (int) $choiceId === 35 || strtolower((string) optional($attribute)->name) === 'dimension';
	});
	$dimensionSkuRowsEnabled = $dimensionChoiceId && count(request('choice_no', [])) === 1 && (int) $colors_active !== 1;
@endphp
@if(count($combinations) > 0)
<div class="table-responsive">
	<table class="table table-bordered sku-dimension-table @if(!$dimensionSkuRowsEnabled) aiz-table @endif" @if($dimensionSkuRowsEnabled) data-dimension-choice-input-name="choice_options_{{ $dimensionChoiceId }}[]" @endif>
	<thead>
		<tr>
			<td class="text-center">
				{{translate('Variant')}}
			</td>
			<td class="text-center">
				{{translate('Variant Price')}}
			</td>
			<td class="text-center" data-breakpoints="lg">
				{{translate('SKU')}}
			</td>
			<td class="text-center" data-breakpoints="lg">
				{{translate('Quantity')}}
			</td>
			<td class="text-center" data-breakpoints="lg">
				{{translate('Dimensions (L x W x H)')}}
			</td>
			<td class="text-center" data-breakpoints="lg">
				{{translate('Unit')}}
			</td>
			<td class="text-center" data-breakpoints="lg">
				{{translate('Photo')}}
			</td>
		</tr>
	</thead>
	<tbody>
	@foreach ($combinations as $key => $combination)
		@php
			$sku = '';
			foreach (explode(' ', $product_name) as $key => $value) {
				$sku .= substr($value, 0, 1);
			}

			$str = '';
			foreach ($combination as $key => $item){
				if($key > 0 ){
					$str .= '-'.str_replace(' ', '', $item);
					$sku .='-'.str_replace(' ', '', $item);
				}
				else{
					if($colors_active == 1){
						$color_name = \App\Models\Color::where('code', $item)->first()->name;
						$str .= $color_name;
						$sku .='-'.$color_name;
					}
					else{
						$str .= str_replace(' ', '', $item);
						$sku .='-'.str_replace(' ', '', $item);
					}
				}
			}
		@endphp
		@if(strlen($str) > 0)
			<tr class="variant">
				<td>
					<div class="sku-dimension-variant-cell">
						<label for="" class="control-label mb-0 sku-dimension-variant-label" data-variant-key="{{ strtolower(preg_replace('/\s+/', '', $str)) }}">{{ $str }}</label>
						@if($dimensionSkuRowsEnabled)
							<div class="sku-dimension-variant-actions">
								<button type="button" class="btn btn-icon btn-soft-danger btn-sm" onclick="skuDimensionRemoveRow(this)" data-variant="{{ $str }}" aria-label="{{ translate('Remove dimension variant') }}" title="{{ translate('Remove dimension variant') }}">
									<i class="las la-trash"></i>
								</button>
								<button type="button" class="btn btn-icon btn-primary btn-sm" onclick="skuDimensionAddRow(this)" aria-label="{{ translate('Add dimension variant') }}" title="{{ translate('Add dimension variant') }}">
									<i class="las la-plus"></i>
								</button>
							</div>
						@endif
					</div>
				</td>
				<td>
					<input type="number" lang="en" name="price_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="{{ $unit_price }}" min="0" step="0.01" class="form-control" required>
				</td>
				<td>
					<input type="text" name="sku_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="" class="form-control">
				</td>
				<td>
					<input type="number" lang="en" name="qty_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="10" min="0" step="1" class="form-control" required>
				</td>
				<td>
					<div class="row gutters-5">
						<div class="col">
							<input type="number" lang="en" name="length_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('L') }}">
						</div>
						<div class="col">
							<input type="number" lang="en" name="width_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('W') }}">
						</div>
						<div class="col">
							<input type="number" lang="en" name="height_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('H') }}">
						</div>
					</div>
				</td>
				<td>
					<select name="unit_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" class="form-control aiz-selectpicker">
						<option value="cm">cm</option>
						<option value="inch">inch</option>
					</select>
				</td>
				<td>
					<div class=" input-group " data-toggle="aizuploader" data-type="image">
						<div class="input-group-prepend">
							<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
						</div>
						<div class="form-control file-amount text-truncate">{{ translate('Choose File') }}</div>
						<input type="hidden" name="img_{{ $str }}@if($dimensionSkuRowsEnabled)[]@endif" class="selected-files">
					</div>
					<div class="file-preview box sm"></div>
				</td>
			</tr>
		@endif
	@endforeach
	</tbody>
	</table>
</div>
@if($dimensionSkuRowsEnabled)
	@include('backend.product.products.sku_dimension_variant_assets', ['unit_price' => $unit_price])
@endif
@endif
