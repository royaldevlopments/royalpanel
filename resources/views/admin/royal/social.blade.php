@extends('layouts.royal', ['navbar' => 'social', 'sideEditor' => false])

@section('title')
    Royal Social
@endsection

@php
    $discordBoxOptions = [
        ['img' => '/royal/social/discord.png', 'value' => 'true', 'label' => 'Enable'],
        ['img' => '/royal/social/discordDisabled.png', 'value' => 'false', 'label' => 'Disable'],
    ];
@endphp

@section('content')
    <form action="{{ route('admin.royal.social') }}" method="POST" class="content-box">
        <div class="header">
            <p>Social settings</p>
            <span class="description-text">Change the social settings.</span>
        </div>
        <x-royal.form-wrapper 
            title="Social Media Configuration" 
            description="Configure social media links and settings for the Royal panel."
        >
            <x-royal.switch 
                id="royal:socialButtons"
                name="royal:socialButtons"
                :value="$socialButtons"
                label="Enable Social Media Buttons"
                helpText="Enable or disable social media buttons on the panel." 
            />
            <div id="dropdown-socials" class="{{ $socialButtons === 'true' ? 'open' : '' }}">
                <div id="social-links-container">
                    @php
                        $socialLinks = is_array($socials) ? $socials : [];
                        if (empty($socialLinks)) {
                            $socialLinks = [['link' => '', 'icon' => '', 'title' => '', 'description' => '']];
                        }
                    @endphp
                    
                    @foreach($socialLinks as $index => $social)
                        <div 
                            class="social-item" 
                            data-index="{{ $index }}" 
                            style="border: 1px solid var(--gray500); border-radius: 8px; padding: 15px; margin-bottom: 15px; position: relative;"
                        >
                            <div class="row">
                                <div class="col-md-6 input-field">
                                    <label>Link URL</label>
                                    <input type="url" name="royal:socials[{{ $index }}][link]" value="{{ old('royal:socials.' . $index . '.link', $social['link'] ?? '') }}" placeholder="https://example.com" />
                                </div>
                                <div class="col-md-6 input-field">
                                    <label>Icon Name</label>
                                    <div class="input-field">
                                        <select
                                            name="royal:socials[{{ $index }}][icon]"
                                            value="{{ old('royal:socials.' . $index . '.icon', $social['icon'] ?? '') }}"
                                        >
                                            <option value="">Select an icon</option>
                                            @php
                                                $icons = ['billing', 'status', 'support', 'discord', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];
                                            @endphp
                                            @foreach($icons as $icon)
                                                <option value="{{ $icon }}" @if(old('royal:socials.' . $index . '.icon', $social['icon'] ?? '') == $icon) selected @endif>{{ ucfirst($icon) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-6 input-field">
                                    <label>Title</label>
                                    <input type="text" name="royal:socials[{{ $index }}][title]" value="{{ old('royal:socials.' . $index . '.title', $social['title'] ?? '') }}" placeholder="Platform Name" />
                                </div>
                                <div class="col-md-6 input-field">
                                    <label>Description</label>
                                    <input type="text" name="royal:socials[{{ $index }}][description]" value="{{ old('royal:socials.' . $index . '.description', $social['description'] ?? '') }}" placeholder="Follow us on..." />
                                </div>
                            </div>
                            @if($index > 0)
                                <button 
                                    type="button" 
                                    class="remove-social-btn" 
                                    onclick="removeSocialItem(this)" 
                                    style="position: absolute; top: 5px; right: 5px; background: none; color: #ea4447ff; border: none; cursor: pointer;"
                                >
                                    Remove
                            </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div>
                    <span id="social-limit-text" style="font-size: 0.9em;color: var(--gray300);"></span>
                    <button 
                        type="button" 
                        id="add-social-btn" 
                        onclick="addSocialItem()"
                        class="button button-secondary"
                        style="width:100%;"
                    >
                        Add Social Link
                    </button>
                </div>
            </div>
        </x-royal.form-wrapper>
        <x-royal.form-wrapper 
            title="Discord Box Configuration" 
            description="Configure the Discord box settings for the Royal panel."
        >
            <x-royal.option-picture
                id="royal:discordBox" 
                :value="$discordBox"
                :options="$discordBoxOptions"
            />
        </x-royal.form-wrapper>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>

    <script>
        let socialItemCount = {{ count($socialLinks) }};
        const maxSocialItems = 5;

        function updateSocialLimitText() {
            const limitText = document.getElementById('social-limit-text');
            const addBtn = document.getElementById('add-social-btn');
            
            if (socialItemCount >= maxSocialItems) {
                limitText.textContent = `Maximum ${maxSocialItems} social links allowed`;
                addBtn.disabled = true;
                addBtn.style.opacity = '0.5';
            } else {
                limitText.textContent = `${socialItemCount}/${maxSocialItems} social links`;
                addBtn.disabled = false;
                addBtn.style.opacity = '1';
            }
        }

        function addSocialItem() {
            if (socialItemCount >= maxSocialItems) return;
            
            const container = document.getElementById('social-links-container');
            const newIndex = socialItemCount;
            
            const newItem = document.createElement('div');
            newItem.className = 'social-item';
            newItem.setAttribute('data-index', newIndex);
            newItem.style.cssText = 'border: 1px solid var(--gray500); border-radius: 8px; padding: 15px; margin-bottom: 15px; position: relative;';
            
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-6 input-field">
                        <label>Link URL</label>
                        <input type="url" name="royal:socials[${newIndex}][link]" value="" placeholder="https://example.com" />
                    </div>
                    <div class="col-md-6 input-field">
                        <label>Icon Name</label>
                        <select
                            name="royal:socials[{{ $index }}][icon]"
                            value="{{ old('royal:socials.' . $index . '.icon', $social['icon'] ?? '') }}"
                        >
                            <option value="">Select an icon</option>
                            @php
                                $icons = ['billing', 'status', 'support', 'discord', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];
                            @endphp
                            @foreach($icons as $icon)
                                <option value="{{ $icon }}" @if(old('royal:socials.' . $index . '.icon', $social['icon'] ?? '') == $icon) selected @endif>{{ ucfirst($icon) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-6 input-field">
                        <label>Title</label>
                        <input type="text" name="royal:socials[${newIndex}][title]" value="" placeholder="Platform Name" />
                    </div>
                    <div class="col-md-6 input-field">
                        <label>Description</label>
                        <input type="text" name="royal:socials[${newIndex}][description]" value="" placeholder="Follow us on..." />
                    </div>
                </div>
                <button 
                    type="button" 
                    class="remove-social-btn" 
                    onclick="removeSocialItem(this)" 
                    style="position: absolute; top: 5px; right: 5px; background: none; color: #ea4447ff; border: none; cursor: pointer;"
                >
                    Remove
                </button>
            `;
            
            container.appendChild(newItem);
            socialItemCount++;
            updateSocialLimitText();
        }

        function removeSocialItem(button) {
            const socialItem = button.closest('.social-item');
            socialItem.remove();
            socialItemCount--;
            
            const items = document.querySelectorAll('.social-item');
            items.forEach((item, index) => {
                item.setAttribute('data-index', index);
                const inputs = item.querySelectorAll('input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    const field = name.match(/\[([^\]]+)\]$/)[1];
                    input.setAttribute('name', `royal:socials[${index}][${field}]`);
                });
            });
            
            updateSocialLimitText();
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSocialLimitText();
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const switchElement = document.querySelector('#royal\\:socialButtons');
        const dropdownBackground = document.querySelector('#dropdown-socials');

        const toggleVisibility = () => {
            if (switchElement.checked) {
                dropdownBackground.classList.add('open');
            } else {
                dropdownBackground.classList.remove('open');
            }
        };

        toggleVisibility();

        switchElement.addEventListener('change', toggleVisibility);
    });
    </script>
@endsection