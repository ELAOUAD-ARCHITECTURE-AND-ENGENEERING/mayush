<?php

namespace App\Services;

use App\Models\NotificationType;
use App\Models\SellerDocument;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\ShopVerificationNotification;
use App\Utility\EmailUtility;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SellerOnboardingNotifier
{
    public function registrationCompleted(Shop $shop): void
    {
        $this->afterCommit(function () use ($shop) {
            $shop = $this->freshShop($shop);
            if (!$shop) {
                return;
            }

            $anchor = $shop->created_at?->format('U.u') ?? (string) $shop->id;
            $this->sendSellerDatabaseNotification(
                $shop,
                'registration_completed',
                'seller_onboarding_registration_incomplete',
                ['onboarding_incomplete' => true],
                'seller.onboarding.index',
                $anchor
            );
            $this->dispatchOnce('email', "registration:{$shop->id}:{$anchor}", function () use ($shop) {
                EmailUtility::seller_onboarding_request_email($shop);
            });
        });
    }

    public function documentsSubmitted(Shop $shop): void
    {
        $this->afterCommit(function () use ($shop) {
            $shop = $this->freshShop($shop);
            if (!$shop) {
                return;
            }

            $latestDocumentId = (int) $shop->documents()->max('id');
            $anchor = ($shop->documents_submitted_at?->format('U.u') ?? 'unknown') . ":{$latestDocumentId}";

            $this->sendSellerDatabaseNotification(
                $shop,
                'documents_submitted',
                'seller_onboarding_documents_submitted',
                [],
                'seller.onboarding.index',
                $anchor
            );
            $this->dispatchOnce('email', "documents-seller:{$shop->id}:{$anchor}", function () use ($shop) {
                EmailUtility::seller_onboarding_event_email($shop, 'seller_documents_submitted');
            });

            $this->sendAdministratorSubmissionNotification($shop, $anchor);
            $this->dispatchOnce('email', "documents-admin:{$shop->id}:{$anchor}", function () use ($shop) {
                EmailUtility::seller_documents_received_admin('seller_documents_received_admin', $shop);
            });
        });
    }

    public function correctionRequired(Shop $shop, SellerDocument $document, string $reason): void
    {
        $documentId = (int) $document->id;
        $documentType = (string) $document->document_type;
        $version = (int) $document->version;
        $anchor = $document->reviewed_at?->format('U.u') ?? (string) $documentId;

        $this->afterCommit(function () use ($shop, $documentId, $documentType, $version, $reason, $anchor) {
            $shop = $this->freshShop($shop);
            if (!$shop) {
                return;
            }

            $context = [
                'document_id' => $documentId,
                'document_type' => $documentType,
                'document_version' => $version,
                'reason' => $reason,
            ];

            $this->sendSellerDatabaseNotification(
                $shop,
                'correction_required',
                'seller_onboarding_correction_required',
                $context,
                'seller.onboarding.index',
                $anchor
            );
            $this->dispatchOnce('email', "correction:{$shop->id}:{$documentId}:{$anchor}", function () use ($shop, $context) {
                EmailUtility::seller_onboarding_event_email(
                    $shop,
                    'seller_document_correction_required',
                    $context
                );
            });
        });
    }

    public function applicationApproved(Shop $shop): void
    {
        $this->applicationStatusChanged($shop, 'approved');
    }

    public function applicationRejected(Shop $shop, string $reason): void
    {
        $this->applicationStatusChanged($shop, 'rejected', $reason);
    }

    public function accessChanged(Shop $shop, bool $suspended): void
    {
        $event = $suspended ? 'suspended' : 'reactivated';
        $notificationType = $suspended
            ? 'seller_onboarding_suspended'
            : 'seller_onboarding_reactivated';
        $emailTemplate = $suspended
            ? 'seller_account_suspended'
            : 'seller_account_reactivated';

        $this->afterCommit(function () use ($shop, $event, $notificationType, $emailTemplate) {
            $shop = $this->freshShop($shop);
            if (!$shop) {
                return;
            }

            $anchor = $shop->user?->updated_at?->format('U.u') ?? (string) $shop->id;
            $this->sendSellerDatabaseNotification(
                $shop,
                $event,
                $notificationType,
                [],
                'seller.dashboard',
                $anchor
            );
            $this->dispatchOnce('email', "access:{$event}:{$shop->id}:{$anchor}", function () use ($shop, $emailTemplate) {
                EmailUtility::seller_onboarding_event_email($shop, $emailTemplate);
            });
        });
    }

    private function applicationStatusChanged(Shop $shop, string $status, ?string $reason = null): void
    {
        $this->afterCommit(function () use ($shop, $status, $reason) {
            $shop = $this->freshShop($shop);
            if (!$shop) {
                return;
            }

            $anchor = $shop->reviewed_at?->format('U.u') ?? (string) $shop->id;
            $notificationType = $status === 'approved'
                ? 'shop_verify_request_approved'
                : 'shop_verify_request_rejected';
            $targetRoute = $status === 'approved'
                ? 'seller.dashboard'
                : 'seller.onboarding.index';

            $this->sendSellerDatabaseNotification(
                $shop,
                $status,
                $notificationType,
                ['reason' => $reason],
                $targetRoute,
                $anchor
            );
            $this->dispatchOnce('email', "application:{$status}:{$shop->id}:{$anchor}", function () use ($shop, $status, $reason) {
                EmailUtility::seller_application_status_email($shop, $status, $reason);
            });
        });
    }

    private function sendSellerDatabaseNotification(
        Shop $shop,
        string $event,
        string $notificationType,
        array $context,
        string $targetRoute,
        string $anchor
    ): void {
        if (!$shop->user) {
            return;
        }

        $type = $this->notificationType($notificationType, 'seller');
        if (!$type || !$type->status) {
            return;
        }

        $this->dispatchOnce('database', "seller:{$event}:{$shop->id}:{$anchor}", function () use ($shop, $event, $type, $context, $targetRoute) {
            Notification::send([$shop->user], new ShopVerificationNotification(array_merge($context, [
                'shop' => $shop,
                'status' => $event,
                'workflow' => 'onboarding',
                'approval_status' => $shop->approval_status,
                'notification_type_id' => $type->id,
                'target_route_name' => $targetRoute,
                'target_route_parameters' => [],
            ])));
        });
    }

    private function sendAdministratorSubmissionNotification(Shop $shop, string $anchor): void
    {
        $type = $this->notificationType('shop_verify_request_submitted', 'admin');
        if (!$type || !$type->status) {
            return;
        }

        $admins = User::query()
            ->whereIn('user_type', ['admin', 'staff'])
            ->where('banned', 0)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $this->dispatchOnce('database', "admin:documents:{$shop->id}:{$anchor}", function () use ($shop, $type, $admins) {
            Notification::send($admins, new ShopVerificationNotification([
                'shop' => $shop,
                'status' => 'submitted',
                'workflow' => 'onboarding',
                'approval_status' => $shop->approval_status,
                'notification_type_id' => $type->id,
                'target_route_name' => 'sellers.registration_pending',
                'target_route_parameters' => ['review_shop' => $shop->id],
            ]));
        });
    }

    private function notificationType(string $type, string $recipient): ?NotificationType
    {
        $query = NotificationType::query()->where('type', $type);

        if (Schema::hasColumn('notification_types', 'user_type')) {
            $query->where('user_type', $recipient);
        }

        return $query->first();
    }

    private function freshShop(Shop $shop): ?Shop
    {
        return Shop::with('user')->find($shop->id);
    }

    private function afterCommit(Closure $callback): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($callback);
            return;
        }

        $callback();
    }

    private function dispatchOnce(string $channel, string $eventKey, Closure $callback): void
    {
        $key = 'seller-onboarding:sent:' . sha1("{$channel}:{$eventKey}");
        $lock = Cache::lock($key . ':lock', 10);

        if (!$lock->get()) {
            return;
        }

        try {
            if (Cache::has($key)) {
                return;
            }

            $callback();
            Cache::put($key, true, now()->addDays(7));
        } catch (Throwable $e) {
            Log::warning("Seller onboarding {$channel} notification failed: {$e->getMessage()}");
        } finally {
            $lock->release();
        }
    }
}
