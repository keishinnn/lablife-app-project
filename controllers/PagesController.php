<?php
namespace Controllers;

class PagesController
{
    public function privacyPolicy()
    {
        view('policy/privacy.view.php');
    }

    public function terms()
    {
        view('policy/terms.view.php');
    }
}
