<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Illuminate\Support\Facades\DB;

class FixTaskStatusEnum extends Migration
{
    public function up()
    {
        // First, update any existing 'in_progress' to 'doing' (if any exist)
        DB::table('aero_clouds_tasks')
            ->where('status', 'in_progress')
            ->update(['status' => 'todo']); // Temporarily set to 'todo'

        // Update any 'review' to 'doing' (if any exist)
        DB::table('aero_clouds_tasks')
            ->where('status', 'review')
            ->update(['status' => 'todo']); // Temporarily set to 'todo'

        // Now alter the enum to match the model
        DB::statement("ALTER TABLE aero_clouds_tasks MODIFY COLUMN status ENUM('todo', 'doing', 'done') NOT NULL DEFAULT 'todo'");
    }

    public function down()
    {
        // Revert to old enum values
        DB::statement("ALTER TABLE aero_clouds_tasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'review', 'done') NOT NULL DEFAULT 'todo'");
    }
}
