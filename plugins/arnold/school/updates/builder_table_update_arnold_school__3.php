<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchool3 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->integer('mail')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->date('lastname')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->date('mail')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->integer('lastname')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
