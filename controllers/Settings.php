<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\Setting;
use Flash;

/**
 * Settings Controller
 *
 * Manages global site configuration (SEO, PWA, etc.)
 */
class Settings extends Controller
{
    public $implement = [
        'Backend\Behaviors\FormController',
    ];

    public $formConfig = 'config_form.yaml';

    /**
     * Required permissions
     */
    public $requiredPermissions = [];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds-setup', 'settings');
    }

    /**
     * Index page - redirect to update
     */
    public function index()
    {
        // Get or create settings for current domain
        $settings = Setting::getOrCreateCurrentSettings();

        // Redirect to update page with the record ID
        return \Backend::redirect('aero/clouds/settings/update/' . $settings->id);
    }

    /**
     * Update settings
     */
    public function update($recordId = null)
    {
        $this->pageTitle = 'Global Settings';

        // If no record ID provided, get or create the settings
        if (!$recordId) {
            $settings = Setting::getOrCreateCurrentSettings();
            $recordId = $settings->id;
        }

        return $this->asExtension('FormController')->update($recordId);
    }

    /**
     * Handle form update AJAX request
     */
    public function update_onSave($recordId = null)
    {
        // If no record ID, get it from the form
        if (!$recordId) {
            $recordId = post('record_id');
        }

        $result = $this->asExtension('FormController')->update_onSave($recordId);

        Flash::success('Settings saved successfully');

        return $result;
    }

    /**
     * Form model finder - required by FormController
     */
    public function formFindModelObject($recordId)
    {
        if (!$recordId) {
            return Setting::getOrCreateCurrentSettings();
        }

        return Setting::findOrFail($recordId);
    }
}
