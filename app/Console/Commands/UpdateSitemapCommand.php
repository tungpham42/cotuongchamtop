<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

class UpdateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update sitemap';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = public_path('sitemap.xml');
        SitemapGenerator::create('https://cotuong.top/')->getSitemap()->writeToFile($path);
        $this->info('Sitemap updated successfully!');
    }
}
