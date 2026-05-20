@extends('layouts.storefront', ['title' => 'Ingresar'])

@section('content')
    <div class="mx-auto mt-8 max-w-md rounded-[2rem] border border-amber-200 bg-white/90 p-8 shadow-xl shadow-amber-100/40">
        <div class="mb-6">
            <p class="text-sm uppercase tracking-[0.25em] text-amber-700">Acceso</p>
            <h2 class="mt-2 text-3xl font-semibold text-stone-900">Iniciar sesion</h2>
            <p class="mt-2 text-sm text-stone-600">Entrar desde Laravel con el mismo estilo simple del frontend actual.</p>
        </div>

        <form action="{{ route('web.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-stone-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-amber-500" placeholder="tu@email.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-stone-700">Contrasena</label>
                <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-amber-500" placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-2xl bg-stone-900 px-4 py-3 font-semibold text-white">Ingresar</button>
        </form>

        <p class="mt-5 text-sm text-stone-600">
            No tienes cuenta?
            <a href="{{ route('web.register') }}" class="font-semibold text-amber-700 underline underline-offset-4">Registrate</a>
        </p>
    </div>
@endsection
