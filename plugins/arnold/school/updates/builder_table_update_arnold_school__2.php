<?php namespace Arnold\School\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateArnoldSchool2 extends Migration
{
    public function up()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->string('phone', 255)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->date('mail')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->integer('lastname')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->string('birthday', 255)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('arnold_school_', function($table)
        {
            $table->integer('phone')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->string('mail', 255)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->string('lastname', 255)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->date('birthday')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
