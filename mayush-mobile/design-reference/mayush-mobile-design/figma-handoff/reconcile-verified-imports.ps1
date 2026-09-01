param(
  [int]$LastVerifiedIndex = 52
)

$handoffRoot = Split-Path -Parent $PSCommandPath
$inventoryPath = Join-Path $handoffRoot 'source-image-inventory.csv'
$summaryPath = Join-Path $handoffRoot 'import-summary.json'
$statePath = Join-Path $handoffRoot 'figma-build-state.json'

# This map is deliberately limited to source frames verified in the live Figma archive.
$verifiedFrameIds = [ordered]@{
  '00-foundation/00-brand-essence-color-typography-summary.png' = '6:3'
  '00-foundation/00-brand-moodboard-ui-kit-overview.png' = '12:2'
  '00-foundation/00-controls-form-components-icons.png' = '14:2'
  '00-foundation/00-design-system-10-section-overview.png' = '14:3'
  '00-foundation/00-design-system-tokens-components-sheet.png' = '14:4'
  '00-foundation/00-full-foundation-with-screen-previews.png' = '14:5'
  '00-foundation/00-navigation-layout-component-board.png' = '17:2'
  '00-foundation/00-product-commerce-component-board.png' = '17:3'
  '00-foundation/00-ui-states-feedback-patterns.png' = '17:4'
  '01-entry/01-splash-screen-logo.png' = '18:2'
  '01-entry/01-language-selection-french-arabic.png' = '18:3'
  '01-entry/01-loading-screen-preparing-experience.png' = '18:4'
  '01-entry/01-onboarding-step1-discover-interior-fr.png' = '18:5'
  '01-entry/01-onboarding-step1-discover-interior-ar.png' = '18:6'
  '01-entry/01-onboarding-step2-choose-with-confidence-fr.png' = '18:7'
  '01-entry/01-onboarding-step2-choose-with-confidence-ar.png' = '18:8'
  '01-entry/01-onboarding-step3-order-simply-fr.png' = '18:9'
  '01-entry/01-onboarding-step3-order-simply-ar.png' = '18:10'
  '02-discovery/02-categories-photo-grid-ar.png' = '22:2'
  '02-discovery/02-categories-photo-grid-fr.png' = '22:3'
  '02-discovery/02-category-landing-living-room-ar.png' = '22:4'
  '02-discovery/02-category-landing-salon-collections-fr.png' = '22:5'
  '02-discovery/02-collection-salon-contemporain-shop-the-look.png' = '22:6'
  '02-discovery/02-filter-panel-category-price-color-material.png' = '22:7'
  '02-discovery/02-flash-deals-countdown-timer.png' = '22:8'
  '02-discovery/02-home-hero-new-arrivals-best-sellers-fr.png' = '22:9'
  '02-discovery/02-home-hero-shop-by-category-ar.png' = '23:2'
  '02-discovery/02-home-logged-in-personalized-recommendations.png' = '23:3'
  '02-discovery/02-promotions-campaigns-offers.png' = '23:4'
  '02-discovery/02-recently-viewed-products.png' = '23:5'
  '02-discovery/02-search-no-results-found.png' = '23:6'
  '02-discovery/02-search-recent-popular-trending-categories.png' = '23:7'
  '02-discovery/02-search-results-grid-fauteuil.png' = '23:8'
  '02-discovery/02-subcategory-canapes-filtered-list.png' = '23:9'
  '03-product/03-product-added-to-cart-confirmation.png' = '27:2'
  '03-product/03-product-customer-reviews-ratings.png' = '27:3'
  '03-product/03-product-delivery-returns-info.png' = '27:4'
  '03-product/03-product-detail-full-description-reviews-specs.png' = '27:5'
  '03-product/03-product-detail-image-carousel-add-to-cart.png' = '27:6'
  '03-product/03-product-gallery-zoom-thumbnails.png' = '27:7'
  '03-product/03-product-specifications-table.png' = '27:8'
  '03-product/03-product-variant-selector-color-material-size.png' = '27:9'
  '04-auth/04-account-created-success-fr.png' = '34:3'
  '04-auth/04-consent-terms-privacy-fr.png' = '34:7'
  '04-auth/04-create-new-password-requirements-ar.png' = '34:11'
  '04-auth/04-create-new-password-requirements-fr.png' = '34:15'
  '04-auth/04-email-verification-link-sent-fr.png' = '34:19'
  '04-auth/04-forgot-password-enter-email-ar.png' = '35:2'
  '04-auth/04-forgot-password-enter-email-fr.png' = '35:6'
  '04-auth/04-login-email-phone-password-ar.png' = '35:10'
  '04-auth/04-login-email-phone-password-fr.png' = '35:14'
  '04-auth/04-login-error-incorrect-credentials-fr.png' = '35:18'
}

$rows = Import-Csv -LiteralPath $inventoryPath
foreach ($row in $rows) {
  if ($verifiedFrameIds.Contains($row.source_relative_path)) {
    $row.import_status = 'IMPORTED'
    $row.figma_node_id = $verifiedFrameIds[$row.source_relative_path]
    $row.error = ''
  }
}
$rows | Export-Csv -LiteralPath $inventoryPath -NoTypeInformation -Encoding utf8

$importedRows = @($rows | Where-Object { $_.import_status -eq 'IMPORTED' })
$byFolder = [ordered]@{}
foreach ($group in ($rows | Group-Object source_folder | Sort-Object Name)) {
  $byFolder[$group.Name] = [ordered]@{
    discovered = @($group.Group).Count
    imported = @($group.Group | Where-Object { $_.import_status -eq 'IMPORTED' }).Count
    failed = @($group.Group | Where-Object { $_.import_status -eq 'FAILED' }).Count
  }
}

$summary = Get-Content -LiteralPath $summaryPath -Raw | ConvertFrom-Json
$summary.importedImageCount = $importedRows.Count
$summary.failedImageCount = @($rows | Where-Object { $_.import_status -eq 'FAILED' }).Count
$summary.skippedImageCount = @($rows | Where-Object { $_.import_status -eq 'SKIPPED' }).Count
$summary.lastImportedIndex = $LastVerifiedIndex
$summary.completed = $false
$summary | Add-Member -NotePropertyName importCountsByFolder -NotePropertyValue $byFolder -Force
$summary | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $summaryPath -Encoding utf8

$state = Get-Content -LiteralPath $statePath -Raw | ConvertFrom-Json
$state.discoveredImageCount = $rows.Count
$state.importedImageCount = $importedRows.Count
$state.failedImageCount = $summary.failedImageCount
$state.activePhase = 'source-image-import'
$state.currentFolder = '04-auth'
$state.currentBatch = @(53,54,55,56,57)
$state.lastSuccessfulSourceIndex = $LastVerifiedIndex
$state.completedFolders = @('00-foundation','01-entry','02-discovery','03-product')
$state.lastSuccessfulOperation = 'Authentication source import batch verified: source indexes 48 through 52 have visible image layers and locked wrappers.'
$state.nextOperation = 'Import 04-auth source images 53 through 57 into the archive.'
$state | Add-Member -NotePropertyName stateReconciliation -NotePropertyValue ([ordered]@{
  inventoryStatus = 'reconciled-through-source-index-42'
  liveFigmaArchiveVerifiedCount = $importedRows.Count
  remainingPendingCount = @($rows | Where-Object { $_.import_status -eq 'PENDING' }).Count
}) -Force
$state | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath $statePath -Encoding utf8

[pscustomobject]@{
  importedImageCount = $importedRows.Count
  pendingImageCount = @($rows | Where-Object { $_.import_status -eq 'PENDING' }).Count
  lastImportedIndex = $LastVerifiedIndex
  completedFolders = $state.completedFolders
} | ConvertTo-Json -Depth 4
