<?php

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\BlogVersion;
use App\Models\User;

class BlogWorkflowService
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const CHANGES_REQUESTED = 'changes_requested';
    public const PUBLISHED = 'published';
    public const ARCHIVED = 'archived';

    public function saveVersion(Blog $blog, ?User $actor, string $action = 'saved'): BlogVersion
    {
        $versionNumber = ((int) $blog->versions()->max('version_number')) + 1;

        return BlogVersion::create([
            'blog_id' => $blog->id,
            'actor_id' => optional($actor)->id,
            'version_number' => $versionNumber,
            'action' => $action,
            'snapshot' => [
                'title' => $blog->title,
                'slug' => $blog->slug,
                'category_id' => $blog->category_id,
                'short_description' => $blog->short_description,
                'description' => $blog->description,
                'content_blocks' => $blog->content_blocks,
                'status' => $blog->status,
                'workflow_status' => $blog->workflow_status,
                'published_at' => optional($blog->published_at)->toDateTimeString(),
            ],
        ]);
    }

    public function saveDraft(Blog $blog): void
    {
        $blog->status = 0;
        $blog->workflow_status = self::DRAFT;
        $blog->submitted_at = null;
        $blog->reviewed_by = null;
        $blog->reviewed_at = null;
    }

    public function submitForReview(Blog $blog): void
    {
        $blog->status = 0;
        $blog->workflow_status = self::SUBMITTED;
        $blog->submitted_at = now();
    }

    public function publish(Blog $blog, User $reviewer): void
    {
        $blog->status = 1;
        $blog->workflow_status = self::PUBLISHED;
        $blog->reviewed_by = $reviewer->id;
        $blog->reviewed_at = now();
        $blog->published_at = $blog->published_at ?: now();
    }

    public function archive(Blog $blog, User $reviewer): void
    {
        $blog->status = 0;
        $blog->workflow_status = self::ARCHIVED;
        $blog->reviewed_by = $reviewer->id;
        $blog->reviewed_at = now();
    }

    public function requestChanges(Blog $blog, User $reviewer, ?string $note): void
    {
        $blog->status = 0;
        $blog->workflow_status = self::CHANGES_REQUESTED;
        $blog->reviewed_by = $reviewer->id;
        $blog->reviewed_at = now();
        $blog->review_note = $note;
    }
}
