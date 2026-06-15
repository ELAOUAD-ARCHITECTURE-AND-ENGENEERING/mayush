{{-- Visual Search Hidden Form --}}
<form id="visual-search-form" action="{{ route('search.visual') }}" method="POST" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" id="visual-search-input" name="visual_image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="handleVisualSearch(this)">
</form>

{{-- Visual Search Loading Overlay --}}
<div id="visual-search-overlay" class="visual-search-overlay d-none">
    <div class="visual-search-loading">
        <div class="visual-search-spinner"></div>
        <p class="mt-3 text-white fw-600 fs-16">{{ translate('Analyzing your image...') }}</p>
        <p class="text-white opacity-70 fs-13">{{ translate('Our AI is identifying products in your photo') }}</p>
    </div>
</div>

<style>
/* Visual Search Camera Button */
.visual-search-btn {
    padding: 2px 6px;
    color: #b5b5bf;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
.visual-search-btn:hover {
    color: var(--primary);
    transform: scale(1.15);
}
.visual-search-btn i {
    font-size: 20px;
}

/* Visual Search Loading Overlay */
.visual-search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.visual-search-loading {
    text-align: center;
    animation: fadeInUp 0.3s ease;
}
.visual-search-spinner {
    width: 56px;
    height: 56px;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function handleVisualSearch(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            AIZ.plugins.notify('danger', '{{ translate("Image must be less than 5MB") }}');
            input.value = '';
            return;
        }
        // Show loading overlay
        document.getElementById('visual-search-overlay').classList.remove('d-none');
        // Submit the form
        document.getElementById('visual-search-form').submit();
    }
}
</script>
