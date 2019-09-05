<?php

namespace KBox\Statistics\Console;

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Console\Input\InputOption;

class DownloadAnalyticsCommand extends Command
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure()
    {
        
        $this->setName('download:analytics')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Authentication token to make requests to the service', null)
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The starting date for the statistics range, e.g. 2019-04-09', null)
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'The end date for the statistics range, e.g. 2019-04-10', date('Y-m-d'))
            ->setDescription('Download analytics from Matomo.');
    }

    protected function fire()
    {
        $token = $this->input->getOption('token');
        $from = $this->input->getOption('from');
        $to = $this->input->getOption('to');

        $analytics_url = rtrim($this->app->config['analyticsServiceUrl'] ?? '', '/');
        $instances = collect($this->app->config['instances'] ?? []);

        if(empty($analytics_url)){
            $this->console->error("No analytics URL configured");
            return 1;
        }
        
        if($instances->isEmpty()){
            $this->console->error("No K-Box instances configured");
            return 2;
        }

        $client = new HttpClient();
        
        $this->console->write('Getting data from the analytics service...');

        $folder = $this->getAbsolutePath("raw-data/analytics");

        if(!is_dir($folder)){
            mkdir($folder, 0777, true);
        }
        
        $instances->each(function($instance, $name) use($analytics_url, $client, $from, $to, $token, $folder){
            
            $this->console->write(" > $name");
            
            $analyticsId = $instance['analyticsId'];

            $response = $client->request(
                'GET',
                "$analytics_url/index.php?date=$from,$to&filter_limit=-1&format=CSV&format_metrics=0&language=en&idSite=$analyticsId&method=API.get&module=API&period=day&token_auth=$token",
                ['sink' => "$folder/$name.csv"]
            );

            if($response->getStatusCode() !== 200){
                $this->console->error("Unable to fetch data for $name [$analyticsId]: {$response->getStatusCode()}");
            };

        });

        $this->console->info("Data retrieved in ./raw-data/analytics/");
    }

    private function getAbsolutePath($path)
    {
        return $this->app->cwd . '/' . trimPath($path);
    }

}
