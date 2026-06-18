@extends('layouts.royal', ['navbar' => 'index', 'sideEditor' => true])

@section('title')
    Royal Theme
@endsection

@section('content')
    <form action="{{ route('admin.royal') }}" method="POST">
        <div class="header">
            <p>General Settings</p>
            <span class="description-text">Change the general settings of Royal Theme.</span>
        </div>
        <x-royal.input-field 
            id="royal:logo" 
            :value="$logo" 
            label="Panel logo (Dark mode)"
        />
        <x-royal.input-field
            id="royal:logoLight" 
            :value="$logoLight" 
            label="Panel logo (Light mode)"
        />
        <x-royal.switch 
            hr="true"
            id="royal:fullLogo"
            name="royal:fullLogo"
            :value="$fullLogo"
            label="Logo only"
            helpText="Enable or disable the text next to the panel logo." 
        />
        <div style="position:relative;">
            <x-royal.input-field 
                hr="true"
                id="royal:logoHeight" 
                :value="$logoHeight" 
                label="Panel logo height"
            />
            <div style="position:absolute;bottom:42px;right:16px">
                px
            </div>
        </div>
        <div>
            <p class="subtitle">Support links</p>
            <x-royal.callout
                message="Leave empty remove the a specific support link from your panel."
            />
        </div>
        <x-royal.input-field 
            hr="true"
            id="royal:discord" 
            :value="$discord" 
            label="Discord ID"
        />
        <x-royal.input-field
            id="royal:support" 
            :value="$support" 
            label="Supportcenter"
        />
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection