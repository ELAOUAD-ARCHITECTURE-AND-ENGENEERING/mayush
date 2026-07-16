<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Traits\PreventDemoModeChanges;

class Shop extends Model
{
  use HasFactory, PreventDemoModeChanges;

  protected $guarded = [];

  protected $with = ['user'];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'bank_name'          => \App\Casts\SafeEncrypted::class,
      'bank_info'          => \App\Casts\SafeEncrypted::class,
      'business_info'      => \App\Casts\SafeEncrypted::class,
      'verification_info'  => \App\Casts\SafeEncrypted::class,
      'social_links'       => 'array',
      'gallery_json'       => 'array',
      'documents_submitted_at' => 'datetime',
      'reviewed_at'        => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
  
  public function seller_package(){
    return $this->belongsTo(SellerPackage::class);
  }

  public function followers(){
    return $this->hasMany(FollowSeller::class);
  }

  /**
   * Documents uploaded during the seller onboarding process.
   */
  public function documents()
  {
    return $this->hasMany(SellerDocument::class);
  }

  /**
   * Admin user who last reviewed this shop's application.
   */
  public function reviewer()
  {
    return $this->belongsTo(User::class, 'reviewed_by');
  }

  // ─── Approval Status Scopes ───────────────────────────────────────────────

  public function scopePendingApproval($query)
  {
    return $query->where('approval_status', 'pending');
  }

  public function scopeUnderReview($query)
  {
    return $query->where('approval_status', 'under_review');
  }

  public function scopeApproved($query)
  {
    return $query->where('approval_status', 'approved');
  }

  public function scopeRejected($query)
  {
    return $query->where('approval_status', 'rejected');
  }

  /**
   * Shops that may be exposed through the public storefront and public APIs.
   * Legacy verification columns are deliberately excluded from this decision.
   */
  public function scopePubliclyVisible(Builder $query): Builder
  {
    $query->where('approval_status', 'approved')
      ->whereHas('user', function (Builder $user) {
        $user->where('user_type', 'seller')
          ->where('banned', 0);
      });

    if (get_setting('vendor_system_activation') != 1) {
      $query->whereHas('user', function (Builder $user) {
        $user->where('is_intern', 1);
      });
    }

    return $query;
  }

  // ─── Approval Helpers ─────────────────────────────────────────────────────

  /**
   * Whether the seller is fully approved and can manage products.
   */
  public function isApproved(): bool
  {
    return $this->isFullyApproved();
  }

  public function canAccessSellerArea(): bool
  {
    return $this->user
      && $this->user->user_type === 'seller'
      && !$this->user->banned;
  }

  public function canSubmitOnboardingDocuments(): bool
  {
    if (!$this->canAccessSellerArea() || $this->resubmission_count >= 10) {
      return false;
    }

    if ($this->approval_status === 'pending' || $this->approval_status === 'rejected') {
      return true;
    }

    // A partial correction can leave another mandatory document rejected.
    // Keep the seller restricted, but allow the remaining correction upload.
    return $this->approval_status === 'under_review'
      && $this->rejectedOnboardingDocumentTypes() !== [];
  }

  public function canManageProducts(): bool
  {
    return $this->isFullyApproved();
  }

  public function isFullyApproved(): bool
  {
    return $this->approval_status === 'approved'
      && $this->user
      && $this->user->user_type === 'seller'
      && !$this->user->banned;
  }

  /**
   * Whether this shop can be exposed through the public storefront/API.
   * Internal sellers remain visible when the vendor marketplace is disabled.
   */
  public function isPubliclyVisible(): bool
  {
    return $this->isFullyApproved()
      && (get_setting('vendor_system_activation') == 1 || (bool) $this->user->is_intern);
  }

  public function requiredDocumentTypes(): array
  {
    return SellerDocument::$mandatoryTypes;
  }

  public function missingRequiredDocumentTypes(): array
  {
    $documents = $this->latestOnboardingDocuments();

    return array_values(array_filter(
      $this->requiredDocumentTypes(),
      fn (string $type): bool => ($documents->get($type)?->status ?? null) !== 'approved'
    ));
  }

  /**
   * Return mandatory document types whose latest version was rejected.
   */
  public function rejectedRequiredDocumentTypes(): array
  {
    $documents = $this->latestOnboardingDocuments();

    return array_values(array_filter(
      $this->requiredDocumentTypes(),
      fn (string $type): bool => $documents->get($type)?->status === 'rejected'
    ));
  }

  /**
   * Return all document types whose latest version needs correction.
   */
  public function rejectedOnboardingDocumentTypes(): array
  {
    return $this->latestOnboardingDocuments()
      ->filter(fn (SellerDocument $document): bool => $document->status === 'rejected')
      ->keys()
      ->values()
      ->all();
  }

  /**
   * Return the newest submitted version for each document type.
   */
  public function latestOnboardingDocuments(): Collection
  {
    return $this->documents()
      ->orderByDesc('version')
      ->orderByDesc('id')
      ->get()
      ->unique('document_type')
      ->keyBy('document_type');
  }

  /**
   * Recalculate the review state after an individual document review.
   * A single approved document must never clear another required rejection.
   */
  public function refreshOnboardingReviewState(): string
  {
    $documents = $this->latestOnboardingDocuments();
    $rejected = collect($this->requiredDocumentTypes())
      ->map(fn (string $type) => $documents->get($type))
      ->filter(fn ($document) => $document && $document->status === 'rejected');

    if ($rejected->isNotEmpty()) {
      $reason = $rejected->pluck('rejection_reason')->filter()->first();
      $this->approval_status = 'rejected';
      if ($reason) {
        $this->rejection_reason = $reason;
        $this->admin_note = $reason;
      }
    } else {
      $this->approval_status = 'under_review';
      $this->rejection_reason = null;
      $this->admin_note = null;
    }

    $this->save();

    return (string) $this->approval_status;
  }

  /**
   * Whether the seller can resubmit documents (max 10 attempts).
   */
  public function canResubmit(): bool
  {
    return $this->approval_status === 'rejected' && $this->resubmission_count < 10;
  }

  /**
   * Human-readable label for the current approval status.
   */
  public function approvalStatusLabel(): string
  {
    return match ($this->approval_status) {
      'pending'      => translate('Pending Approval'),
      'under_review' => translate('Under Review'),
      'approved'     => translate('Approved'),
      'rejected'     => translate('Rejected'),
      default        => translate('Unknown'),
    };
  }

  /**
   * Get the active Elite subscription for this shop.
   */
  public function activeEliteSubscription()
  {
    return $this->hasOne(\App\Models\EliteSubscription::class)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
  }

  /**
   * Determine if this shop currently has an active Elite subscription
   * AND the global Elite system is enabled.
   */
  public function isElite(): bool
  {
    if (get_setting('elite_system_active') != 1) {
        return false;
    }
    return $this->activeEliteSubscription()->exists();
  }
}
