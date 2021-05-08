# K-Box reporter

> Template website and tool to show and retrieve K-Box usage data

Creating usage report for a K-Box can be time consuming. 
This project offer a template website and tools to quickly assemble usage statistics of your K-Box.

The K-Box reporter is made possible thanks to [Jigsaw](https://jigsaw.tighten.co/) and [TailwindCSS](https://tailwindcss.com/).

## Get Started

**requirements**

- [GIT](https://git-scm.com/)
- PHP 7.4 (newer version might work too, but are not tested)
- [Composer](https://getcomposer.org/)
- [Node JS](https://nodejs.org/en/)
- [Yarn](https://yarnpkg.com/lang/en/)

_Before proceeding download a copy of the repository or create a new repository starting from this template._

### Installation

Clone the repository on your machine and grab the dependencies to run a local copy of the website.
All the command must be run from the root of the cloned repository.

```bash
composer install
yarn
```


### Configuration

Configuration is written in [`config.php`](./config.php) and [`config.production.php`](./config.production.php).
The first is the general configuration file, while the second is used for production.

Before the website can be generated some configuration parameters are required:

- List of K-Box instances to include in the reports
- Analytics service URL

> **So far only Matomo is supported as analytics service provider**

**K-Box instances**

The generation of the reports is based on the K-Box instances. The list control how those entries should be named in 
the report and the configuration to retrieve analytics.

Each instance is defined as an associative array:

```php
'instances' => [
  'instance-key' => [
    'label' => 'Instance Label',
    'url' => 'https://instance.url',
    'analyticsId' => 5,
  ],
],
```

where `instance-key` is the unique identifier assigned to the K-Box. It control the name of the statistics 
data file to use and the name of the aggregated reports. `label` is the readable name assigned to the K-Box that
will be presented inside the report. `url` is the link to the K-Box instance. `analyticsId` is the Matomo site identifier
of the K-Box to retrieve the analytics data.


**Analytics service**

The URL of the analytics service need to be added to the configuration array ([`config.php`](./config.php)) in the key `analyticsServiceUrl`:

```php
// the URL of the analytics service from which download visitors data
'analyticsServiceUrl' => 'https://your.service.url',
```

### Add a monthly report

Adding a monthly report requires three macro actions:

1. Download the K-Box usage statistics from each instance
2. Get analytics from Matomo
3. Generate the report

**1. Download the K-Box usage statistics from each instance**

- For each instance configured generate the usage statistic CSV file by running the [`php artisan statistics` command](https://github.com/k-box/k-box/blob/master/docs/developer/commands/statistics-command.md)
- Download the CSV file in the `raw-data/kbox` folder. Make sure the file name reflects the instance key given during the [configuration](#configuration)


**2. Get analytics from Motomo**

- Get the authentication token of your Matomo instance
  - Login into Matomo
  - Go to a site that represents an instance
  - Go to the visitors section
  - Click the export button below the graph
  - Press show export url. Click on the export URL to view the full text and from it copy the `token_auth` value (we are interested in the string after = and before eventual &)
- From the root of the project execute `php statistics download:analytics --token=<TOKEN> --from=<DATE_FROM> --to=<DATE_TO>`, where `<TOKEN>` is the `token_auth` value previously copied, `<FROM>` and `<TO>` are respectively the start and end date of the report formatted as english dates, e.g. `2019-04-01`

**3. Generate the report**

Execute the `process` command to get a full monthly report: `php statistics process --month=<MONTH> --year=<YEAR>`, where `<MONTH>` is the number of the month to generate the report for, e.g. `4` and `<YEAR>` is the year (year can be omitted and will be the current year)

At the end of the procedure a file named `<YEAR>-<MONTH>.md` will appear under `source/_months` and new json files will be available under `source/static`.

### Build & Preview

The build and preview can be obtained using PHP or Yarn commands. We encourage to read
the [official Jigsaw documentation](https://jigsaw.tighten.co/docs/building-and-previewing/) on the topic before continuing.

In general we suggest to use:

```bash
yarn watch
```

In this way the website will be generated and served on http://localhost:3000/. 
With `watch` the [browsersync](https://jigsaw.tighten.co/docs/compiling-assets/#watching-for-changes) extension is active
so any changes made to the pages will cause a rebuild and an immediate update of the browser.


## Digging deeper


### Docker image

The website can be generated and browsed via a Docker image.

> Docker 1.16 is required


```bash
# Building the image
docker build -t report .

# Running it
docker run --rm -p 8000:80 report
```

The tag `report` is used as example.

### Modify content templates

The content templates reside in the `source` directory. 
To better understand how to edit and modify them please refer to the 
official [Jigsaw documentation](https://jigsaw.tighten.co/docs/content/)


### Modify CSS

The CSS for the website follows the [utility-first approach](https://tailwindcss.com/docs/utility-first). 
Utility classes are generated starting from a configuration file.

The work is performed by [TailwindCSS](https://tailwindcss.com/). 
Please refer to Tailwind extensive documentation for more information.

## License

This project is licensed under AGPL-3.0. See [LICENSE](./LICENSE) for more details.




