<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestCurses2 extends Migration
{
    public function up()
    {
        Schema::table('aero_test_curses', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->dropColumn('curse_created');
            $table->dropColumn('curse_updated');
            $table->dropColumn('curse_deleted');
        });
    }
    
    public function down()
    {
        Schema::table('aero_test_curses', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->date('curse_created')->nullable();
            $table->date('curse_updated')->nullable();
            $table->date('curse_deleted')->nullable();
        });
    }
}
