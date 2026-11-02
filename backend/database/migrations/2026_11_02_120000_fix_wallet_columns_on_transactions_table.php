<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The API stores `wallet_number` and accepts Orange / MTN wallets, but the original
 * transactions migration had neither, so payments failed on a fresh database.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded: some existing databases received this column manually
        if (! Schema::hasColumn('transactions', 'wallet_number')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('wallet_number')->nullable()->after('wallet_type');
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('wallet_type', ['wave', 'djamo', 'moov', 'orange', 'mtn'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('wallet_type', ['wave', 'djamo', 'moov'])->change();
        });
    }
};
