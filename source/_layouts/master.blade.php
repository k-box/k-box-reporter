<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>@yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">
    </head>
    <body>
        
        <header class="print-hidden bg-gray-200 mb-4">
            <div class="container mx-auto py-4">
                
                <h1 class="text-xl font-bold @if($page->client) mb-4 @endif">
                    <a href="/">{{ $page->reportTitle }}</a>
                </h1>
                
                @if($page->client)
                    <p class="mb-2">
                        <span class="block"><strong>Recipient</strong>: {{ $page->client }}</span>
                    </p>
                @endif
            </div>
        </header>


        @yield('body')

        <footer class="mb-8"></footer>

        <script src="https://d3js.org/d3.v5.js"></script>
        
        @stack('scripts')
    </body>
</html>
