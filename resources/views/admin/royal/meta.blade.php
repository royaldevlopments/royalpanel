@extends('layouts.royal', ['navbar' => 'meta', 'sideEditor' => true])

@section('title')
    Royal Meta data
@endsection

@section('content')
    <form action="{{ route('admin.royal.meta') }}" method="POST">
        <div class="header">
            <p>Meta Data settings</p>
            <span class="description-text">Change the meta data settings of Royal Theme.</span>
        </div>
        <x-royal.input-field 
            id="royal:meta_favicon" 
            :value="$meta_favicon" 
            label="Favicon"
        />
        <x-royal.input-field
            id="royal:meta_title" 
            :value="$meta_title" 
            label="Meta title"
        />
        <x-royal.input-field
            id="royal:meta_image" 
            :value="$meta_image" 
            label="Meta image"
        />
        <x-royal.textarea-field
            id="royal:meta_description" 
            :value="$meta_description" 
            label="Meta description"
        />
        <div class="input-field">
            <label for="royal:meta_color">Meta color</label>
            <x-royal.color-input
                target="meta_color"
                id="royal:meta_color" 
                :value="$meta_color"
            />
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>

    <!-- <form action="{{ route('admin.royal.meta') }}" method="POST">
        <div class="input-field hr">
            <label for="royal:meta_favicon">Favicon</label>
            <input type="text" id="royal:meta_favicon" name="royal:meta_favicon" value="{{ old('royal:meta_favicon', $meta_favicon) }}" />
        </div>
        <div class="input-field hr">
            <label for="royal:meta_title">Meta title</label>
            <input type="text" id="royal:meta_title" name="royal:meta_title" value="{{ old('royal:meta_title', $meta_title) }}" />
        </div>
        <div class="input-field hr">
            <label for="royal:meta_image">Meta image</label>
            <input type="text" id="royal:meta_image" name="royal:meta_image" value="{{ old('royal:meta_image', $meta_image) }}" />
        </div>
        <div class="input-field">
            <label for="royal:meta_description">Meta description</label>
            <textarea type="text" id="royal:meta_description" name="royal:meta_description" width="100%" rows="5">{{ old('royal:meta_description', $meta_description) }}</textarea>
        </div>
        <div class="input-field hr">
            <label for="royal:meta_color">Meta color</label>
            <input type="color" id="royal:meta_color" name="royal:meta_color" value="{{ old('royal:meta_color', $meta_color) }}" />
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form> -->
@endsection