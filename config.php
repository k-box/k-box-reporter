<?php

return [

    // report title
    'reportTitle' => 'Usage statistics of K-Box',

    // client that commissioned the report, if any
    'client' => '',

    // the URL of the analytics service from which download visitors data
    'analyticsServiceUrl' => null,

    // The K-Box instances monitored for this report
    'instances' => [
        /*
            'instance-key' => [
                'label' => 'Instance Label',
                'url' => 'https://instance.url',
                'analyticsId' => 5,
            ],
        */
    ],



    // the lines below should not be touched


    'production' => false,
    'baseUrl' => '',
    'collections' => [
        'months' => [
            'path' => '{year}/{month}',
            'author' => 'K-Box Reporter',
            'sort' => '-period_start'
        ],
    ],

    'colors' => [
        // colors for each type of value that can be plotted
        // must be valid Tailwind text- and bg- colors
        "documents" => 'red-500',
        "users" => 'orange-500',
        "publications" => 'green-500',
        "visits" => 'indigo-500',
        "searches" => 'purple-500',
        "downloads" => 'pink-500',
        "public_links" => 'teal-500',
    ],
    'measures' => [
        // The keys represent the column in the raw CSV data, while
        // the value is the name we will use to refer to that measure
        'Documents Created (incl. Trash)' => 'documents',
        'Users Created' => 'users',
        'Publications performed' => 'publications',
        'Public Links Created' => 'public_links',
        'nb_visits' => 'visits',
        'nb_searches' => 'searches',
        'nb_downloads' => 'downloads',
    ],

    'measure_labels' => [
        'documents' => 'New Documents',
        'users' => 'New Users',
        'publications' => 'New Publications',
        'public_links' => 'New Public Links',
        'visits' => 'Visits',
        'searches' => 'Searches',
        'downloads' => 'Downloads',
    ],

    'loadInstanceData' => function ($page, $dataFile) {

        $file = "./source/static/$dataFile";

        return collect(json_decode(file_get_contents($file)));
    },
    
    'getInstancePerformance' => function ($page, $data) {

        $overall = collect($page->measures->values())->mapWithKeys(function($m) use ($data){
            return [$m => $data->sum($m)];
        });

        return $overall;
    },

    'getLabelForMeasure' => function ($page, $measure) {
        return $page->measure_labels[$measure] ?? str_replace('_', ' ', \Illuminate\Support\Str::title($measure));
    },

    'getPreviousReport' => function($page, $reports){

        $current = \Carbon\Carbon::createFromDate($page->year, $page->month, 1);
        $before = $current->copy()->subMonth(1);
        
        return $reports->filter(function($r) use ($before) {
            return $r->month == $before->month && $r->year == $before->year;
        })->first();
    },
    'getNextReport' => function($page, $reports){

        $current = \Carbon\Carbon::createFromDate($page->year, $page->month, 1);
        $after = $current->copy()->addMonth(1);
        
        return $reports->filter(function($r) use ($after) {
            return $r->month == $after->month && $r->year == $after->year;
        })->first();
    },
];
