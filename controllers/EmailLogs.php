<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\EmailLog;
use Flash;

class EmailLogs extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'aero.clouds.manage_emails'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'main-menu-item', 'side-menu-emails');
    }

    /**
     * Ver detalles del correo
     */
    public function preview($recordId = null)
    {
        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'Detalles del Correo';

        $model = EmailLog::findOrFail($recordId);
        $this->vars['model'] = $model;

        return $this->makePartial('preview');
    }

    /**
     * Reenviar correo
     */
    public function onResend()
    {
        $recordId = post('record_id');
        $log = EmailLog::findOrFail($recordId);

        try {
            $data = json_decode($log->data, true) ?? [];
            $metadata = json_decode($log->metadata, true) ?? [];

            $result = \Aero\Clouds\Classes\Mail\MailService::send(
                $log->template_code,
                $log->recipient_email,
                $data,
                $metadata
            );

            if ($result['success']) {
                Flash::success('Correo reenviado exitosamente');
            } else {
                Flash::error('Error al reenviar: ' . ($result['error'] ?? 'Desconocido'));
            }

        } catch (\Exception $e) {
            Flash::error('Error al reenviar correo: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Eliminar correos antiguos
     */
    public function onDeleteOld()
    {
        $days = post('days', 30);

        try {
            $deleted = EmailLog::where('created_at', '<', now()->subDays($days))->delete();
            Flash::success("Se eliminaron {$deleted} registros antiguos");
        } catch (\Exception $e) {
            Flash::error('Error al eliminar registros: ' . $e->getMessage());
        }

        return $this->listRefresh();
    }

    /**
     * Estadísticas de correos
     */
    public function stats()
    {
        $this->pageTitle = 'Estadísticas de Correos';

        $stats = [
            'today' => EmailLog::whereDate('created_at', today())->count(),
            'week' => EmailLog::where('created_at', '>=', now()->subDays(7))->count(),
            'month' => EmailLog::where('created_at', '>=', now()->subDays(30))->count(),
            'sent' => EmailLog::sent()->count(),
            'failed' => EmailLog::failed()->count(),
            'by_template' => EmailLog::selectRaw('template_code, COUNT(*) as count')
                ->groupBy('template_code')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'recent_failed' => EmailLog::failed()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];

        $this->vars['stats'] = $stats;
    }
}
