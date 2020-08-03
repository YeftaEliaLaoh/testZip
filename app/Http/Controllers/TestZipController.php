<?php
namespace App\Http\Controllers;
use File;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TestZipController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $zip = new ZipArchive;

        $fileName = 'app-debug.aab';
        $files = public_path('myFiles'); // get all file names
        $this->rrmdir($files);

        $res = $zip->open(public_path($fileName));

        if ($res === true)
        {
            $zip->extractTo(public_path('myFiles'));
            $zip->close();
        }
        else
        {
            echo 'doh!';
        }

        $file_path = public_path('myFiles/base/res/raw/tes');
        if (!file_exists($file_path))
        {
            echo 'doh!';
        }

        $data_to_write = "abcdefg";
        $file_handle = fopen($file_path, 'w');
        fwrite($file_handle, $data_to_write);
        fclose($file_handle);

        $zip->open(public_path($fileName) , ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(public_path('myFiles')) , RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(public_path('myFiles')) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
        
        $output = shell_exec('java -jar bundletool-all-1.0.0.jar build-apks --overwrite --mode=universal --bundle=/var/www/html/testApp/public/app-debug.aab --output=/var/www/html/testApp/public/app.apks');
        echo "<pre>$output</pre>";
        
        
        $res = $zip->open(public_path('app.apks'));

        if ($res === true)
        {
            $zip->extractTo(public_path('result'));
            $zip->close();
        }
        else
        {
            echo 'doh!';
        }

        //return view('home');
        return response()->download(public_path('result/universal.apk'));
        
    }

    function rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (filetype($dir . "/" . $object) == "dir") $this->rrmdir($dir . "/" . $object);
                    else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

}


