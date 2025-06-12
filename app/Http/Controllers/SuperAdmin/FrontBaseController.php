<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\FooterMenu;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontMenu;
use App\Models\SuperAdmin\FrontWidget;
use App\Models\SuperAdmin\TrFrontDetail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class FrontBaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->showInstall();

        $this->middleware(function ($request, $next) {

            $this->frontDetail = \App\Models\SuperAdmin\FrontDetail::first(); // ← Corrected

            $this->languages = language_setting();
            $this->global = $this->globalSetting = $this->setting = global_setting();

            $this->locale = 'en';
            if ($this->frontDetail && $this->frontDetail->locale) {
                $this->locale = $this->frontDetail->locale;
            }

            if (session()->has('language')) {
                $this->locale = session('language');
            }

            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);
            setlocale(LC_TIME, $this->locale . '_' . strtoupper($this->locale));

            $this->enLocaleLanguage = language_setting_locale('en');
            $this->localeLanguage = $this->locale != 'en'
                ? language_setting_locale($this->locale)
                : $this->enLocaleLanguage;
            $this->localeLanguage = $this->localeLanguage ?: $this->enLocaleLanguage;

            $this->footerSettings = \App\Models\SuperAdmin\FooterMenu::whereNotNull('slug')->get();

            return $next($request);
        });
    }




}
