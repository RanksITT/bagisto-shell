@props(['chapter' => ''])

{{--
    The chapter is named by an editor, so it is matched against a fixed list rather
    than interpolated into a component name. An unknown name simply draws nothing.
--}}
@switch ($chapter)
    @case ('engine')
        <x-shop::story.engine />

        @break
    @case ('viscosity')
        <x-shop::story.viscosity />

        @break
    @case ('figures')
        <x-shop::story.figures />

        @break
    @case ('protection')
        <x-shop::story.protection />

        @break
@endswitch
