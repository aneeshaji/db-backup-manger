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
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron Job running at ". now());
        $schema1 = 'gmmso';
        $password = '';
        $filename = "backup-" .Str::random(4).'-'. Carbon::now()->format('Y-m-d') . ".sql";
        // Create backup folder and set permission if not exist.
        $storageAt = storage_path() . "/db-backups/".Carbon::now()->format('Y').'/';

        if (!File::exists($storageAt)) {
            File::makeDirectory($storageAt, 0755, true, true);
        }

        $command1 = sprintf('C:\xampp\mysql\bin\mysqldump %s -u root -p > %s', $schema1, $storageAt.$filename);

        $returnVar = NULL;
        $output = NULL;
        exec($command1, $output, $returnVar);

        // Creating file object
        //$fileObject = $this->createFileObject($storageAt);

        // Upload backup file to AWS S3 Bucket 
        $backupFilePath = $storageAt . $filename;
        $s3Uploadpath = "/db-backups/".Carbon::now()->format('Y').'/'. $filename;
        //dd($filename);
        if (File::exists($backupFilePath)) {
            Storage::disk('s3')->put($s3Uploadpath, file_get_contents($backupFilePath), 'public');
            $urlPath = Storage::disk('s3')->url($s3Uploadpath);
            if ($urlPath) {
                info("Cron Job completed at ". now());
            } else {
                info("Error");
            }
        }
    }

    /**
     * Function for creating fileObject.
     *
     * @var array
     */
    public function createFileObject($url){
  
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