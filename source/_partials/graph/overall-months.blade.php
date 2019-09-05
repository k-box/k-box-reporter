[
    @foreach ($months->reverse()->slice(1) as $item)
        {
            "label": "{{ $item->title }}",
            "documents": {{ data_get($item->data, 'overall.documents') }},
            "users": {{ data_get($item->data, 'overall.users') }},
            "publications": {{ data_get($item->data, 'overall.publications') }},
            "visits": {{ data_get($item->data, 'overall.visits') }},
            "searches": {{ data_get($item->data, 'overall.searches') }},
            "downloads": {{ data_get($item->data, 'overall.downloads') }}
        },
    @endforeach
]