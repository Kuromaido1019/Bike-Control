<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ]);
        return $this->sendResetLinkEmailTrait($request);
    }

    // Renombra el método original del trait para llamarlo manualmente
    protected function sendResetLinkEmailTrait(Request $request)
    {
        return \Illuminate\Foundation\Auth\SendsPasswordResetEmails::sendResetLinkEmail($request);
    }
}
