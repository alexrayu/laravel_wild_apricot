<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * CSV service provider.
 */
class CsvProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CsvProvider::class, function ($app) {
            return new CsvProvider($app);
        });
    }

    /**
     * Get content of csv file.
     *
     * @param  string  $file_name
     *   File name to import.
     * @param  string  $key_id
     *   Identifier for the main (id) key.
     * @param  int  $header_rows
     *   How many rows are in the header.
     * @return array
     *   Imported CSV.
     */
    public function getCSV($file_name, $key_id = 'id', $header_rows = 1)
    {
        $data = [];
        $header = [];
        $new_key = false;
        $pos_id = 0;
        if ($handle = fopen($file_name, 'r')) {
            for ($i = 0; $i < $header_rows; $i++) {
                $header = fgetcsv($handle);
                if (! in_array($key_id, $header)) {
                    $new_key = true;
                    array_unshift($header, $key_id);
                }
            }
            while (($fragment = fgetcsv($handle)) !== false) {
                if ($new_key) {
                    array_unshift($fragment, $pos_id);
                    $joined = array_combine($header, $fragment);
                    $data[$pos_id] = $joined;
                    $pos_id++;
                } else {
                    $joined = array_combine($header, $fragment);
                    $data[$joined[$key_id]] = $joined;
                }
            }
            fclose($handle);
        }

        return ['header' => $header, 'data' => $data];
    }

    /**
     * Write the csv file.
     *
     *
     * @return bool
     */
    public function putCSV($output_file_name, $data)
    {
        if ($handle = fopen($output_file_name, 'w')) {
            foreach ($data as $line) {
                fputcsv($handle, $line);
            }
            fclose($handle);

            return true;
        }

        return false;
    }
}
