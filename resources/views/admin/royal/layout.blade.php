@extends('layouts.royal', ['navbar' => 'layout', 'sideEditor' => true])

@section('title')
    Royal Layout
@endsection

@section('content')

    <form action="{{ route('admin.royal.layout') }}" method="POST">
        <div class="header">
            <p>General layout settings</p>
            <span class="description-text">Change the general layout settings of Royal Theme.</span>
        </div>
        <div>
            <p class="subtitle">General Layout</p>
            <div class="layout-grid">
                <x-royal.layout-option 
                    id="royal:layout:1" 
                    name="royal:layout"
                    value="1"
                    :oldValue="$layout" 
                    label="Sidebar"
                    img="/royal/layout/layout-1.svg"
                />
                <x-royal.layout-option 
                    id="royal:layout:2" 
                    name="royal:layout"
                    value="2"
                    :oldValue="$layout" 
                    label="Sidebar Power Actions"
                    img="/royal/layout/layout-2.svg"
                />
                <x-royal.layout-option 
                    id="royal:layout:3" 
                    name="royal:layout"
                    value="3"
                    :oldValue="$layout" 
                    label="Top Navigation"
                    img="/royal/layout/layout-3.svg"
                />
                <x-royal.layout-option 
                    id="royal:layout:4" 
                    name="royal:layout"
                    value="4"
                    :oldValue="$layout" 
                    label="Slim Sidebar"
                    img="/royal/layout/layout-4.svg"
                />
                <x-royal.layout-option 
                    id="royal:layout:5" 
                    name="royal:layout"
                    value="5"
                    :oldValue="$layout" 
                    label="Sidebar Filled Hover"
                    img="/royal/layout/layout-5.svg"
                />
            </div>
        </div>

        <div class="header">
            <p>Login layout settings</p>
            <span class="description-text">Change the layout settings of the auth pages of Royal Theme.</span>
        </div>
        <div>
            <p class="subtitle">Login layout</p>
            <div class="layout-grid">
                <x-royal.layout-option 
                    id="royal:loginLayout:1" 
                    name="royal:loginLayout"
                    value="1"
                    :oldValue="$loginLayout" 
                    label="Default"
                    img="/royal/layout/loginLayout-1.svg"
                />
                <x-royal.layout-option 
                    id="royal:loginLayout:2" 
                    name="royal:loginLayout"
                    value="2"
                    :oldValue="$loginLayout" 
                    label="Side Banner"
                    img="/royal/layout/loginLayout-2.svg"
                />
                <x-royal.layout-option 
                    id="royal:loginLayout:3" 
                    name="royal:loginLayout"
                    value="3"
                    :oldValue="$loginLayout" 
                    label="Floating Image"
                    img="/royal/layout/loginLayout-3.svg"
                />
                <x-royal.layout-option 
                    id="royal:loginLayout:4" 
                    name="royal:loginLayout"
                    value="4"
                    :oldValue="$loginLayout" 
                    label="Flat"
                    img="/royal/layout/loginLayout-4.svg"
                />
            </div>
        </div>

        <x-royal.switch
            id="royal:loginGradient"
            name="royal:loginGradient"
            :value="$loginGradient"
            label="Background gradient overlay"
        />
        <hr />
        <p class="subtitle">Hero Content</p>
        <x-royal.input-field
            id="royal:heroBadge"
            :value="$heroBadge"
            label="Hero badge text"
        />
        <x-royal.input-field
            id="royal:heroTitle"
            :value="$heroTitle"
            label="Hero title"
        />
        <x-royal.input-field
            id="royal:heroTagline"
            :value="$heroTagline"
            label="Hero tagline"
        />
        <div class="input-field">
            <label>Social position</label>
            <select name="royal:socialPosition">
                <option value="1" @if(old('royal:socialPosition', $socialPosition) == 1) selected @endif>Above form</option>
                <option value="2" @if(old('royal:socialPosition', $socialPosition) == 2) selected @endif>Under form</option>
            </select>
            <small>Where do you want the social buttons on the login screen.</small>
        </div>
        <div class="input-field">
            <label>Logo Position</label>
            <select name="royal:logoPosition">
                <option value="1" @if(old('royal:logoPosition', $logoPosition) == 1) selected @endif>Above form</option>
                <option value="2" @if(old('royal:logoPosition', $logoPosition) == 2) selected @endif>Top corner</option>
            </select>
            <small>Where do you want the logo on the login screen.</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection