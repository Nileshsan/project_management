<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Leave;
use App\Models\LeaveFile;
use App\Helper\Files;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction();

            // Get all existing leave IDs without using withTrashed
            $existingLeaveIds = Leave::select('id')
                ->get()
                ->pluck('id')
                ->toArray();

            $folder = LeaveFile::FILE_PATH;
            $folderPath = public_path(Files::UPLOAD_FOLDER . '/' . $folder);

            if (!File::exists($folderPath)) {
                Log::info('Leave files folder does not exist: ' . $folderPath);
                DB::commit();
                return;
            }

            $deletedCount = 0;
            $leaveFolders = File::directories($folderPath);
            
            foreach ($leaveFolders as $leaveFolder) {
                try {
                    $leaveId = basename($leaveFolder);

                    if (!in_array($leaveId, $existingLeaveIds)) {
                        // Delete associated records from leave_files table
                        LeaveFile::where('leave_id', $leaveId)->delete();
                        
                        // Delete the physical directory
                        if (File::deleteDirectory($leaveFolder)) {
                            $deletedCount++;
                            Log::info('Deleted leave folder: ' . $leaveFolder);
                        } else {
                            Log::warning('Failed to delete leave folder: ' . $leaveFolder);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing leave folder: ' . $leaveFolder . ' - ' . $e->getMessage());
                    continue;
                }
            }

            Log::info('Cleanup completed. Deleted ' . $deletedCount . ' leave folders');
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave cleanup migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for cleanup
        return;
    }
};