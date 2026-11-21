<?php

use App\Http\Controllers\Api\V1\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\V1\Admin\ContentController;
use App\Http\Controllers\Api\V1\Admin\ForumModerationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConsultationController;
use App\Http\Controllers\Api\V1\ContactMessageController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\ForumCategoryController;
use App\Http\Controllers\Api\V1\ForumTopicController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\NewsletterSubscriptionController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service:slug}', [ServiceController::class, 'show']);
    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/{article:slug}', [NewsController::class, 'show']);
    Route::get('/faqs', [FaqController::class, 'index']);

    Route::middleware('throttle:5,1')->group(function (): void {
        Route::post('/consultations', [ConsultationController::class, 'store']);
        Route::post('/contact-messages', [ContactMessageController::class, 'store']);
        Route::post('/newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store']);
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::get('/forum/categories', [ForumCategoryController::class, 'index']);
    Route::get('/forum/topics', [ForumTopicController::class, 'index']);
    Route::get('/forum/topics/{topic}', [ForumTopicController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/forum/topics', [ForumTopicController::class, 'store'])
            ->middleware('throttle:10,1');
        Route::post('/forum/topics/{topic}/posts', [ForumTopicController::class, 'reply'])
            ->middleware('throttle:10,1');
        Route::post('/forum/topics/{topic}/reports', [ForumTopicController::class, 'report'])
            ->middleware('throttle:5,1');

        Route::prefix('admin')->group(function (): void {
            Route::middleware('can:viewAny,App\Models\Consultation')->group(function (): void {
                Route::get('/consultations', [AdminConsultationController::class, 'index']);
                Route::patch('/consultations/{consultation}', [AdminConsultationController::class, 'update'])
                    ->middleware('can:update,consultation');
            });
            Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])
                ->middleware('can:viewAny,App\Models\ContactMessage');
            Route::patch('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'update'])
                ->middleware('can:update,contactMessage');

            Route::middleware('can:manageContent')->group(function (): void {
                Route::get('/services', [ContentController::class, 'services']);
                Route::post('/services', [ContentController::class, 'storeService']);
                Route::patch('/services/{service}', [ContentController::class, 'updateService']);
                Route::get('/news', [ContentController::class, 'news']);
                Route::post('/news', [ContentController::class, 'storeNews']);
                Route::patch('/news/{article}', [ContentController::class, 'updateNews']);
                Route::get('/faqs', [ContentController::class, 'faqs']);
                Route::post('/faqs', [ContentController::class, 'storeFaq']);
                Route::patch('/faqs/{faq}', [ContentController::class, 'updateFaq']);
            });

            Route::middleware('can:moderateForum')->group(function (): void {
                Route::get('/forum/pending', [ForumModerationController::class, 'pending']);
                Route::get('/forum/reports', [ForumModerationController::class, 'reports']);
                Route::patch('/forum/topics/{topic}', [ForumModerationController::class, 'updateTopic']);
                Route::patch('/forum/posts/{post}', [ForumModerationController::class, 'updatePost']);
                Route::patch('/forum/reports/{report}', [ForumModerationController::class, 'updateReport']);
            });
        });
    });
});
