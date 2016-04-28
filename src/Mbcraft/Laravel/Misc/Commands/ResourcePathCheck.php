<?php

namespace Mbcraft\Laravel\Misc\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

use Mbcraft\Piol\File;
use Mbcraft\Piol\Dir;

class ResourcePathCheck extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:check_path';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the path inside views of js and css resources included using @require_local_js and @require_local_css.';
    
    
    private $has_broken_paths = false;
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $views_root = new Dir("/resources/views/");
        
        $this->info("Checking resources path inside views ...");
        $views_root->visit($this);
        
        if (!$this->has_broken_paths)
            $this->info("[[[ OK : All paths are consistent ]]]");
    }
    
    public function visit(Dir $dir) {
        $files = $dir->listFiles();
        
        foreach ($files as $f) {
            if ($f instanceof File && $f->getFullExtension()=="blade.php") {
                $content = $f->getContent();
                $broken_list = array();
                $matches = array();
                preg_match_all("/\@require_local_js\(['\"](?P<path>[^'\"]+)['\"]\)/",$content,$matches,PREG_SET_ORDER);

                foreach ($matches as $m) {
                    if (!$this->check_path($m["path"]))
                        $broken_list[]=$m[0];
                }
                preg_match_all("/\@require_local_css\(['\"](?P<path>[^'\"]+)['\"]\)/",$content,$matches,PREG_SET_ORDER);
                foreach ($matches as $m) {
                    if (!$this->check_path($m["path"]))
                        $broken_list[]=$m[0];
                }
                if (!empty($broken_list)) {
                    $this->has_broken_paths = true;
                    $this->info("");
                    $this->error("Broken paths inside file ".$f->getPath()." : ");
                    foreach ($broken_list as $bk) {
                        $this->info($bk." is broken.");
                    }
                }
                
            }
        }
        return true;
    }
    
    private function check_path($path) {
        $f = new File('/public/'.$path);
        return $f->exists();
    }
}