<?php namespace Aero\Test\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroTestCurses extends Migration
{
    public function up()
    {
        Schema::rename('aero_test_cursos', 'aero_test_curses');
    }
    
    public function down()
    {
        Schema::rename('aero_test_curses', 'aero_test_cursos');
    }
}
