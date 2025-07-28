<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang');
            $table->integer('qty');
            $table->text('status_yang_mengembalikan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Approval
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('pengeluaran_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang');
            $table->integer('qty');
            $table->string('status')->nullable();
            $table->string('tujuan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Approval
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('stock_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang');
            $table->integer('qty');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang');
            $table->integer('qty');
            $table->unsignedBigInteger('id_barang_masuk')->nullable();
            $table->unsignedBigInteger('id_barang_keluar')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Approval
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type'); // Polymorphic relation
            $table->unsignedBigInteger('approvable_id');
            $table->unsignedBigInteger('approved_by');
            $table->timestamp('approved_at');
            $table->text('notes')->nullable();
        });

        // Foreign key constraints
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->foreign('id_barang')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('pengeluaran_barang', function (Blueprint $table) {
            $table->foreign('id_barang')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('stock_barang', function (Blueprint $table) {
            $table->foreign('id_barang')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('history', function (Blueprint $table) {
            $table->foreign('id_barang')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('id_barang_masuk')->references('id')->on('barang_masuk')->onDelete('set null');
            $table->foreign('id_barang_keluar')->references('id')->on('pengeluaran_barang')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
        });

    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('history');
        Schema::dropIfExists('stock_barang');
        Schema::dropIfExists('pengeluaran_barang');
        Schema::dropIfExists('barang_masuk');
    }
};
