<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class DomainExtensions extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    // public $requiredPermissions = [
    //     'aero.clouds.access_domain_extensions'
    // ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds', 'domainextensions');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = \Aero\Clouds\Models\DomainExtension::find($recordId)) {
                    continue;
                }
                $record->delete();
            }

            \Flash::success('Successfully deleted selected domain extensions');
        }

        return $this->listRefresh();
    }

    public function onApplySalePrices()
    {
        try {
            $markupType = post('markup_type');
            $markupValue = post('markup_value');

            if (!$markupType || !is_numeric($markupValue) || $markupValue < 0) {
                throw new \ApplicationException('Invalid markup configuration');
            }

            $extensions = \Aero\Clouds\Models\DomainExtension::all();
            $updated = 0;

            foreach ($extensions as $extension) {
                if ($markupType === 'percentage') {
                    // Apply percentage markup: price * (1 + percentage/100)
                    $extension->sale_price = $extension->registration_price * (1 + ($markupValue / 100));
                } else {
                    // Apply fixed amount markup: price + fixed_amount
                    $extension->sale_price = $extension->registration_price + $markupValue;
                }
                $extension->save();
                $updated++;
            }

            return [
                'message' => "Successfully updated sale prices for {$updated} extension(s)"
            ];
        } catch (\Exception $e) {
            throw new \ApplicationException('Error applying sale prices: ' . $e->getMessage());
        }
    }
}
