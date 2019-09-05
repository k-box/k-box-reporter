
<div class="container m-auto flex flex-col page-break-prevent print-pt-8">

    <div class="flex justify-between sticky text-gray-700 mb-1">
        <div class="w-1/6 flex-shrink-0">&nbsp;</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['documents'] }}"></span> {{ $page->getLabelForMeasure('documents') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['users'] }}"></span> {{ $page->getLabelForMeasure('users') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['publications'] }}"></span> {{ $page->getLabelForMeasure('publications') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['visits'] }}"></span> {{ $page->getLabelForMeasure('visits') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['searches'] }}"></span> {{ $page->getLabelForMeasure('searches') }}</div>
        <div class="w-1/6"><span class="inline-block w-3 h-3 rounded-full bg-{{ $page->colors['downloads'] }}"></span> {{ $page->getLabelForMeasure('downloads') }}</div>
    </div>

    <div class="flex justify-between p-2 mb-1 {{ $loop->odd ? 'bg-gray-200' : '' }} hover:bg-blue-200">
        <div class="w-1/6 flex-shrink-0">
            Total
        </div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.documents');})->values()->sum() }}</div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.users');})->values()->sum() }}</div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.publications');})->values()->sum() }}</div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.visits');})->values()->sum() }}</div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.searches');})->values()->sum() }}</div>
        <div class="w-1/6">{{ collect($months)->map(function($v){ return data_get($v->data, 'overall.downloads');})->values()->sum() }}</div>
    </div>
    <div class="flex justify-between p-2 mb-1 {{ $loop->odd ? 'bg-gray-200' : '' }} hover:bg-blue-200">
        <div class="w-1/6 flex-shrink-0">
            Average (since February 2018)
        </div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.documents');})->values()->sum() / ($months->count()-1), 2) }}</div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.users');})->values()->sum() / ($months->count()-1), 2) }}</div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.publications');})->values()->sum() / ($months->count()-1), 2) }}</div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.visits');})->values()->sum() / ($months->count()-1), 2) }}</div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.searches');})->values()->sum() / ($months->count()-1), 2) }}</div>
        <div class="w-1/6">{{ round(collect($months)->map(function($v){ return data_get($v->data, 'overall.downloads');})->values()->sum() / ($months->count()-1), 2) }}</div>
    </div>

    @foreach ($months as $item)

        <div class="flex justify-between p-2 mb-1 {{ $loop->odd ? 'bg-gray-200' : '' }} hover:bg-blue-200">
            <div class="w-1/6 flex-shrink-0">
                <a href="{{ $item->getUrl() }}">
                    {{ $item->title }}
                </a>
            </div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.documents') }}</div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.users') }}</div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.publications') }}</div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.visits') }}</div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.searches') }}</div>
            <div class="w-1/6">{{ data_get($item->data, 'overall.downloads') }}</div>
        </div>
        
    @endforeach

    

</div>

