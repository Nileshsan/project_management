<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait GoogleOAuth
{
    public function setGoogleoAuthConfig()
    {
        // Prevent crashing when app is not installed
        if (!env('APP_INSTALLED', false)) {
            return;
        }

        $setting = global_setting();

        if (!$setting) {
            return;
        }

        $subdomain = config('app.main_application_subdomain');
        $rootCrmSubDomain = preg_replace('#^https?://#', '', $subdomain); // Remove 'http://' or 'https://'

        $domain = request()->getScheme() . '://' . ($rootCrmSubDomain ?: getDomain());

        Config::set('services.google.client_id', $setting->google_client_id);
        Config::set('services.google.client_secret', $setting->google_client_secret);
        Config::set('services.google.redirect_uri', $domain . '/account/settings/google-auth');
    }
}
