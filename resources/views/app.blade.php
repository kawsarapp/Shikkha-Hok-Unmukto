<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'EducationAlwaysFree') }}</title>

        <!-- Google Fonts: Inter & Full Bengali Font Suite (Hind Siliguri, Noto Sans Bengali, Tiro Bangla, Atma, Mina, Galada, Anek Bangla) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;600;700&family=Atma:wght@600;700&family=Galada&family=Hind+Siliguri:wght@300;400;500;600;700&family=Mina:wght@700&family=Noto+Sans+Bengali:wght@400;600;700&family=Tiro+Bangla:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- KaTeX Math Equation & Fraction Support -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
        <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body);"></script>

        <!-- MS Word-like Quill WYSIWYG Rich Text Editor -->
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 min-h-screen">
        @inertia
    </body>
</html>
