<?php

namespace KBox\Statistics\Console;

use DatePeriod;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;
use Carbon\CarbonInterval;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ProcessCommand extends Command
{
    const DATE_FIELD = 'date';

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('process')
            ->addOption('month', null, InputOption::VALUE_REQUIRED, 'The month to produce statistics for, e.g. 04', null)
            ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'The year to produce statistics for, e.g. 2019', date('Y'))
            ->setDescription('Process the raw data to produce monthly statistics for all instances.');
    }

    protected function fire()
    {
        $month = (int) $this->input->getOption('month');
        $year = (int) $this->input->getOption('year');

        $from = Carbon::createFromDate($year, $month, 1);
        $to = $from->copy()->addDays($from->daysInMonth-1);

        $month_formatted = $from->format('m');

        $instances = collect($this->app->config['instances'] ?? []);

        if($instances->isEmpty()){
            $this->console->error("No K-Box instances configured");
            return 2;
        }
        
        $this->console->write("Gathering $year-$month data...");

        $base_folder = $this->getAbsolutePath("raw-data");
        $save_folder = $this->getAbsolutePath("source/static");
        $cache_folder = $this->getAbsolutePath("cache");

        if(!is_dir($save_folder)){
            mkdir($save_folder, 0777, true);
        }

        if(!is_dir($cache_folder)){
            mkdir($cache_folder, 0777, true);
        }

        $instance_sum = collect();
        $instance_files = collect();
        
        $instances->each(function($instance, $name) use($month, $year, $base_folder, $save_folder, $instance_sum, $month_formatted, $instance_files){
            
            $this->console->write(" > $name");

            $raw = $this->assembleRawData($name, $base_folder, $month, $year);
            
            \file_put_contents("$save_folder/$name-$year-$month_formatted.json", json_encode($raw->toArray()));
            $instance_files->put($name, "$name-$year-$month_formatted.json");

            $instance_sum->push(collect(array_values($this->app->config['measures']))->mapWithKeys(function($m) use ($raw){
                return [$m => $raw->sum($m)];
            }));
            
        });

        $this->console->write("Calculating month overall...");
        
        $overall = collect(array_values($this->app->config['measures']))->mapWithKeys(function($m) use ($instance_sum){
            return [$m => $instance_sum->sum($m)];
        });

        $this->console->write("Generating report...");
        
        $report_folder = $this->getAbsolutePath('source/_months');

        \file_put_contents("$report_folder/{$from->year}-$month_formatted.md", $this->app['blade']->render('report', [
            'title' => $from->format('F Y'),
            'date' => Carbon::now()->toDateString(),
            'year' => $from->year,
            'month' => $from->format('m'),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'overall' => $overall,
            'instances' => $instance_files,
        ]));

        $this->console->info("Month report prepared.");
    }

    private function getAbsolutePath($path)
    {
        return $this->app->cwd . '/' . trimPath($path);
    }


    /**
     * Create a compund file that contains the raw analytics data + raw KBox usage data
     * with the expected configured measures
     */
    protected function assembleRawData($instance, $folder, $month, $year)
    {
        $measures = $this->app->config['measures'];

        $from = Carbon::createFromDate($year, $month, 1);

        $to = $from->copy()->addDays($from->daysInMonth);

        $period = new DatePeriod($from, CarbonInterval::days(1), $to);

        $usage = $this->getRecordsFromRawData("$folder/kbox/$instance.csv", $month, $year);
        $analytics = $this->getRecordsFromRawData("$folder/analytics/$instance.csv", $month, $year);
        
        $graph = [];

        $default = array_combine(array_values($measures), array_fill(0, count($measures), 0));
        
        foreach ($period as $date) {
            $vals = array_merge($usage->get($date->format('Y-n-j')) ?? [], $analytics->get($date->toDateString()) ?? []);
            $graph[] = array_merge([self::DATE_FIELD => $date->toDateString()], $default, $vals);
        }

        
        return collect($graph);
    }

    protected function getRecordsFromRawData($file, $month, $year)
    {
        $measures = $this->app->config['measures'];
        
        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        $input_bom = $csv->getInputBOM();

        if ($input_bom === Reader::BOM_UTF16_LE || $input_bom === Reader::BOM_UTF16_BE) {
            $csv->addStreamFilter('convert.iconv.UTF-16/UTF-8');
        }

        $filter = (new Statement())
            ->where(function($record) use( $month, $year){
                return Str::startsWith($record[self::DATE_FIELD], [
                    "$year-$month",
                    "$year-". ($month < 10 ? "0$month" : $month),
                ]);
            })
        ;

        $records = $filter->process($csv);

        $entries = collect($records)->mapWithKeys(function($v) use ($measures){

            return [
                $v[self::DATE_FIELD] => $this->mapRawFieldToMeasures($v, $measures)
            ];

        });

        return $entries;
    }

    private function mapRawFieldToMeasures($fields, $measures)
    {
        $intersect = array_intersect_key($fields, $measures);

        return collect($intersect)->mapWithKeys(function($v, $k) use($measures){
            return [$measures[$k] => $v];
        })->toArray();
    }

}
