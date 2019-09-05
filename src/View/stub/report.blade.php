---
extends: _layouts.report
title: {{ $title }}
date: "{{ $date }}"
year: {{ $year }}
month: {{ $month }}
period_start: "{{ $from }}"
period_end: "{{ $to }}"
section: content
data:
 overall:
@foreach ($overall as $key => $item)
  {{ $key }}: {{ $item }}
@endforeach
 instances:
@foreach ($instances as $name => $file)
  {{ $name }}: "{{ $file }}"
@endforeach
---
