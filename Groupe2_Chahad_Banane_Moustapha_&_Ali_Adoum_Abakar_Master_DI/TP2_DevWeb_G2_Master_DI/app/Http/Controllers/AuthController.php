<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /** Messages d'erreur en français (pas de fichier lang/fr dans le projet). */
    private const MESSAGES = [
        'name.required'      => 'Le nom est obligatoire.',
        'name.max'           => 'Le nom ne doit pas dépasser 100 caractères.',
        'email.required'     => 'L\'adresse e-mail est obligatoire.',
        'email.email'        => 'L\'adresse e-mail n\'est pas valide.',
        'email.max'          => 'L\'adresse e-mail est trop longue.',
        'email.unique'       => 'Un compte existe déjà avec cette adresse e-mail.',
        'password.required'  => 'Le mot de passe est obligatoire.',
        'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
    ];

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], self::MESSAGES);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Nouvel identifiant de session : protège contre la fixation de session
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Même message pour e-mail inconnu et mot de passe faux (on ne révèle pas lequel)
        return back()
            ->withErrors(['email' => 'Adresse e-mail ou mot de passe incorrect.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], self::MESSAGES);

        // Le mot de passe est haché automatiquement (cast 'hashed' du modèle User)
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Compte créé avec succès. Bienvenue !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous êtes déconnecté.');
    }
}
