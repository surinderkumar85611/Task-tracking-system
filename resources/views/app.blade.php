<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('uploads/favicon-1.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('uploads/favicon-1.png') }}">
    <link rel="shortcut icon" href="{{ asset('uploads/favicon-1.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('uploads/favicon-1.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>