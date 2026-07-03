<?php

namespace Tests\Feature\Queue;

use App\Jobs\AggregateDailyAnalyticsJob;
use App\Jobs\AggregateSecurityMetricsJob;
use App\Jobs\OptimizeStaticImageJob;
use App\Jobs\OptimizeUploadedImageJob;
use App\Jobs\ProcessFrequentlyBoughtJob;
use App\Jobs\SyncSemanticEmbeddingJob;
use App\Mail\InvoiceEmailManager;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CriticalErrorNotification;
use App\Notifications\ProductRestockedNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;
use Mayush\Shipping\Onessta\Jobs\PollTrackingJob;
use Tests\TestCase;

class QueueArchitectureTest extends TestCase
{
    public function test_horizon_is_configured_with_correct_supervisors()
    {
        $environments = Config::get('horizon.environments.production');
        
        $this->assertArrayHasKey('supervisor-critical', $environments);
        $this->assertArrayHasKey('supervisor-communications', $environments);
        $this->assertArrayHasKey('supervisor-media-search', $environments);
        $this->assertArrayHasKey('supervisor-maintenance', $environments);
        
        $defaults = Config::get('horizon.defaults');
        
        $this->assertEquals(['critical', 'payments', 'shipping'], $defaults['supervisor-critical']['queue']);
        $this->assertEquals(['notifications', 'emails', 'sms'], $defaults['supervisor-communications']['queue']);
        $this->assertEquals(['search', 'embeddings', 'images'], $defaults['supervisor-media-search']['queue']);
        $this->assertEquals(['reports', 'audits', 'default'], $defaults['supervisor-maintenance']['queue']);
    }

    public function test_runtime_job_dispatching()
    {
        Queue::fake();

        // 1 & 2: Images
        dispatch(new OptimizeUploadedImageJob(1));
        Queue::assertPushedOn('images', OptimizeUploadedImageJob::class);

        dispatch(new OptimizeStaticImageJob('path/to/image.jpg'));
        Queue::assertPushedOn('images', OptimizeStaticImageJob::class);

        // 3: Embeddings
        $product = new Product();
        dispatch(new SyncSemanticEmbeddingJob($product));
        Queue::assertPushedOn('embeddings', SyncSemanticEmbeddingJob::class);

        // 4: Search
        dispatch(new ProcessFrequentlyBoughtJob());
        Queue::assertPushedOn('search', ProcessFrequentlyBoughtJob::class);

        // 5 & 6: Shipping
        dispatch(new CreateShipmentJob(1, []));
        Queue::assertPushedOn('shipping', CreateShipmentJob::class);

        dispatch(new PollTrackingJob());
        Queue::assertPushedOn('shipping', PollTrackingJob::class);

        // 10: Reports
        dispatch(new AggregateDailyAnalyticsJob());
        Queue::assertPushedOn('reports', AggregateDailyAnalyticsJob::class);

        // 11: Audits
        dispatch(new AggregateSecurityMetricsJob());
        Queue::assertPushedOn('audits', AggregateSecurityMetricsJob::class);
    }

    public function test_runtime_notification_dispatching()
    {
        Notification::fake();
        $user = User::factory()->make();

        // 7: Critical
        $criticalEvent = new \App\Events\CriticalSystemError('test_component', 'test_message');
        $user->notify(new CriticalErrorNotification($criticalEvent));
        
        Notification::assertSentTo($user, CriticalErrorNotification::class, function ($notification) {
            return $notification->queue === 'critical';
        });

        // 8: Notifications
        $product = new Product(['name' => 'Test Product']);
        $user->notify(new ProductRestockedNotification($product));
        
        Notification::assertSentTo($user, ProductRestockedNotification::class, function ($notification) {
            return $notification->queue === 'notifications';
        });
    }

    public function test_runtime_mailable_dispatching()
    {
        Mail::fake();
        
        // 9: Emails
        // Assuming InvoiceEmailManager implements Mailable and is queued
        $order = (object)['id' => 1, 'code' => 'ORD-123'];
        Mail::to('test@example.com')->queue(new InvoiceEmailManager($order));

        Mail::assertQueued(InvoiceEmailManager::class, function ($mail) {
            return $mail->queue === 'emails';
        });
    }
}
