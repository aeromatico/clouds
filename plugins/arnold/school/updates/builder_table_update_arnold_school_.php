<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchool extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->string('lastname', 255);
            $table->date('birthday');
            $table->integer('ci');
            $table->string('address');
            $table->dropColumn('adress');
            $table->dropColumn('turn');
            $table->dropColumn('active');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->dropColumn('lastname');
            $table->dropColumn('birthday');
            $table->dropColumn('ci');
            $table->dropColumn('address');
            $table->string('adress', 255);
            $table->string('turn', 255);
            $table->boolean('active');
        });
    }
}
