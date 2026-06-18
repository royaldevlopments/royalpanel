@extends('layouts.royal', ['navbar' => 'components', 'sideEditor' => false])

@section('title')
    Royal Layout
@endsection

@php
    $serverRowOptions = [
        ['img' => '/royal/components/ServerRow-1.svg', 'value' => 1, 'label' => 'Enable'],
        ['img' => '/royal/components/ServerRow-2.svg', 'value' => 2, 'label' => 'Disable'],
        ['img' => '/royal/components/ServerRow-3.svg', 'value' => 3, 'label' => 'Disable'],
    ];
    $statCardsOptions = [
        ['img' => '/royal/components/Console.svg', 'value' => 1, 'label' => 'No statistics cards'],
        ['img' => '/royal/components/StatsCards-1.svg', 'value' => 2, 'label' => 'Statistics cards top'],
        ['img' => '/royal/components/StatsCards-2.svg', 'value' => 3, 'label' => 'Statistics cards bottom'],
    ];
    $sideGraphOptions = [
        ['img' => '/royal/components/Console.svg', 'value' => 1, 'label' => 'No vertical graphs'],
        ['img' => '/royal/components/SideGraphs-1.svg', 'value' => 2, 'label' => 'Vertical graphs left'],
        ['img' => '/royal/components/SideGraphs-2.svg', 'value' => 3, 'label' => 'Vertical graphs right'],
    ];
    $graphsOptions = [
        ['img' => '/royal/components/Console.svg', 'value' => 1, 'label' => 'No horizontal graphs'],
        ['img' => '/royal/components/Graphs-1.svg', 'value' => 2, 'label' => 'Horizontal graphs top'],
        ['img' => '/royal/components/Graphs-2.svg', 'value' => 3, 'label' => 'Horizontal graphs bottom'],
    ];
@endphp

@section('content')

    <form action="{{ route('admin.royal.components') }}" method="POST" class="content-box content-box-wide">
        <div class="header">
            <p>Components Settings</p>
            <span class="description-text">Customize the components shown on Royal Theme.</span>
        </div>
        
        <x-royal.form-wrapper 
            title="Dashboard page" 
            description="Customize the dashboard server page easily with a drag and drop"
        >
            <a class="drag-n-drop-banner" href="{{ route('admin.royal.dashboard') }}">
                <h3>Customize dashboard page with drag and drop!</h3>
                <p>Open Dashboard Editor <i data-lucide="arrow-right"></i></p>
            </a>
        </x-royal.form-wrapper>

        <x-royal.form-wrapper 
            title="Server cards" 
            description="Choose a different style for the server cards shown on the homepage"
        >
            <x-royal.option-picture-2
                id="royal:serverRow" 
                :value="$serverRow"
                :options="$serverRowOptions"
            />
        </x-royal.form-wrapper>

        <x-royal.form-wrapper 
            title="Console page" 
            description="Customize what and how stats is shown on the console page"
        >
            <x-royal.option-picture-2
                id="royal:statsCards" 
                :value="$statsCards"
                :options="$statCardsOptions"
            />
            <x-royal.option-picture-2
                id="royal:sideGraphs" 
                :value="$sideGraphs"
                :options="$sideGraphOptions"
            />
            <x-royal.option-picture-2
                id="royal:graphs" 
                :value="$graphs"
                :options="$graphsOptions"
            />
        </x-royal.form-wrapper>

        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection