<?php namespace Aero\SocialAuth\Components;

use Cms\Classes\ComponentBase;
use Laravel\Socialite\Facades\Socialite;
use RainLab\User\Models\User;
use Auth;
use Redirect;
use Flash;
use Exception;

class SocialAuth extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Social Authentication',
            'description' => 'Handles social authentication with Google and Github'
        ];
    }

    /**
     * Redirect to Google OAuth
     */
    public function onGoogleLogin()
    {
        return Redirect::to($this->redirectToProvider('google'));
    }

    /**
     * Redirect to Github OAuth
     */
    public function onGithubLogin()
    {
        return Redirect::to($this->redirectToProvider('github'));
    }

    /**
     * Redirect to provider
     */
    protected function redirectToProvider($provider)
    {
        try {
            return Socialite::driver($provider)->redirect()->getTargetUrl();
        } catch (Exception $e) {
            Flash::error('Error al conectar con ' . ucfirst($provider) . ': ' . $e->getMessage());
            return Redirect::to('/login');
        }
    }

    /**
     * Handle Google callback
     */
    public function onGoogleCallback()
    {
        return $this->handleProviderCallback('google');
    }

    /**
     * Handle Github callback
     */
    public function onGithubCallback()
    {
        return $this->handleProviderCallback('github');
    }

    /**
     * Handle provider callback
     */
    protected function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'username' => $this->generateUsername($socialUser->getEmail()),
                    'is_activated' => true,
                    'password' => str_random(16), // Random password since they'll login via OAuth
                ]);

                Flash::success('Cuenta creada exitosamente. Bienvenido!');
            } else {
                if (!$user->is_activated) {
                    Flash::error('Tu cuenta no está activada.');
                    return Redirect::to('/login');
                }

                Flash::success('Bienvenido de nuevo!');
            }

            // Log the user in
            Auth::login($user, true); // true = remember me

            return Redirect::to('/dashboard');

        } catch (Exception $e) {
            \Log::error('Social auth error: ' . $e->getMessage());
            Flash::error('Error al autenticar con ' . ucfirst($provider) . '. Por favor intenta de nuevo.');
            return Redirect::to('/login');
        }
    }

    /**
     * Generate unique username from email
     */
    protected function generateUsername($email)
    {
        $username = explode('@', $email)[0];
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Check if OAuth is configured
     */
    public function isGoogleConfigured()
    {
        return !empty(env('GOOGLE_CLIENT_ID')) && !empty(env('GOOGLE_CLIENT_SECRET'));
    }

    /**
     * Check if Github OAuth is configured
     */
    public function isGithubConfigured()
    {
        return !empty(env('GITHUB_CLIENT_ID')) && !empty(env('GITHUB_CLIENT_SECRET'));
    }
}
