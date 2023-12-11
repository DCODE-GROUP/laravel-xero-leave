<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('leaveable');
            $table->string('xero_leave_application_id', 50)->nullable()->comment('The identifier returned from xero');
            $table->string('xero_employee_id', 50)->nullable()->comment('Just saves having to do a lookup all the time');
            $table->string('xero_leave_type_id', 50)->nullable()->comment('The identifier returned from xero stored in the configuration');
            $table->date('start_date');
            $table->date('end_date');
            $table->double('units', 8, 2)->nullable()->comment('in case the period is less than one day');
            $table->string('title', 50);
            $table->string('description', 200)->nullable();
            $table->json('xero_periods')->nullable();
            $table->text('xero_exception_message')->nullable();
            $table->mediumText('xero_exception')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamp('xero_synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}
