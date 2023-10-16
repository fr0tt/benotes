<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\ConfigurationUrlParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\Sqlite;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class RunBackupCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup your application data';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        $tempDirPath = config('benotes.temporary_directory');

        $tempDirectory = (new TemporaryDirectory($tempDirPath))
            ->name('tmp')
            ->force()
            ->create()
            ->empty();

        $dumper = $this->dumpDatabase();
        $dbDumpFilename = 'database.sql';
        $pathToDbDump = $tempDirectory->path($dbDumpFilename);

        try {

            $dumper->dumpToFile($pathToDbDump);

            $this->info('Zipping files and directories...');

            $backupFilename = date('Y_m_d_Hi') . '_backup.zip';
            $pathToZip = $tempDirectory->path($backupFilename);
            $zip = new \ZipArchive;

            if (true === ($zip->open($pathToZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))) {
                foreach (Storage::allFiles() as $file) {
                    if (!Str::endsWith($file, '.gitignore')) {
                        $zip->addFile(Storage::path($file), $file);
                    }
                }

                $zip->addFile($pathToDbDump, $dbDumpFilename);

                if (config('benotes.backup_include_env')) {
                    $zip->addFile(base_path('.env'), '.env');
                }

                $zip->close();
            }

            $this->info('Zip was successfully created.');

            Storage::disk(config('benotes.backup_disk'))
                ->putFileAs('', $pathToZip, $backupFilename);

        } catch (\Exception $exception) {
            $this->error('Backup attempt failed.');
            $this->warn($exception->getMessage());
            $tempDirectory->delete();
        }

        $tempDirectory->delete();

    }

    private function dumpDatabase()
    {
        $dbConnection = config('database.default');

        $parser = new ConfigurationUrlParser();
        try {
            $dbConfig = $parser->parseConfiguration(config("database.connections.{$dbConnection}"));
        } catch (\Exception $e) {
            throw new \Exception('Unsupported driver for ' . $dbConnection);
        }

        $driver = strtolower(config("database.connections.{$dbConnection}.driver"));

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $dumper = new MySql();
        } else if ($driver === 'pgsql') {
            $dumper = new PostgreSql();
        } else if ($driver === 'sqlite') {
            $dumper = new Sqlite();
        }

        if (!empty($dbConfig['url'])) {
            return $dumper->setDatabaseUrl($dbConfig['url']);
        }

        if ($driver === 'sqlite') {
            // put here, just in case someone might use sqlite 
            // and provides a database url
            return $dumper->setDbName($dbConfig['database']);
        }

        return $dumper
            ->setHost($dbConfig['host'])
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password'])
            ->setPort($dbConfig['port']);
    }

}
