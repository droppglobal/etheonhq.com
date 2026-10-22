<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Page;

/**
 * Public, built-in pages linked from the landing page footer:
 * About us, Contact us, and Support and Documentation.
 */
class InfoPageController extends Controller
{
    public function __construct()
    {
        // public pages follow the language of the main account, like the custom pages do
        $user = \App\Models\User::find(1);
        if ($user && !empty($user->lang)) {
            app()->setLocale($user->lang);
        }
    }

    public function about()
    {
        return $this->render('about', __('About us'));
    }

    public function contact()
    {
        return $this->render('contact', __('Contact us'));
    }

    public function support()
    {
        $FAQs = FAQ::where('enabled', 1)->get();

        return $this->render('support', __('Support and Documentation'), compact('FAQs'));
    }

    private function render(string $view, string $title, array $data = [])
    {
        $settings = settings();
        $menus = Page::where('enabled', 1)->get();

        return view('Pages.info', array_merge($data, [
            'infoView' => 'Pages.info.' . $view,
            'infoTitle' => $title,
            'settings' => $settings,
            'menus' => $menus,
        ]));
    }
}
