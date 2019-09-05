@extends('_layouts.master')

@section('title')
    {{ $page->title }} - {{ $page->reportTitle }}
    
@endsection



@push('scripts')

<script>
function graph(instance, rawData){

var graphArea = d3.select(".js-graph-" + instance);

var rect = graphArea.node().getBoundingClientRect();

var margin = {top: 10, right: 10, bottom: 80, left: 50},
    width = rect.width - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

// List of series to plot
var series = {!! json_encode($page->measures->values()) !!};

// Reformat the data: we need an array of arrays of {x, y} tuples
var data = series.map( function(seriesName) { 
    return {
        name: seriesName,
        max: d3.max(rawData, function(d) { return +d[seriesName]; }),
        values: rawData.map(function(d) {
            return {label: d.date.replace("{{$page->year}}-{{$page->month < 10 ? '0' . $page->month : $page->month}}-", ""), value: +d[seriesName]};
        })
    };
});

// Define a color scale to map each series
var colors = {!! json_encode($page->colors ) !!};

var labels = rawData.map(function(d){ return d.date.replace("{{$page->year}}-{{$page->month < 10 ? '0' . $page->month : $page->month}}-", ""); });

var x = d3.scalePoint().range([0, width]);
var y = d3.scaleLinear().range([height, 0]);

// Scale the range of the data
x.domain(labels);
// we grab the maximum of all the series to not have a cropped graph
y.domain([0, d3.max(data, function(d) { return d.max; })+10]);

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

var tooltip = graphArea.select(".js-tooltip-" + instance);
var highlightTimeout = null;

// Add the valueline path.

// add the group that will contain each series and append the line in it
var seriesGroup = svg.selectAll("lines")
      .data(data)
      .enter()
        .append('g')
        .attr("class",  function(d) { return "transition js-series-" + instance + " js-series-" + instance+"-"+d.name+" text-" + colors[d.name] + ""; })
        .on("mouseover", function(a, b, c) { 
            d3.selectAll(".js-series-" + instance).classed("opacity-10", true);
            
            d3.select(".js-series-" + instance + "-" + a.name).classed("opacity-10", false).classed("z-10", true);

            if(highlightTimeout){
                clearTimeout(highlightTimeout);
            }
        })
        .on("mouseout", function(a){
            highlightTimeout = setTimeout(function(){
                d3.selectAll(".js-series-" + instance).classed("opacity-10", false).classed("z-10", false);
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
}
</script>
    
@endpush

@section('body')

    <section class="container mx-auto mb-4">

        <h2 class="text-2xl font-bold mb-2 flex justify-between">
            <div>{{ $page->title }}</div>
            <div class="text-sm font-normal">
                @php
                    $previous = $page->getPreviousReport($months);
                    $next = $page->getNextReport($months);
                @endphp

                @if ($previous)
                    <a class="inline-block mr-2 print-hidden" href="{{ $previous->getUrl() }}">&lt; {{ $previous->title }}</a>
                @endif
                <span class="inline-block px-2 py-1 bg-gray-800 text-gray-100 rounded">{{ $page->period_start }} &mdash; {{ $page->period_end }}</span>
                @if ($next)
                    <a class="inline-block ml-2 print-hidden" href="{{ $next->getUrl() }}">{{ $next->title }} &gt;</a>
                @endif
            </div>
        </h2>

        <div class="flex justify-around text-center">
            @foreach (data_get($page->data, "overall") as $measure => $value)
                <div class="px-2 py-3 m-2 border-b-4 border-{{data_get($page->colors, $measure)}} bg-gray-200 w-1/6 text-gray-900">
                    <p class="text-2xl">{{ $value }} </p>
                    <p>{{ $page->getLabelForMeasure($measure) }}</p>
                </div>
            @endforeach
        </div>

    </section>


    <section class="container mx-auto">
        @yield('content')
    </section>

    <section class="container mx-auto ">
        @php
            $instances = data_get($page->data, "instances") ?? [];
        @endphp
        @forelse ($instances as $instance => $file)

            <article class="mb-8 page-break-prevent">

                <h3 class="font-bold text-lg">{{ data_get($page->instances, "$instance.label") }} <span class="ml-4 inline-block text-gray-700 text-base font-normal"><a class="print-link" href="{{ data_get($page->instances, "$instance.url") }}">Visit instance</a></span></h3>

                @php
                    $data = $page->loadInstanceData($file);
                    $performance = $page->getInstancePerformance($data);
                @endphp

                @if ($performance->sum() === 0)
                    <p class="my-4 text-gray-600">No data recorded for this period</p>
                
                @else 
                    <div class="flex">
                        <div class="w-1/4 print-w-full mr-4 mt-8">
                            
                            @foreach ($performance as $measure => $value)
                                <div class="mb-1">
                                    <span class="rounded p-1 inline-block w-10 text-right {{ $value > 0 ? 'font-bold bg-'. $page->colors[$measure] :'bg-gray-100' }}">{{ $value }}</span>
                                    <span> {{ $page->getLabelForMeasure($measure) }} </span>
                                </div>
                            @endforeach
                        </div>
                        <div class="w-3/4 print-w-full relative js-graph-{{$instance}}">

                            <div class="js-tooltip-{{$instance}} absolute bg-gray-900 text-white shadow-lg p-4 transition select-none pointer-events-none opacity-0">

                            </div>
                        
                        </div>

                        @push('scripts')
                            <script>
                                graph("{{$instance}}", {!! json_encode($data) !!});
                            </script>
                        @endpush

                    </div>
                @endif
            </article>
            
        @empty
            
            <p class="p-4 mt-8 border-l-4 border-red-600 bg-red-100">
                <strong class="block">No details available</strong>
                Daily data are not available for this report
            </p>

        @endforelse
    </section>

    

@endsection


