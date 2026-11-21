<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportSqlForHostinger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-sql {--path= : Custom output path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export full database as MySQL dump compatible with Hostinger phpMyAdmin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating MySQL dump for Hostinger phpMyAdmin...');

        require_once base_path('database/export_mysql.php');

        $this->info('Successfully generated: database/menahem_hostinger_phpmyadmin.sql');

        return self::SUCCESS;
    }
}
