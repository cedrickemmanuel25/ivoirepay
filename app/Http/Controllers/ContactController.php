<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|min:2|max:100',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:20',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required'    => 'Le nom est obligatoire.',
            'email.required'   => 'L\'adresse email est obligatoire.',
            'email.email'      => 'Veuillez saisir un email valide.',
            'message.required' => 'Le message est obligatoire.',
            'message.min'      => 'Le message doit contenir au moins 10 caractères.',
        ]);

        // Log the contact request (email delivery can be wired later)
        Log::info('Contact form submitted', [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()->route('landing')->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons sous 24h.');
    }
}
