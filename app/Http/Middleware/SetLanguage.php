<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SystemSettingsService; // 🚀 استدعاء الـ Service
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    protected $settingsService;

    // بنعمل Inject للـ Service جوه الـ Constructor
    public function __construct(SystemSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. شوف لو الـ React باعت لغة في الـ Header
        $locale = $request->header('Accept-Language');

        if ($locale && in_array($locale, ['ar', 'en', 'fr'])) {
            App::setLocale($locale);
        } else {
            // 2. لو مفيش، اسحب اللغة المعتمدة من إعدادات قاعدة البيانات (الـ Service)
            $dbLocale = $this->settingsService->getSystemLanguage();
            App::setLocale($dbLocale);
        }

        return $next($request);
    }
}