<?php namespace Aero\Manager\Components;

use Cms;
use Auth;
use Event;
use Validator;
use RainLab\User\Models\User;
use RainLab\User\Models\Setting;
use RainLab\User\Models\UserLog;
use RainLab\User\Helpers\User as UserHelper;
use Cms\Classes\ComponentBase;
use NotFoundException;
use Carbon\Carbon;

/**
 * CustomRegistration component - Auto-activates users and sends welcome email
 */
class CustomRegistration extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Custom Registration',
            'description' => 'User registration with auto-activation and welcome email'
        ];
    }

    public function onRegister()
    {
        if (!$this->canRegister()) {
            throw new NotFoundException;
        }

        $input = post();

        // If the password confirmation field is absent, add it
        if (!array_key_exists('password_confirmation', $input)) {
            $input['password_confirmation'] = $input['password'] ?? '';
        }

        // Validate input
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => UserHelper::passwordRules(),
        ])->validate();

        // Create user
        $user = User::create([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'password_confirmation' => $input['password_confirmation'],
        ]);

        // ACTIVATE USER IMMEDIATELY
        $user->activated_at = Carbon::now();
        $user->save();

        // Send welcome email (user:welcome_email) instead of verification
        $user->sendEmailConfirmationNotification();

        // Log the new user
        UserLog::createRecord($user->getKey(), UserLog::TYPE_NEW_USER, [
            'user_full_name' => $user->full_name,
        ]);

        // Login the user
        Auth::login($user);

        // Fire the register event
        Event::fire('rainlab.user.register', [$this, $user]);

        // Redirect to the intended page after successful registration
        if ($redirect = Cms::redirectIntendedFromPost()) {
            return $redirect;
        }
    }

    public function canRegister(): bool
    {
        return Setting::get('allow_registration');
    }
}
