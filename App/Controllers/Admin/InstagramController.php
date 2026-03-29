<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use App\Services\InstagramService;

class InstagramController extends Controller
{
    private InstagramService $instagramService;

    public function __construct()
    {
        // Enforce admin/staff access.
        if (!Auth::check() || (Auth::user()['role'] !== 'admin' && Auth::user()['role'] !== 'user')) {
            Session::flash('error', 'Acceso denegado a la estrategia de Instagram.');
            $this->redirect('/dashboard');
        }
        $this->instagramService = new InstagramService();
    }

    public function index()
    {
        $calendars = $this->instagramService->getAllCalendars();
        
        $this->viewLayout('admin/instagram/index', 'admin', [
            'title' => 'Asistente de Estrategia Instagram | ' . \Core\Config::get('business.company_name'),
            'calendars' => $calendars
        ]);
    }

    public function view($view = null, $data = [])
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/instagram');
        }

        $calendar = $this->instagramService->getCalendar($id);
        if (!$calendar) {
            $this->redirect('/admin/instagram');
        }

        $this->viewLayout('admin/instagram/view', 'admin', [
            'title' => 'Calendario: ' . $calendar['week_label'],
            'calendar' => $calendar
        ]);
    }

    public function generate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $weekNumber = date('W', strtotime($startDate));
            $year = date('Y', strtotime($startDate));
            $weekLabel = "Semana $weekNumber - $year";

            $calendarId = $this->instagramService->generateWeeklyCalendar($startDate, $weekLabel);

            if ($calendarId) {
                Session::flash('success', 'Calendario generado exitosamente.');
                $this->redirect('/admin/instagram/view?id=' . $calendarId);
            } else {
                Session::flash('error', 'Ocurrió un error al generar el calendario.');
                $this->redirect('/admin/instagram');
            }
        }

        $this->viewLayout('admin/instagram/generate', 'admin', [
            'title' => 'Generar Estrategia Semanal'
        ]);
    }

    public function updatePost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId = intval($_POST['post_id'] ?? 0);
            $calendarId = intval($_POST['calendar_id'] ?? 0);

            $data = [
                'internal_title' => $_POST['internal_title'] ?? '',
                'copy_text' => $_POST['copy_text'] ?? '',
                'cta_text' => $_POST['cta_text'] ?? '',
                'hashtags' => $_POST['hashtags'] ?? '',
                'visual_prompt' => $_POST['visual_prompt'] ?? '',
                'publish_date' => $_POST['publish_date'] ?? date('Y-m-d'),
                'publish_time' => $_POST['publish_time'] ?? date('H:i')
            ];

            if ($this->instagramService->updatePost($postId, $data)) {
                Session::flash('success', 'Cambios guardados correctamente.');
                $this->redirect('/admin/instagram/view?id=' . $calendarId);
            } else {
                Session::flash('error', 'No se pudieron guardar los cambios.');
                $this->redirect('/admin/instagram/view?id=' . $calendarId);
            }
        }
    }

    public function finalize()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->instagramService->finalizeCalendar($id);
                Session::flash('success', 'Calendario finalizado.');
            }
            $this->redirect('/admin/instagram/view?id=' . $id);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->instagramService->deleteCalendar($id);
                Session::flash('success', 'Calendario eliminado.');
            }
            $this->redirect('/admin/instagram');
        }
    }

    public function regeneratePost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postId = intval($_POST['post_id'] ?? 0);
            $calendarId = intval($_POST['calendar_id'] ?? 0);

            if ($this->instagramService->regeneratePost($postId)) {
                Session::flash('success', 'Contenido regenerado con IA.');
                $this->redirect('/admin/instagram/view?id=' . $calendarId);
            } else {
                Session::flash('error', 'Fallo al regenerar el contenido.');
                $this->redirect('/admin/instagram/view?id=' . $calendarId);
            }
        }
    }

    public function downloadCsv()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) return;

        $csv = $this->instagramService->exportCalendarToCsv($id);
        $calendar = $this->instagramService->getCalendar($id);

        $filename = "Calendario_Instagram_" . str_replace(' ', '_', $calendar['week_label']) . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }
}
