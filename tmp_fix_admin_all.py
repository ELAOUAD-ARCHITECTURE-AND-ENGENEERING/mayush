import os
import re

directories = ['minima', 'metro', 'megamart', 'classic']

pane_template = """
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
													@foreach (\\App\\Models\\Category::all() as $category)
														<option value="{{ $category->id }}" @if(get_setting('promoted_category_id') == $category->id) selected @endif>{{ $category->getTranslation('name') }}</option>
													@endforeach
												</select>
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
"""

script_block = """
	<script>
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
"""

for d in directories:
    path = f"c:\\\\xampp\\\\htdocs\\\\mayush\\\\resources\\\\views\\\\backend\\\\website_settings\\\\pages\\\\{d}\\\\home_page_edit.blade.php"
    if not os.path.exists(path):
        continue
    
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 1. Check if pane exists
    if 'id="promotional_category"' not in content:
        # Insert before the end of the tab-content div
        # In these files, tab-content usually ends with:
        # 					</div>
        # 				</div>
        # 			</div>
        # 		</div>
        # 	</div>
        # @endsection
        
        # We look for the </div> that comes after the last </div>\\s+</div> of the last tab pane and before the final closing wrappers.
        content = re.sub(r'(\\t\\t\\t\\t</div>\\s+</div>\\s+</div>\\s+</div>\\s+@endsection)', pane_template + r'\\1', content, flags=re.MULTILINE)
        
    # 2. Add script if needed
    if 'function loadPromotionalProducts' not in content:
        content = content.replace('@endsection', script_block + '\\n@endsection', 1)
        
    # 3. Final cleanup - remove the trash script at the end if it exists
    # Find anything after the final @endsection but it might not be @endsection if scripts were added after it.
    # Actually, let's just find the pattern of the trash script and remove it.
    trash_pattern = r'<script>\\s*\\$\\(document\\)\\.ready\\(function\\(\\)\\{\\s*function loadPromotionalProducts.*?\\n</script>\\s*$'
    content = re.sub(trash_pattern, '', content, flags=re.DOTALL)
    
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Fixed {path}")
