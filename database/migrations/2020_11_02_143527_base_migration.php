<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BaseMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ## Admin Table
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('admin_reference_number');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile_number');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        ## Doctor Table
        Schema::create('doctor', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_reference_number');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile_number');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        ## Doctor Configuration Table
        Schema::create('doctor_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_reference_number');
            $table->string('degree');
            $table->string('speciality');
            $table->string('aadhaar');
            $table->string('pan');
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('city_reference_number');
            $table->string('state_reference_number');
            $table->integer('pincode');
            $table->timestamps();
        });

        ## Treatment Table
        Schema::create('treatment', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_reference_number');
            $table->string('treatment_type');
            $table->string('treatment_cost');
            $table->string('remark');
            $table->string('no_of_seating');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        ## Treatment Record Table
        Schema::create('treatment_record', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_record_reference_number');
            $table->string('treatment_reference_number');
            $table->string('patient_reference_number');
            $table->string('doctor_reference_number');
            $table->string('appointment_reference_number');
            $table->string('remark');
            $table->dateTime('treatment_start_date');
            $table->dateTime('treatment_end_date');
            $table->string('no_of_seating');
            $table->enum('status',['OPEN','CLOSED'])->default('OPEN');
            $table->timestamps();
        });

        ## Patient Table
        Schema::create('patient', function (Blueprint $table) {
            $table->id();
            $table->string('patient_reference_number');
            $table->string('patient_name');
            $table->string('mobile_number');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('gender',['MALE','FEMALE']);
            $table->date('dob');
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('city_reference_number');
            $table->string('state_reference_number');
            $table->integer('pincode');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        ## Appointment Table
        Schema::create('appointment', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_reference_number');
            $table->string('patient_reference_number');
            $table->string('doctor_reference_number');
            $table->dateTime('appointment_date_time');
            $table->string('reason');
            $table->enum('status',['PENDING','APPROVED','CANCELLED','VISITED'])->default('PENDING');
            $table->enum('appointment_type',['NEW','FOLLOWUP']);
            $table->timestamps();
        });

        ## Prescription Table
        Schema::create('prescription', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_reference_number');
            $table->string('patient_reference_number');
            $table->string('doctor_reference_number');
            $table->string('treatment_record_reference_number');
            $table->string('appointment_reference_number');
            $table->dateTime('prescription_date_time');
            $table->timestamps();
        });

        ## Prescription Record Table
        Schema::create('prescription_record', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_record_reference_number');
            $table->string('prescription_reference_number');
            $table->string('medicine_name');
            $table->string('cost');
            $table->string('unit');
            $table->string('dosage');
            $table->string('remark');
            $table->timestamps();
        });

        ## Billing Table
        Schema::create('billing', function (Blueprint $table) {
            $table->id();
            $table->string('billing_reference_number');
            $table->string('patient_reference_number');
            $table->string('appointment_reference_number');
            $table->string('doctor_reference_number');
            $table->string('treatment_reference_number');
            $table->double('billing_amount',8,2);
            $table->double('discount',8,2);
            $table->double('final_billing_amount',8,2);
            $table->enum('payment_method',['CASH','DEBITCARD','CREDITCARD','UPI']);
            $table->timestamps();
        });

        ## Payment Table
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference_number');
            $table->string('billing_reference_number');
            $table->double('payment_amount',8,2);
            $table->date('payment_date');
            $table->enum('status',['PENDING','PAID','NOT_PAID']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
