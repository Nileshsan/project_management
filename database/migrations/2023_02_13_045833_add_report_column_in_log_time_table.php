<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        // Check and modify log_time_for table
        if (Schema::hasTable('log_time_for')) {
            Schema::table('log_time_for', function (Blueprint $table) {
                if (!Schema::hasColumn('log_time_for', 'timelog_report')) {
                    $table->boolean('timelog_report')->default(false);
                }
                if (!Schema::hasColumn('log_time_for', 'daily_report_roles')) {
                    $table->string('daily_report_roles')->nullable();
                }
            });
        }

        // Check and modify users_chat table
        if (Schema::hasTable('users_chat')) {
            Schema::table('users_chat', function (Blueprint $table) {
                if (!Schema::hasColumn('users_chat', 'notification_sent')) {
                    $table->boolean('notification_sent')->default(true);
                }
            });
        }

        // Check and modify message_settings table
        if (Schema::hasTable('message_settings')) {
            Schema::table('message_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('message_settings', 'send_sound_notification')) {
                    $table->boolean('send_sound_notification')->default(false);
                }
            });
        }

        // PostgreSQL-compatible way to modify the column
        if (Schema::hasColumn('smtp_settings', 'mail_encryption')) {
            // Create new column
            Schema::table('smtp_settings', function (Blueprint $table) {
                $table->string('mail_encryption_new')->nullable()->default('tls');
            });

            // Copy data
            DB::statement("UPDATE smtp_settings SET mail_encryption_new = COALESCE(mail_encryption, 'tls')");

            // Drop old column and rename new column
            Schema::table('smtp_settings', function (Blueprint $table) {
                $table->dropColumn('mail_encryption');
            });

            DB::statement('ALTER TABLE smtp_settings RENAME COLUMN mail_encryption_new TO mail_encryption');

            // Add check constraint
            DB::statement("ALTER TABLE smtp_settings ADD CONSTRAINT smtp_settings_mail_encryption_check CHECK (mail_encryption IN ('ssl', 'tls', 'starttls'))");
        } else {
            // If column doesn't exist, just create it with the right properties
            Schema::table('smtp_settings', function (Blueprint $table) {
                $table->string('mail_encryption')->nullable()->default('tls');
            });
            DB::statement("ALTER TABLE smtp_settings ADD CONSTRAINT smtp_settings_mail_encryption_check CHECK (mail_encryption IN ('ssl', 'tls', 'starttls'))");
        }

        // Check and modify log_time table
        if (Schema::hasTable('log_time')) {
            Schema::table('log_time', function (Blueprint $table) {
                if (!Schema::hasColumn('log_time', 'report')) {
                    $table->boolean('report')->default(false);
                }
            });
        } else {
            // Create log_time table if it doesn't exist
            Schema::create('log_time', function (Blueprint $table) {
                $table->id();
                $table->boolean('report')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Safely drop columns from log_time_for if table and columns exist
        if (Schema::hasTable('log_time_for')) {
            Schema::table('log_time_for', function (Blueprint $table) {
                $columns = ['timelog_report', 'daily_report_roles'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('log_time_for', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Safely drop column from users_chat if table and column exist
        if (Schema::hasTable('users_chat')) {
            Schema::table('users_chat', function (Blueprint $table) {
                if (Schema::hasColumn('users_chat', 'notification_sent')) {
                    $table->dropColumn('notification_sent');
                }
            });
        }

        // Safely drop column from message_settings if table and column exist
        if (Schema::hasTable('message_settings')) {
            Schema::table('message_settings', function (Blueprint $table) {
                if (Schema::hasColumn('message_settings', 'send_sound_notification')) {
                    $table->dropColumn('send_sound_notification');
                }
            });
        }

        // Drop report column from log_time if table and column exist
        if (Schema::hasTable('log_time')) {
            Schema::table('log_time', function (Blueprint $table) {
                if (Schema::hasColumn('log_time', 'report')) {
                    $table->dropColumn('report');
                }
            });
        }
    }
};
