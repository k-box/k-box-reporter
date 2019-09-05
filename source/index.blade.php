@extends('_layouts.master')

@section('title')
    {{ $page->reportTitle }}
@endsection

@section('body')

<section class="container mx-auto flex ">

    <div class="lg:w-1/2 lg:pr-8 print-w-1/2 print-pr-8">
    
        <h2 class="text-lg font-bold mb-2">Introduction</h2>
    
        <p class="mb-5 max-w-75-char">
            This report provides some basic information about the use of the K-Boxes.
            The below presented data are generated from two different sources, directly collected from (hosted) K-Boxes, and from a self hosted browser
            Analytics tool (called <a href="https://matomo.org/" class="print-link" target="_blank" rel="noopener noreferrer">Matomo</a>) that is tracking some user actions (it respect the privacy, data is anonymized, eventual active blockers
            on the browser will affect the data acquisition). All data are collected at the K-Boxes level. Statistics on the K-Link level are thus derived from K-Box’s indicators (e.g., number of published documents
            are derived from the corresponding publishing sources at the K-Box’s level). 
        </p>
    </div>

    
    <div class="lg:w-1/2 print-w-1/2">
        <h2 class="text-lg font-bold mb-2">Collected data</h2>
        <p class="">All data are aggregated based on the day of the action<sup><a href="#note-1">1</a></sup></p>
    
        <h4 class="mt-2 font-bold">Documents</h4>
        <span>number of newly added documents (including file versions)</span>
        
        <h4 class="mt-2 font-bold">Users</h4>
        <span>number of newly registered users</span>
        
        <h4 class="mt-2 font-bold">Use of the system</h4>
    
        <ul class="mb-2">
            <li>number of visits<sup><a href="#note-2">2</a></sup></li>
            <li>number of downloads<sup><a href="#note-2">2</a></sup></li>
            <li>number of public links created</li>
            <li>number of searches<sup><a href="#note-2">2</a></sup></li>
        </ul>
        
        <h4 class="mt-2 font-bold">K-Link Network</h4>
        <span>number of published documents<sup><a href="#note-3">3</a></sup></span>
    </div>

</section>

<section class="mt-8 page-break">

    <div class="container m-auto">
        <h2 class="text-lg font-bold mb-2">Overall performance</h2>
    </div>

    <div class="px-32 my-8 js-graph relative print-px-0" >
        

        <div class="js-tooltip absolute bg-gray-900 text-white shadow-lg p-4 transition select-none pointer-events-none opacity-0">

        </div>
    </div>

    <div class="flex justify-between sticky text-gray-700 mb-8">
        <div class="w-1/6 flex-shrink-0">&nbsp;</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['documents'] }}"></span> {{ $page->getLabelForMeasure('documents') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['users'] }}"></span> {{ $page->getLabelForMeasure('users') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['publications'] }}"></span> {{ $page->getLabelForMeasure('publications') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['visits'] }}"></span> {{ $page->getLabelForMeasure('visits') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['searches'] }}"></span> {{ $page->getLabelForMeasure('searches') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['downloads'] }}"></span> {{ $page->getLabelForMeasure('downloads') }}</div>
    </div>

    <div class="page-break"></div>

    @include('_partials.months.list')


</section>


<section class="mt-16 container m-auto print-pt-8">

    <ul class="text-sm">
        <li><span id="note-1">[1]</span> Unless specified all data is collected at K-Box level</li>
        <li><span id="note-2">[2]</span> Data collected using the Analytics Platform. The user could opt-out from data collection as per EU 679/2016 (GDPR).</li>
        <li><span id="note-3">[3]</span> Only published documents are taken into consideration. Document removal are not considered</li>
    </ul>

</section>

@endsection


@push('scripts')

<script>
var rawData = @include('_partials.graph.overall-months');

var graphArea = d3.select(".js-graph");

var rect = graphArea.node().getBoundingClientRect();

var margin = {top: 20, right: 100, bottom: 30, left: 100},
    width = rect.width - (8*2*16) - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

// List of series to plot
var series = {!! json_encode($page->measures->flip()->except('public_links')->keys()) !!};

// Reformat the data: we need an array of arrays of {x, y} tuples
var data = series.map( function(seriesName) { 
    return {
        name: seriesName,
        max: d3.max(rawData, function(d) { return d[seriesName]; }),
        values: rawData.map(function(d) {
            return {label: d.label, value: +d[seriesName]};
        })
    };
});

// Define a color scale to map each series
var colors = {!! json_encode($page->colors ) !!};

// set the ranges
var labels = rawData.map(function(d){ return d.label; });

var x = d3.scalePoint().range([0, width]);
var y = d3.scaleLinear().range([height, 0]);

// Scale the range of the data
x.domain(labels);
// we grab the maximum of all the series to not have a cropped graph
y.domain([0, d3.max(data, function(d) { return d.max; })]);

// define the line
var valueline = d3.line()
    .x(function(d, i) { return x(d.label); })
    .y(function(d) { return y(d.value); })
    .curve(d3.curveMonotoneX);

// append the svg obgect to the page
// appends a 'group' element to 'svg'
// moves the 'group' element to the top left margin
var svg = graphArea.append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform",
          "translate(" + margin.left + "," + margin.top + ")");

var tooltip = graphArea.select(".js-tooltip");
var highlightTimeout = null;

// Add the valueline path.

// add the group that will contain each series and append the line in it
var seriesGroup = svg.selectAll("lines")
      .data(data)
      .enter()
        .append('g')
        .attr("class",  function(d) { return "transition js-series js-series-"+d.name+" text-" + colors[d.name] + ""; })
        .on("mouseover", function(a, b, c) { 
            d3.selectAll(".js-series").classed("opacity-25", true);
            
            d3.select(".js-series-" + a.name).classed("opacity-25", false).classed("z-10", true);

            if(highlightTimeout){
                clearTimeout(highlightTimeout);
            }
        })
        .on("mouseout", function(a){
            highlightTimeout = setTimeout(function(){
                d3.selectAll(".js-series").classed("opacity-25", false).classed("z-10", false);
            }, 1000);
        });

// add the line
seriesGroup.append("path")
    .attr("d", function(d){ return valueline(d.values) } )
    .attr("class", "line stroke-current transition")
    .attr("fill", "none")
    .attr("stroke-width", 4);

// add the dots
seriesGroup.append('g')
    .attr("class",  "dot fill-current transition")
    .selectAll("dot")
    .data(function(d){ return d.values })
    .enter()
    .append("circle")
    .attr("cx", function(d) { return x(d.label) } )
    .attr("cy", function(d) { return y(d.value) } )
    .attr("r", 5)
    .attr("stroke-width", 2)
    .attr("stroke", "white")
    .on("mouseover", function(d, b, c) { 

        var mousePos = d3.mouse(this);
        
        tooltip
            .html("<strong>"+d.label+"</strong>: " + d.value)
            .style("left", (mousePos[0] + margin.left) + "px")
            .style("top", (mousePos[1]-20) + "px")
            .style("opacity", 1)
        
    })
    .on("mouseleave", function(d){
        tooltip.style("opacity", 0)
    });

// Add the X Axis
svg.append("g")
    .attr("transform", "translate(0," + height + ")")
    .call(d3.axisBottom(x));

// Add the Y Axis
svg.append("g")
    .call(d3.axisLeft(y));


</script>
    
@endpush