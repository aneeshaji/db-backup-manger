<?php
  
namespace App\Console\Commands;
  
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    /**
     * Function for creating database backup.
     *
     * @var array
     */
    public function createDatabasebackup($database, $user, $password) 
    {
        info("Database backup cron job started at ". now());
        $filename = "backup-" .$database.'-'.Str::random(4).'-'. Carbon::now()->format('Y-m-d') . ".sql";
        // Create backup folder and set permission if not exist.
        $storageAt = storage_path() . "/db-backups/".Carbon::now()->format('Y').'/';

        if (!File::exists($storageAt)) {
            File::makeDirectory($storageAt, 0755, true, true);
        }

        $env = env('APP_ENV');
        if ($env == 'local') {
            $command1 = sprintf('C:\xampp\mysql\bin\mysqldump %s -u %s -p%s> %s', $database, $user, $password, $storageAt.$filename);
        } else {
            $command1 = sprintf('mysqldump %s -u %s -p%s > %s', $database, $user, $password, $storageAt.$filename);
        }
        $returnVar = NULL;
        $output = NULL;
        exec($command1, $output, $returnVar);
        
        if ($returnVar == 0) {
            info("Mysqldump process completed at ". now());
            // Upload backup file to AWS S3 Bucket 
            $backupFilePath = $storageAt . $filename;
            $s3Uploadpath = "/db-backups/".Carbon::now()->format('Y').'/'. $filename;
            if (File::exists($backupFilePath)) {
                Storage::disk('s3')->put($s3Uploadpath, file_get_contents($backupFilePath), 'public');
                $urlPath = Storage::disk('s3')->url($s3Uploadpath);
                if ($urlPath) {
                    info("Cron Job completed at ". now());
                } else {
                    info("Cron job encountered an error at ", now());
                }
            }
        } else {
            info("Mysqldump process encountered a problem at ". now());
        }
    } 

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // For First Database
        $database_1 = env('BACKUP_1_DATABASE');
        $user_1 = env('BACKUP_1_USER');
        $password_1 = env('BACKUP_1_PASSWORD');
        if ($database_1 != null && $user_1 != null) {
            $this->createDatabasebackup($database_1, $user_1, $password_1);
        }

        // For second Database
        $database_2 = env('BACKUP_2_DATABASE');
        $user_2 = env('BACKUP_2_USER');
        $password_2 = env('BACKUP_2_PASSWORD');
        if ($database_2 != null && $user_2 != null) {
            $this->createDatabasebackup($database_2, $user_2, $password_2);
        }
    }

    /**
     * Function for creating fileObject.
     *
     * @var array
     */
    public function createFileObject($url) {
  
        $path_parts = pathinfo($url);
        $newPath = $path_parts['dirname'] . '/tmp-files/';
        if(!is_dir ($newPath)){
            mkdir($newPath, 0777);
        }
  
        $newUrl = $newPath . $path_parts['basename'];
        copy($url, $newUrl);
        $imgInfo = getimagesize($newUrl);

        $file = new UploadedFile(
            $newUrl,
            $path_parts['basename'],
            //$imgInfo['mime'],
            filesize($url),
            true,
            TRUE
        );
  
        return $file;
    }
}