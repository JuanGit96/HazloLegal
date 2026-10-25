@props(['faqs' => [], 'open' => 0])

@php
    $items = collect($faqs)->map(function ($faq) {
        if (is_array($faq)) {
            return ['question' => $faq['question'] ?? '', 'answer' => $faq['answer'] ?? ''];
        }

        return ['question' => $faq->question, 'answer' => $faq->answer];
    })->filter(fn ($faq) => filled($faq['question']))->values();
@endphp

@if ($items->isNotEmpty())
    <div class="faq">
        @foreach ($items as $i => $faq)
            <details class="faq__item" @if ($i < $open) open @endif>
                <summary class="faq__q">{{ $faq['question'] }}</summary>
                <div class="faq__a">{!! \Illuminate\Support\Str::markdown($faq['answer']) !!}</div>
            </details>
        @endforeach
    </div>
@endif
