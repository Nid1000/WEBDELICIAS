@extends('layouts.admin', ['title' => 'Nueva categoria'])

@section('content')
    <section class="admin-card max-w-3xl">
        <form action="{{ route('web.admin.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nombre" class="label">Nombre</label>
                <input id="nombre" name="nombre" value="{{ old('nombre') }}" required class="input">
            </div>
            <div>
                <label for="descripcion" class="label">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="5" class="input min-h-36">{{ old('descripcion') }}</textarea>
            </div>
            <div>
                <label for="imagen_url" class="label">Imagen URL</label>
                <input id="imagen_url" name="imagen_url" value="{{ old('imagen_url') }}" class="input" placeholder="https://...">
            </div>
            <div class="flex gap-3">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('web.admin.categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
