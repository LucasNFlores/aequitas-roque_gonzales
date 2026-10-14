{{-- resources/views/layouts/app.blade.php : layout base AEQUITAS --}}
{{-- Lo usan categorias/* y reportes/* vía @extends('layouts.app') + @section('content'). --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AEQUITAS · @yield('title', 'Sistema de gestión')</title>
  <style>
    body { font-family: sans-serif; margin: 0; color: #222; }
    header.top { background: #1f2a44; color: #fff; padding: 10px 20px; }
    header.top small { color: #c9d2e3; }
    nav.menu { background: #eef1f6; padding: 8px 20px; border-bottom: 1px solid #ccd3e0; }
    nav.menu a { margin-right: 14px; text-decoration: none; color: #1f2a44; }
    main { padding: 16px 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
    th { background: #eef1f6; }
    .alert { padding: 8px 10px; margin: 10px 0; border-radius: 4px; }
    .alert-success { background: #e6ffe6; border: 1px solid #6c6; }
    .alert-warning { background: #fff8e1; border: 1px solid #e6c200; }
    .error { color: #b00; font-size: 0.9em; }
    .btn { display: inline-block; padding: 6px 12px; border: 1px solid #1f2a44; border-radius: 4px; text-decoration: none; color: #1f2a44; margin-right: 6px; }
    footer { margin-top: 24px; padding: 10px 20px; border-top: 1px solid #ccd3e0; font-size: 0.85em; color: #666; }
  </style>
</head>
<body>
<header class="top">
  <strong>AEQUITAS</strong> · Estudio jurídico — Sistema de gestión<br>
  <small>Av. del Parque 1250, Posadas, Misiones · informes@aequitas.example</small>
  @auth
    <span style="float:right"><small>{{ auth()->user()->name }} ({{ auth()->user()->rol }})</small></span>
  @endauth
</header>

<nav class="menu">
  <a href="{{ url('/') }}">Inicio</a>
  {{-- CU37: solo coordinador/administrador ven y usan categorías --}}
  @auth
    @if(in_array(auth()->user()->rol, ['coordinador', 'administrador'], true))
      <a href="{{ route('categorias.index') }}">Categorías</a>
      <a href="{{ route('reportes.documentos.index') }}">Reporte documentos</a>
    @endif
  @endauth
</nav>

<main>
  @yield('content')
</main>

<footer>AEQUITAS · Uso interno · Datos ficticios para clase</footer>
</body>
</html>
