<?php namespace Aero\Clouds\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Aero\Clouds\Models\Task;
use Flash;

/**
 * Tasks Backend Controller
 */
class Tasks extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array Permissions required
     */
    public $requiredPermissions = ['aero.clouds.access_tasks'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Aero.Clouds', 'cloud-teams', 'tasks');
    }

    /**
     * Extend list query to show only visible tasks
     */
    public function listExtendQuery($query)
    {
        $query->visibleToUser()->notArchived();
    }

    /**
     * Quick status change via AJAX
     */
    public function onQuickStatusChange()
    {
        $taskId = post('task_id');
        $newStatus = post('status');

        $task = Task::findOrFail($taskId);
        $task->status = $newStatus;
        $task->save();

        Flash::success('Task status updated to ' . $task->getStatusOptions()[$newStatus]);

        return $this->listRefresh();
    }

    /**
     * Quick priority change via AJAX
     */
    public function onQuickPriorityChange()
    {
        $taskId = post('task_id');
        $newPriority = post('priority');

        $task = Task::findOrFail($taskId);
        $task->priority = $newPriority;
        $task->save();

        Flash::success('Task priority updated');

        return $this->listRefresh();
    }

    /**
     * Quick assign via AJAX
     */
    public function onQuickAssign()
    {
        $taskId = post('task_id');
        $userId = post('user_id');

        $task = Task::findOrFail($taskId);
        $task->assigned_to = $userId;
        $task->save();

        Flash::success('Task assigned successfully');

        return $this->listRefresh();
    }

    /**
     * Kanban board view
     */
    public function kanban()
    {
        $this->pageTitle = 'Task Board';

        // Change menu context for Kanban
        BackendMenu::setContext('Aero.Clouds', 'cloud-teams', 'kanban');

        // Eager load assigned_users to avoid N+1 queries
        $this->vars['todoTasks'] = Task::with('assigned_users')
            ->todo()->visibleToUser()->notArchived()->ordered()->get();
        $this->vars['doingTasks'] = Task::with('assigned_users')
            ->doing()->visibleToUser()->notArchived()->ordered()->get();
        $this->vars['doneTasks'] = Task::with('assigned_users')
            ->done()->visibleToUser()->notArchived()->ordered()->get();
        $this->vars['backendUsers'] = \Backend\Models\User::all();
    }

    /**
     * Move task between columns (AJAX)
     */
    public function onMoveTask()
    {
        $taskId = post('task_id');
        $newStatus = post('status');
        $newOrder = post('order', 0);

        $task = Task::findOrFail($taskId);
        $currentUser = \BackendAuth::getUser();

        if (!$currentUser) {
            Flash::error('You must be logged in to move tasks');
            return ['error' => 'Not authenticated'];
        }

        // Basic permission check: user must be able to view the task
        if (!$task->canUserView($currentUser->id)) {
            Flash::error('You do not have permission to edit this task');
            return ['error' => 'Permission denied'];
        }

        // Special validation ONLY for frozen tasks
        if ($task->isFrozen()) {
            // Frozen tasks can only be moved by the creator
            if (!$task->canUserEdit($currentUser->id)) {
                Flash::error('This task is frozen. Only the creator can move it.');
                return [
                    'error' => 'Task is frozen',
                    'message' => 'Only the task creator can move frozen tasks'
                ];
            }

            // If moving frozen task to "done", check completion permissions
            if ($newStatus === 'done' && !$task->canUserMoveToCompleted($currentUser->id)) {
                Flash::error('This task is frozen. Only the creator can mark it as done.');
                return [
                    'error' => 'Task is frozen',
                    'message' => 'Only the task creator can complete frozen tasks'
                ];
            }
        }

        // All checks passed - move the task
        $task->status = $newStatus;
        $task->order = $newOrder;
        $task->save();

        return ['success' => true];
    }

    /**
     * Add reply to task (AJAX) - from form
     */
    public function onAddTaskReply()
    {
        $taskId = input('task_id');
        $message = input('reply_message');

        \Log::info('onAddTaskReply called', [
            'task_id' => $taskId,
            'message' => $message,
            'all_input' => input()
        ]);

        if (!$message || trim($message) === '') {
            Flash::error('Please enter a message');
            \Log::warning('Empty message received');
            return ['error' => 'Please enter a message'];
        }

        try {
            $task = Task::findOrFail($taskId);

            // Check if user can view this task
            if (!$task->canUserView()) {
                Flash::error('You do not have permission to reply to this task');
                return ['error' => 'Permission denied'];
            }

            // Get current backend user
            $currentUser = \BackendAuth::getUser();
            if (!$currentUser) {
                Flash::error('You must be logged in to reply');
                return ['error' => 'Not authenticated'];
            }

            $reply = new \Aero\Clouds\Models\TaskReply();
            $reply->task_id = $taskId;
            $reply->user_id = $currentUser->id;  // Assign user_id before validation
            $reply->message = $message;
            $reply->save();

            \Log::info('Reply saved successfully', ['reply_id' => $reply->id]);

            Flash::success('Reply added successfully');

            return ['success' => true, 'reply_id' => $reply->id];
        } catch (\Exception $e) {
            \Log::error('Error saving reply: ' . $e->getMessage());
            Flash::error('Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Archive completed tasks (AJAX)
     */
    public function onArchiveCompleted()
    {
        $completed = Task::done()->notArchived()->get();
        $count = $completed->count();

        foreach ($completed as $task) {
            $task->archive();
        }

        Flash::success("Archived {$count} completed tasks");

        return $this->listRefresh();
    }

    /**
     * View archived tasks
     */
    public function archived()
    {
        $this->pageTitle = 'Archived Tasks';
        $this->vars['tasks'] = Task::archived()->visibleToUser()->ordered()->paginate(20);
    }

    /**
     * Unarchive a task (AJAX)
     */
    public function onUnarchive()
    {
        $taskId = post('task_id');
        $task = Task::findOrFail($taskId);
        $task->unarchive();

        Flash::success('Task unarchived successfully');

        return $this->listRefresh();
    }

    /**
     * Performance reports view
     */
    public function reports()
    {
        $this->pageTitle = 'Performance Reports';
        BackendMenu::setContext('Aero.Clouds', 'cloud-teams', 'reports');

        // Get filter parameters
        $period = input('period', 'month'); // day, week, month, year
        $userId = input('user_id', null);
        $startDate = input('start_date', null);
        $endDate = input('end_date', null);

        // Calculate date range based on period
        $dateRange = $this->getDateRangeForPeriod($period, $startDate, $endDate);

        // Get all backend users for filter
        $this->vars['users'] = \Backend\Models\User::orderBy('login')->lists('login', 'id');
        $this->vars['currentPeriod'] = $period;
        $this->vars['currentUserId'] = $userId;
        $this->vars['startDate'] = $dateRange['start'];
        $this->vars['endDate'] = $dateRange['end'];

        // Generate report data
        $this->vars['reportData'] = $this->generateReportData($dateRange, $userId);
    }

    /**
     * Get date range for period
     */
    protected function getDateRangeForPeriod($period, $customStart = null, $customEnd = null)
    {
        if ($customStart && $customEnd) {
            return [
                'start' => \Carbon\Carbon::parse($customStart)->startOfDay(),
                'end' => \Carbon\Carbon::parse($customEnd)->endOfDay()
            ];
        }

        $now = \Carbon\Carbon::now();

        switch ($period) {
            case 'day':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'month':
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Generate comprehensive report data
     */
    protected function generateReportData($dateRange, $userId = null)
    {
        $query = Task::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('assigned_users', function($q2) use ($userId) {
                      $q2->where('backend_users.id', $userId);
                  });
            });
        }

        $tasks = $query->with(['assigned_users', 'creator'])->get();

        // Calculate metrics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $inProgressTasks = $tasks->where('status', 'doing')->count();
        $todoTasks = $tasks->where('status', 'todo')->count();

        // Calculate completion rate
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        // Tasks completed on time vs late
        $completedOnTime = 0;
        $completedLate = 0;
        $averageCompletionTime = 0;
        $totalCompletionTime = 0;
        $completedWithDeadline = 0;

        foreach ($tasks->where('status', 'done') as $task) {
            if ($task->completed_at) {
                $completionTime = $task->created_at->diffInHours($task->completed_at);
                $totalCompletionTime += $completionTime;

                if ($task->due_date) {
                    $completedWithDeadline++;
                    if ($task->completed_at->lte($task->due_date)) {
                        $completedOnTime++;
                    } else {
                        $completedLate++;
                    }
                }
            }
        }

        if ($completedTasks > 0) {
            $averageCompletionTime = round($totalCompletionTime / $completedTasks, 2);
        }

        // Overdue tasks
        $overdueTasks = $tasks->filter(function($task) {
            return $task->due_date
                && $task->due_date->isPast()
                && $task->status !== 'done';
        })->count();

        // Frozen tasks
        $frozenTasks = $tasks->filter(function($task) {
            return $task->isFrozen();
        })->count();

        // Performance by user
        $userPerformance = [];
        $users = \Backend\Models\User::all();

        foreach ($users as $user) {
            $userTasks = $tasks->filter(function($task) use ($user) {
                return $task->created_by == $user->id
                    || $task->assigned_users->contains('id', $user->id);
            });

            if ($userTasks->count() > 0) {
                $userCompleted = $userTasks->where('status', 'done')->count();
                $userTotal = $userTasks->count();

                $userPerformance[] = [
                    'user' => $user->login,
                    'user_id' => $user->id,
                    'total' => $userTotal,
                    'completed' => $userCompleted,
                    'in_progress' => $userTasks->where('status', 'doing')->count(),
                    'todo' => $userTasks->where('status', 'todo')->count(),
                    'completion_rate' => $userTotal > 0 ? round(($userCompleted / $userTotal) * 100, 2) : 0,
                    'overdue' => $userTasks->filter(function($t) {
                        return $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                    })->count()
                ];
            }
        }

        // Sort by completion rate
        usort($userPerformance, function($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });

        // Tasks by priority
        $priorityBreakdown = [
            'low' => $tasks->where('priority', 'low')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'urgent' => $tasks->where('priority', 'urgent')->count(),
        ];

        // Daily completion trend (for charts)
        $dailyCompletions = [];
        $currentDate = $dateRange['start']->copy();

        while ($currentDate->lte($dateRange['end'])) {
            $dayTasks = $tasks->filter(function($task) use ($currentDate) {
                return $task->completed_at
                    && $task->completed_at->isSameDay($currentDate);
            })->count();

            $dailyCompletions[] = [
                'date' => $currentDate->format('Y-m-d'),
                'label' => $currentDate->format('M j'),
                'count' => $dayTasks
            ];

            $currentDate->addDay();
        }

        return [
            'summary' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'todo_tasks' => $todoTasks,
                'completion_rate' => $completionRate,
                'completed_on_time' => $completedOnTime,
                'completed_late' => $completedLate,
                'overdue_tasks' => $overdueTasks,
                'frozen_tasks' => $frozenTasks,
                'average_completion_hours' => $averageCompletionTime,
            ],
            'user_performance' => $userPerformance,
            'priority_breakdown' => $priorityBreakdown,
            'daily_completions' => $dailyCompletions,
        ];
    }
}
