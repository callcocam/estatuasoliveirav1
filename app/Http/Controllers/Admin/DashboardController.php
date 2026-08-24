<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Quote;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'productsPublished' => Product::query()->where('status', PublishStatus::Published)->count(),
                'productsDraft' => Product::query()->where('status', PublishStatus::Draft)->count(),
                'categories' => Category::query()->count(),
                'quotesPending' => Quote::query()->where('status', QuoteStatus::Pending)->count(),
                'messagesUnread' => ContactMessage::query()->unread()->count(),
            ],
            'latestMessages' => ContactMessage::query()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (ContactMessage $message): array => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'subject' => $message->subject,
                    'read' => $message->isRead(),
                    'createdAt' => $message->created_at?->toIso8601String(),
                ]),
            'latestQuotes' => Quote::query()
                ->with('user')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Quote $quote): array => [
                    'id' => $quote->id,
                    'userName' => $quote->user?->name,
                    'status' => $quote->status->value,
                    'statusLabel' => $quote->status->label(),
                    'total' => $quote->total,
                    'createdAt' => $quote->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
