<?php namespace Aero\SocialAuth;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Social Auth',
            'description' => 'Social authentication with Google and Github',
            'author' => 'Aero',
            'icon' => 'icon-lock'
        ];
    }

    public function registerComponents()
    {
        return [
            'Aero\SocialAuth\Components\SocialAuth' => 'socialAuth'
        ];
    }
}
