@blaze

@php
$classes = Flux::classes()
    ->add('bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6')
    ;
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
