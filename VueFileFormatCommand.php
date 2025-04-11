<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VueFileFormatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue:format {format : has 3 char(s,t,l) order of the file format s:scripts, t:template, l:style} {--ask : ask before updating the file}';
    
   
    /**
     * The console command description.
     *
     * @var string
     */
  
    protected $description = "Format Vue files in the specified order";
   

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get the format argument
        $format = $this->argument('format');
        $ask = $this->option('ask');
        $this->info('Format: ' . $format);
        $this->info('Ask: ' . $ask);
     
        // check if the format is valid
        // check if the format is 3 characters long
        // check if the format is s,t,l
        // check if the format is s,t,l in any order
        if (strlen($format) != 3) {
            $this->error('Invalid format');
            return;
        }
        if (!preg_match('/^[stl]{3}$/', $format)) {
            $this->error('Invalid format');
            return;
        }
        $folder = base_path('resources/js/');
        // get all files in the folder and subfolders
    
        $dir = new \RecursiveDirectoryIterator($folder);
        $iterator = new \RecursiveIteratorIterator($dir);
        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'vue') {
                $files[] = $file->getPathname();
            }
        }
      
        $skippedAll = false;
        foreach ($files as $file) {
          
            if(!$skippedAll && $ask == 'true'){
                $this->info('Processing file: ' . $file);
                // yes or no or skip all
                $response = $this->choice('Do you want to format this file?', ['y'=>'yes', 'n'=>'no','s'=> 'skip all'], 0);
                //$response = $this->confirm('Do you want to format this file?');
                if($response == 's'){
                    $skippedAll = true;
                    $response = 'n';
                }
            }else{
                $response = 'y';
            }
            if($response == 'n'){
                $this->info('Skipping file: ' . $file);
                continue;
            }
            
                $this->formattingFile($format,$file);
              
        }
        $this->info('Files formatted successfully');
        
    }

    protected function formattingFile($format,$file){
        {
            $this->info('Processing file: ' . $file);
            $content = file_get_contents($file);
            $script = '';
            $template = '';
            $style = '';
            preg_match('/<template>(.*?)<\/template>/s', $content, $matches);
            if (isset($matches[1])) {
                $template = $matches[1];
            }
            preg_match_all('/<script(.*?)>(.*?)<\/script>/s', $content, $matches);
            if (isset($matches[0])) {
                foreach ($matches[0] as $match) {
                    if($script == '') {
                        $script = $match;
                    }
                    else {
                        $script .= "\n".$match;
                    }
                    
                }
            }
            preg_match_all('/<style(.*?)>(.*?)<\/style>/s', $content, $matches);
            if (isset($matches[0])) {
                foreach ($matches[0] as $match) {
                    if($style == '') {
                        $style = $match;
                    }
                    else {
                        $style .= "\n\r".$match;
                    }
                    
                }
            }
            // now update the file with the new format
            $content = '';
            for ($index = 0; $index < strlen($format); $index++) {
                $char = $format[$index];
                if ($char == 's') {
                    $content .= $script;
                } elseif ($char == 't') {
                    $content .= "<template>$template</template>";
                } elseif ($char == 'l') {
                    $content .= $style;
                }
                if($index!== (strlen($format)-1)) $content .= "\n\n";
            }
          
                file_put_contents($file, $content);
               
        }
    }
}
